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
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\DashboardController;

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

/**
 * Sign In
 */
Route::get('/login', function() {
    return view('login');
})->name('login');
Route::post('/login', [LoginController::class, 'auth'])->name('loginAuth');

/**
 * To be authenticated is required
 */
Route::middleware('login')->group(function() {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    /**
     * Pricing
     */
    Route::get('/pricing', function () {
        return view('calculate/pricing');
    })->name('pricing.index');

    Route::get('/pdf', [EstimateController::class, 'pdf']);

    /**
     * Estimate
     */
    // GET
    Route::get('/estimates', [EstimateController::class, 'index'])->name('estimates.index');
    Route::get('/estimates/create', [EstimateController::class, 'create'])->name('estimates.create');
    Route::get('/estimates/edit/{id}', [EstimateController::class, 'edit'])->name('estimates.edit');
    Route::get('/estimates/pdf/{id}', [EstimateController::class, 'pdf'])->name('estimates.pdf');
    Route::get('/estimates/csv/{id}', [EstimateController::class, 'csv'])->name('estimates.csv');
    Route::get('/estimates/view/{id}', [EstimateController::class, 'view'])->name('estimates.view');
    Route::get('/estimates/delete/{id}', [EstimateController::class, 'delete'])->name('estimates.delete');
    Route::get('/estimates/restore/{id}', [EstimateController::class, 'restore'])->name('estimates.restore');
    Route::get('/estimates/trash', [EstimateController::class, 'trash'])->name('estimates.trash');
    Route::get('/estimates/detail', function () {
        return view('estimates/detail');
    })->name('estimates.detail');
    Route::get('/estimates/trash', function () {
        return view('estimates/trash');
    })->name('estimates.trash');
    // POST
    Route::post('/estimates/create', [EstimateController::class, 'create']);
    Route::post('/estimates/edit/{id}', [EstimateController::class, 'edit']);

    /**
     * Invoice
     */
    // GET
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('/invoices/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::get('/invoices/detail', function () {
        return view('invoices/detail');
    })->name('invoices.detail');
    Route::get('/invoices/trash', function () {
        return view('invoices/trash');
    })->name('invoices.trash');

    /**
     * Order
     */
    // GET
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::get('/orders/edit/{id}', [OrderController::class, 'edit'])->name('orders.edit');
    Route::get('/orders/pdf/{id}', [OrderController::class, 'pdf'])->name('orders.pdf');
    Route::get('/orders/csv/{id}', [OrderController::class, 'csv'])->name('orders.csv');
    Route::get('/orders/view/{id}', [OrderController::class, 'view'])->name('orders.view');
    Route::get('/orders/delete/{id}', [OrderController::class, 'delete'])->name('orders.delete');
    Route::get('/orders/restore/{id}', [OrderController::class, 'restore'])->name('orders.restore');
    Route::get('/orders/trash', [OrderController::class, 'trash'])->name('orders.trash');
    // POST
    Route::post('/orders/create', [OrderController::class, 'create']);
    Route::post('/orders/edit/{id}', [OrderController::class, 'edit']);
    Route::post('/orders/set-status', [OrderController::class, 'setStatus']);

    /**
     * Item
     */
    // GET
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::get('/items/edit/{id}', [ItemController::class, 'edit'])->name('items.edit');
    // POST
    Route::post('/items/create', [ItemController::class, 'create']);
    Route::post('/items/edit/{id}', [ItemController::class, 'edit']);

    /**
     * Inquiry
     */
    // GET
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/create', [InquiryController::class, 'create'])->name('inquiries.create');
    Route::get('/inquiries/view/{id}', [InquiryController::class, 'view'])->name('inquiries.view');
    Route::get('/inquiries/truncate', [InquiryController::class, 'truncate'])->name('inquiries.truncate');
    // POST
    Route::post('/inquiries/create', [InquiryController::class, 'create']);

    /**
     * Client
     */
    // GET
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::get('/clients/edit/{id}', [ClientController::class, 'edit'])->name('clients.edit');
    Route::get('/clients/truncate', [ClientController::class, 'truncate'])->name('clients.truncate');
    // POST
    Route::post('/clients/create', [ClientController::class, 'create']);
    Route::post('/clients/edit/{id}', [ClientController::class, 'edit']);

    /**
     * Trip
     */
    // GET
    Route::get('/trips', [TravelController::class, 'index'])->name('trips.index');
    Route::get('/trips/create', [TravelController::class, 'create'])->name('trips.create');
    Route::get('/trips/pdf/{id}', [TravelController::class, 'pdf'])->name('trips.pdf');
    // POST
    Route::post('/trips/create', [TravelController::class, 'create']);

    /**
     * Expense
     */
    // GET
    Route::get('/expenses', [TravelExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [TravelExpenseController::class, 'create'])->name('expenses.create');
    Route::get('/expenses/pdf/{id}', [TravelExpenseController::class, 'pdf'])->name('expenses.pdf');

    /**
     * Tasks
     */
    // GET
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::get('/tasks/edit/{id}', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::get('/tasks/view/{id}', [TaskController::class, 'view'])->name('tasks.view');
    Route::get('/tasks/delete/{id}', [TaskController::class, 'delete'])->name('tasks.delete');
    // POST
    Route::post('/tasks/create', [TaskController::class, 'create']);

    /**
     * Works
     */
    // GET
    Route::get('/works', [WorkController::class, 'index'])->name('works.index');
    Route::get('/works/analysis', [WorkController::class, 'analysis'])->name('works.analysis');
    Route::get('/works/create', [WorkController::class, 'create'])->name('works.create');
    Route::get('/works/edit/{id}', [WorkController::class, 'edit'])->name('works.edit');
    // POST
    Route::post('/works/create', [WorkController::class, 'create']);
    Route::post('/works/edit/{id}', [WorkController::class, 'edit']);

    /**
     * User
     */
    // GET
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    // POST
    Route::post('/users/create', [UserController::class, 'create']);
    Route::post('/users/edit/{id}', [UserController::class, 'edit']);

    /**
     * Logs
     */
    // GET
    // Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/access', [LogController::class, 'access'])->name('logs.access');
});
