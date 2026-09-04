<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Repository;

use App\Domain\Accounting\Entity\JournalEntry;

interface JournalEntryRepositoryInterface
{
    /**
     * 年月・キーワード・勘定科目で絞り込む。いずれも null なら条件にしない。
     *
     * キーワードは摘要とその他の両方を見る（参考にした移植元の検索欄が
     * 「摘要/その他で検索...」だったのに合わせている）。
     *
     * @return list<JournalEntry>
     */
    public function search(?int $year, ?int $month, ?string $keyword, ?int $accountId): array;

    public function findById(int $id): ?JournalEntry;

    public function save(JournalEntry $entry): JournalEntry;

    public function delete(int $id): void;
}
