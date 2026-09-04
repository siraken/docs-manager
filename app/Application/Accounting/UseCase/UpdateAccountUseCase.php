<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Input\AccountInput;
use App\Domain\Accounting\Entity\Account;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateAccountUseCase
{
    public function __construct(private AccountRepositoryInterface $accounts)
    {
    }

    public function execute(int $id, AccountInput $input): Account
    {
        $account = $this->accounts->findById($id)
            ?? throw EntityNotFoundException::of('勘定科目', $id);

        $account->update(
            name: $input->name,
            code: $input->code,
            type: $input->typeValue(),
            isActive: $input->isActive,
            note: $input->note,
        );

        return $this->accounts->save($account);
    }
}
