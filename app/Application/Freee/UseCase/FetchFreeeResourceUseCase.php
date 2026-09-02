<?php

declare(strict_types=1);

namespace App\Application\Freee\UseCase;

use App\Application\Freee\Input\FreeeResource;
use App\Application\Freee\Port\FreeeApiClientInterface;

final readonly class FetchFreeeResourceUseCase
{
    public function __construct(private FreeeApiClientInterface $client)
    {
    }

    /** @return array<string, mixed> */
    public function execute(FreeeResource $resource): array
    {
        return $this->client->fetch($resource);
    }
}
