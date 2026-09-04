<?php

declare(strict_types=1);

namespace App\Application\Project\UseCase;

use App\Application\Project\Input\ProjectInput;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;

final readonly class CreateProjectUseCase
{
    public function __construct(private ProjectRepositoryInterface $projects)
    {
    }

    public function execute(ProjectInput $input): Project
    {
        $project = Project::create(
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
