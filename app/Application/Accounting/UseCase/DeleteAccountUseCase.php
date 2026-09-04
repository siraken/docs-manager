<?php

declare(strict_types=1);

namespace App\Application\Accounting\UseCase;

use App\Application\Accounting\Exception\AccountInUseException;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 勘定科目を削除する。
 *
 * 仕訳から参照されている科目は消せない。消すと過去の仕訳が宙に浮くため。
 * 使わなくなった科目は無効化して選択肢から外す。
 */
final readonly class DeleteAccountUseCase
{
    public function __construct(private AccountRepositoryInterface $accounts)
    {
    }

    public function execute(int $id): void
    {
        $account = $this->accounts->findById($id)
            ?? throw EntityNotFoundException::of('勘定科目', $id);

        if ($this->accounts->isReferenced($id)) {
            throw AccountInUseException::of($account->name());
        }

        $this->accounts->delete($id);
    }
}
