<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Domain\Learning\Entity\Course as CourseEntity;
use App\Domain\Learning\ValueObject\ExperiencePoint;
use App\Infrastructure\Persistence\Eloquent\Models\Course as CourseModel;

final class CourseMapper
{
    public static function toDomain(CourseModel $model): CourseEntity
    {
        return CourseEntity::reconstitute(
            id: (int) $model->id,
            title: (string) $model->title,
            description: $model->description,
            // sqlite は integer カラムを文字列で返す
            exp: ExperiencePoint::fromNullable($model->exp),
            isPublished: (bool) $model->is_published,
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(CourseEntity $course): array
    {
        return [
            'title' => $course->title(),
            'description' => $course->description(),
            'exp' => $course->exp()->value,
            'is_published' => $course->isPublished(),
        ];
    }
}
