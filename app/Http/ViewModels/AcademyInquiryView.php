<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Academy\Entity\AcademyInquiry;
use Illuminate\Support\Collection;

final readonly class AcademyInquiryView
{
    private function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public string $inquiry,
    ) {
    }

    public static function fromEntity(AcademyInquiry $inquiry): self
    {
        return new self(
            id: $inquiry->id(),
            name: $inquiry->name(),
            email: (string) $inquiry->email(),
            inquiry: $inquiry->inquiry(),
        );
    }

    /**
     * @param list<AcademyInquiry> $inquiries
     * @return Collection<int, self>
     */
    public static function collection(array $inquiries): Collection
    {
        return collect($inquiries)->map(self::fromEntity(...))->values();
    }
}
