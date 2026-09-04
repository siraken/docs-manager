<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Input\JournalEntryInput;
use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateJournalEntryUseCase
{
    public function __construct(private JournalEntryRepositoryInterface $entries)
    {
    }

    public function execute(int $id, JournalEntryInput $input): JournalEntry
    {
        $entry = $this->entries->findById($id)
            ?? throw EntityNotFoundException::of('仕訳', $id);

        $entry->update(
            date: $input->dateValue(),
            debitAccountId: $input->debitAccountId,
            creditAccountId: $input->creditAccountId,
            amount: $input->amountValue(),
            description: $input->description,
            note: $input->note,
        );

        return $this->entries->save($entry);
    }
}
