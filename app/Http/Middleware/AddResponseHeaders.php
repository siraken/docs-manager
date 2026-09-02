<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * レスポンスに Server ヘッダを足す。
 *
 * 値の設定に $response->header() ではなく $response->headers->set() を使う。
 * 前者は Illuminate\Http\Response のメソッドで、ファイルのダウンロードで返る
 * BinaryFileResponse や StreamedResponse には存在しない。移行前はこれが原因で
 * /downloader/{file} が必ず 500 になっていた
 * (Call to undefined method BinaryFileResponse::header())。
 * headers プロパティは Symfony の全レスポンスが持つ。
 */
class AddResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Server', 'Novalumo Server');

        return $response;
    }
}
