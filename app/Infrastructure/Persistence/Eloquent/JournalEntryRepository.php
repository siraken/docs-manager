<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\JournalEntryMapper;
use App\Infrastructure\Persistence\Eloquent\Models\JournalEntry as JournalEntryModel;

final class JournalEntryRepository implements JournalEntryRepositoryInterface
{
    /** @return list<JournalEntry> */
    public function search(?int $year, ?int $month, ?string $keyword, ?int $accountId): array
    {
        $query = JournalEntryModel::query();

        if ($year !== null) {
            $query->whereYear('date', $year);
        }

        if ($month !== null) {
            $query->whereMonth('date', $month);
        }

        if ($keyword !== null) {
            // LIKE のワイルドカードを打ち込まれても部分一致のままにする
            $escaped = addcslashes($keyword, '\\%_');

            // 摘要とその他の両方を見る (参考実装の検索欄と同じ範囲)
            $query->where(function ($q) use ($escaped): void {
                $q->where('description', 'like', '%' . $escaped . '%')
                    ->orWhere('note', 'like', '%' . $escaped . '%');
            });
        }

        if ($accountId !== null) {
            // 借方・貸方のどちらで使われていても拾う
            $query->where(function ($q) use ($accountId): void {
                $q->where('debit_account_id', $accountId)
                    ->orWhere('credit_account_id', $accountId);
            });
        }

        return $query->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->map(JournalEntryMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?JournalEntry
    {
        $model = JournalEntryModel::find($id);

        return $model === null ? null : JournalEntryMapper::toDomain($model);
    }

    public function save(JournalEntry $entry): JournalEntry
    {
        $model = $entry->id() === null
            ? new JournalEntryModel()
            : JournalEntryModel::find($entry->id()) ?? new JournalEntryModel();

        $model->fill(JournalEntryMapper::toAttributes($entry));
        $model->save();

        $entry->assignId((int) $model->id);

        return $entry;
    }

    public function delete(int $id): void
    {
        JournalEntryModel::destroy($id);
    }
}
