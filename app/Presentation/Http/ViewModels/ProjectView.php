<?php

declare(strict_types=1);

namespace App\Presentation\Http\ViewModels;

use App\Domain\Project\Entity\Project;
use Illuminate\Support\Collection;

final readonly class ProjectView
{
    private function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public ?int $clientId,
        public ?int $relatedTaskId,
        public ?string $startDate,
        public ?string $endDate,
        public ?string $paymentDate,
        public string $startDateLabel,
        public string $endDateLabel,
        public string $paymentDateLabel,
        public int $price,
        public string $priceLabel,
        public int $statusValue,
        public string $status,
    ) {
    }

    public static function fromEntity(Project $project): self
    {
        return new self(
            id: $project->id(),
            name: $project->name(),
            description: $project->description(),
            clientId: $project->clientId(),
            relatedTaskId: $project->relatedTaskId(),
            startDate: $project->startDate()?->format('Y-m-d'),
            endDate: $project->endDate()?->format('Y-m-d'),
            paymentDate: $project->paymentDate()?->format('Y-m-d'),
            startDateLabel: $project->startDate()?->format('Y/m/d') ?? '-',
            endDateLabel: $project->endDate()?->format('Y/m/d') ?? '-',
            paymentDateLabel: $project->paymentDate()?->format('Y/m/d') ?? '-',
            price: $project->price()->amount,
            priceLabel: $project->price()->format(),
            statusValue: $project->status()->value,
            // 移行前は switch の誤用でここが常にずれていた
            status: $project->status()->label(),
        );
    }

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        return new self(null, '', null, null, null, null, null, null, '-', '-', '-', 0, '0', 0, '');
    }

    /**
     * @param list<Project> $projects
     * @return Collection<int, self>
     */
    public static function collection(array $projects): Collection
    {
        return collect($projects)->map(self::fromEntity(...))->values();
    }
}
