<?php

declare(strict_types=1);

namespace App\Application\Report\UseCase;

use App\Application\Report\Input\ReportInput;
use App\Domain\Report\Entity\Report;
use App\Domain\Report\Repository\ReportRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

final readonly class UpdateReportUseCase
{
    public function __construct(private ReportRepositoryInterface $reports)
    {
    }

    public function execute(int $id, ReportInput $input): Report
    {
        $report = $this->reports->findById($id)
            ?? throw EntityNotFoundException::of('勤務報告', $id);

        $report->update(
            userId: $input->userId,
            customerId: $input->customerId,
            projectId: $input->projectId,
            title: $input->title,
            description: $input->description,
            date: $input->dateValue(),
            startTime: $input->startTimeValue(),
            endTime: $input->endTimeValue(),
            declaredWorkTime: $input->workTimeValue(),
        );

        return $this->reports->save($report);
    }
}
