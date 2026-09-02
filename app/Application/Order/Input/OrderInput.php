<?php

declare(strict_types=1);

namespace App\Application\Order\Input;

use App\Application\Shared\DateParser;
use App\Domain\Order\Entity\OrderLine;
use App\Domain\Order\ValueObject\OrderNo;

/**
 * 発注書の作成・更新の入力。
 *
 * HTTP の形 (item_name[] のような配列パラメータ) を知っているのは
 * Presentation 層の FormRequest で、ここには整形済みの値が渡る。
 */
final readonly class OrderInput
{
    /** @param list<OrderLineInput> $lines */
    public function __construct(
        public int $customerId,
        public ?string $responsible,
        public ?string $honorTitle,
        public mixed $issuedDate,
        public mixed $expDate,
        public string $orderNo,
        public ?string $title,
        public ?string $remarks,
        public array $lines,
    ) {
    }

    public function orderNoValue(): OrderNo
    {
        return OrderNo::fromString($this->orderNo);
    }

    public function issuedDateValue(): \DateTimeImmutable
    {
        return DateParser::parse($this->issuedDate, '発行日');
    }

    public function expDateValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->expDate, '有効期限');
    }

    /**
     * 未入力の行を落として明細に変換する。
     *
     * @return list<OrderLine>
     */
    public function toOrderLines(): array
    {
        $lines = [];

        foreach ($this->lines as $line) {
            if ($line->isBlank()) {
                continue;
            }

            $lines[] = $line->toOrderLine();
        }

        return $lines;
    }
}
