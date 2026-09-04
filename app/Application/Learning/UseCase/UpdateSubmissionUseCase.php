<?php

declare(strict_types=1);

namespace App\Application\Learning\UseCase;

use App\Application\Learning\Exception\DuplicateSubmissionException;
use App\Application\Learning\Input\SubmissionInput;
use App\Domain\Learning\Entity\Submission;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Domain\Learning\Repository\SubmissionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class UpdateSubmissionUseCase
{
    public function __construct(
        private SubmissionRepositoryInterface $submissions,
        private AssignmentRepositoryInterface $assignments,
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(int $id, SubmissionInput $input): Submission
    {
        $submission = $this->submissions->findById($id)
            ?? throw EntityNotFoundException::of('提出物', $id);

        // 提出者や課題を付け替えた結果、別の行と重ならないか見る
        $duplicate = $this->submissions->findByUserAndAssignment($input->userId, $input->assignmentId);

        if ($duplicate !== null && $duplicate->id() !== $id) {
            throw DuplicateSubmissionException::of(
                $this->users->findById($input->userId)?->name() ?? '該当ユーザー',
                $this->assignments->findById($input->assignmentId)?->title() ?? '該当課題',
            );
        }

        $submission->update(
            assignmentId: $input->assignmentId,
            userId: $input->userId,
            status: $input->statusValue(),
            submittedAt: $input->submittedAtValue(),
            body: $input->body,
            feedback: $input->feedback,
        );

        return $this->submissions->save($submission);
    }
}
