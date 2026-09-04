<?php

declare(strict_types=1);

namespace App\Domain\Report\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 時刻 (日付を持たない)。始業・終業のように「その日の何時何分か」だけを表す。
 *
 * reports.start_time / end_time は time 型で、MySQL は "09:00:00"、
 * sqlite は入れた文字列をそのまま返すなど表現がぶれる。境界でここに寄せる。
 */
final readonly class TimeOfDay implements \Stringable
{
    private const MINUTES_PER_DAY = 24 * 60;

    private function __construct(public int $minutesOfDay)
    {
    }

    /** @throws InvalidValueException */
    public static function fromString(string $value): self
    {
        // "09:00" / "09:00:00" のどちらでも受ける
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', trim($value), $matches) !== 1) {
            throw new InvalidValueException(sprintf('時刻の形式が不正です: %s', $value));
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];

        if ($hours > 23 || $minutes > 59) {
            throw new InvalidValueException(sprintf('存在しない時刻です: %s', $value));
        }

        return new self($hours * 60 + $minutes);
    }

    /** null / 空文字はそのまま null で返す (始業・終業は任意入力のため) */
    public static function parseNullable(mixed $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return new self((int) $value->format('H') * 60 + (int) $value->format('i'));
        }

        if (!is_string($value)) {
            throw new InvalidValueException('時刻として解釈できません。');
        }

        return self::fromString($value);
    }

    /**
     * この時刻から相手の時刻までの分数。
     *
     * 相手が自分より前 (または同じ) なら日を跨いだものとして 24 時間を足す。
     * 22:00 出社 - 02:00 退社のような夜勤で、負の勤務時間にならないようにする。
     */
    public function minutesUntil(self $other): int
    {
        $diff = $other->minutesOfDay - $this->minutesOfDay;

        return $diff > 0 ? $diff : $diff + self::MINUTES_PER_DAY;
    }

    public function equals(self $other): bool
    {
        return $this->minutesOfDay === $other->minutesOfDay;
    }

    /** "09:00" 形式。DB へもこの形で書く */
    public function format(): string
    {
        return sprintf('%02d:%02d', intdiv($this->minutesOfDay, 60), $this->minutesOfDay % 60);
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
