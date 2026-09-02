<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\ValueObject\HashedPassword;

/**
 * パスワードのハッシュ化と照合。
 *
 * ドメイン層は具体的なハッシュ方式 (bcrypt / argon2) を知らない。
 * 実装は Infrastructure 層に置き、Laravel の Hash ファサードに委譲する。
 */
interface PasswordHasherInterface
{
    public function hash(string $plainPassword): HashedPassword;

    public function verify(string $plainPassword, HashedPassword $hashedPassword): bool;
}
