<?php

namespace App\Http\Controllers;

use App\Models\Request as PrintRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrinterController extends Controller
{
    /**
     * Toon het dashboard voor de printer.
     */
    public function index()
    {
        // 1. Alle aanvragen voor de stats (inclusief pending)
        $statsRequests = PrintRequest::all();

        // 2. Gefilterde aanvragen voor de tabel (ZONDER 'pending' orders)
        $allRequests = PrintRequest::with('user')
            ->where('payment_status', '!=', 'pending')
            ->latest()
            ->get();

        return view('printer.dashboard', compact('allRequests', 'statsRequests'));
    }

    /**
     * Update de productiestatus vanuit het Admin Dashboard.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:printing,shipped'
        ]);

        $order = PrintRequest::findOrFail($id);

        // Update de status naar 'printing' of 'shipped'
        $order->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'De productiestatus is succesvol bijgewerkt naar ' . strtoupper($request->status) . '.');
    }

    /**
     * Klant annuleert de order (Alleen toegestaan bij 'pending' of 'in_production')
     */
    public function customerCancel($id)
    {
        $order = PrintRequest::findOrFail($id);

        // Extra backend beveiliging: Check de huidige status
        $currentStatus = strtolower($order->status ?? 'pending');
        $allowedStatuses = ['pending', 'in_production'];

        if (!in_array($currentStatus, $allowedStatuses)) {
            return redirect()->back()->with('error', 'Annuleren is helaas niet meer mogelijk omdat de printers al draaien of de bestelling is verzonden.');
        }

        // Update de status naar geannuleerd
        $order->update([
            'payment_status' => 'cancelled',
            'status' => 'cancelled'
        ]);

        return redirect()->back()->with('success', 'Je printverzoek is succesvol geannuleerd.');
    }

    /**
     * Klant meldt een defect / vraagt geld terug (Alleen bij status 'shipped')
     */
    public function customerDispute(Request $request, $id)
    {
        $request->validate([
            'defect_reason' => 'required|string|max:1000',
            'defect_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB
        ]);

        $order = PrintRequest::findOrFail($id);

        // Sla de afbeelding op in de public storage onder de map 'defects'
        if ($request->hasFile('defect_image')) {
            $path = $request->file('defect_image')->store('defects', 'public');

            // Sla de reden en het bestandspad op in de database
            $order->update([
                'payment_status' => 'disputed',
                'defect_reason' => $request->defect_reason,
                'defect_image_path' => $path
            ]);

            return redirect()->back()->with('success', 'Je terugbetalingsaanvraag en schadefoto zijn succesvol ingediend. We nemen zo snel mogelijk contact met je op.');
        }

        return redirect()->back()->with('error', 'Er is iets misgegaan bij het uploaden van de foto.');
    }
}
