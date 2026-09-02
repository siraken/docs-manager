<?php

declare(strict_types=1);

namespace App\Application\Freee\UseCase;

use App\Application\Freee\Port\FreeeApiClientInterface;

final readonly class ExchangeFreeeAuthorizationCodeUseCase
{
    public function __construct(private FreeeApiClientInterface $client)
    {
    }

    /** @return array<string, mixed> */
    public function execute(string $code): array
    {
        return $this->client->exchangeAuthorizationCode($code);
    }
}
