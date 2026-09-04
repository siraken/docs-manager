<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Accounting\ValueObject\AccountBalance;
use Illuminate\Support\Collection;

/**
 * 残高試算表の 1 行。
 */
final readonly class TrialBalanceRowView implements \JsonSerializable
{
    private function __construct(
        public int $accountId,
        public string $accountName,
        public string $typeValue,
        public string $type,
        public int $debitTotal,
        public string $debitTotalLabel,
        public int $creditTotal,
        public string $creditTotalLabel,
        public int $debitBalance,
        public string $debitBalanceLabel,
        public int $creditBalance,
        public string $creditBalanceLabel,
    ) {
    }

    public static function fromBalance(AccountBalance $balance): self
    {
        $account = $balance->account;

        return new self(
            accountId: (int) $account->id(),
            accountName: $account->displayName(),
            typeValue: $account->type()->value,
            type: $account->type()->label(),
            debitTotal: $balance->debitTotal->amount,
            debitTotalLabel: $balance->debitTotal->format(),
            creditTotal: $balance->creditTotal->amount,
            creditTotalLabel: $balance->creditTotal->format(),
            debitBalance: $balance->debitBalance()->amount,
            // 0 は「-」で出す。数字が並ぶと残高がどちら側か読み取りにくいため
            debitBalanceLabel: $balance->debitBalance()->isZero() ? '-' : $balance->debitBalance()->format(),
            creditBalance: $balance->creditBalance()->amount,
            creditBalanceLabel: $balance->creditBalance()->isZero() ? '-' : $balance->creditBalance()->format(),
        );
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'accountId' => $this->accountId,
            'accountName' => $this->accountName,
            'typeValue' => $this->typeValue,
            'type' => $this->type,
            'debitTotal' => $this->debitTotal,
            'debitTotalLabel' => $this->debitTotalLabel,
            'creditTotal' => $this->creditTotal,
            'creditTotalLabel' => $this->creditTotalLabel,
            'debitBalance' => $this->debitBalance,
            'debitBalanceLabel' => $this->debitBalanceLabel,
            'creditBalance' => $this->creditBalance,
            'creditBalanceLabel' => $this->creditBalanceLabel,
        ];
    }

    /**
     * @param list<AccountBalance> $balances
     * @return Collection<int, self>
     */
    public static function collection(array $balances): Collection
    {
        return collect($balances)->map(self::fromBalance(...))->values();
    }
}
