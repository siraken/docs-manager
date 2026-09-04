<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Input\JournalFilter;
use App\Application\Accounting\Output\Journal;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;

final readonly class ListJournalEntriesUseCase
{
    public function __construct(private JournalEntryRepositoryInterface $entries)
    {
    }

    public function execute(JournalFilter $filter): Journal
    {
        return Journal::of($this->entries->search(
            $filter->year,
            $filter->month,
            $filter->keyword,
            $filter->accountId,
        ));
    }
}
