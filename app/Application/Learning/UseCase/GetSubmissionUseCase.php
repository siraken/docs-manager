<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Entity\Submission;
use App\Domain\Learning\Repository\SubmissionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetSubmissionUseCase
{
    public function __construct(private SubmissionRepositoryInterface $submissions)
    {
    }

    public function execute(int $id): Submission
    {
        return $this->submissions->findById($id)
            ?? throw EntityNotFoundException::of('提出物', $id);
    }
}
