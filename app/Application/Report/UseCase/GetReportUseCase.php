<?php

declare(strict_types=1);

namespace App\Application\Report\UseCase;

use App\Domain\Report\Entity\Report;
use App\Domain\Report\Repository\ReportRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class GetReportUseCase
{
    public function __construct(private ReportRepositoryInterface $reports)
    {
    }

    public function execute(int $id): Report
    {
        return $this->reports->findById($id)
            ?? throw EntityNotFoundException::of('勤務報告', $id);
    }
}
