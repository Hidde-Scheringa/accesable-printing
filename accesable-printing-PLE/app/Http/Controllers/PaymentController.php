<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Request as PrintRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * PaymentController manages the entire payment lifecycle and escrow logic.
 * The system follows this flow: Pending -> Escrow (paid) -> Paid (approved/released).
 */
class PaymentController extends Controller
{
    /**
     * Handle the redirect after a successful Stripe checkout.
     * Updates the status to 'escrow' to secure the order funds.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentSuccess($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        // Once the customer returns, we set the order to escrow.
        if ($order->payment_status === 'pending') {
            $order->update(['payment_status' => 'escrow']);
        }

        return redirect()->route('dashboard')->with('success', 'Thank you! Your payment was successful and is now held securely in escrow.');
    }

    /**
     * Handle payment session cancellation by the customer.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentCancel($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        return redirect()->route('dashboard')->with('error', 'The payment has been cancelled.');
    }

    /**
     * Customer approves the delivery. Funds are released to the printer.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveDelivery($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        // Only possible if funds are currently held in escrow.
        if ($order->payment_status !== 'escrow') {
            return back()->with('error', 'This action is not available at this time.');
        }

        $order->update(['payment_status' => 'paid']);
        return back()->with('success', 'Thank you! The funds have been released to the printer.');
    }

    /**
     * Admin approves a dispute claim and performs an automatic refund.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminApproveDispute($id)
    {
        $order = PrintRequest::findOrFail($id);

        if ($order->payment_status !== 'disputed') {
            return back()->with('error', 'This order has no open dispute claim.');
        }

        if ($order->suggested_refund <= 0) {
            return back()->with('error', 'No valid refund amount found.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_checkout_id);

            \Stripe\Refund::create([
                'payment_intent' => $session->payment_intent,
                'amount'         => (int)($order->suggested_refund * 100),
                'reason'         => 'requested_by_customer',
            ]);

            // Update status to 'refunded'
            $order->update([
                'payment_status' => 'refunded',
                'suggested_refund' => 0
            ]);

            return back()->with('success', 'Claim approved and funds have been refunded.');

        } catch (\Exception $e) {
            return back()->with('error', 'Stripe Error: ' . $e->getMessage());
        }
    }

    /**
     * Admin rejects a dispute claim; funds remain with the printer (status: paid).
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminRejectDispute($id)
    {
        $order = PrintRequest::findOrFail($id);

        // Revert to 'paid' because the claim was rejected and the printer keeps the funds
        $order->update([
            'payment_status' => 'paid',
        ]);

        return back()->with('success', 'Claim rejected: Payment remains with the printer.');
    }

    /**
     * Allows the customer to cancel an order. Refunds if currently in escrow.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customerCancel($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        $currentStatus = strtolower($order->status ?? 'pending');
        $allowedStatuses = ['pending', 'in_production'];

        if (!in_array($currentStatus, $allowedStatuses) || in_array($order->payment_status, ['paid', 'cancelled', 'disputed'])) {
            return redirect()->back()->with('error', 'Cancellation is no longer possible.');
        }

        // If paid, refund via Stripe.
        if ($order->payment_status === 'escrow' && !empty($order->stripe_checkout_id)) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::retrieve($order->stripe_checkout_id);
                if (!empty($session->payment_intent)) {
                    \Stripe\Refund::create(['payment_intent' => $session->payment_intent, 'reason' => 'requested_by_customer']);
                }
            } catch (\Exception $e) {
                Log::error('Refund Failed: ' . $e->getMessage());
            }
        }

        $order->update(['payment_status' => 'cancelled', 'status' => 'cancelled']);
        return redirect()->back()->with('success', 'Your print request has been cancelled and refunded.');
    }

    /**
     * Stripe Webhook listener.
     * Updates order status as soon as payment is confirmed.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        if (!$request->isMethod('post')) {
            return response('Unauthorized', 403);
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, config('services.stripe.webhook_secret'));

            // When payment is complete, set status to 'escrow'
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $order = PrintRequest::find($session->metadata->order_id);

                if ($order) {
                    $order->update([
                        'payment_status' => 'escrow',
                        'stripe_checkout_id' => $session->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook Invalid'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Submit a dispute claim for a specific order.
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customerDispute(Request $request, $id)
    {
        $order = PrintRequest::findOrFail($id);
        $files = is_array($order->stl_files) ? $order->stl_files : json_decode($order->stl_files, true);

        // 1. Initialize refund variable
        $suggestedRefund = 0;

        // 2. Calculation logic
        foreach ($request->items as $index => $itemName) {
            $defectQty = (int)($request->qtys[$index] ?? 0);
            $item = collect($files)->firstWhere('title', $itemName);

            if ($item && $defectQty > 0) {
                $batchPrice = (float)($item['price'] ?? 0);
                $batchQuantity = (int)($item['quantity'] ?? 1);
                $pricePerPiece = $batchPrice / $batchQuantity;

                $suggestedRefund += ($defectQty * $pricePerPiece);
            }
        }

        // 3. Rounding and saving
        $suggestedRefund = round($suggestedRefund, 2);

        $paths = [];
        if ($request->hasFile('defect_images')) {
            foreach ($request->file('defect_images') as $file) {
                $paths[] = $file->store('defects', 'public');
            }
        }

        $order->update([
            'payment_status'    => 'disputed',
            'suggested_refund'  => $suggestedRefund,
            'defect_reason'     => $request->defect_reason,
            'defect_image_path' => $paths
        ]);

        return redirect()->back()->with('success', 'Claim submitted for €' . number_format($suggestedRefund, 2));
    }

    /**
     * Resume a pending payment session.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resumePayment($id)
    {
        $order = \App\Models\Request::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        if ($order->payment_status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'This order can no longer be paid.');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['ideal', 'card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => "Print: " . $order->title],
                    'unit_amount' => (int)($order->total_price * 100)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => ['order_id' => $order->id],
            'success_url' => route('payment.success', $order->id),
            'cancel_url'  => route('payment.cancel', $order->id),
        ]);

        $order->update(['stripe_checkout_id' => $session->id]);

        return redirect($session->url);
    }
}
