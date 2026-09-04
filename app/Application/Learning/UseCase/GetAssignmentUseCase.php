<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetAssignmentUseCase
{
    public function __construct(private AssignmentRepositoryInterface $assignments)
    {
    }

    public function execute(int $id): Assignment
    {
        return $this->assignments->findById($id)
            ?? throw EntityNotFoundException::of('課題', $id);
    }
}
