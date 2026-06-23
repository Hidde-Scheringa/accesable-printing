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
     * Display the catalog index page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 1. Initialize the query
        $query = CatalogItem::where('is_active', true);

        // 2. Filter by category if provided in the URL (?category=...)
        if ($request->has('category')) {
            $category = $request->category;

            // Matches the URL value with the database (e.g., 'animals' -> 'Animals')
            $query->where('category', ucfirst($category));
        }

        // 3. Retrieve results with pagination
        $items = $query->paginate(9)->withQueryString();

        return view('catalog.index', compact('items'));
    }

    /**
     * Show the form to add a new item (Admin).
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('catalog.create');
    }

    /**
     * Store a new catalog item including STL analysis data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'title' => 'required|string|max:255',
            'files' => 'required',
            'category' => 'required',
            'price' => 'required',
        ]);

        $storedFiles = [];

        // 2. Process and store files
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

        // 3. Create record in database
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
                'message' => 'Model successfully added to the catalog!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Storage error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add an item to the temporary session selection.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToSelection(Request $request, $id)
    {
        $selection = Session::get('print_selection', []);

        $selection[$id] = [
            'quantity' => ($selection[$id]['quantity'] ?? 0) + 1,
            'scale' => $selection[$id]['scale'] ?? 100
        ];

        Session::put('print_selection', $selection);
        return redirect()->back()->with('success', 'Item added to selection.');
    }

    /**
     * Remove an item from the selection.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromSelection($id)
    {
        $selection = Session::get('print_selection', []);
        if (isset($selection[$id])) unset($selection[$id]);
        Session::put('print_selection', $selection);
        return redirect()->route('catalog.selection');
    }

    /**
     * Clear the entire selection.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearSelection()
    {
        Session::forget('print_selection');
        return redirect()->route('catalog.index');
    }

    /**
     * Display the selection (cart) page.
     *
     * @return \Illuminate\View\View
     */
    public function selection()
    {
        $selection = Session::get('print_selection', []);
        $items = CatalogItem::whereIn('id', array_keys($selection))->get();
        return view('catalog.selection', compact('items'));
    }

    /**
     * Navigate to the checkout page.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request)
    {
        $selection = Session::get('print_selection', []);

        // Update session from form (if coming from cart)
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

        // Prepare preview data
        foreach ($items as $item) {
            $totalVolume = 0;
            if (is_array($item->stl_files)) {
                foreach ($item->stl_files as $file) {
                    $totalVolume += ($file['volume'] ?? 0);
                }
            }
            $item->total_volume_mm3 = $totalVolume;
            $item->current_scale = $selection[$item->id]['scale'] ?? 100;
        }

        return view('catalog.checkout', compact('items', 'selection'));
    }

    /**
     * Process the checkout and initiate Stripe payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processCheckout(Request $request)
    {
        // 1. Validation
        $request->validate([
            'title'        => 'required|string',
            'street'       => 'required|string',
            'streetnumber' => 'required|string',
            'zipcode'      => 'required|string',
            'city'         => 'required|string',
            'total_price_hidden' => 'required|numeric',
        ]);

        $selection = session('print_selection', []);
        if (empty($selection)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 400);
        }

        $submittedScales = $request->input('scales', []);
        $submittedColors = $request->input('colors', []);
        $submittedMaterials = $request->input('materials', []);

        $stlFilesForDb = [];

        // 2. Loop through selection
        foreach ($selection as $itemId => $details) {
            $catalogItem = CatalogItem::find($itemId);

            $scale = $submittedScales[$itemId] ?? $details['scale'] ?? 100;
            $scaleFactor = $scale / 100;

            if ($catalogItem) {
                foreach ($catalogItem->stl_files as $stl) {
                    $basisX = $stl['x'] ?? 0;
                    $basisY = $stl['y'] ?? 0;
                    $basisZ = $stl['z'] ?? 0;

                    $stlFilesForDb[] = [
                        'title'        => $catalogItem->title,
                        'path'         => $stl['path'],
                        'scale'        => (int)$scale,
                        'quantity'     => (int)($details['quantity'] ?? 1),
                        'price'        => (float)($request->calculated_prices[$itemId] ?? 0),
                        'color'        => $submittedColors[$itemId] ?? 'Gray',
                        'material'     => $submittedMaterials[$itemId] ?? 'FDM',
                        'from_catalog' => true,
                        // Conversion: (Base value in mm * scale) / 10 = cm
                        'x_cm'         => ($basisX * $scaleFactor) / 10,
                        'y_cm'         => ($basisY * $scaleFactor) / 10,
                        'z_cm'         => ($basisZ * $scaleFactor) / 10,
                    ];
                }
            }
        }

        $firstItem = !empty($stlFilesForDb) ? $stlFilesForDb[0] : null;

        try {
            $order = PrintRequest::create([
                'user_id'        => auth()->id(),
                'title'          => $request->title,
                'description'    => $request->description ?? 'Catalog order',
                'total_price'    => (float) $request->total_price_hidden,
                'stl_files'      => $stlFilesForDb,
                'color'          => $firstItem['color'] ?? 'Gray',
                'material'       => $firstItem['material'] ?? 'FDM',
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
                'cancel_url' => route('payment.cancel', $order->id),
            ]);

            $order->update(['stripe_checkout_id' => $session->id]);
            session()->forget('print_selection');

            return response()->json(['success' => true, 'stripe_url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
