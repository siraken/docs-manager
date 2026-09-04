<?php

declare(strict_types=1);

namespace App\Application\Report\UseCase;

use App\Application\Report\Input\ReportFilter;
use App\Application\Report\Output\ReportSummary;
use App\Domain\Report\Repository\ReportRepositoryInterface;

/**
 * 絞り込み結果と、その全件に対する集計を返す。
 */
final readonly class ListReportsUseCase
{
    public function __construct(private ReportRepositoryInterface $reports)
    {
    }

    public function execute(ReportFilter $filter): ReportSummary
    {
        return ReportSummary::of(
            $this->reports->search($filter->year, $filter->month, $filter->keyword),
        );
    }
}
