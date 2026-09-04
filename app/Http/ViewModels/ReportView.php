<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Report\Entity\Report;
use Illuminate\Support\Collection;

final readonly class ReportView implements \JsonSerializable
{
    /**
     * @param array<int, string> $names 表示用の名前 (担当者・取引先・案件)
     */
    private function __construct(
        public ?int $id,
        public ?int $userId,
        public string $userName,
        public ?int $customerId,
        public string $customerName,
        public ?int $projectId,
        public string $projectName,
        public string $title,
        public ?string $description,
        public string $date,
        public string $dateLabel,
        public ?string $startTime,
        public ?string $endTime,
        public int $workMinutes,
        public float $workHours,
        public string $workTimeLabel,
    ) {
    }

    /**
     * 担当者・取引先・案件の名前は、一覧で N+1 を避けるためコントローラが
     * まとめて引いた対応表から渡す (OrderView と同じ形)。
     */
    public static function fromEntity(
        Report $report,
        string $userName = '',
        string $customerName = '',
        string $projectName = '',
    ): self {
        $workTime = $report->workTime();

        return new self(
            id: $report->id(),
            userId: $report->userId(),
            userName: $userName,
            customerId: $report->customerId(),
            customerName: $customerName,
            projectId: $report->projectId(),
            projectName: $projectName,
            title: $report->title(),
            description: $report->description(),
            // 前者はフォームの value、後者は表示に使う
            date: $report->date()->format('Y-m-d'),
            dateLabel: $report->date()->format('Y/m/d'),
            startTime: $report->startTime()?->format(),
            endTime: $report->endTime()?->format(),
            workMinutes: $workTime->minutes,
            workHours: $workTime->hours(),
            workTimeLabel: $workTime->format(),
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
            'userId' => $this->userId,
            'userName' => $this->userName,
            'customerId' => $this->customerId,
            'customerName' => $this->customerName,
            'projectId' => $this->projectId,
            'projectName' => $this->projectName,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'dateLabel' => $this->dateLabel,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'workMinutes' => $this->workMinutes,
            'workHours' => $this->workHours,
            'workTimeLabel' => $this->workTimeLabel,

            'urls' => $this->id === null ? null : [
                'show' => route('reports.view', ['id' => $this->id]),
                'edit' => route('reports.edit', ['id' => $this->id]),
                'delete' => route('reports.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * @param list<Report> $reports
     * @param array<int, string> $userNames ユーザー ID => 名前
     * @param array<int, string> $customerNames 顧客 ID => 名前
     * @param array<int, string> $projectNames 案件 ID => 名前
     * @return Collection<int, self>
     */
    public static function collection(
        array $reports,
        array $userNames = [],
        array $customerNames = [],
        array $projectNames = [],
    ): Collection {
        return collect($reports)
            ->map(static fn (Report $report): self => self::fromEntity(
                $report,
                $userNames[$report->userId()] ?? '',
                $customerNames[$report->customerId()] ?? '',
                $projectNames[$report->projectId()] ?? '',
            ))
            ->values();
    }
}
