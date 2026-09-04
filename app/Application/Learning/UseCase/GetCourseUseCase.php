<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetCourseUseCase
{
    public function __construct(private CourseRepositoryInterface $courses)
    {
    }

    public function execute(int $id): Course
    {
        return $this->courses->findById($id)
            ?? throw EntityNotFoundException::of('講座', $id);
    }
}
