<?php

declare(strict_types=1);

namespace App\Application\Shared;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * フォームや CSV から届く日付文字列を DateTimeImmutable に直す。
 *
 * 時刻を持たない項目 (発行日・出張日など) ばかりなので、常に 00:00:00 に丸める。
 * これがないと "同じ日なのに比較で一致しない" という事故が起きる。
 */
final class DateParser
{
    /** @throws InvalidValueException */
    public static function parse(mixed $value, string $label = '日付'): \DateTimeImmutable
    {
        $parsed = self::parseNullable($value, $label);

        if ($parsed === null) {
            throw new InvalidValueException(sprintf('%sが指定されていません。', $label));
        }

        return $parsed;
    }

    /** @throws InvalidValueException */
    public static function parseNullable(mixed $value, string $label = '日付'): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value)->setTime(0, 0);
        }

        if (!is_string($value)) {
            throw new InvalidValueException(sprintf('%sとして解釈できません。', $label));
        }

        try {
            return (new \DateTimeImmutable($value))->setTime(0, 0);
        } catch (\Exception) {
            throw new InvalidValueException(sprintf('%sの形式が不正です: %s', $label, $value));
        }
    }
}
