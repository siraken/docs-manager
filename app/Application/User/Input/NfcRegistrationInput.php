<?php

declare(strict_types=1);

namespace App\Application\User\Input;

final readonly class NfcRegistrationInput
{
    public function __construct(
        public string $serialNumber,
        public string $pin,
    ) {
    }
}
