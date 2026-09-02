<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\User\Port\TwoFactorSetupStoreInterface;
use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\User\ValueObject\TwoFactorSecret;
use Illuminate\Session\Store;

/**
 * 設定中の 2FA シークレットをセッションに一時保管する。
 *
 * セッションの serialization が json なので、値オブジェクトではなく
 * 文字列で出し入れする。
 */
final readonly class SessionTwoFactorSetupStore implements TwoFactorSetupStoreInterface
{
    private const KEY_PREFIX = 'two_factor_setup.';

    public function __construct(private Store $session)
    {
    }

    public function put(int $userId, TwoFactorSecret $secret): void
    {
        $this->session->put(self::KEY_PREFIX . $userId, $secret->value);
    }

    public function get(int $userId): ?TwoFactorSecret
    {
        $value = $this->session->get(self::KEY_PREFIX . $userId);

        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            return TwoFactorSecret::fromString($value);
        } catch (InvalidValueException) {
            return null;
        }
    }

    public function forget(int $userId): void
    {
        $this->session->forget(self::KEY_PREFIX . $userId);
    }
}
