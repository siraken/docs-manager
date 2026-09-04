<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetAccountUseCase
{
    public function __construct(private AccountRepositoryInterface $accounts)
    {
    }

    public function execute(int $id): Account
    {
        return $this->accounts->findById($id)
            ?? throw EntityNotFoundException::of('勘定科目', $id);
    }
}
