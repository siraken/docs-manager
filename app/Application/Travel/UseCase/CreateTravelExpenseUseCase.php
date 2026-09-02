<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Travel\Input\TravelExpenseInput;
use App\Domain\Travel\Entity\TravelExpense;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;

final readonly class CreateTravelExpenseUseCase
{
    public function __construct(private TravelExpenseRepositoryInterface $expenses)
    {
    }

    public function execute(TravelExpenseInput $input): TravelExpense
    {
        return $this->expenses->save($input->toTravelExpense());
    }
}
