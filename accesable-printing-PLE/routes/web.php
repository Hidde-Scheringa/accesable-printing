<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LandingspageController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- Publieke Routes ---
Route::get('/', [LandingspageController::class, 'index'])->name('welcome');
Route::get('/completed-prints', [PortfolioController::class, 'index'])->name('showcase.index');

Route::get('/home', function () {
    if (!Auth::check()) return redirect()->route('welcome');
    return Auth::user()->isPrinter() ? redirect()->route('printer.dashboard') : redirect()->route('dashboard');
})->name('home');

// --- Catalogus Basis ---
Route::get('/catalogus', [CatalogController::class, 'index'])->name('catalog.index');
Route::post('/catalogus/add/{id}', [CatalogController::class, 'addToSelection'])->name('catalog.add');
Route::get('/catalogus/selection', [CatalogController::class, 'selection'])->name('catalog.selection');
Route::get('/catalogus/remove/{id}', [CatalogController::class, 'removeFromSelection'])->name('catalog.remove');
Route::get('/catalogus/clear', [CatalogController::class, 'clearSelection'])->name('catalog.clear');

// --- Stripe Webhook & Feedback (Publiek/Klant toegankelijk) ---
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);
Route::get('/payment-success/{id}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment-cancel/{id}', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');

// --- Geauthenticeerde Routes ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return Auth::user()->isPrinter() ? redirect()->route('printer.dashboard') : view('dashboard');
    })->name('dashboard');
});

// --- Klant Specifieke Routes ---
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');

    Route::get('/catalogus/checkout', [CatalogController::class, 'checkout'])->name('catalog.checkout');
    Route::post('/catalogus/process', [CatalogController::class, 'processCheckout'])->name('catalog.process');

    Route::get('/catalogus/beheer/toevoegen', [CatalogController::class, 'create'])->name('catalog.create');
    Route::post('/catalogus/beheer/toevoegen', [CatalogController::class, 'store'])->name('catalog.store');

    // --- TOEGEVOEGD: De route die de klant naar de Stripe Checkout URL stuurt ---
    Route::get('/payment/checkout/{id}', [PaymentController::class, 'checkout'])->name('payment.checkout');
});

// --- Printer (Admin) Specifieke Routes ---
Route::middleware(['auth', 'printer'])->group(function () {
    Route::get('/printer/dashboard', [PrinterController::class, 'index'])->name('printer.dashboard');
    Route::get('/printer/download-zip/{id}', [PrinterController::class, 'downloadZip'])->name('printer.download-zip');

    // STRIPE: De actie voor de admin om de sessie aan te maken en de mail te sturen
    Route::get('/printer/send-payment/{id}', [PaymentController::class, 'sendPaymentRequest'])->name('admin.send_payment');
});

//onepager route
Route::view('/info', 'onepage')->name('onepage');

require __DIR__.'/auth.php';
