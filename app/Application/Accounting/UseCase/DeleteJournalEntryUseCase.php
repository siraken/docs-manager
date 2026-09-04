<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class DeleteJournalEntryUseCase
{
    public function __construct(private JournalEntryRepositoryInterface $entries)
    {
    }

    public function execute(int $id): void
    {
        if ($this->entries->findById($id) === null) {
            throw EntityNotFoundException::of('仕訳', $id);
        }

        $this->entries->delete($id);
    }
}
