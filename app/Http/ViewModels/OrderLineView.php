<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Order\Entity\OrderLine;

final readonly class OrderLineView
{
    private function __construct(
        public string $itemName,
        public int $quantity,
        public ?string $unit,
        public int $unitCost,
        public int $taxId,
        public string $taxLabel,
        public int $total,
    ) {
    }

    public static function fromEntity(OrderLine $line): self
    {
        return new self(
            itemName: $line->itemName,
            quantity: $line->quantity,
            unit: $line->unit,
            unitCost: $line->unitCost->amount,
            taxId: $line->taxRate->value,
            taxLabel: $line->taxRate->label(),
            total: $line->total()->amount,
        );
    }
}
