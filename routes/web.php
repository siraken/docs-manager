<?php

declare(strict_types=1);

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FreeeController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpreadSheetController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TravelExpenseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| 認証は Illuminate\Auth ではなく独自セッション。保護したいルートは
| Route::middleware('login') で囲う ('auth' ではない)。
|
| フォーム表示と保存は同じ URL で GET / POST に分かれる。移行前は 1 つの
| メソッドが $request->isMethod('POST') で分岐していたが、FormRequest による
| 検証を効かせるためにメソッドを分けている (GET でフォーム表示のルールが
| 走ってしまうため)。ルート名は GET 側に付いていて、移行前から変わらない。
|
*/

/**
 * Sign In
 */
Route::prefix('login')
    ->controller(LoginController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('login');
        Route::post('/', 'auth')->name('loginAuth');
        Route::post('/login-nfc', 'authWithNfc')->name('login-nfc');
        Route::post('/login-metamask', 'authWithMetamask')->name('login-metamask');
    });

/**
 * 認証不要
 *
 * ファイルの受け渡しは取引先にも使ってもらう想定のため認証を掛けていない。
 * 一覧はログイン中だけ出る (FileController::index と files/index.blade.php)。
 */
Route::get('/downloader', [FileController::class, 'index'])->name('files.index');
Route::post('/uploader', [FileController::class, 'upload'])->name('files.upload');

Route::post('/lumo-academy/register', [AcademyController::class, 'register'])->name('academy.register');

/**
 * 要認証
 */
