<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Learning\Entity\Assignment as AssignmentEntity;
use App\Infrastructure\Persistence\Eloquent\Models\Assignment as AssignmentModel;

final class AssignmentMapper
{
    public static function toDomain(AssignmentModel $model): AssignmentEntity
    {
        return AssignmentEntity::reconstitute(
            id: (int) $model->id,
            // sqlite は integer カラムを文字列で返す
            courseId: (int) $model->course_id,
            title: (string) $model->title,
            description: $model->description,
            dueOn: DateParser::parseNullable($model->due_on, '提出期限'),
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(AssignmentEntity $assignment): array
    {
        return [
            'course_id' => $assignment->courseId(),
            'title' => $assignment->title(),
            'description' => $assignment->description(),
            'due_on' => $assignment->dueOn()?->format('Y-m-d'),
        ];
    }
}
