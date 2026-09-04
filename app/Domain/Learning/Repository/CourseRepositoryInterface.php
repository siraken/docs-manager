<?php

declare(strict_types=1);

namespace App\Domain\Learning\Repository;

use App\Domain\Learning\Entity\Course;

interface CourseRepositoryInterface
{
    /** @return list<Course> */
    public function listAll(): array;

    /**
     * 受講記録フォームの選択肢に出す講座。下書きは除く。
     *
     * @return list<Course>
     */
    public function listPublished(): array;

    public function findById(int $id): ?Course;

    public function save(Course $course): Course;

    public function delete(int $id): void;

    /** その講座の受講記録があるか。削除の可否判定に使う */
    public function isEnrolled(int $id): bool;
}
