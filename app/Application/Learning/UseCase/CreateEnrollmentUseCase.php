<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Exception\DuplicateEnrollmentException;
use App\Application\Learning\Input\EnrollmentInput;
use App\Domain\Learning\Entity\Enrollment;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class CreateEnrollmentUseCase
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollments,
        private CourseRepositoryInterface $courses,
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(EnrollmentInput $input): Enrollment
    {
        // 同じ受講者・同じ講座の記録が 2 行あると、完了なのか受講中なのかが
        // 決められなくなる。受け直しは既存の記録を更新する
        if ($this->enrollments->findByUserAndCourse($input->userId, $input->courseId) !== null) {
            throw DuplicateEnrollmentException::of(
                $this->users->findById($input->userId)?->name() ?? '該当ユーザー',
                $this->courses->findById($input->courseId)?->title() ?? '該当講座',
            );
        }

        return $this->enrollments->save(Enrollment::create(
            userId: $input->userId,
            courseId: $input->courseId,
            status: $input->statusValue(),
            startedAt: $input->startedAtValue(),
            completedAt: $input->completedAtValue(),
            note: $input->note,
        ));
    }
}
