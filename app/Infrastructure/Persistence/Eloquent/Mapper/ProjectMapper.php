<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Application\Shared\DateParser;
use App\Domain\Project\Entity\Project as ProjectEntity;
use App\Domain\Project\ValueObject\JiraKey;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Domain\Shared\ValueObject\Money;
use App\Infrastructure\Persistence\Eloquent\Models\Project as ProjectModel;

final class ProjectMapper
{
    public static function toDomain(ProjectModel $model): ProjectEntity
    {
        return ProjectEntity::reconstitute(
            id: (int) $model->id,
            name: (string) $model->name,
            description: $model->description,
            clientId: $model->client_id === null ? null : (int) $model->client_id,
            jiraKey: JiraKey::parseNullable($model->jira_key),
            startDate: DateParser::parseNullable($model->start_date, '開始日'),
            endDate: DateParser::parseNullable($model->end_date, '終了日'),
            paymentDate: DateParser::parseNullable($model->payment_date, '支払日'),
            price: Money::fromNumeric($model->price ?? 0),
            status: ProjectStatus::fromNullable($model->status),
        );
    }

    /** @return array<string, mixed> */
    public static function toAttributes(ProjectEntity $project): array
    {
        return [
            'name' => $project->name(),
            'description' => $project->description(),
            'client_id' => $project->clientId(),
            'jira_key' => $project->jiraKey()?->value,
            'start_date' => $project->startDate()?->format('Y-m-d'),
            'end_date' => $project->endDate()?->format('Y-m-d'),
            'payment_date' => $project->paymentDate()?->format('Y-m-d'),
            'price' => $project->price()->amount,
            'status' => $project->status()->value,
        ];
    }
}
