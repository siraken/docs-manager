<?php

declare(strict_types=1);

namespace App\Application\User\Port;

use App\Domain\User\ValueObject\TwoFactorSecret;

/**
 * 二段階認証の設定中シークレットの一時保管。
 *
 * QR を表示してから認証コードを入力してもらうまでの間、シークレットを
 * どこかに置く必要がある。ユーザーのレコードに直接書くと「コードを一度も
 * 検証していないのに 2FA が有効になっている」状態ができてしまうため、
 * 検証が通るまではこちら (実装はセッション) に置く。
 */
interface TwoFactorSetupStoreInterface
{
    public function put(int $userId, TwoFactorSecret $secret): void;

    public function get(int $userId): ?TwoFactorSecret;

    public function forget(int $userId): void;
}
