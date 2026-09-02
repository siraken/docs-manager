<?php

use App\Http\Middleware\AddResponseHeaders;
use App\Http\Middleware\LoginMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // レスポンスに Server ヘッダを足すだけの独自ミドルウェア
        $middleware->web(append: [
            AddResponseHeaders::class,
        ]);

        // Laravel 11 以降、api グループの既定は SubstituteBindings だけで
        // throttle はオプトインになった。旧 app/Http/Kernel.php では
        // 'throttle:api' が有効だったので、明示的に復元しておく。
        // 参照する 'api' リミッター (60/min) は AppServiceProvider で定義している。
        $middleware->throttleApi();

        // このアプリは Illuminate\Auth を使わず素のセッションで認証している。
        // 認証が必要なルートは Route::middleware('login') で囲う。
        $middleware->alias([
            'login' => LoginMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
