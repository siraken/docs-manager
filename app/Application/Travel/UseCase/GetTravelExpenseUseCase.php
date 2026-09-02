<?php

declare(strict_types=1);

namespace App\Application\Travel\UseCase;

use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Travel\Entity\TravelExpense;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;

final readonly class GetTravelExpenseUseCase
{
    public function __construct(private TravelExpenseRepositoryInterface $expenses)
    {
    }

    public function execute(int $id): TravelExpense
    {
        return $this->expenses->findById($id)
            ?? throw EntityNotFoundException::of('出張旅費精算', $id);
    }
}
