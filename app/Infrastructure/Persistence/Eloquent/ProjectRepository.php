<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Shared\Exception\InvalidValueException;
use App\Infrastructure\Persistence\Eloquent\Mapper\ProjectMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Project as ProjectModel;

final class ProjectRepository implements ProjectRepositoryInterface
{
    /** 集計に使ってよい日付カラム。ここに無い値はクエリに渡さない */
    private const ALLOWED_DATE_FIELDS = ['payment_date', 'start_date', 'end_date'];

    /** @return list<Project> */
    public function listAll(): array
    {
        return ProjectModel::orderBy('id')->get()
            ->map(ProjectMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Project
    {
        $model = ProjectModel::find($id);

        return $model === null ? null : ProjectMapper::toDomain($model);
    }

    public function save(Project $project): Project
    {
        $model = $project->id() === null
            ? new ProjectModel()
            : ProjectModel::find($project->id()) ?? new ProjectModel();

        $model->fill(ProjectMapper::toAttributes($project));
        $model->save();

        $project->assignId((int) $model->id);

        return $project;
    }

    /** @return list<Project> */
    public function listByYearMonth(string $dateField, int $year, int $month): array
    {
        if (!in_array($dateField, self::ALLOWED_DATE_FIELDS, true)) {
            throw new InvalidValueException(sprintf('集計対象にできない日付項目です: %s', $dateField));
        }

        return ProjectModel::whereYear($dateField, $year)
            ->whereMonth($dateField, $month)
            ->orderBy('id')
            ->get()
            ->map(ProjectMapper::toDomain(...))
            ->all();
    }
}
