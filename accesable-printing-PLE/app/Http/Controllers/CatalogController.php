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

        // Synchroniseer de gewijzigde aantallen uit het winkelwagen-formulier naar de sessie
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

        return view('catalog.checkout', compact('items', 'selection'));
    }

    /**
     * Verwerk de uiteindelijke bestelling en genereer een Stripe Checkout Sessie
     */
    public function processCheckout(Request $request)
    {
        // 1. Uitgebreide validatie
        $request->validate([
            'title'        => 'required|string|max:255',
            'street'       => 'required|string',
            'streetnumber' => 'required|string',
            'zipcode'      => 'required|string',
            'city'         => 'required|string',
            'scales'       => 'required|array',
            'colors'       => 'required|array',
            'materials'    => 'required|array',
            'total_price_hidden' => 'required|numeric'
        ]);

        $selection = session('print_selection', []);
        if (empty($selection)) {
            return response()->json(['success' => false, 'message' => 'Winkelwagen is leeg.'], 400);
        }

        $stlFilesForDb = [];
        $totalPrice = 0;
        $totalQuantity = 0;

        // Standaardwaarden instellen (voor de algemene kolommen van de tabel)
        $chosenMaterial = 'FDM';
        $chosenColor = 'Grijs';

        // 2. Server-side herberekening op basis van de geselecteerde opties per product
        foreach ($selection as $itemId => $details) {
            $catalogItem = CatalogItem::find($itemId);

            if ($catalogItem) {
                // Haal de schaal, kleur en materiaal op die specifiek voor dit item zijn gekozen
                $currentScale = isset($request->scales[$itemId]) ? intval($request->scales[$itemId]) : ($details['scale'] ?? 100);
                $currentQty = $details['quantity'] ?? ($details['qty'] ?? 1);

                $itemColor = $request->colors[$itemId] ?? 'Grijs';
                $itemMaterial = $request->materials[$itemId] ?? 'FDM';

                // Bewaar de configuratie van het eerste item als fallback voor de hoofd-kolommen
                if ($totalQuantity === 0) {
                    $chosenColor = $itemColor;
                    $chosenMaterial = $itemMaterial;
                }

                $scaleFactor = $currentScale / 100;
                $totalQuantity += $currentQty;

                // Prijs herberekenen (volume schaalt met kubieke factor)
                $itemPrice = ($catalogItem->price * $currentQty) * pow($scaleFactor, 3);
                $totalPrice += $itemPrice;

                foreach ($catalogItem->stl_files as $stl) {
                    $stlFilesForDb[] = [
                        'title'         => $stl['name'] ?? $catalogItem->title,
                        'original_name' => $stl['name'] ?? 'model.stl',
                        'path'          => $stl['path'],
                        'scale'         => $currentScale,
                        'quantity'      => $currentQty,
                        'color'         => $itemColor,
                        'material'      => $itemMaterial,
                        'price'         => number_format($itemPrice, 2, '.', ''),
                        'x'             => $stl['x'] ?? 0,
                        'y'             => $stl['y'] ?? 0,
                        'z'             => $stl['z'] ?? 0,
                        'from_catalog'  => true
                    ];
                }
            }
        }

        // Verzending en stapelkorting berekenen
        $shippingCosts = 8.50;
        if ($totalQuantity == 2) {
            $shippingCosts = 7.50;
        } elseif ($totalQuantity >= 3) {
            $shippingCosts = 6.50;
        }

        if ($totalPrice >= 24.00) {
            $shippingCosts = 0.00;
        }

        $grandTotal = $totalPrice + $shippingCosts;

        try {
            // 3. Maak het project aan in de database
            $order = PrintRequest::create([
                'user_id'        => auth()->id(),
                'title'          => $request->title,
                'description'    => $request->description ?? "Catalogus bestelling",
                'material'       => $chosenMaterial,
                'color'          => $chosenColor,
                'total_price'    => $grandTotal,
                'stl_files'      => $stlFilesForDb,
                'street'         => $request->street,
                'streetnumber'   => $request->streetnumber,
                'zipcode'        => $request->zipcode,
                'city'           => $request->city,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // 4. Genereer de Stripe Checkout-sessie
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::create([
                'payment_method_types' => ['ideal', 'card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => "3D Print Bestelling: " . $order->title,
                            'description' => "Order #" . $order->id . " via Productcatalogus",
                        ],
                        'unit_amount' => (int)($grandTotal * 100), // Bedrag in centen
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $order->id
                ],
                'customer_email' => auth()->user()->email,
                'success_url' => route('payment.success', $order->id),
                'cancel_url' => route('payment.cancel', $order->id),
            ]);

            // 5. Koppel de Stripe Checkout-sessie aan de gecreëerde aanvraag
            $order->update([
                'stripe_checkout_id' => $session->id,
                'payment_status'     => 'pending'
            ]);

            // 6. Winkelwagen legen na succesvolle initialisatie
            session()->forget('print_selection');

            // Geef de Stripe-URL terug zodat de AJAX-handler in Blade de klant kan doorsturen
            return response()->json([
                'success' => true,
                'stripe_url' => $session->url
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout tijdens initialiseren betaling: ' . $e->getMessage()
            ], 500);
        }
    }
}
