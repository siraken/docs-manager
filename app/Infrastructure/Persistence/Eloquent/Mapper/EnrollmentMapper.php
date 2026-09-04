<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Learning\Entity\Enrollment as EnrollmentEntity;
use App\Domain\Learning\ValueObject\EnrollmentStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Enrollment as EnrollmentModel;

final class EnrollmentMapper
{
    public static function toDomain(EnrollmentModel $model): EnrollmentEntity
    {
        return EnrollmentEntity::reconstitute(
            id: (int) $model->id,
            userId: (int) $model->user_id,
            courseId: (int) $model->course_id,
            status: EnrollmentStatus::fromNullable($model->status),
            startedAt: DateParser::parseNullable($model->started_at, '受講開始日'),
            completedAt: DateParser::parseNullable($model->completed_at, '完了日'),
            note: $model->note,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(EnrollmentEntity $enrollment): array
    {
        return [
            'user_id' => $enrollment->userId(),
            'course_id' => $enrollment->courseId(),
            'status' => $enrollment->status()->value,
            'started_at' => $enrollment->startedAt()?->format('Y-m-d'),
            'completed_at' => $enrollment->completedAt()?->format('Y-m-d'),
            'note' => $enrollment->note(),
        ];
    }
}
