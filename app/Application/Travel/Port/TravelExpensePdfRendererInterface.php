<?php

declare(strict_types=1);

namespace App\Application\Travel\Port;

use App\Domain\Travel\Entity\TravelExpense;

/** 出張旅費精算書 PDF (テンプレート PDF への重ね書き) */
interface TravelExpensePdfRendererInterface
{
    public function render(TravelExpense $expense): string;
}
