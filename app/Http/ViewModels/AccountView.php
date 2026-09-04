<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Accounting\Entity\Account;
use Illuminate\Support\Collection;

final readonly class AccountView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public string $name,
        public ?string $code,
        public string $displayName,
        public string $typeValue,
        public string $type,
        public string $normalBalance,
        public bool $isActive,
        public ?string $note,
    ) {
    }

    public static function fromEntity(Account $account): self
    {
        return new self(
            id: $account->id(),
            name: $account->name(),
            code: $account->code(),
            displayName: $account->displayName(),
            typeValue: $account->type()->value,
            type: $account->type()->label(),
            normalBalance: $account->normalBalance()->label(),
            isActive: $account->isActive(),
            note: $account->note(),
        );
    }

    /**
     * Inertia の props 用。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'displayName' => $this->displayName,
            'typeValue' => $this->typeValue,
            'type' => $this->type,
            'normalBalance' => $this->normalBalance,
            'isActive' => $this->isActive,
            'note' => $this->note,

            'urls' => $this->id === null ? null : [
                'edit' => route('accounts.edit', ['id' => $this->id]),
                'delete' => route('accounts.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * セレクトの選択肢。仕訳フォームの借方・貸方が使う。
     *
     * 区分を添えているのは、画面で科目を選んだときに「資産」「費用」などを
     * 出して選び間違いを減らすため。
     *
     * @param list<Account> $accounts
     * @return list<array{id: int|null, name: string, type: string}>
     */
    public static function options(array $accounts): array
    {
        return array_map(
            static fn (Account $account): array => [
                'id' => $account->id(),
                'name' => $account->displayName(),
                'type' => $account->type()->label(),
            ],
            $accounts,
        );
    }

    /**
     * @param list<Account> $accounts
     * @return Collection<int, self>
     */
    public static function collection(array $accounts): Collection
    {
        return collect($accounts)->map(self::fromEntity(...))->values();
    }
}
