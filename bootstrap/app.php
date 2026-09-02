<?php

use App\Presentation\Http\Middleware\AddResponseHeaders;
use App\Presentation\Http\Middleware\LoginMiddleware;
use App\Domain\Shared\Exception\DomainException;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Presentation\Http\Support\Flash;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        // ドメイン層はフレームワークを知らないので、HTTP への変換はここで行う。

        // 対象が見つからない → 404
        $exceptions->map(fn (EntityNotFoundException $e): NotFoundHttpException => new NotFoundHttpException($e->getMessage(), $e));

        // 業務ルール違反 (値の不正、削除できない等) → 元の画面へ戻してメッセージを出す。
        // API / fetch からの呼び出しには JSON で返す。
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with(Flash::error($e->getMessage()));
        });
    })->create();
