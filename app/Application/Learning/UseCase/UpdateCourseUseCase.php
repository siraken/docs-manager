<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Input\CourseInput;
use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateCourseUseCase
{
    public function __construct(private CourseRepositoryInterface $courses)
    {
    }

    public function execute(int $id, CourseInput $input): Course
    {
        $course = $this->courses->findById($id)
            ?? throw EntityNotFoundException::of('講座', $id);

        $course->update(
            title: $input->title,
            description: $input->description,
            exp: $input->expValue(),
            isPublished: $input->isPublished,
        );

        return $this->courses->save($course);
    }
}
