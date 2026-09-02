<?php

declare(strict_types=1);

namespace App\Application\Project\Input;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 売上分析の検索条件。
 *
 * 集計に使う日付カラムはそのままクエリに渡るため、許可した 3 つ以外は弾く
 * (移行前はリクエストの type をそのまま whereYear に渡していた)。
 */
final readonly class SalesAnalysisCriteria
{
    /** @var array<string, string> field => 表示名 */
    public const DATE_FIELDS = [
        'payment_date' => '支払日',
        'start_date' => '開始日',
        'end_date' => '終了日',
    ];

    private function __construct(
        public string $dateField,
        public int $year,
        public int $month,
    ) {
    }

    public static function of(?string $dateField, mixed $year, mixed $month, ?\DateTimeImmutable $now = null): self
    {
        $now ??= new \DateTimeImmutable();

        $dateField ??= array_key_first(self::DATE_FIELDS);

        if (!array_key_exists($dateField, self::DATE_FIELDS)) {
            throw new InvalidValueException(sprintf('集計対象にできない日付項目です: %s', $dateField));
        }

        $year = is_numeric($year) ? (int) $year : (int) $now->format('Y');
        $month = is_numeric($month) ? (int) $month : (int) $now->format('n');

        if ($month < 1 || $month > 12) {
            throw new InvalidValueException(sprintf('月の指定が不正です: %d', $month));
        }

        return new self($dateField, $year, $month);
    }
}
