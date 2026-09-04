<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Exception\AssignmentInUseException;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 課題を削除する。提出物のある課題は消せない。
 */
final readonly class DeleteAssignmentUseCase
{
    public function __construct(private AssignmentRepositoryInterface $assignments)
    {
    }

    public function execute(int $id): void
    {
        $assignment = $this->assignments->findById($id)
            ?? throw EntityNotFoundException::of('課題', $id);

        if ($this->assignments->hasSubmissions($id)) {
            throw AssignmentInUseException::of($assignment->title());
        }

        $this->assignments->delete($id);
    }
}
