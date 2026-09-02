<?php

declare(strict_types=1);

namespace App\Application\Project\UseCase;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;

final readonly class ListProjectsUseCase
{
    public function __construct(private ProjectRepositoryInterface $projects)
    {
    }

    /** @return list<Project> */
    public function execute(): array
    {
        return $this->projects->listAll();
    }
}
