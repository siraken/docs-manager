<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Service;

use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Accounting\ValueObject\AccountBalance;
use App\Domain\Shared\ValueObject\Money;

/**
 * 仕訳の集まりから残高試算表を組み立てる。
 *
 * ドメインサービスとして切り出しているのは、集計が「特定の 1 件」ではなく
 * 仕訳と勘定科目の集合にまたがる規則だから。Laravel には依存しないので
 * Unit テストで検証できる。
 *
 * **借方合計と貸方合計は必ず一致する**（単一仕訳しか作れないため）。
 * 一致しない結果が出たらそれ自体が不具合の証拠になるので、
 * 呼び出し側が検算できるよう合計も出せるようにしてある。
 */
final class TrialBalanceBuilder
{
    /**
     * @param list<Account> $accounts
     * @param list<JournalEntry> $entries
     * @param bool $includeEmpty 動きの無い科目も行に出すか
     * @return list<AccountBalance> 科目の区分 → コード → 名前の順
     */
    public function build(array $accounts, array $entries, bool $includeEmpty = false): array
    {
        /** @var array<int, int> $debits 科目 ID => 借方合計 */
        $debits = [];
        /** @var array<int, int> $credits */
        $credits = [];

        foreach ($entries as $entry) {
            $amount = $entry->amount()->amount;
            $debits[$entry->debitAccountId()] = ($debits[$entry->debitAccountId()] ?? 0) + $amount;
            $credits[$entry->creditAccountId()] = ($credits[$entry->creditAccountId()] ?? 0) + $amount;
        }

        $rows = [];

        foreach ($this->sorted($accounts) as $account) {
            $id = (int) $account->id();

            $row = AccountBalance::of(
                $account,
                Money::fromInt($debits[$id] ?? 0),
                Money::fromInt($credits[$id] ?? 0),
            );

            if ($includeEmpty || !$row->isEmpty()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * 貸借対照表・損益計算書の慣習に合わせて並べる
     * (資産 → 負債 → 純資産 → 収益 → 費用、同じ区分ではコード順)。
     *
     * @param list<Account> $accounts
     * @return list<Account>
     */
    private function sorted(array $accounts): array
    {
        usort($accounts, static function (Account $a, Account $b): int {
            return [$a->type()->sortOrder(), $a->code() ?? '', $a->name()]
                <=> [$b->type()->sortOrder(), $b->code() ?? '', $b->name()];
        });

        return array_values($accounts);
    }
}
