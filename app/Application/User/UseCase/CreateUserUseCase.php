<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\Input\CreateUserInput;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\EmailAddress;

final readonly class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher,
    ) {
    }

    public function execute(CreateUserInput $input): User
    {
        $user = User::create(
            name: $input->name,
            email: EmailAddress::fromString($input->email),
            password: $this->hasher->hash($input->password),
        );

        return $this->users->save($user);
    }
}
