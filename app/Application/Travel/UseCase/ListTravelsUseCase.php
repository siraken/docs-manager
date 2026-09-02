<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Domain\Travel\Entity\Travel;
use App\Domain\Travel\Repository\TravelRepositoryInterface;

final readonly class ListTravelsUseCase
{
    public function __construct(private TravelRepositoryInterface $travels)
    {
    }

    /** @return list<Travel> */
    public function execute(): array
    {
        return $this->travels->listAll();
    }
}
