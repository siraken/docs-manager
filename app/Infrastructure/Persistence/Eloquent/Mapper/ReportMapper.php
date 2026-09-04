<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Report\Entity\Report as ReportEntity;
use App\Domain\Report\ValueObject\TimeOfDay;
use App\Domain\Report\ValueObject\WorkTime;
use App\Infrastructure\Persistence\Eloquent\Models\Report as ReportModel;

final class ReportMapper
{
    public static function toDomain(ReportModel $model): ReportEntity
    {
        return ReportEntity::reconstitute(
            id: (int) $model->id,
            userId: $model->user_id === null ? null : (int) $model->user_id,
            customerId: $model->customer_id === null ? null : (int) $model->customer_id,
            projectId: $model->project_id === null ? null : (int) $model->project_id,
            title: (string) $model->title,
            description: $model->description,
            date: DateParser::parse($model->date, '勤務日'),
            startTime: TimeOfDay::parseNullable($model->start_time),
            endTime: TimeOfDay::parseNullable($model->end_time),
            // sqlite は integer を文字列で返す
            workTime: WorkTime::fromMinutes((int) ($model->work_minutes ?? 0)),
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(ReportEntity $report): array
    {
        return [
            'user_id' => $report->userId(),
            'customer_id' => $report->customerId(),
            'project_id' => $report->projectId(),
            'title' => $report->title(),
            'description' => $report->description(),
            'date' => $report->date()->format('Y-m-d'),
            'start_time' => $report->startTime()?->format(),
            'end_time' => $report->endTime()?->format(),
            'work_minutes' => $report->workTime()->minutes,
        ];
    }
}
