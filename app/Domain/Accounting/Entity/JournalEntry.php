<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Entity;

use App\Domain\Shared\Exception\InvalidValueException;
use App\Domain\Shared\ValueObject\Money;

/**
 * 仕訳 1 件。単一仕訳（借方 1 科目・貸方 1 科目）を表す。
 *
 * **金額は 1 つしか持たない。** 参考にした移植元は借方金額と貸方金額を別々に
 * 入力させていたが、単一仕訳なら両者は必ず一致する。2 つ持つと「貸借が
 * ずれた帳簿」を作れてしまい、しかもそれを検出する術が無かった。1 つに
 * まとめることで貸借一致が構造的に保証される。
 *
 * TODO: 消費税を分けて記帳したい場合は複合仕訳 (借方 N・貸方 M) が要る。
 *       いまは「売掛金 / 売上」と「売掛金 / 仮受消費税」の 2 件に分けて記帳する。
 */
final class JournalEntry
{
    private function __construct(
        private ?int $id,
        private \DateTimeImmutable $date,
        private int $debitAccountId,
        private int $creditAccountId,
        private Money $amount,
        private string $description,
        private ?string $note,
    ) {
    }

    public static function create(
        \DateTimeImmutable $date,
        int $debitAccountId,
        int $creditAccountId,
        Money $amount,
        string $description,
        ?string $note,
    ): self {
        self::assertValid($debitAccountId, $creditAccountId, $amount);

        return new self(null, $date, $debitAccountId, $creditAccountId, $amount, $description, $note);
    }

    /**
     * 保存済みの状態から組み直す。
     *
     * 検証は通さない。過去に保存された行は、規則を後から足したときにも
     * そのまま読めるようにしておく（読めないと一覧すら開けなくなる）。
     */
    public static function reconstitute(
        int $id,
        \DateTimeImmutable $date,
        int $debitAccountId,
        int $creditAccountId,
        Money $amount,
        string $description,
        ?string $note,
    ): self {
        return new self($id, $date, $debitAccountId, $creditAccountId, $amount, $description, $note);
    }

    public function update(
        \DateTimeImmutable $date,
        int $debitAccountId,
        int $creditAccountId,
        Money $amount,
        string $description,
        ?string $note,
    ): void {
        self::assertValid($debitAccountId, $creditAccountId, $amount);

        $this->date = $date;
        $this->debitAccountId = $debitAccountId;
        $this->creditAccountId = $creditAccountId;
        $this->amount = $amount;
        $this->description = $description;
        $this->note = $note;
    }

    /**
     * 仕訳として成立しているか。
     *
     * @throws InvalidValueException
     */
    private static function assertValid(int $debitAccountId, int $creditAccountId, Money $amount): void
    {
        if ($debitAccountId === $creditAccountId) {
            // 「現金 / 現金」は何も動かない。書き間違いとしか考えられない
            throw new InvalidValueException('借方と貸方に同じ勘定科目は指定できません。');
        }

        if ($amount->amount <= 0) {
            throw new InvalidValueException('仕訳の金額は 1 円以上にしてください。');
        }
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function debitAccountId(): int
    {
        return $this->debitAccountId;
    }

    public function creditAccountId(): int
    {
        return $this->creditAccountId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function note(): ?string
    {
        return $this->note;
    }
}
