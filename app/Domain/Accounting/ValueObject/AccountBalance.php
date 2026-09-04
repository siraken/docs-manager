<?php

declare(strict_types=1);

namespace App\Domain\Accounting\ValueObject;

use App\Domain\Accounting\Entity\Account;
use App\Domain\Shared\ValueObject\Money;

/**
 * 残高試算表の 1 行。1 つの勘定科目の借方合計・貸方合計と、その差額。
 *
 * 残高をどちらの側に出すかは科目の区分で決まる。資産・費用は借方が増加なので
 * 「借方合計 - 貸方合計」が正なら借方残高、負なら貸方残高になる。
 * 負債・純資産・収益はその逆。
 */
final readonly class AccountBalance
{
    private function __construct(
        public Account $account,
        public Money $debitTotal,
        public Money $creditTotal,
        public BalanceSide $side,
        public Money $balance,
    ) {
    }

    public static function of(Account $account, Money $debitTotal, Money $creditTotal): self
    {
        $normal = $account->normalBalance();

        // 本来の側から見た差額。マイナスなら反対側に残高が立っている
        $signed = $normal === BalanceSide::Debit
            ? $debitTotal->amount - $creditTotal->amount
            : $creditTotal->amount - $debitTotal->amount;

        return new self(
            account: $account,
            debitTotal: $debitTotal,
            creditTotal: $creditTotal,
            side: $signed >= 0 ? $normal : $normal->opposite(),
            balance: Money::fromInt(abs($signed)),
        );
    }

    /** 借方にも貸方にも動きが無い科目。試算表から省ける */
    public function isEmpty(): bool
    {
        return $this->debitTotal->isZero() && $this->creditTotal->isZero();
    }

    public function debitBalance(): Money
    {
        return $this->side === BalanceSide::Debit ? $this->balance : Money::zero();
    }

    public function creditBalance(): Money
    {
        return $this->side === BalanceSide::Credit ? $this->balance : Money::zero();
    }
}
