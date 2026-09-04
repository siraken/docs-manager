<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Entity\Enrollment;
use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetEnrollmentUseCase
{
    public function __construct(private EnrollmentRepositoryInterface $enrollments)
    {
    }

    public function execute(int $id): Enrollment
    {
        return $this->enrollments->findById($id)
            ?? throw EntityNotFoundException::of('受講記録', $id);
    }
}
