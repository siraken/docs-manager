<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Domain\Travel\Entity\TravelExpense;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;

final readonly class ListTravelExpensesUseCase
{
    public function __construct(private TravelExpenseRepositoryInterface $expenses)
    {
    }

    /** @return list<TravelExpense> */
    public function execute(): array
    {
        return $this->expenses->listAll();
    }
}