Route::middleware('login')->group(function (): void {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Google スプレッドシート連携の疎通確認
    Route::get('/sheet', [SpreadSheetController::class, 'store'])->name('sheet.store');

    /**
     * 設定 (自社情報)
     */
    Route::prefix('settings')
        ->controller(SettingController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('settings.index');
            Route::post('/', 'update');
        });

    /**
     * 発注書
     */
    Route::prefix('orders')
        ->controller(OrderController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('orders.index');
            Route::get('/trash', 'trash')->name('orders.trash');
            Route::get('/create', 'create')->name('orders.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('orders.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::get('/view/{id}', 'show')->name('orders.view')->whereNumber('id');
            Route::get('/pdf/{id}', 'pdf')->name('orders.pdf')->whereNumber('id');
            Route::get('/csv/{id}', 'csv')->name('orders.csv')->whereNumber('id');
            Route::get('/delete/{id}', 'delete')->name('orders.delete')->whereNumber('id');
            Route::get('/restore/{id}', 'restore')->name('orders.restore')->whereNumber('id');
            Route::post('/set-status', 'setStatus')->name('orders.setStatus');
        });

    /**
     * 出張申請
     */
    Route::prefix('trips')
        ->controller(TravelController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('trips.index');
            Route::get('/create', 'create')->name('trips.create');
            Route::post('/create', 'store');
            Route::get('/view/{id}', 'show')->name('trips.view')->whereNumber('id');
            Route::get('/pdf/{id}', 'pdf')->name('trips.pdf')->whereNumber('id');
            Route::post('/import', 'csvImport')->name('trips.import');
        });

    /**
     * 出張旅費精算
     */
    Route::prefix('expenses')
        ->controller(TravelExpenseController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('expenses.index');
            Route::get('/create', 'create')->name('expenses.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('expenses.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::get('/view/{id}', 'show')->name('expenses.view')->whereNumber('id');
            Route::get('/pdf/{id}', 'pdf')->name('expenses.pdf')->whereNumber('id');
            Route::post('/import', 'csvImport')->name('expenses.import');
        });

    /**
     * 案件
     */
    Route::prefix('projects')
        ->controller(ProjectController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('projects.index');
            Route::get('/analysis', 'analysis')->name('projects.analysis');
            Route::get('/create', 'create')->name('projects.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('projects.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
        });

    /**
     * 勤務報告 (in-house-timecard-app から移植)
     */
    Route::prefix('reports')
        ->controller(ReportController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('reports.index');
            Route::get('/create', 'create')->name('reports.create');
            Route::post('/create', 'store');
            Route::get('/view/{id}', 'show')->name('reports.view')->whereNumber('id');
            Route::get('/edit/{id}', 'edit')->name('reports.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('reports.delete')->whereNumber('id');
        });

    /**
     * 契約 (in-house-timecard-app から移植)
     */
    Route::prefix('contracts')
        ->controller(ContractController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('contracts.index');
            Route::get('/create', 'create')->name('contracts.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('contracts.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('contracts.delete')->whereNumber('id');
        });

    /**
     * 仕訳帳 (会計)
     *
     * in-house-timecard-app に CakePHP 時代のテンプレートだけが残っていた
     * 機能を、参考にしつつ新規に作り直したもの。
     */
    Route::prefix('journal')
        ->controller(JournalController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('journal.index');
            Route::get('/trial-balance', 'trialBalance')->name('journal.trialBalance');
            Route::get('/create', 'create')->name('journal.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('journal.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('journal.delete')->whereNumber('id');
        });

    /**
     * 勘定科目 (仕訳帳のマスタ)
     */
    Route::prefix('accounts')
        ->controller(AccountController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('accounts.index');
            Route::get('/create', 'create')->name('accounts.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('accounts.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('accounts.delete')->whereNumber('id');
        });

    /**
     * 社内研修 (novalumo/e-learning を参考に新規開発)
     */
    Route::prefix('enrollments')
        ->controller(EnrollmentController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('enrollments.index');
            Route::get('/create', 'create')->name('enrollments.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('enrollments.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('enrollments.delete')->whereNumber('id');
        });

    /**
     * 講座 (受講記録のマスタ)
     */
    Route::prefix('courses')
        ->controller(CourseController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('courses.index');
            Route::get('/create', 'create')->name('courses.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('courses.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('courses.delete')->whereNumber('id');
        });

    /**
     * 提出物 (社内研修の課題)
     */
    Route::prefix('submissions')
        ->controller(SubmissionController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('submissions.index');
            Route::get('/create', 'create')->name('submissions.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('submissions.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('submissions.delete')->whereNumber('id');
        });

    /**
     * 課題 (提出物のマスタ)
     */
    Route::prefix('assignments')
        ->controller(AssignmentController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('assignments.index');
            Route::get('/create', 'create')->name('assignments.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('assignments.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('assignments.delete')->whereNumber('id');
        });

    /**
     * チャット (novalumo/e-learning を参考に作り直したもの)
     *
     * 全員が読み書きする 1 つのルーム。更新は専用の JSON エンドポイントでは
     * なく Inertia の部分リロードで行うため、取得用のルートは無い。
     */
    Route::prefix('chat')
        ->controller(ChatController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('chat.index');
            Route::post('/', 'store');
            Route::delete('/delete/{id}', 'destroy')->name('chat.delete')->whereNumber('id');
        });

    /**
     * 顧客
     */
    Route::prefix('customers')
        ->controller(CustomerController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('customers.index');
            Route::get('/create', 'create')->name('customers.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('customers.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
        });

    /**
     * ユーザー
     */
    Route::prefix('users')
        ->controller(UserController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('users.index');
            Route::get('/create', 'create')->name('users.create');
            Route::post('/create', 'store');
            Route::get('/edit/{id}', 'edit')->name('users.edit')->whereNumber('id');
            Route::post('/edit/{id}', 'update')->whereNumber('id');
            Route::delete('/delete/{id}', 'destroy')->name('users.delete')->whereNumber('id');
            Route::post('/nfc/{id}', 'registerNfc')->name('users.nfc')->whereNumber('id');
            Route::get('/2fa/{id}', 'twoFactor')->name('users.2fa')->whereNumber('id');
            Route::post('/2fa/{id}', 'confirmTwoFactor')->whereNumber('id');
        });

    /**
     * ファイル (ダウンロードと削除はログイン中のみ)
     */
    Route::get('/downloader/{file}', [FileController::class, 'download'])->name('files.download');
    Route::delete('/downloader/delete/{file}', [FileController::class, 'delete'])->name('files.delete');

    /**
     * Lumo Academy (問い合わせ一覧)
     */
    Route::get('/lumo-academy', [AcademyController::class, 'index'])->name('academy.index');

    /**
     * freee 会計 API
     */
    Route::prefix('freee')
        ->controller(FreeeController::class)
        ->group(function (): void {
            Route::get('/by-code/{code}', 'getAccessTokenByAuthCode')->name('freee.getTokenByCode');
            Route::get('/token/{refresh_token}', 'getTokenByRefreshToken')->name('freee.getTokenByRefreshToken');
            Route::get('/companies', 'getCompanies')->name('freee.getCompanies');
            Route::get('/walletables', 'getWalletables')->name('freee.getWalletables');
            Route::get('/partners', 'getPartners')->name('freee.getPartners');
            Route::get('/quotations', 'getQuotations')->name('freee.getQuotations');
            Route::get('/invoices', 'getInvoices')->name('freee.getInvoices');
        });
});
