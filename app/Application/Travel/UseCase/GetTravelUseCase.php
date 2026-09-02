<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Travel\Entity\Travel;
use App\Domain\Travel\Repository\TravelRepositoryInterface;

final readonly class GetTravelUseCase
{
    public function __construct(private TravelRepositoryInterface $travels)
    {
    }

    public function execute(int $id): Travel
    {
        return $this->travels->findById($id)
            ?? throw EntityNotFoundException::of('出張申請', $id);
    }
}
