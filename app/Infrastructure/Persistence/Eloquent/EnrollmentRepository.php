<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Learning\Entity\Enrollment;
use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\EnrollmentMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Enrollment as EnrollmentModel;

final class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    /** @return list<Enrollment> */
    public function search(?int $userId, ?int $courseId, ?string $status): array
    {
        $query = EnrollmentModel::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($courseId !== null) {
            $query->where('course_id', $courseId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get()
            ->map(EnrollmentMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Enrollment
    {
        $model = EnrollmentModel::find($id);

        return $model === null ? null : EnrollmentMapper::toDomain($model);
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment
    {
        $model = EnrollmentModel::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        return $model === null ? null : EnrollmentMapper::toDomain($model);
    }

    public function save(Enrollment $enrollment): Enrollment
    {
        $model = $enrollment->id() === null
            ? new EnrollmentModel()
            : EnrollmentModel::find($enrollment->id()) ?? new EnrollmentModel();

        $model->fill(EnrollmentMapper::toAttributes($enrollment));
        $model->save();

        $enrollment->assignId((int) $model->id);

        return $enrollment;
    }

    public function delete(int $id): void
    {
        EnrollmentModel::destroy($id);
    }
}
