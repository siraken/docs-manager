<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Input\CourseInput;
use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Repository\CourseRepositoryInterface;

final readonly class CreateCourseUseCase
{
    public function __construct(private CourseRepositoryInterface $courses)
    {
    }

    public function execute(CourseInput $input): Course
    {
        return $this->courses->save(Course::create(
            title: $input->title,
            description: $input->description,
            exp: $input->expValue(),
            isPublished: $input->isPublished,
        ));
    }
}
