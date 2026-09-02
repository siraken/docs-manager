<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Travel\Entity\Travel;
use Illuminate\Support\Collection;

final readonly class TravelView
{
    private function __construct(
        public ?int $id,
        public string $relId,
        public string $destination,
        public string $purpose,
        public int $price,
        public string $priceLabel,
        public string $dateFrom,
        public string $dateTo,
        public string $applyDate,
        public string $applyPerson,
    ) {
    }

    public static function fromEntity(Travel $travel): self
    {
        return new self(
            id: $travel->id(),
            relId: $travel->relId(),
            destination: $travel->destination(),
            purpose: $travel->purpose(),
            price: $travel->price()->amount,
            priceLabel: $travel->price()->format(),
            dateFrom: $travel->dateFrom()->format('Y-m-d'),
            dateTo: $travel->dateTo()->format('Y-m-d'),
            applyDate: $travel->applyDate()->format('Y-m-d'),
            applyPerson: $travel->applyPerson(),
        );
    }

    /**
     * @param list<Travel> $travels
     * @return Collection<int, self>
     */
    public static function collection(array $travels): Collection
    {
        return collect($travels)->map(self::fromEntity(...))->values();
    }

    /** 一覧で目的を短く出す */
    public function shortPurpose(int $width = 30): string
    {
        return mb_strimwidth($this->purpose, 0, $width, '...');
    }
}
