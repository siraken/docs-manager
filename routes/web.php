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
Route::get('/order', [OrderController::class, 'index'])->name('order');
Route::get('/order/create', [OrderController::class, 'create'])->name('order');
Route::post('/order/create', [OrderController::class, 'create'])->name('order');
Route::get('/order/edit', [OrderController::class, 'edit'])->name('order');
Route::get('/order/detail', function () {
    return view('order/detail');
})->name('order');
Route::get('/order/trash', function () {
    return view('order/trash');
})->name('order');
Route::post('/order/set-status', [OrderController::class, 'setStatus'])->name('order');

/**
 * Item
 */
Route::get('/item', [ItemController::class, 'index'])->name('item');
Route::get('/item/create', [ItemController::class, 'create'])->name('item');
Route::post('/item/create', [ItemController::class, 'create']);
// Route::get('/item/edit', [EstimateController::class, 'edit']);

/**
 * Inquiry
 */
Route::get('/inquiry', [InquiryController::class, 'index'])->name('inquiry');
Route::get('/inquiry/create', [InquiryController::class, 'create'])->name('inquiry');
Route::post('/inquiry/create', [InquiryController::class, 'create']);
Route::get('/inquiry/view/{id}', [InquiryController::class, 'view'])->name('inquiry');
Route::get('/inquiry/truncate', [InquiryController::class, 'truncate'])->name('inquiry');

/**
 * Client
 */
Route::get('/client', [ClientController::class, 'index'])->name('client');
Route::get('/client/create', [ClientController::class, 'create'])->name('client');
Route::post('/client/create', [ClientController::class, 'create']);
Route::get('/client/view/{id}', [ClientController::class, 'view'])->name('client');
Route::get('/client/truncate', [ClientController::class, 'truncate'])->name('client');

/**
 * Trip
 */
Route::get('/trip', [TravelController::class, 'index'])->name('trip');
Route::get('/trip/create', [TravelController::class, 'create'])->name('trip');
Route::post('/trip/create', [TravelController::class, 'create']);
Route::get('/trip/pdf/{id}', [TravelController::class, 'pdf'])->name('trip');

/**
 * Expense
 */
Route::get('/expense', [TravelExpenseController::class, 'index'])->name('expense');
Route::get('/expense/create', [TravelExpenseController::class, 'create'])->name('expense');
Route::get('/expense/pdf/{id}', [TravelExpenseController::class, 'pdf'])->name('expense');
