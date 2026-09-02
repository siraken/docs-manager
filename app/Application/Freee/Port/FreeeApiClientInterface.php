<?php

declare(strict_types=1);

namespace App\Application\Freee\Port;

use App\Application\Freee\Input\FreeeResource;

/**
 * freee 会計 API のクライアント。実装は Infrastructure 層。
 *
 * 移行前はコントローラのメソッドが直接 cURL を叩いており、レスポンスを
 * 生の文字列のまま返していた (エラー判定も無し)。
 */
interface FreeeApiClientInterface
{
    /** 認可コードをアクセストークンに交換する @return array<string, mixed> */
    public function exchangeAuthorizationCode(string $code): array;

    /** リフレッシュトークンでアクセストークンを取り直す @return array<string, mixed> */
    public function refreshAccessToken(?string $refreshToken): array;

    /** @return array<string, mixed> */
    public function fetch(FreeeResource $resource): array;
}
