<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Exception\DuplicateEnrollmentException;
use App\Application\Learning\Input\EnrollmentInput;
use App\Domain\Learning\Entity\Enrollment;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class UpdateEnrollmentUseCase
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollments,
        private CourseRepositoryInterface $courses,
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(int $id, EnrollmentInput $input): Enrollment
    {
        $enrollment = $this->enrollments->findById($id)
            ?? throw EntityNotFoundException::of('受講記録', $id);

        // 受講者や講座を付け替えた結果、別の記録と重ならないか見る
        $duplicate = $this->enrollments->findByUserAndCourse($input->userId, $input->courseId);

        if ($duplicate !== null && $duplicate->id() !== $id) {
            throw DuplicateEnrollmentException::of(
                $this->users->findById($input->userId)?->name() ?? '該当ユーザー',
                $this->courses->findById($input->courseId)?->title() ?? '該当講座',
            );
        }

        $enrollment->update(
            userId: $input->userId,
            courseId: $input->courseId,
            status: $input->statusValue(),
            startedAt: $input->startedAtValue(),
            completedAt: $input->completedAtValue(),
            note: $input->note,
        );

        return $this->enrollments->save($enrollment);
    }
}
