<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Travel\Input\TravelInput;
use App\Domain\Travel\Entity\Travel;
use App\Domain\Travel\Repository\TravelRepositoryInterface;

final readonly class CreateTravelUseCase
{
    public function __construct(private TravelRepositoryInterface $travels)
    {
    }

    public function execute(TravelInput $input): Travel
    {
        return $this->travels->save($input->toTravel());
    }
}
