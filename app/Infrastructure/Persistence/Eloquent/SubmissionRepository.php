<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Learning\Entity\Submission;
use App\Domain\Learning\Repository\SubmissionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\SubmissionMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Submission as SubmissionModel;

final class SubmissionRepository implements SubmissionRepositoryInterface
{
    /** @return list<Submission> */
    public function search(?int $userId, ?int $assignmentId, ?string $status): array
    {
        $query = SubmissionModel::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($assignmentId !== null) {
            $query->where('assignment_id', $assignmentId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(SubmissionMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Submission
    {
        $model = SubmissionModel::find($id);

        return $model === null ? null : SubmissionMapper::toDomain($model);
    }

    public function findByUserAndAssignment(int $userId, int $assignmentId): ?Submission
    {
        $model = SubmissionModel::where('user_id', $userId)
            ->where('assignment_id', $assignmentId)
            ->first();

        return $model === null ? null : SubmissionMapper::toDomain($model);
    }

    public function save(Submission $submission): Submission
    {
        $model = $submission->id() === null
            ? new SubmissionModel()
            : SubmissionModel::find($submission->id()) ?? new SubmissionModel();

        $model->fill(SubmissionMapper::toAttributes($submission));
        $model->save();

        $submission->assignId((int) $model->id);

        return $submission;
    }

    public function delete(int $id): void
    {
        SubmissionModel::destroy($id);
    }
}
