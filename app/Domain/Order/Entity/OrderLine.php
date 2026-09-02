<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

use App\Domain\Order\ValueObject\TaxRate;
use App\Domain\Shared\ValueObject\Money;

/**
 * 発注書の明細 1 行。
 *
 * 金額 (order_details.price) は**税込**で保存する。列見出しが「金額」で、
 * 小計・消費税・合計を別行に出しているため。フロント (order-form.ts) の
 * calcAll() と同じ計算式をここに置いており、保存される金額はこの計算結果が正。
 * フォームから送られてくる price[] は画面表示用であり、信用しない。
 */
final readonly class OrderLine
{
    public function __construct(
        public ?int $id,
        public string $itemName,
        public int $quantity,
        public ?string $unit,
        public Money $unitCost,
        public TaxRate $taxRate,
    ) {
    }

    /** 税抜金額 (数量 × 単価) */
    public function subtotal(): Money
    {
        return $this->unitCost->multiply($this->quantity);
    }

    /** 消費税額 */
    public function tax(): Money
    {
        return $this->subtotal()->multiply($this->taxRate->rate());
    }

    /** 税込金額。order_details.price に保存する値 */
    public function total(): Money
    {
        return $this->subtotal()->add($this->tax());
    }
}
