<?php

declare(strict_types=1);

namespace App\Domain\Accounting\Entity;

use App\Domain\Accounting\ValueObject\AccountType;
use App\Domain\Accounting\ValueObject\BalanceSide;

/**
 * 勘定科目。
 *
 * 参考にした移植元は科目を自由入力の文字列で持っていたため、「現金」と
 * 「げんきん」が別の科目として記録され、集計のしようがなかった。ここでは
 * マスタにして仕訳から id で参照する。
 *
 * 過去の仕訳が参照している科目は消せないので、使わなくなった科目は削除ではなく
 * 無効化 (isActive = false) して選択肢から外す。
 */
final class Account
{
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $code,
        private AccountType $type,
        private bool $isActive,
        private ?string $note,
    ) {
    }

    public static function create(
        string $name,
        ?string $code,
        AccountType $type,
        bool $isActive,
        ?string $note,
    ): self {
        return new self(null, $name, $code, $type, $isActive, $note);
    }

    public static function reconstitute(
        int $id,
        string $name,
        ?string $code,
        AccountType $type,
        bool $isActive,
        ?string $note,
    ): self {
        return new self($id, $name, $code, $type, $isActive, $note);
    }

    public function update(
        string $name,
        ?string $code,
        AccountType $type,
        bool $isActive,
        ?string $note,
    ): void {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->isActive = $isActive;
        $this->note = $note;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function type(): AccountType
    {
        return $this->type;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    /** 残高が立つ側。区分から決まる */
    public function normalBalance(): BalanceSide
    {
        return $this->type->normalBalance();
    }

    /** 「101 現金」のような表示。コードが無ければ名前だけ */
    public function displayName(): string
    {
        return $this->code === null || $this->code === ''
            ? $this->name
            : $this->code . ' ' . $this->name;
    }
}
