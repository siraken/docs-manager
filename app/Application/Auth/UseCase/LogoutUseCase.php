<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCase;

use App\Application\Auth\Port\AuthSessionInterface;

final readonly class LogoutUseCase
{
    public function __construct(private AuthSessionInterface $session)
    {
    }

    public function execute(): void
    {
        $this->session->logout();
    }
}
