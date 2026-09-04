<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Input\EnrollmentFilter;
use App\Application\Learning\Output\LearningSummary;
use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;

final readonly class ListEnrollmentsUseCase
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollments,
        private CourseRepositoryInterface $courses,
    ) {
    }

    public function execute(EnrollmentFilter $filter): LearningSummary
    {
        $byId = [];

        foreach ($this->courses->listAll() as $course) {
            $byId[(int) $course->id()] = $course;
        }

        return LearningSummary::of(
            $this->enrollments->search($filter->userId, $filter->courseId, $filter->status),
            $byId,
        );
    }
}
