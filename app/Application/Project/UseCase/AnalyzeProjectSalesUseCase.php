<?php

declare(strict_types=1);

namespace App\Application\Project\UseCase;

use App\Application\Project\Input\SalesAnalysisCriteria;
use App\Application\Project\Output\SalesAnalysis;
use App\Domain\Project\Repository\ProjectRepositoryInterface;

/** 指定した年月の案件を集計する */
final readonly class AnalyzeProjectSalesUseCase
{
    public function __construct(private ProjectRepositoryInterface $projects)
    {
    }

    public function execute(SalesAnalysisCriteria $criteria): SalesAnalysis
    {
        return new SalesAnalysis(
            projects: $this->projects->listByYearMonth($criteria->dateField, $criteria->year, $criteria->month),
            dateField: $criteria->dateField,
            year: $criteria->year,
            month: $criteria->month,
        );
    }
}
