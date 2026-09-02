<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 金額。日本円のみを扱うため通貨は持たず、最小単位 (円) の整数で保持する。
 *
 * 税額の計算で小数が出るため、内部計算は float で行い境界で丸める。
 * 丸めは切り捨て (floor) —— 発注書の消費税は端数切り捨てが一般的で、
 * フロント (order-form.ts) も同じ値になるよう Presentation 側で表示する。
 *
 * TODO: 端数処理は本来 settings (companies.tax_round) から引くべき設定値。
 *       マスタ画面が未実装のため、いまは切り捨て固定にしている。
 */
final readonly class Money implements \Stringable, \JsonSerializable
{
    private function __construct(public int $amount)
    {
    }

    public static function fromInt(int $amount): self
    {
        return new self($amount);
    }

    /**
     * 文字列や null を含む外部入力から作る。
     * 数値として読めない値は 0 として扱う (未入力の明細行が空文字で届くため)。
     */
    public static function fromNumeric(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::zero();
        }

        if (!is_numeric($value)) {
            throw new InvalidValueException(sprintf('金額として解釈できません: %s', var_export($value, true)));
        }

        return new self((int) floor((float) $value));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function multiply(int|float $multiplier): self
    {
        return new self((int) floor($this->amount * $multiplier));
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function format(): string
    {
        return number_format($this->amount);
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }

    public function jsonSerialize(): int
    {
        return $this->amount;
    }
}
