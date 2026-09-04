<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Exception\DuplicateSubmissionException;
use App\Application\Learning\Input\SubmissionInput;
use App\Domain\Learning\Entity\Submission;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Domain\Learning\Repository\SubmissionRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class CreateSubmissionUseCase
{
    public function __construct(
        private SubmissionRepositoryInterface $submissions,
        private AssignmentRepositoryInterface $assignments,
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(SubmissionInput $input): Submission
    {
        // 2 行あると合格なのか差し戻しなのかが決められない。
        // 再提出は既存の行を更新する
        if ($this->submissions->findByUserAndAssignment($input->userId, $input->assignmentId) !== null) {
            throw DuplicateSubmissionException::of(
                $this->users->findById($input->userId)?->name() ?? '該当ユーザー',
                $this->assignments->findById($input->assignmentId)?->title() ?? '該当課題',
            );
        }

        return $this->submissions->save(Submission::create(
            assignmentId: $input->assignmentId,
            userId: $input->userId,
            status: $input->statusValue(),
            submittedAt: $input->submittedAtValue(),
            body: $input->body,
            feedback: $input->feedback,
        ));
    }
}
