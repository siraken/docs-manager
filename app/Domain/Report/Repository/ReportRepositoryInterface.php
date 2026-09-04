<?php

declare(strict_types=1);

namespace App\Domain\Report\Repository;

use App\Domain\Report\Entity\Report;

interface ReportRepositoryInterface
{
    /**
     * 年月とキーワードで絞り込む。いずれも null なら条件にしない。
     *
     * ProjectRepositoryInterface::listByYearMonth() と同じく、ドメイン層が
     * アプリケーション層の条件オブジェクトを知らずに済むよう素の値で受ける。
     *
     * @return list<Report>
     */
    public function search(?int $year, ?int $month, ?string $keyword): array;

    public function findById(int $id): ?Report;

    public function save(Report $report): Report;

    public function delete(int $id): void;
}
