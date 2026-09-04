<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;

final readonly class ListAssignmentsUseCase
{
    public function __construct(private AssignmentRepositoryInterface $assignments)
    {
    }

    /** @return list<Assignment> */
    public function execute(): array
    {
        return $this->assignments->listAll();
    }
}
