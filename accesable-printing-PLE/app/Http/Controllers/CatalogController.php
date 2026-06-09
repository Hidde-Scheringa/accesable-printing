<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\Request as PrintRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CatalogController extends Controller
{
    /**
     * Toon de catalogus index pagina
     */
    public function index(Request $request)
    {
        $items = CatalogItem::where('is_active', true)->paginate(9);
        return view('catalog.index', compact('items'));
    }

    /**
     * Toon het formulier om een nieuw item toe te voegen (Admin)
     */
    public function create()
    {
        return view('catalog.create');
    }

    /**
     * Sla een nieuw catalogus item op inclusief STL analyse data
     */
    public function store(Request $request)
    {
        // 1. Validatie
        $request->validate([
            'title' => 'required|string|max:255',
            'files' => 'required',
            'category' => 'required',
            'price' => 'required',
        ]);

        $storedFiles = [];

        // 2. Bestanden verwerken en opslaan
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $key => $file) {
                $path = $file->store('catalog_stls', 'public');

                $storedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'x'    => $request->x[$key] ?? 0,
                    'y'    => $request->y[$key] ?? 0,
                    'z'    => $request->z[$key] ?? 0,
                    'volume' => $request->volumes[$key] ?? 0,
                ];
            }
        }

        // 3. Aanmaken in database
        try {
            CatalogItem::create([
                'title'       => $request->title,
                'description' => $request->description,
                'category'    => $request->category,
                'price'       => (float) $request->price,
                'stl_files'   => $storedFiles,
                'is_active'   => true,
            ]);

            return response()->json([
                'success' => true,
                'redirect' => route('catalog.index'),
                'message' => 'Model succesvol toegevoegd aan de catalogus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Fout bij opslaan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Voeg een item toe aan de tijdelijke selectie (sessie)
     */
    public function addToSelection(Request $request, $id)
    {
        $selection = Session::get('print_selection', []);

        $selection[$id] = [
            'quantity' => ($selection[$id]['quantity'] ?? 0) + 1,
            'scale' => $selection[$id]['scale'] ?? 100
        ];

        Session::put('print_selection', $selection);
        return redirect()->back()->with('success', 'Item toegevoegd aan selectie.');
    }

    /**
     * Verwijder een item uit de selectie
     */
    public function removeFromSelection($id)
    {
        $selection = Session::get('print_selection', []);
        if (isset($selection[$id])) unset($selection[$id]);
        Session::put('print_selection', $selection);
        return redirect()->route('catalog.selection');
    }

    /**
     * Leeg de gehele selectie
     */
    public function clearSelection()
    {
        Session::forget('print_selection');
        return redirect()->route('catalog.index');
    }

    /**
     * Toon de selectie (winkelwagen) pagina
     */
    public function selection()
    {
        $selection = Session::get('print_selection', []);
        $items = CatalogItem::whereIn('id', array_keys($selection))->get();
        return view('catalog.selection', compact('items'));
    }

    /**
     * Ga naar de checkout pagina
     */
    public function checkout(Request $request)
    {
        $selection = Session::get('print_selection', []);

        if ($request->has('quantities')) {
            foreach ($request->quantities as $id => $qty) {
                $selection[$id]['quantity'] = max(1, intval($qty));
            }
        }

        if ($request->has('scales')) {
            foreach ($request->scales as $id => $scale) {
                if (isset($selection[$id])) {
                    $selection[$id]['scale'] = max(1, intval($scale));
                }
            }
        }

        Session::put('print_selection', $selection);
        $items = CatalogItem::whereIn('id', array_keys($selection))->get();

        if ($items->isEmpty()) {
            return redirect()->route('catalog.index');
        }

        foreach ($items as $item) {
            $totalVolume = 0;
            if (is_array($item->stl_files)) {
                foreach ($item->stl_files as $file) {
                    $totalVolume += ($file['volume'] ?? 0);
                }
            }
            $item->total_volume_mm3 = $totalVolume;
        }

        return view('catalog.checkout', compact('items', 'selection'));
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'street'       => 'required|string',
            'streetnumber' => 'required|string',
            'zipcode'      => 'required|string',
            'city'         => 'required|string',
            'total_price_hidden' => 'required|numeric'
        ]);

        $selection = session('print_selection', []);
        if (empty($selection)) {
            return response()->json(['success' => false, 'message' => 'Winkelwagen leeg.'], 400);
        }

        $stlFilesForDb = [];
        $totalPrice = (float) $request->total_price_hidden;
        $totalQuantity = 0;

        foreach ($selection as $itemId => $details) {
            $totalQuantity += ($details['quantity'] ?? 1);
            $catalogItem = CatalogItem::find($itemId);

            if ($catalogItem) {
                foreach ($catalogItem->stl_files as $stl) {
                    // HIER BEREKENEN WE DE CM WAARDEN VOOR DE DATABASE
                    $stlFilesForDb[] = [
                        'title'         => $catalogItem->title,
                        'path'          => $stl['path'],
                        'scale'         => $details['scale'] ?? 100,
                        'quantity'      => $details['quantity'] ?? 1,
                        'price'         => $request->calculated_prices[$itemId] ?? 0,
                        'from_catalog'  => true,
                        // Opgeslagen als float voor makkelijke berekeningen in de toekomst
                        'x_cm'          => ($stl['x'] ?? 0) / 10,
                        'y_cm'          => ($stl['y'] ?? 0) / 10,
                        'z_cm'          => ($stl['z'] ?? 0) / 10,
                    ];
                }
            }
        }

        try {
            $order = PrintRequest::create([
                'user_id'        => auth()->id(),
                'title'          => $request->title,
                'description'    => $request->description ?? 'Catalogus bestelling',
                'total_price'    => $totalPrice,
                'stl_files'      => $stlFilesForDb,
                'street'         => $request->street,
                'streetnumber'   => $request->streetnumber,
                'zipcode'        => $request->zipcode,
                'city'           => $request->city,
                'status'         => 'pending',
                'payment_status' => 'pending',
            ]);

            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::create([
                'payment_method_types' => ['ideal', 'card'],
                'line_items' => [[
                    'price_data' => ['currency' => 'eur', 'product_data' => ['name' => "Print: " . $order->title], 'unit_amount' => (int)($totalPrice * 100)],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => ['order_id' => $order->id],
                'success_url' => route('payment.success', $order->id),
                'cancel_url' => route('payment.cancel', $order->id),
            ]);

            $order->update(['stripe_checkout_id' => $session->id]);
            session()->forget('print_selection');

            return response()->json(['success' => true, 'stripe_url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
