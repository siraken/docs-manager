<?php

declare(strict_types=1);

namespace App\Domain\Learning\Repository;

use App\Domain\Learning\Entity\Enrollment;

interface EnrollmentRepositoryInterface
{
    /**
     * 受講者・講座・状態で絞り込む。いずれも null なら条件にしない。
     *
     * @return list<Enrollment>
     */
    public function search(?int $userId, ?int $courseId, ?string $status): array;

    public function findById(int $id): ?Enrollment;

    /** 同じ受講者・同じ講座の記録。重複登録の判定に使う */
    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment;

    public function save(Enrollment $enrollment): Enrollment;

    public function delete(int $id): void;
}
