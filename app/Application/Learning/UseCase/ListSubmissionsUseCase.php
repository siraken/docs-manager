<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Input\SubmissionFilter;
use App\Domain\Learning\Entity\Submission;
use App\Domain\Learning\Repository\SubmissionRepositoryInterface;

final readonly class ListSubmissionsUseCase
{
    public function __construct(private SubmissionRepositoryInterface $submissions)
    {
    }

    /** @return list<Submission> */
    public function execute(SubmissionFilter $filter): array
    {
        return $this->submissions->search($filter->userId, $filter->assignmentId, $filter->status);
    }
}
