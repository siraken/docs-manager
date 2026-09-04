<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Project\Entity\Project;
use Illuminate\Support\Collection;

final readonly class ProjectView implements \JsonSerializable
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

    /**
     * Inertia の props 用。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'clientId' => $this->clientId,
            'relatedTaskId' => $this->relatedTaskId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'paymentDate' => $this->paymentDate,
            'startDateLabel' => $this->startDateLabel,
            'endDateLabel' => $this->endDateLabel,
            'paymentDateLabel' => $this->paymentDateLabel,
            'price' => $this->price,
            'priceLabel' => $this->priceLabel,
            'statusValue' => $this->statusValue,
            'status' => $this->status,

            'urls' => $this->id === null ? null : [
                'edit' => route('projects.edit', ['id' => $this->id]),
            ],
        ];
    }

    /** 新規作成フォーム用の空の入れ物 */
    public static function empty(): self
    {
        return new self(null, '', null, null, null, null, null, null, '-', '-', '-', 0, '0', 0, '');
    }

    /**
     * セレクトの選択肢。勤務報告の案件セレクトが使う。
     *
     * @param list<Project> $projects
     * @return list<array{id: int|null, name: string}>
     */
    public static function options(array $projects): array
    {
        return array_map(
            static fn (Project $project): array => [
                'id' => $project->id(),
                'name' => $project->name(),
            ],
            $projects,
        );
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
