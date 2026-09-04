<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Input\AccountInput;
use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;

final readonly class CreateAccountUseCase
{
    public function __construct(private AccountRepositoryInterface $accounts)
    {
    }

    public function execute(AccountInput $input): Account
    {
        return $this->accounts->save(Account::create(
            name: $input->name,
            code: $input->code,
            type: $input->typeValue(),
            isActive: $input->isActive,
            note: $input->note,
        ));
    }
}
