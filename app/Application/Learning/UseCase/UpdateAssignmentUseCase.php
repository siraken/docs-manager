<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Input\AssignmentInput;
use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateAssignmentUseCase
{
    public function __construct(private AssignmentRepositoryInterface $assignments)
    {
    }

    public function execute(int $id, AssignmentInput $input): Assignment
    {
        $assignment = $this->assignments->findById($id)
            ?? throw EntityNotFoundException::of('課題', $id);

        $assignment->update(
            courseId: $input->courseId,
            title: $input->title,
            description: $input->description,
            dueOn: $input->dueOnValue(),
        );

        return $this->assignments->save($assignment);
    }
}
