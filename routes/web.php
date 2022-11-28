<?php

use App\Http\Controllers\AcademyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TravelExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\freeeController;
use App\Http\Controllers\SpreadSheetController;

/**
 * Sign In
 */
Route::prefix('login')
    ->controller(LoginController::class)
    ->group(function () {
        Route::get('/', 'index')->name('login');
        Route::post('/', 'auth')->name('loginAuth');
        Route::post('/login-nfc', 'auth_with_nfc')->name('login-nfc');
        Route::post('/login-metamask', 'auth_with_metamask')->name('login-metamask');
    });

/**
 * To be not authenticated is required to access
 */
Route::get('/downloader', function () {
    // Get files on uploads directory
    $files = array_diff(scandir(storage_path('app/public/uploads/')), array('.', '..'));
    $files = array_values($files);

    return view('files.index', compact('files'));
})->name('files.index');

Route::post('/lumo-academy/register', [AcademyController::class, 'register'])->name('academy.register');

/**
 * To be authenticated is required
 */
Route::middleware('login')->group(function () {

    // spreadsheet test
    Route::get('/sheet', [SpreadSheetController::class, 'store']);

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/settings', function () {
        return view('settings/index');
    })->name('settings.index');

    /**
     * Order
     */
    Route::prefix('orders')
        ->controller(OrderController::class)
        ->group(function () {
            // GET
            Route::get('/', 'index')->name('orders.index');
            Route::get('/create', 'create')->name('orders.create');
            Route::get('/edit/{id}', 'edit')->name('orders.edit');
            Route::get('/pdf/{id}', 'pdf')->name('orders.pdf');
            Route::get('/csv/{id}', 'csv')->name('orders.csv');
            Route::get('/view/{id}', 'view')->name('orders.view');
            Route::get('/delete/{id}', 'delete')->name('orders.delete');
            Route::get('/restore/{id}', 'restore')->name('orders.restore');
            Route::get('/trash', 'trash')->name('orders.trash');
            // POST
            Route::post('/create', 'create');
            Route::post('/edit/{id}', 'edit');
            Route::post('/set-status', 'setStatus');
        });

    /**
     * Trip
     */
    Route::prefix('trips')
        ->controller(TravelController::class)
        ->group(function () {
            // GET
            Route::get('/', 'index')->name('trips.index');
            Route::get('/create', 'create')->name('trips.create');
            Route::get('/pdf/{id}', 'pdf')->name('trips.pdf');
            // POST
            Route::post('/create', 'create');
            Route::post('/import', 'csvImport')->name('trips.import');
        });


    /**
     * Expense
     */
    Route::prefix('expenses')
        ->controller(TravelExpenseController::class)
        ->group(function () {
            // GET
            Route::get('/', 'index')->name('expenses.index');
            Route::get('/create', 'create')->name('expenses.create');
            Route::get('/edit/{id}', 'edit')->name('expenses.edit');
            Route::get('/pdf/{id}', 'pdf')->name('expenses.pdf');
            // POST
            Route::post('/create', 'create');
            Route::post('/edit/{id}', 'edit');
            Route::post('/import', 'csvImport')->name('expenses.import');
        });

    /**
     * Projects
     */
    Route::prefix('projects')
        ->controller(ProjectController::class)
        ->group(function () {
            // GET
            Route::get('/', 'index')->name('projects.index');
            Route::get('/analysis', 'analysis')->name('projects.analysis');
            Route::get('/create', 'create')->name('projects.create');
            Route::get('/edit/{id}', 'edit')->name('projects.edit');
            // POST
            Route::post('/create', 'create');
            Route::post('/edit/{id}', 'edit');
        });

    /**
     * Customer
     */
    Route::prefix('customers')
        ->controller(CustomerController::class)
        ->group(function () {
            // GET
            Route::get('/', 'index')->name('customers.index');
            Route::get('/create', 'create')->name('customers.create');
            Route::get('/edit/{id}', 'edit')->name('customers.edit');
            // POST
            Route::post('/create', 'create');
            Route::post('/edit/{id}', 'edit');
        });

    /**
     * User
     */
    Route::prefix('users')
        ->controller(UserController::class)
        ->group(function () {
            // GET
            Route::get('/', 'index')->name('users.index');
            Route::get('/create', 'create')->name('users.create');
            Route::get('/edit/{id}', 'edit')->name('users.edit');
            Route::get('/2fa/{id}', 'register_2fa_auth')->name('users.2fa');
            // POST
            Route::post('/create', 'create');
            Route::post('/edit/{id}', 'edit');
            Route::post('/2fa/{id}', 'register_2fa_auth');
        });

    /**
     * Upload and download
     */
    // Route::get('/downloader', function() {
    //     // Get files on uploads directory
    //     $files = array_diff(scandir(storage_path('app/public/uploads/')), array('.', '..'));
    //     $files = array_values($files);

    //     return view('files.index', compact('files'));
    // })->name('files.index');
    Route::get('/downloader/{file}', function ($file) {
        return response()->download(storage_path('app/public/uploads/' . $file));
    })->name('files.download');
    Route::post('/uploader', function () {
        $sender_name = $_POST['name'];
        $sender_email = $_POST['email'];
        $sender_file = $_FILES['file'];

        // Save file on storage folder
        $file_name = $sender_name . "_" . $sender_file['name'];
        $file_tmp_name = $sender_file['tmp_name'];
        $file_path = storage_path('app/public/uploads/' . $file_name);

        move_uploaded_file($file_tmp_name, $file_path);

        // Redirect if success with success message
        return redirect()->route('files.index')->with('success', 'File uploaded successfully!');
    })->name('files.upload');
    Route::delete('/downloader/delete/{file}', function ($file) {
        unlink(storage_path('app/public/uploads/' . $file));
        return redirect()->route('files.index');
    })->name('files.delete');

    /**
     * Lumo Academy
     */
    Route::get('/lumo-academy/', [AcademyController::class, 'index'])->name('academy.index');

    /**
     * freee Basic API
     */
    Route::prefix('freee')
        ->controller(freeeController::class)
        ->group(function () {
            Route::get('/by-code/{code}', 'getAccessTokenByAuthCode')->name('freee.getTokenByCode');
            Route::get('/token/{refresh_token}', 'getTokenByRefreshToken')->name('freee.getTokenByRefreshToken');
            Route::get('/companies/', 'getCompanies')->name('freee.getCompanies');
            Route::get('/walletables/', 'getWalletables')->name('freee.getWalletables');
            Route::get('/partners/', 'getPartners')->name('freee.getPartners');
            Route::get('/quotations/', 'getQuotations')->name('freee.getQuotations');
            Route::get('/invoices/', 'getInvoices')->name('freee.getInvoices');
        });
});
