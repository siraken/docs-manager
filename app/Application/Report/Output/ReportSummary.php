<?php

declare(strict_types=1);

namespace App\Application\Report\Output;

use App\Domain\Report\Entity\Report;
use App\Domain\Report\ValueObject\WorkTime;

/**
 * 勤務報告の集計。
 *
 * 移植前の一覧は
 *   - 総勤務日数を $reports->sum('work_days') で出していたが work_days という
 *     カラムは存在せず、常に 0 だった
 *   - 総勤務時間はビューの中でページ内の行だけを足していたため、2 ページ目
 *     以降の分が抜けていた (「総」勤務時間ではなかった)
 * という二重の壊れ方をしていた。集計対象は必ず「絞り込み結果の全件」。
 */
final readonly class ReportSummary
{
    /** @param list<Report> $reports */
    private function __construct(
        public array $reports,
        public WorkTime $totalWorkTime,
        public int $workDays,
    ) {
    }

    /** @param list<Report> $reports */
    public static function of(array $reports): self
    {
        $total = WorkTime::zero();
        $dates = [];

        foreach ($reports as $report) {
            $total = $total->add($report->workTime());
            // 同じ日に複数件あっても 1 日と数える
            $dates[$report->date()->format('Y-m-d')] = true;
        }

        return new self($reports, $total, count($dates));
    }
}
