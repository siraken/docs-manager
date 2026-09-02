<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

final readonly class EmailAddress implements \Stringable
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(?string $value): self
    {
        $value = trim((string) $value);

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidValueException(sprintf('メールアドレスの形式が不正です: %s', $value));
        }

        return new self($value);
    }

    /**
     * 永続化層からの復元。形式検証を行わない。
     *
     * 既存 DB には移行前に保存された行があり、そこに形式不正なアドレスが
     * 混じっていても一覧表示が落ちないようにするため。
     *
     * TODO: 既存データの移行後に廃止し、fromString() へ一本化すること。
     */
    public static function fromStorage(string $value): self
    {
        return new self(trim($value));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
