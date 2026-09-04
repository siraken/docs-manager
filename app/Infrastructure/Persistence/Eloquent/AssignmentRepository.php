<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\AssignmentMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Assignment as AssignmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\Submission as SubmissionModel;

final class AssignmentRepository implements AssignmentRepositoryInterface
{
    /** @return list<Assignment> */
    public function listAll(): array
    {
        // 期限の近いものから。期限が無いものは末尾へ回す
        return AssignmentModel::orderByRaw('due_on IS NULL')
            ->orderBy('due_on')
            ->orderBy('id')
            ->get()
            ->map(AssignmentMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Assignment
    {
        $model = AssignmentModel::find($id);

        return $model === null ? null : AssignmentMapper::toDomain($model);
    }

    public function save(Assignment $assignment): Assignment
    {
        $model = $assignment->id() === null
            ? new AssignmentModel()
            : AssignmentModel::find($assignment->id()) ?? new AssignmentModel();

        $model->fill(AssignmentMapper::toAttributes($assignment));
        $model->save();

        $assignment->assignId((int) $model->id);

        return $assignment;
    }

    public function delete(int $id): void
    {
        AssignmentModel::destroy($id);
    }

    public function hasSubmissions(int $id): bool
    {
        return SubmissionModel::where('assignment_id', $id)->exists();
    }
}
