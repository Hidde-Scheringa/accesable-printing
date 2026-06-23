<?php

use App\Http\Controllers\{RequestController, CatalogController, LandingspageController, PortfolioController, PrinterController, PaymentController};
use Illuminate\Support\Facades\{Route, Auth};

/**
 * Route definitions for the 3D printing service application.
 * Organized by public access, authenticated customer flows, and printer/admin dashboards.
 */

// --- Public Routes ---
Route::get('/', [LandingspageController::class, 'index'])->name('welcome');
Route::get('/completed-prints', [PortfolioController::class, 'index'])->name('showcase.index');
Route::view('/info', 'onepage')->name('onepage');

// Stripe Webhook Processing
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);
Route::any('/stripe/webhook', fn() => redirect()->route('welcome'));

Route::get('/home', function () {
    if (!Auth::check()) return redirect()->route('welcome');
    return Auth::user()->isPrinter() ? redirect()->route('printer.dashboard') : redirect()->route('dashboard');
})->name('home');

// --- Catalog Basics ---
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::post('/catalogus/add/{id}', [CatalogController::class, 'addToSelection'])->name('catalog.add');
Route::get('/catalogus/selection', [CatalogController::class, 'selection'])->name('catalog.selection');
Route::get('/catalogus/remove/{id}', [CatalogController::class, 'removeFromSelection'])->name('catalog.remove');
Route::get('/catalogus/clear', [CatalogController::class, 'clearSelection'])->name('catalog.clear');

// --- Authenticated Routes (Customer) ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => Auth::user()->isPrinter() ? redirect()->route('printer.dashboard') : view('dashboard'))->name('dashboard');

    // Payment Processing
    Route::get('/payment-success/{id}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment-cancel/{id}', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
    Route::post('/order/{id}/approve', [PaymentController::class, 'approveDelivery'])->name('order.approve');
    Route::post('/order/{id}/cancel', [PaymentController::class, 'customerCancel'])->name('order.cancel');
    Route::post('/order/{id}/dispute', [PaymentController::class, 'customerDispute'])->name('order.dispute');
});

// --- Customer Specific Routes ---
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/catalogus/checkout', [CatalogController::class, 'checkout'])->name('catalog.checkout');
    Route::get('/catalog/create', [CatalogController::class, 'create'])->name('catalog.create');
    Route::post('/catalogus/process', [CatalogController::class, 'processCheckout'])->name('catalog.process');
    Route::get('/payment/checkout/{id}', [PaymentController::class, 'checkout'])->name('payment.checkout');

    // Route for resuming a payment session
    Route::get('/payment/resume/{id}', [PaymentController::class, 'resumePayment'])->name('payment.resume');

    Route::post('/order/{id}/dispute', [PaymentController::class, 'customerDispute'])->name('order.dispute');
});

// --- Printer (Admin) Dashboard ---
Route::middleware(['auth', 'printer'])->group(function () {
    Route::get('/printer/dashboard', [PrinterController::class, 'index'])->name('printer.dashboard');
    Route::post('/admin/dispute/{id}/approve', [PaymentController::class, 'adminApproveDispute'])->name('admin.dispute.approve');
    Route::post('/admin/dispute/{id}/reject', [PaymentController::class, 'adminRejectDispute'])->name('admin.dispute.reject');
    Route::post('/printer/request/{id}/update-status', [PrinterController::class, 'updateStatus'])->name('printer.update-status');

    // Route for canceling a specific part within an order
    Route::post('/printer/cancel-part/{orderId}/{fileIndex}', [App\Http\Controllers\PrinterController::class, 'cancelPrintablePart'])
        ->name('printer.cancel-part');
});

require __DIR__.'/auth.php';
