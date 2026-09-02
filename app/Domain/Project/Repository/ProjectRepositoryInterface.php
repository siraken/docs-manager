<?php

declare(strict_types=1);

namespace App\Domain\Project\Repository;

use App\Domain\Project\Entity\Project;

interface ProjectRepositoryInterface
{
    /** @return list<Project> */
    public function listAll(): array;

    public function findById(int $id): ?Project;

    public function save(Project $project): Project;

    /**
     * 指定した日付カラムが年月に一致する案件を返す。売上分析で使う。
     *
     * @param 'payment_date'|'start_date'|'end_date' $dateField
     * @return list<Project>
     */
    public function listByYearMonth(string $dateField, int $year, int $month): array;
}
