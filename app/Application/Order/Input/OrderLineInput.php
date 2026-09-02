<?php

declare(strict_types=1);

namespace App\Application\Order\Input;

use App\Domain\Order\Entity\OrderLine;
use App\Domain\Order\ValueObject\TaxRate;
use App\Domain\Shared\ValueObject\Money;

/**
 * 明細 1 行分の入力。
 *
 * 金額 (price) は受け取らない。フォームの price[] は画面表示のために
 * フロントが計算した値で、保存する金額はドメイン側で計算し直すため。
 */
final readonly class OrderLineInput
{
    public function __construct(
        public ?string $itemName,
        public mixed $quantity,
        public ?string $unit,
        public mixed $unitCost,
        public mixed $taxId,
    ) {
    }

    /** 品名が空の行は「未入力の行」とみなして保存しない */
    public function isBlank(): bool
    {
        return $this->itemName === null || trim($this->itemName) === '';
    }

    public function toOrderLine(): OrderLine
    {
        return new OrderLine(
            id: null,
            itemName: (string) $this->itemName,
            quantity: is_numeric($this->quantity) ? (int) $this->quantity : 0,
            unit: $this->unit === '' ? null : $this->unit,
            unitCost: Money::fromNumeric($this->unitCost),
            taxRate: TaxRate::fromNullable($this->taxId),
        );
    }
}
