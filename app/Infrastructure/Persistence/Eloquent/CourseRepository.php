<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\CourseMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Course as CourseModel;
use App\Infrastructure\Persistence\Eloquent\Models\Enrollment as EnrollmentModel;

final class CourseRepository implements CourseRepositoryInterface
{
    /** @return list<Course> */
    public function listAll(): array
    {
        return CourseModel::orderBy('id')->get()->map(CourseMapper::toDomain(...))->all();
    }

    /** @return list<Course> */
    public function listPublished(): array
    {
        return CourseModel::where('is_published', true)
            ->orderBy('id')
            ->get()
            ->map(CourseMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Course
    {
        $model = CourseModel::find($id);

        return $model === null ? null : CourseMapper::toDomain($model);
    }

    public function save(Course $course): Course
    {
        $model = $course->id() === null
            ? new CourseModel()
            : CourseModel::find($course->id()) ?? new CourseModel();

        $model->fill(CourseMapper::toAttributes($course));
        $model->save();

        $course->assignId((int) $model->id);

        return $course;
    }

    public function delete(int $id): void
    {
        CourseModel::destroy($id);
    }

    public function isEnrolled(int $id): bool
    {
        return EnrollmentModel::where('course_id', $id)->exists();
    }
}
