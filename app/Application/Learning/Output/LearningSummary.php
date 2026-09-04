<?php

declare(strict_types=1);

namespace App\Application\Learning\Output;

use App\Domain\Learning\Entity\Course;
use App\Domain\Learning\Entity\Enrollment;
use App\Domain\Learning\ValueObject\ExperiencePoint;

/**
 * 受講記録の一覧と、その集計。
 *
 * ポイントは完了した受講だけを足す。受講中の講座のぶんまで数えると
 * 「まだ終わっていないのに獲得済み」になってしまう。
 */
final readonly class LearningSummary
{
    /** @param list<Enrollment> $enrollments */
    private function __construct(
        public array $enrollments,
        public int $completedCount,
        public int $inProgressCount,
        public ExperiencePoint $earnedExp,
    ) {
    }

    /**
     * @param list<Enrollment> $enrollments
     * @param array<int, Course> $coursesById 講座 ID => 講座
     */
    public static function of(array $enrollments, array $coursesById): self
    {
        $completed = 0;
        $inProgress = 0;
        $exp = ExperiencePoint::zero();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->earnsExperience()) {
                $completed++;
                // 講座が消えている場合はポイントを数えない
                $course = $coursesById[$enrollment->courseId()] ?? null;
                $exp = $course === null ? $exp : $exp->add($course->exp());
                continue;
            }

            if ($enrollment->status()->value === 'in_progress') {
                $inProgress++;
            }
        }

        return new self($enrollments, $completed, $inProgress, $exp);
    }
}
