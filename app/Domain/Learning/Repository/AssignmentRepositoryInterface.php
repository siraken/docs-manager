<?php

declare(strict_types=1);

namespace App\Domain\Learning\Repository;

use App\Domain\Learning\Entity\Assignment;

interface AssignmentRepositoryInterface
{
    /** @return list<Assignment> */
    public function listAll(): array;

    public function findById(int $id): ?Assignment;

    public function save(Assignment $assignment): Assignment;

    public function delete(int $id): void;

    /** その課題の提出物があるか。削除の可否判定に使う */
    public function hasSubmissions(int $id): bool;
}
