<?php

declare(strict_types=1);

namespace App\Application\Report\UseCase;

use App\Domain\Report\Repository\ReportRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * 勤務報告を削除する。
 *
 * 移植前はルートが destroy を指しているのにコントローラのメソッド名が
 * delete で、削除しようとすると必ず 500 になっていた。
 */
final readonly class DeleteReportUseCase
{
    public function __construct(private ReportRepositoryInterface $reports)
    {
    }

    public function execute(int $id): void
    {
        if ($this->reports->findById($id) === null) {
            throw EntityNotFoundException::of('勤務報告', $id);
        }

        $this->reports->delete($id);
    }
}
