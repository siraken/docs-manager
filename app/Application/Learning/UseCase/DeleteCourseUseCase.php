<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Exception\CourseInUseException;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 講座を削除する。受講記録のある講座は消せない。
 */
final readonly class DeleteCourseUseCase
{
    public function __construct(private CourseRepositoryInterface $courses)
    {
    }

    public function execute(int $id): void
    {
        $course = $this->courses->findById($id)
            ?? throw EntityNotFoundException::of('講座', $id);

        if ($this->courses->isEnrolled($id)) {
            throw CourseInUseException::of($course->title());
        }

        $this->courses->delete($id);
    }
}
