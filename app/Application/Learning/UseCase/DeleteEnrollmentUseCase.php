<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class DeleteEnrollmentUseCase
{
    public function __construct(private EnrollmentRepositoryInterface $enrollments)
    {
    }

    public function execute(int $id): void
    {
        if ($this->enrollments->findById($id) === null) {
            throw EntityNotFoundException::of('受講記録', $id);
        }

        $this->enrollments->delete($id);
    }
}
