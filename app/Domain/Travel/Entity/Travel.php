<?php

declare(strict_types=1);

namespace App\Domain\Travel\Entity;

use App\Domain\Shared\ValueObject\Money;

/**
 * 出張申請。
 */
final class Travel
{
    private function __construct(
        private ?int $id,
        private string $relId,
        private string $destination,
        private string $purpose,
        private Money $price,
        private \DateTimeImmutable $dateFrom,
        private \DateTimeImmutable $dateTo,
        private \DateTimeImmutable $applyDate,
        private string $applyPerson,
    ) {
    }

    public static function create(
        string $relId,
        string $destination,
        string $purpose,
        Money $price,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        \DateTimeImmutable $applyDate,
        string $applyPerson,
    ): self {
        return new self(null, $relId, $destination, $purpose, $price, $dateFrom, $dateTo, $applyDate, $applyPerson);
    }

    public static function reconstitute(
        int $id,
        string $relId,
        string $destination,
        string $purpose,
        Money $price,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        \DateTimeImmutable $applyDate,
        string $applyPerson,
    ): self {
        return new self($id, $relId, $destination, $purpose, $price, $dateFrom, $dateTo, $applyDate, $applyPerson);
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function relId(): string
    {
        return $this->relId;
    }

    /** 出張先 (travels.dir) */
    public function destination(): string
    {
        return $this->destination;
    }

    public function purpose(): string
    {
        return $this->purpose;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function dateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function dateTo(): \DateTimeImmutable
    {
        return $this->dateTo;
    }

    public function applyDate(): \DateTimeImmutable
    {
        return $this->applyDate;
    }

    public function applyPerson(): string
    {
        return $this->applyPerson;
    }
}
