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

        // Controleer of er een refund bedrag is ingesteld
        if ($order->suggested_refund <= 0) {
            return back()->with('error', 'Geen geldig refund bedrag gevonden.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_checkout_id);

            // Stripe Refund met een specifiek bedrag (in centen)
            \Stripe\Refund::create([
                'payment_intent' => $session->payment_intent,
                'amount'         => (int)($order->suggested_refund * 100), // Converteer euro naar centen
                'reason'         => 'requested_by_customer',
            ]);

            // Status bijwerken: De order is niet meer 'disputed', maar deels terugbetaald
            // We zetten de status op 'paid' (aangezien de rest van het bedrag bij jou blijft)
            $order->update([
                'payment_status' => 'paid',
                'suggested_refund' => 0 // Reset de claim
            ]);

            return back()->with('success', 'Claim goedgekeurd: €' . number_format($order->suggested_refund, 2, ',', '.') . ' is teruggestort.');

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

    // In PaymentController.php

    public function customerDispute(Request $request, $id)
    {
        // 1. Valideer de input
        $request->validate([
            'items'         => 'required|array',
            'qtys'          => 'required|array',
            'defect_reason' => 'required|string|max:1000',
            'defect_image'  => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $order = PrintRequest::findOrFail($id);
        $files = is_array($order->stl_files) ? $order->stl_files : json_decode($order->stl_files, true);
        $suggestedRefund = 0;

        // 2. Bereken het totaalbedrag op basis van de JSON
        // In PaymentController.php

        foreach ($request->items as $index => $itemName) {
            $defectQty = (int)($request->qtys[$index] ?? 0);

            // Zoek het model in de JSON
            $item = collect($files)->firstWhere('title', $itemName);

            if ($item && $defectQty > 0) {
                // 1. Haal de totaalprijs van de batch op (uit de JSON)
                $batchPrice = (float)($item['price'] ?? 0);

                // 2. Haal de totale hoeveelheid van de batch op
                $batchQuantity = (int)($item['quantity'] ?? 1);

                // 3. Bereken de prijs per stuk
                $pricePerPiece = $batchPrice / $batchQuantity;

                // 4. Vermenigvuldig met het aantal defecte stuks
                $suggestedRefund += ($defectQty * $pricePerPiece);
            }
        }

        // 3. Sla gegevens op
        $path = $request->file('defect_image')->store('defects', 'public');

        $order->update([
            'payment_status'    => 'disputed',
            'suggested_refund'  => $suggestedRefund,
            'defect_reason'     => $request->defect_reason,
            'defect_image_path' => $path
        ]);

        return redirect()->back()->with('success', 'Je claim voor €' . number_format($suggestedRefund, 2) . ' is ingediend.');
    }
}
