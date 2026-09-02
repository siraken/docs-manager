<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class GetUserUseCase
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(int $id): User
    {
        return $this->users->findById($id)
            ?? throw EntityNotFoundException::of('ユーザー', $id);
    }
}
