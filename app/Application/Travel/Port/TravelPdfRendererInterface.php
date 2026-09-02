<?php

declare(strict_types=1);

namespace App\Application\Travel\Port;

use App\Domain\Travel\Entity\Travel;

/** 出張申請書 PDF (テンプレート PDF への重ね書き) */
interface TravelPdfRendererInterface
{
    public function render(Travel $travel): string;
}
