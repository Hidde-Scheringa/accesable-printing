<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Request as PrintRequest;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * De klant keert succesvol terug van Stripe.
     * We sturen ze direct door naar het dashboard met een nette melding.
     */
    public function paymentSuccess($id)
    {
        return redirect()->route('dashboard')->with('success', 'Bedankt! Je betaling is geslaagd en we gaan direct voor je aan de slag. Houd rekening met een levertijd tot 2 weken.');
    }

    /**
     * De klant heeft de betaling geannuleerd of er ging iets mis.
     */
    public function paymentCancel($id)
    {
        return redirect()->route('dashboard')->with('error', 'De betaling is geannuleerd. Je kunt je project eventueel opnieuw indienen.');
    }

    /**
     * Stripe Webhook handelt de statuswijzigingen asynchroon op de achtergrond af.
     */
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Signature Error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Als de betaling met succes is voldaan
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $order = PrintRequest::find($session->metadata->order_id);

            if ($order) {
                $order->update([
                    'payment_status' => 'paid',      // Nu staat hij direct op betaald!
                    'status'         => 'printing'    // Zet de order direct door naar de printer queue
                ]);

                Log::info('Order #' . $order->id . ' succesvol gemarkeerd als betaald.');
            }
        }

        return response()->json(['status' => 'success']);
    }
}
