<?php

declare(strict_types=1);

namespace App\Application\Project\UseCase;

use App\Application\Project\Input\ProjectInput;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateProjectUseCase
{
    public function __construct(private ProjectRepositoryInterface $projects)
    {
    }

    public function execute(int $id, ProjectInput $input): Project
    {
        $project = $this->projects->findById($id)
            ?? throw EntityNotFoundException::of('案件', $id);

        $project->update(
            name: $input->name,
            description: $input->description,
            clientId: $input->clientId,
            jiraKey: $input->jiraKeyValue(),
            startDate: $input->startDateValue(),
            endDate: $input->endDateValue(),
            paymentDate: $input->paymentDateValue(),
            price: $input->priceValue(),
            status: $input->statusValue(),
        );

        return $this->projects->save($project);
    }
}
