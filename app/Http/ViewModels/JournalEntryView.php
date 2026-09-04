<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Accounting\Entity\JournalEntry;
use Illuminate\Support\Collection;

final readonly class JournalEntryView implements \JsonSerializable
{
    private function __construct(
        public ?int $id,
        public string $date,
        public string $dateLabel,
        public int $debitAccountId,
        public string $debitAccountName,
        public int $creditAccountId,
        public string $creditAccountName,
        public int $amount,
        public string $amountLabel,
        public string $description,
        public ?string $note,
    ) {
    }

    /**
     * @param array<int, string> $accountNames 科目 ID => 表示名。一覧で仕訳ごとに
     *                                         科目を引くと N+1 になるため対応表で渡す
     */
    public static function fromEntity(JournalEntry $entry, array $accountNames = []): self
    {
        return new self(
            id: $entry->id(),
            // 前者はフォームの value、後者は表示に使う
            date: $entry->date()->format('Y-m-d'),
            dateLabel: $entry->date()->format('Y/m/d'),
            debitAccountId: $entry->debitAccountId(),
            debitAccountName: $accountNames[$entry->debitAccountId()] ?? '(削除された科目)',
            creditAccountId: $entry->creditAccountId(),
            creditAccountName: $accountNames[$entry->creditAccountId()] ?? '(削除された科目)',
            amount: $entry->amount()->amount,
            amountLabel: $entry->amount()->format(),
            description: $entry->description(),
            note: $entry->note(),
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
            'date' => $this->date,
            'dateLabel' => $this->dateLabel,
            'debitAccountId' => $this->debitAccountId,
            'debitAccountName' => $this->debitAccountName,
            'creditAccountId' => $this->creditAccountId,
            'creditAccountName' => $this->creditAccountName,
            'amount' => $this->amount,
            'amountLabel' => $this->amountLabel,
            'description' => $this->description,
            'note' => $this->note,

            'urls' => $this->id === null ? null : [
                'edit' => route('journal.edit', ['id' => $this->id]),
                'delete' => route('journal.delete', ['id' => $this->id]),
            ],
        ];
    }

    /**
     * @param list<JournalEntry> $entries
     * @param array<int, string> $accountNames
     * @return Collection<int, self>
     */
    public static function collection(array $entries, array $accountNames = []): Collection
    {
        return collect($entries)
            ->map(static fn (JournalEntry $entry): self => self::fromEntity($entry, $accountNames))
            ->values();
    }
}
