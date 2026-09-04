<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;

final readonly class ListAccountsUseCase
{
    public function __construct(private AccountRepositoryInterface $accounts)
    {
    }

    /**
     * @param bool $activeOnly 仕訳フォームの選択肢に使うときは true
     * @return list<Account>
     */
    public function execute(bool $activeOnly = false): array
    {
        return $activeOnly ? $this->accounts->listActive() : $this->accounts->listAll();
    }
}
