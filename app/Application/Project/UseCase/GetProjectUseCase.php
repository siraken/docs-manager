<?php

declare(strict_types=1);

namespace App\Application\Project\UseCase;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetProjectUseCase
{
    public function __construct(private ProjectRepositoryInterface $projects)
    {
    }

    public function execute(int $id): Project
    {
        return $this->projects->findById($id)
            ?? throw EntityNotFoundException::of('案件', $id);
    }
}
