<?php

declare(strict_types=1);

namespace App\Application\Report\Input;

/**
 * 勤務報告の絞り込み条件。
 *
 * 移植前は一覧の年月セレクトが GET で year / month を送るのに、
 * コントローラはルートパラメータ (/reports/{year}/{month}) で受けており、
 * どちらのルートも登録されていなかったため絞り込みが一切効かなかった。
 */
final readonly class ReportFilter
{
    private function __construct(
        public ?int $year,
        public ?int $month,
        public ?string $keyword,
    ) {
    }

    /**
     * リクエストの生の値から組む。
     *
     * 年月の指定が無いときは当月に寄せる。勤務報告は月単位で見るものだし、
     * 何も指定しないと全期間が返って集計の意味が薄れるため。
     */
    public static function of(mixed $year, mixed $month, mixed $keyword, ?\DateTimeImmutable $today = null): self
    {
        $today ??= new \DateTimeImmutable();

        $parsedYear = self::intOrNull($year);
        $parsedMonth = self::intOrNull($month);

        if ($parsedYear === null && $parsedMonth === null) {
            $parsedYear = (int) $today->format('Y');
            $parsedMonth = (int) $today->format('n');
        }

        if ($parsedMonth !== null && ($parsedMonth < 1 || $parsedMonth > 12)) {
            $parsedMonth = null;
        }

        $parsedKeyword = is_string($keyword) && trim($keyword) !== '' ? trim($keyword) : null;

        return new self($parsedYear, $parsedMonth, $parsedKeyword);
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
