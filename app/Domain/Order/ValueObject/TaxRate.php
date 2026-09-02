<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 明細行の税区分。
 *
 * 値は order_details.tax_id に入る整数で、選択肢は Blade の x-order-row と
 * フロントの order-form.ts (TAX_RATES) に同じものが定義されている。
 * 3 箇所に散っていた定義のうち、サーバー側の正はここ。
 *
 * TODO: 税区分は本来マスタとして持つべきもので、軽減税率の追加・廃止に
 *       追随できるようにテーブル化したい。いまは登場する 5 種類を enum で
 *       固定している (移行前は削除済みの App\Lib\Common::getTaxes が同じ配列を持っていた)。
 */
enum TaxRate: int
{
    case Standard = 1;      // 10%
    case ReducedFood = 2;   // 軽減 8%
    case EightPercent = 3;  // 8%
    case FivePercent = 4;   // 5%
    case Exempt = 5;        // 対象外

    public static function fromNullable(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::Standard;
        }

        if (!is_numeric($value)) {
            throw new InvalidValueException(sprintf('税区分として解釈できません: %s', var_export($value, true)));
        }

        return self::tryFrom((int) $value)
            ?? throw new InvalidValueException(sprintf('未知の税区分です: %s', var_export($value, true)));
    }

    /** 税率。消費税額はこの率を掛けて求める */
    public function rate(): float
    {
        return match ($this) {
            self::Standard => 0.1,
            self::ReducedFood, self::EightPercent => 0.08,
            self::FivePercent => 0.05,
            self::Exempt => 0.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Standard => '10%',
            self::ReducedFood => '軽減8%',
            self::EightPercent => '8%',
            self::FivePercent => '5%',
            self::Exempt => '対象外',
        };
    }

    /** @return array<int, string> value => label */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
