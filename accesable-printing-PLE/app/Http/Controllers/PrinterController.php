<?php

namespace App\Http\Controllers;

use App\Models\Request as PrintRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * PrinterController manages the internal dashboard, production status updates,
 * and dispute handling for the service provider.
 */
class PrinterController extends Controller
{
    /**
     * Display the printer dashboard.
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        // 1. All requests for statistics (including pending)
        $statsRequests = PrintRequest::all();

        // 2. Filtered requests for the table (excluding 'pending' orders)
        $allRequests = PrintRequest::with('user')
            ->where('payment_status', '!=', 'pending')
            ->latest()
            ->get();

        return view('printer.dashboard', compact('allRequests', 'statsRequests'));
    }

    /**
     * Update production status from the Admin Dashboard.
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:printing,shipped'
        ]);

        $order = PrintRequest::findOrFail($id);

        // Update status to 'printing' or 'shipped'
        $order->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Production status successfully updated to ' . strtoupper($request->status) . '.');
    }

    /**
     * Customer cancels the order (Only allowed if status is 'pending' or 'in_production').
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customerCancel($id)
    {
        $order = PrintRequest::findOrFail($id);

        // Backend validation: Check current status
        $currentStatus = strtolower($order->status ?? 'pending');
        $allowedStatuses = ['pending', 'in_production'];

        if (!in_array($currentStatus, $allowedStatuses)) {
            return redirect()->back()->with('error', 'Cancellation is no longer possible as production has started or the order has shipped.');
        }

        // Update status to cancelled
        $order->update([
            'payment_status' => 'cancelled',
            'status' => 'cancelled'
        ]);

        return redirect()->back()->with('success', 'Your print request has been successfully cancelled.');
    }

    /**
     * Customer reports a defect / requests a refund (Only valid if status is 'shipped').
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function customerDispute(Request $request, $id)
    {
        $request->validate([
            'defect_reason' => 'required|string|max:1000',
            'defect_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
        ]);

        $order = PrintRequest::findOrFail($id);

        // Store image in public storage 'defects' folder
        if ($request->hasFile('defect_image')) {
            $path = $request->file('defect_image')->store('defects', 'public');

            // Save reason and file path to database
            $order->update([
                'payment_status' => 'disputed',
                'defect_reason' => $request->defect_reason,
                'defect_image_path' => $path
            ]);

            return redirect()->back()->with('success', 'Your refund request and photo evidence have been submitted. We will contact you shortly.');
        }

        return redirect()->back()->with('error', 'Something went wrong while uploading the photo.');
    }

    /**
     * Cancel a specific part within an order.
     * * @param Request $request
     * @param int $orderId
     * @param int $fileIndex
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelPrintablePart(Request $request, $orderId, $fileIndex)
    {
        try {
            $order = PrintRequest::findOrFail($orderId);
            $files = $order->stl_files;

            if (!isset($files[$fileIndex])) {
                return redirect()->back()->with('error', 'Part not found.');
            }

            // 1. Prepare refund calculation
            $itemToCancel = $files[$fileIndex];
            $isLastItem = (count($files) === 1);

            $refundAmount = $isLastItem ? $order->total_price : $itemToCancel['price'];

            // 2. Execute Stripe Refund
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_checkout_id);
            $paymentIntentId = $session->payment_intent;

            $refundData = ['payment_intent' => $paymentIntentId];

            // Only specify amount if it's a partial refund
            if (!$isLastItem) {
                $refundData['amount'] = (int)($refundAmount * 100);
            }

            \Stripe\Refund::create($refundData);

            // 3. Update Database
            unset($files[$fileIndex]);
            $newFiles = array_values($files);

            $details = "Item partially/fully unprintable and cancelled. Cancelled part: " . ($itemToCancel['title'] ?? 'Model') .
                " | Refunded: € " . number_format($refundAmount, 2, ',', '.');

            $updateData = [
                'stl_files' => $newFiles,
                'total_price' => $order->total_price - $refundAmount,
                'cancellation_details' => $order->cancellation_details ? $order->cancellation_details . "\n" . $details : $details
            ];

            // Update status if last item is cancelled
            if ($isLastItem) {
                $updateData['status'] = 'cancelled';
                $updateData['payment_status'] = 'refunded';
                $updateData['total_price'] = 0;
            }

            $order->update($updateData);

            return redirect()->back()->with('success', 'Part cancelled. ' . ($isLastItem ? 'Full refund processed.' : 'Partial refund processed.'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}
