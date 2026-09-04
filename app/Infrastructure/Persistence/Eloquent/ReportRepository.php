<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Report\Entity\Report;
use App\Domain\Report\Repository\ReportRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\ReportMapper;
use App\Infrastructure\Persistence\Eloquent\Models\Report as ReportModel;

final class ReportRepository implements ReportRepositoryInterface
{
    /** @return list<Report> */
    public function search(?int $year, ?int $month, ?string $keyword): array
    {
        $query = ReportModel::query();

        if ($year !== null) {
            $query->whereYear('date', $year);
        }

        if ($month !== null) {
            $query->whereMonth('date', $month);
        }

        if ($keyword !== null) {
            // LIKE のワイルドカードを打ち込まれても部分一致のままにする
            $escaped = addcslashes($keyword, '\\%_');
            $query->where('title', 'like', '%' . $escaped . '%');
        }

        return $query->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->map(ReportMapper::toDomain(...))
            ->all();
    }

    public function findById(int $id): ?Report
    {
        $model = ReportModel::find($id);

        return $model === null ? null : ReportMapper::toDomain($model);
    }

    public function save(Report $report): Report
    {
        $model = $report->id() === null
            ? new ReportModel()
            : ReportModel::find($report->id()) ?? new ReportModel();

        $model->fill(ReportMapper::toAttributes($report));
        $model->save();

        $report->assignId((int) $model->id);

        return $report;
    }

    public function delete(int $id): void
    {
        ReportModel::destroy($id);
    }
}
