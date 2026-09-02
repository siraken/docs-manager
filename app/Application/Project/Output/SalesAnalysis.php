<?php

declare(strict_types=1);

namespace App\Application\Project\Output;

use App\Domain\Project\Entity\Project;
use App\Domain\Shared\ValueObject\Money;

/**
 * 売上分析の結果。対象の案件一覧と、その合計金額を持つ。
 *
 * 移行前は「一覧を取る」「合計を取る」で同じ条件のクエリを 2 回投げていた。
 * 合計は取得済みの案件から求めるので、クエリは 1 回で済む。
 */
final readonly class SalesAnalysis
{
    /** @param list<Project> $projects */
    public function __construct(
        public array $projects,
        public string $dateField,
        public int $year,
        public int $month,
    ) {
    }

    public function totalPrice(): Money
    {
        return array_reduce(
            $this->projects,
            static fn (Money $carry, Project $project): Money => $carry->add($project->price()),
            Money::zero(),
        );
    }
}
