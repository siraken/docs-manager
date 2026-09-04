<?php

declare(strict_types=1);

namespace App\Domain\Learning\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 講座を修了すると得られるポイント。
 *
 * 参考にした e-learning の classes.exp をそのまま引き継いだ概念。あちらは
 * 整数カラムがあるだけで、加算する処理も表示する画面も無かった。
 */
final readonly class ExperiencePoint implements \Stringable, \JsonSerializable
{
    private function __construct(public int $value)
    {
    }

    /** @throws InvalidValueException */
    public static function of(int $value): self
    {
        if ($value < 0) {
            throw new InvalidValueException('獲得ポイントに負の値は指定できません。');
        }

        return new self($value);
    }

    /** 未入力は 0 として扱う (ポイントを設定しない講座もある) */
    public static function fromNullable(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::zero();
        }

        if (!is_numeric($value)) {
            throw new InvalidValueException(sprintf('獲得ポイントとして解釈できません: %s', var_export($value, true)));
        }

        return self::of((int) $value);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function format(): string
    {
        return number_format($this->value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
