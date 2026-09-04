<?php

declare(strict_types=1);

namespace App\Application\Report\Input;

use App\Application\Shared\DateParser;
use App\Domain\Report\ValueObject\TimeOfDay;
use App\Domain\Report\ValueObject\WorkTime;

final readonly class ReportInput
{
    public function __construct(
        public ?int $userId,
        public ?int $customerId,
        public ?int $projectId,
        public string $title,
        public ?string $description,
        public mixed $date,
        public mixed $startTime,
        public mixed $endTime,
        public mixed $workTimeHours,
    ) {
    }

    public function dateValue(): \DateTimeImmutable
    {
        return DateParser::parse($this->date, '勤務日');
    }

    public function startTimeValue(): ?TimeOfDay
    {
        return TimeOfDay::parseNullable($this->startTime);
    }

    public function endTimeValue(): ?TimeOfDay
    {
        return TimeOfDay::parseNullable($this->endTime);
    }

    /**
     * フォームが申告する勤務時間 (時間単位)。
     *
     * 始業・終業が両方揃っている場合、この値は Report 側で使われない。
     */
    public function workTimeValue(): WorkTime
    {
        return WorkTime::fromHours($this->workTimeHours);
    }
}
