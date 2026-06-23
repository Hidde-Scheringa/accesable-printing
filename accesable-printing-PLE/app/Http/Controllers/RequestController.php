<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as PrintRequest;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

/**
 * RequestController handles the upload of custom 3D files and initiates
 * the payment process through Stripe.
 */
class RequestController extends Controller
{
    /**
     * Show the request form.
     * * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('requests.create');
    }

    /**
     * Store a new custom 3D print request and create a Stripe checkout session.
     * * @param HttpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(HttpRequest $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'material'     => 'required|string',
            'color'        => 'required|string',
            'city'         => 'required|string',
            'street'       => 'required|string',
            'streetnumber' => 'required|string',
            'zipcode'      => 'required|string',
            'stl_files'    => 'required|array',
            'stl_files.*'  => 'file|max:150000',
            'scales'       => 'required|array',
            'quantities'   => 'required|array',
            'prices'       => 'required|array',
            'heights'      => 'required|array',
            'widths'       => 'required|array',
            'depths'       => 'required|array',
            'total_price_hidden' => 'required|numeric',
        ]);

        $fileData = [];

        // 2. Process file uploads
        if ($request->hasFile('stl_files')) {
            foreach ($request->file('stl_files') as $index => $file) {
                $path = $file->store('blueprints', 'public');

                $fileData[] = [
                    'title'         => $file->getClientOriginalName(),
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'scale'         => $request->scales[$index] ?? 100,
                    'quantity'      => $request->quantities[$index] ?? 1,
                    'price'         => $request->prices[$index] ?? 0,
                    'h'             => $request->heights[$index] ?? '?',
                    'b'             => $request->widths[$index] ?? '?',
                    'd'             => $request->depths[$index] ?? '?',
                ];
            }
        }

        try {
            // 3. Save request to database
            $order = Auth::user()->requests()->create([
                'title'          => $validated['title'],
                'description'    => $request->description ?? '3D Print Project via upload',
                'material'       => $validated['material'],
                'color'          => $validated['color'],
                'total_price'    => $validated['total_price_hidden'],
                'city'           => $validated['city'],
                'street'         => $validated['street'],
                'streetnumber'   => $validated['streetnumber'],
                'zipcode'        => $validated['zipcode'],
                'stl_files'      => $fileData,
                'status'         => 'pending',
                'payment_status' => 'unpaid'
            ]);

            // 4. Generate Stripe Checkout session
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::create([
                'payment_method_types' => ['ideal', 'card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => "3D Print: " . $order->title,
                            'description' => "Order #" . $order->id . " (Production time up to 2 weeks)",
                        ],
                        'unit_amount' => (int)($order->total_price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $order->id
                ],
                'customer_email' => Auth::user()->email,
                'success_url' => route('payment.success', $order->id),
                'cancel_url' => route('payment.cancel', $order->id),
            ]);

            // 5. Update order with Stripe Session ID
            $order->update([
                'stripe_checkout_id' => $session->id,
                'payment_status'     => 'pending'
            ]);

            return response()->json([
                'success'    => true,
                'stripe_url' => $session->url
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment request: ' . $e->getMessage()
            ], 500);
        }
    }
}
