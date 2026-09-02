<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\HashedPassword;
use Illuminate\Support\Facades\Hash;

/**
 * Laravel の Hash ファサードに委譲するハッシャ。
 * 方式 (bcrypt / argon2) は config/hashing.php ではなくフレームワークの既定に従う。
 */
final class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): HashedPassword
    {
        return HashedPassword::fromHash(Hash::make($plainPassword));
    }

    public function verify(string $plainPassword, HashedPassword $hashedPassword): bool
    {
        if ($plainPassword === '') {
            return false;
        }

        return Hash::check($plainPassword, $hashedPassword->value);
    }
}
