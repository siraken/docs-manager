<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TravelExpenseController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/pricing', function () {
    return view('calculate/pricing');
})->name('pricing');

Route::get('/pdf', [EstimateController::class, 'pdf']);

/**
 * Estimate
 */
// GET
Route::get('/estimate', [EstimateController::class, 'index'])->name('estimate');
Route::get('/estimate/create', [EstimateController::class, 'create'])->name('estimate');
Route::get('/estimate/edit', [EstimateController::class, 'edit'])->name('estimate');
Route::get('/estimate/detail', function () {
    return view('estimate/detail');
})->name('estimate');
Route::get('/estimate/trash', function () {
    return view('estimate/trash');
})->name('estimate');

/**
 * Invoice
 */
// GET
Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice');
Route::get('/invoice/create', [InvoiceController::class, 'create'])->name('invoice');
Route::get('/invoice/edit', [InvoiceController::class, 'edit'])->name('invoice');
Route::get('/invoice/detail', function () {
    return view('invoice/detail');
})->name('invoice');
Route::get('/invoice/trash', function () {
    return view('invoice/trash');
})->name('invoice');

/**
 * Order
 */
// GET
Route::get('/orders', [OrderController::class, 'index'])->name('orders');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders');
Route::get('/orders/edit/{id}', [OrderController::class, 'edit'])->name('orders');
Route::get('/orders/pdf/{id}', [OrderController::class, 'pdf'])->name('orders');
Route::get('/orders/detail', function () {
    return view('order/detail');
})->name('orders');
Route::get('/orders/trash', function () {
    return view('order/trash');
})->name('orders');
// POST
Route::post('/orders/create', [OrderController::class, 'create']);
Route::post('/orders/edit/{id}', [OrderController::class, 'edit']);
Route::post('/orders/set-status', [OrderController::class, 'setStatus']);

/**
 * Item
 */
// GET
Route::get('/items', [ItemController::class, 'index'])->name('items');
Route::get('/items/create', [ItemController::class, 'create'])->name('items');
Route::get('/items/edit/{id}', [ItemController::class, 'edit'])->name('items');
// POST
Route::post('/items/create', [ItemController::class, 'create']);
Route::post('/items/edit/{id}', [ItemController::class, 'edit']);

/**
 * Inquiry
 */
// GET
Route::get('/inquiry', [InquiryController::class, 'index'])->name('inquiry');
Route::get('/inquiry/create', [InquiryController::class, 'create'])->name('inquiry');
Route::get('/inquiry/view/{id}', [InquiryController::class, 'view'])->name('inquiry');
Route::get('/inquiry/truncate', [InquiryController::class, 'truncate'])->name('inquiry');
// POST
Route::post('/inquiry/create', [InquiryController::class, 'create']);

/**
 * Client
 */
// GET
Route::get('/clients', [ClientController::class, 'index'])->name('clients');
Route::get('/clients/create', [ClientController::class, 'create'])->name('clients');
Route::get('/clients/edit/{id}', [ClientController::class, 'edit'])->name('clients');
Route::get('/clients/truncate', [ClientController::class, 'truncate'])->name('clients');
// POST
Route::post('/clients/create', [ClientController::class, 'create']);
Route::post('/clients/edit/{id}', [ClientController::class, 'edit']);

/**
 * Trip
 */
// GET
Route::get('/trip', [TravelController::class, 'index'])->name('trip');
Route::get('/trip/create', [TravelController::class, 'create'])->name('trip');
Route::get('/trip/pdf/{id}', [TravelController::class, 'pdf'])->name('trip');
// POST
Route::post('/trip/create', [TravelController::class, 'create']);

/**
 * Expense
 */
// GET
Route::get('/expense', [TravelExpenseController::class, 'index'])->name('expense');
Route::get('/expense/create', [TravelExpenseController::class, 'create'])->name('expense');
Route::get('/expense/pdf/{id}', [TravelExpenseController::class, 'pdf'])->name('expense');
