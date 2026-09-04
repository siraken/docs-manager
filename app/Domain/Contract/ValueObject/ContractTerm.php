<?php

declare(strict_types=1);

namespace App\Domain\Contract\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 契約期間。開始日と終了日の組で、どちらも省略できる。
 *
 * 終了日だけを持つ契約 (開始日不明) も表現できるようにしてあるのは、
 * 移植元の contracts テーブルが両方 nullable だったため。
 */
final readonly class ContractTerm
{
    private function __construct(
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $endDate,
    ) {
    }

    /** @throws InvalidValueException 終了日が開始日より前のとき */
    public static function of(?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate): self
    {
        if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
            throw new InvalidValueException('契約の終了日は開始日以降にしてください。');
        }

        return new self($startDate, $endDate);
    }

    public static function empty(): self
    {
        return new self(null, null);
    }

    /**
     * 基準日における状態。
     *
     * 開始日が無いものは「開始済み」、終了日が無いものは「期限なし」として扱う。
     * 境界は両端とも含む (開始日当日は契約中、終了日当日も契約中)。
     */
    public function statusOn(\DateTimeImmutable $date): ContractStatus
    {
        $on = $date->setTime(0, 0);

        if ($this->startDate !== null && $on < $this->startDate) {
            return ContractStatus::Scheduled;
        }

        if ($this->endDate !== null && $on > $this->endDate) {
            return ContractStatus::Expired;
        }

        return ContractStatus::Active;
    }

    /** 「2026/09/01 〜 2027/03/31」のような表示。両端とも未設定なら "-" */
    public function label(): string
    {
        if ($this->startDate === null && $this->endDate === null) {
            return '-';
        }

        return sprintf(
            '%s 〜 %s',
            $this->startDate?->format('Y/m/d') ?? '',
            $this->endDate?->format('Y/m/d') ?? '',
        );
    }
}
