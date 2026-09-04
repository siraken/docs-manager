<?php

declare(strict_types=1);

namespace App\Domain\Report\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 勤務時間。分の整数で保持する。
 *
 * 移植元は float の時間 (work_time) を手入力させていた。始業・終業も別に
 * 入力させていたため、両者が食い違っても誰も気付けない状態だった。
 * ここでは Money が円を整数で持つのと同じ理由で分の整数にしてある
 * ("7.4 時間" のような丸め誤差を持ち込まないため)。
 */
final readonly class WorkTime implements \Stringable, \JsonSerializable
{
    private function __construct(public int $minutes)
    {
    }

    /** @throws InvalidValueException */
    public static function fromMinutes(int $minutes): self
    {
        if ($minutes < 0) {
            throw new InvalidValueException('勤務時間に負の値は指定できません。');
        }

        return new self($minutes);
    }

    /**
     * 時間 (小数可) から作る。フォームの「7.5」や移植元の float カラムを受ける。
     * 分に満たない端数は切り捨てる。
     */
    public static function fromHours(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::zero();
        }

        if (!is_numeric($value)) {
            throw new InvalidValueException(sprintf('勤務時間として解釈できません: %s', var_export($value, true)));
        }

        return self::fromMinutes((int) floor((float) $value * 60));
    }

    /**
     * 始業から終業までを勤務時間とする。日跨ぎは TimeOfDay 側で吸収される。
     *
     * 休憩時間の控除は移植元にも無かったため行っていない。
     * TODO: 休憩時間を入力できるようにして差し引く。
     */
    public static function between(TimeOfDay $start, TimeOfDay $end): self
    {
        return new self($start->minutesUntil($end));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->minutes + $other->minutes);
    }

    public function isZero(): bool
    {
        return $this->minutes === 0;
    }

    public function equals(self $other): bool
    {
        return $this->minutes === $other->minutes;
    }

    /** 集計や表示に使う時間数。小数第 2 位まで */
    public function hours(): float
    {
        return round($this->minutes / 60, 2);
    }

    /** "7:30" 形式 */
    public function format(): string
    {
        return sprintf('%d:%02d', intdiv($this->minutes, 60), $this->minutes % 60);
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function jsonSerialize(): int
    {
        return $this->minutes;
    }
}
