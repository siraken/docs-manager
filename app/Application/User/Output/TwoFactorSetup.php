<?php

declare(strict_types=1);

namespace App\Application\User\Output;

use App\Domain\User\ValueObject\TwoFactorSecret;

/**
 * 二段階認証の設定画面に渡す情報。
 *
 * TODO: QR コード画像は未生成。uri を認証アプリに手入力してもらう前提になっている。
 *       画像が要るなら QR エンコーダを Infrastructure 層のポート実装として足すこと。
 */
final readonly class TwoFactorSetup
{
    public function __construct(
        public TwoFactorSecret $secret,
        public string $uri,
        public bool $alreadyEnabled,
    ) {
    }
}
