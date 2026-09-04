<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Repository\SubmissionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class DeleteSubmissionUseCase
{
    public function __construct(private SubmissionRepositoryInterface $submissions)
    {
    }

    public function execute(int $id): void
    {
        if ($this->submissions->findById($id) === null) {
            throw EntityNotFoundException::of('提出物', $id);
        }

        $this->submissions->delete($id);
    }
}
