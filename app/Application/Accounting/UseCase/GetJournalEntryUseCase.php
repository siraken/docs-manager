<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetJournalEntryUseCase
{
    public function __construct(private JournalEntryRepositoryInterface $entries)
    {
    }

    public function execute(int $id): JournalEntry
    {
        return $this->entries->findById($id)
            ?? throw EntityNotFoundException::of('仕訳', $id);
    }
}
