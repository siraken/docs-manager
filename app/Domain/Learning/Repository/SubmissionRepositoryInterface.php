<?php

declare(strict_types=1);

namespace App\Domain\Learning\Repository;

use App\Domain\Learning\Entity\Submission;

interface SubmissionRepositoryInterface
{
    /**
     * 提出者・課題・状態で絞り込む。いずれも null なら条件にしない。
     *
     * @return list<Submission>
     */
    public function search(?int $userId, ?int $assignmentId, ?string $status): array;

    public function findById(int $id): ?Submission;

    /** 同じ提出者・同じ課題の提出物。重複登録の判定に使う */
    public function findByUserAndAssignment(int $userId, int $assignmentId): ?Submission;

    public function save(Submission $submission): Submission;

    public function delete(int $id): void;
}
