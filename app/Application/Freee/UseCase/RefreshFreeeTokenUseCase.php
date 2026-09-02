<?php

declare(strict_types=1);

namespace App\Application\Freee\UseCase;

use App\Application\Freee\Port\FreeeApiClientInterface;

final readonly class RefreshFreeeTokenUseCase
{
    public function __construct(private FreeeApiClientInterface $client)
    {
    }

    /**
     * @param string|null $refreshToken null または "null" のときは .env のトークンを使う
     * @return array<string, mixed>
     */
    public function execute(?string $refreshToken): array
    {
        return $this->client->refreshAccessToken($refreshToken);
    }
}
