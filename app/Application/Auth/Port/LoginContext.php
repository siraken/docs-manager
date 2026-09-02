<?php

declare(strict_types=1);

namespace App\Application\Auth\Port;

/**
 * ログイン通知に載せる、リクエスト由来の情報。
 * ユースケースが Illuminate\Http\Request を知らずに済むようにするための入れ物。
 */
final readonly class LoginContext
{
    public function __construct(
        public ?string $ipAddress,
        public ?string $userAgent,
        public \DateTimeImmutable $occurredAt,
    ) {
    }
}
