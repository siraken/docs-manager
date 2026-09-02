<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * ハッシュ済みのパスワード。平文をこの型に入れることはできない。
 * ハッシュ化と照合は PasswordHasherInterface の実装が行う。
 */
final readonly class HashedPassword implements \Stringable
{
    private function __construct(public string $value)
    {
    }

    /** ハッシュ化済みの文字列から作る。実装は PasswordHasherInterface からのみ呼ぶこと */
    public static function fromHash(string $hash): self
    {
        if ($hash === '') {
            throw new InvalidValueException('パスワードハッシュが空です。');
        }

        return new self($hash);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
