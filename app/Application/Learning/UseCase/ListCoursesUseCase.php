<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Repository\CourseRepositoryInterface;

final readonly class ListCoursesUseCase
{
    public function __construct(private CourseRepositoryInterface $courses)
    {
    }

    /**
     * @param bool $publishedOnly 受講記録フォームの選択肢に使うときは true
     * @return list<Course>
     */
    public function execute(bool $publishedOnly = false): array
    {
        return $publishedOnly ? $this->courses->listPublished() : $this->courses->listAll();
    }
}
