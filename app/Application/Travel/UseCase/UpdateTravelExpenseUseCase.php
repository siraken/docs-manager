<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Application\Travel\Input\TravelExpenseInput;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Travel\Entity\TravelExpense;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;

final readonly class UpdateTravelExpenseUseCase
{
    public function __construct(private TravelExpenseRepositoryInterface $expenses)
    {
    }

    public function execute(int $id, TravelExpenseInput $input): TravelExpense
    {
        $expense = $this->expenses->findById($id)
            ?? throw EntityNotFoundException::of('出張旅費精算', $id);

        $input->applyTo($expense);

        return $this->expenses->save($expense);
    }
}
