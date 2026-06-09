<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Request as PrintRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * PaymentController beheert de gehele betaalcyclus en escrow-logica.
 * Het systeem volgt een flow: Pending -> Escrow (betaald) -> Paid (goedgekeurd).
 */
class PaymentController extends Controller
{
    /**
     * Handelt de redirect af na een succesvolle Stripe checkout.
     * Update de status naar 'escrow' om de order te beveiligen.
     */
    public function paymentSuccess($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        // Zodra de klant terugkeert, zetten we de order in escrow.
        if ($order->payment_status === 'pending') {
            $order->update(['payment_status' => 'escrow']);
        }

        return redirect()->route('dashboard')->with('success', 'Bedankt! Je betaling is succesvol verwerkt en staat veilig in escrow.');
    }

    /**
     * Verwerkt de annulering van een betaalsessie door de klant.
     */
    public function paymentCancel($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        return redirect()->route('dashboard')->with('error', 'De betaling is geannuleerd.');
    }

    /**
     * Klant keurt de zending goed. Geld wordt vrijgegeven aan de printer.
     */
    public function approveDelivery($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        // Alleen mogelijk als geld nog in escrow staat.
        if ($order->payment_status !== 'escrow') {
            return back()->with('error', 'Deze actie is op dit moment niet mogelijk.');
        }

        $order->update(['payment_status' => 'paid']);
        return back()->with('success', 'Bedankt! Het geld is vrijgegeven aan de printer.');
    }

    /**
     * Admin keurt een schadeclaim goed en voert een automatische refund uit.
     */
    public function adminApproveDispute($id)
    {
        $order = PrintRequest::findOrFail($id);

        if ($order->payment_status !== 'disputed') {
            return back()->with('error', 'Deze order heeft geen openstaande schadeclaim.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_checkout_id);

            // Stripe refund uitvoeren
            \Stripe\Refund::create([
                'payment_intent' => $session->payment_intent,
                'reason'         => 'requested_by_customer',
            ]);

            $order->update(['payment_status' => 'cancelled', 'status' => 'cancelled']);
            return back()->with('success', 'Claim goedgekeurd: Geld is automatisch teruggestort.');
        } catch (\Exception $e) {
            return back()->with('error', 'Fout bij Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Admin wijst schadeclaim af; geld blijft bij de printer (status: paid).
     */
    public function adminRejectDispute($id)
    {
        $order = PrintRequest::findOrFail($id);
        $order->update(['payment_status' => 'paid']);

        return back()->with('success', 'Claim afgewezen: Betaling blijft behouden.');
    }

    /**
     * Laat de klant een order annuleren. Refund indien al in escrow.
     */
    public function customerCancel($id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        $currentStatus = strtolower($order->status ?? 'pending');
        $allowedStatuses = ['pending', 'in_production'];

        if (!in_array($currentStatus, $allowedStatuses) || in_array($order->payment_status, ['paid', 'cancelled', 'disputed'])) {
            return redirect()->back()->with('error', 'Annuleren is niet meer mogelijk.');
        }

        // Als er betaald is, moeten we het geld via Stripe terugstorten.
        if ($order->payment_status === 'escrow' && !empty($order->stripe_checkout_id)) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::retrieve($order->stripe_checkout_id);
                if (!empty($session->payment_intent)) {
                    \Stripe\Refund::create(['payment_intent' => $session->payment_intent, 'reason' => 'requested_by_customer']);
                }
            } catch (\Exception $e) {
                Log::error('Refund Mislukt: ' . $e->getMessage());
            }
        }

        $order->update(['payment_status' => 'cancelled', 'status' => 'cancelled']);
        return redirect()->back()->with('success', 'Je printverzoek is geannuleerd en terugbetaald.');
    }

    /**
     * Klant dient een klacht in over de kwaliteit (defect).
     */
    public function customerDispute(Request $request, $id)
    {
        $order = PrintRequest::findOrFail($id);
        if (auth()->id() !== $order->user_id) abort(403);

        if ($order->payment_status !== 'escrow') {
            return redirect()->back()->with('error', 'Je kunt geen claim indienen op dit verzoek.');
        }

        $request->validate([
            'defect_reason' => 'required|string|max:1000',
            'defect_image'  => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('defect_image')) {
            $path = $request->file('defect_image')->store('defects', 'public');
            $order->update([
                'payment_status'    => 'disputed',
                'defect_reason'     => $request->defect_reason,
                'defect_image_path' => $path
            ]);
            return redirect()->back()->with('success', 'Schadeclaim ingediend.');
        }
        return redirect()->back()->with('error', 'Upload mislukt.');
    }

    /**
     * Webhook luistert naar events vanuit Stripe (buiten de browser om).
     * Zorgt ervoor dat status wordt bijgewerkt zodra betaling succesvol is.
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

            // Wanneer de betaling voltooid is, zet de status op 'escrow'.
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
            Log::error('Stripe Webhook Fout: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook Invalid'], 400);
        }

        return response()->json(['status' => 'success']);
    }
}
