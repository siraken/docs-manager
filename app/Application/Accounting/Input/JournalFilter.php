<?php

declare(strict_types=1);

namespace App\Application\Accounting\Input;

/**
 * 仕訳帳の絞り込み条件。
 *
 * 参考実装の検索は摘要・その他の全文一致だけで、日付でも科目でも絞れなかった。
 */
final readonly class JournalFilter
{
    private function __construct(
        public ?int $year,
        public ?int $month,
        public ?string $keyword,
        public ?int $accountId,
    ) {
    }

    /**
     * リクエストの生の値から組む。
     *
     * 勤務報告 (ReportFilter) と違って年月の既定値は入れない。仕訳は
     * 「当月分だけ見たい」とは限らず、期を通した残高を見ることが多いため。
     */
    public static function of(mixed $year, mixed $month, mixed $keyword, mixed $accountId = null): self
    {
        $parsedMonth = self::intOrNull($month);

        if ($parsedMonth !== null && ($parsedMonth < 1 || $parsedMonth > 12)) {
            $parsedMonth = null;
        }

        return new self(
            self::intOrNull($year),
            $parsedMonth,
            is_string($keyword) && trim($keyword) !== '' ? trim($keyword) : null,
            self::intOrNull($accountId),
        );
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
