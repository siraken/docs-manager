<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Input\JournalEntryInput;
use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;

final readonly class CreateJournalEntryUseCase
{
    public function __construct(private JournalEntryRepositoryInterface $entries)
    {
    }

    public function execute(JournalEntryInput $input): JournalEntry
    {
        return $this->entries->save(JournalEntry::create(
            date: $input->dateValue(),
            debitAccountId: $input->debitAccountId,
            creditAccountId: $input->creditAccountId,
            amount: $input->amountValue(),
            description: $input->description,
            note: $input->note,
        ));
    }
}
