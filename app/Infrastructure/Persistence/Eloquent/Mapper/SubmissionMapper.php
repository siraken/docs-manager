<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Learning\Entity\Submission as SubmissionEntity;
use App\Domain\Learning\ValueObject\SubmissionStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Submission as SubmissionModel;

final class SubmissionMapper
{
    public static function toDomain(SubmissionModel $model): SubmissionEntity
    {
        return SubmissionEntity::reconstitute(
            id: (int) $model->id,
            assignmentId: (int) $model->assignment_id,
            userId: (int) $model->user_id,
            status: SubmissionStatus::fromNullable($model->status),
            submittedAt: DateParser::parseNullable($model->submitted_at, '提出日'),
            body: $model->body,
            feedback: $model->feedback,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(SubmissionEntity $submission): array
    {
        return [
            'assignment_id' => $submission->assignmentId(),
            'user_id' => $submission->userId(),
            'status' => $submission->status()->value,
            'submitted_at' => $submission->submittedAt()?->format('Y-m-d'),
            'body' => $submission->body(),
            'feedback' => $submission->feedback(),
        ];
    }
}
