<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Input\AssignmentInput;
use App\Domain\Learning\Entity\Assignment;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;

final readonly class CreateAssignmentUseCase
{
    public function __construct(private AssignmentRepositoryInterface $assignments)
    {
    }

    public function execute(AssignmentInput $input): Assignment
    {
        return $this->assignments->save(Assignment::create(
            courseId: $input->courseId,
            title: $input->title,
            description: $input->description,
            dueOn: $input->dueOnValue(),
        ));
    }
}
