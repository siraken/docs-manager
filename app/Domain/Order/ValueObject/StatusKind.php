<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 一覧のステータスピルがどちらの状態を切り替えるか。
 * フロント (status.ts) が JSON の "type" として送ってくる値と対応する。
 */
enum StatusKind: string
{
    case Issue = 'issued';
    case Order = 'ordered';

    public static function fromString(?string $value): self
    {
        return self::tryFrom((string) $value)
            ?? throw new InvalidValueException(sprintf('未知のステータス種別です: %s', var_export($value, true)));
    }
}
