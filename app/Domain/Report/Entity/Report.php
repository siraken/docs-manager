<?php

declare(strict_types=1);

namespace App\Domain\Report\Entity;

use App\Domain\Report\ValueObject\TimeOfDay;
use App\Domain\Report\ValueObject\WorkTime;

/**
 * 勤務報告。1 件が「誰が・いつ・どの案件で・何時間働いたか」を表す。
 *
 * 勤務時間は始業・終業が揃っていればそこから計算する。フォームが送ってくる
 * 申告値を使うのは、時刻が片方でも欠けているときだけ。発注書の金額を
 * サーバー側で計算し直しているのと同じ考え方で、画面と保存値が食い違わない
 * ようにしている。
 */
final class Report
{
    private function __construct(
        private ?int $id,
        private ?int $userId,
        private ?int $customerId,
        private ?int $projectId,
        private string $title,
        private ?string $description,
        private \DateTimeImmutable $date,
        private ?TimeOfDay $startTime,
        private ?TimeOfDay $endTime,
        private WorkTime $workTime,
    ) {
    }

    public static function create(
        ?int $userId,
        ?int $customerId,
        ?int $projectId,
        string $title,
        ?string $description,
        \DateTimeImmutable $date,
        ?TimeOfDay $startTime,
        ?TimeOfDay $endTime,
        WorkTime $declaredWorkTime,
    ): self {
        return new self(
            null,
            $userId,
            $customerId,
            $projectId,
            $title,
            $description,
            $date,
            $startTime,
            $endTime,
            self::resolveWorkTime($startTime, $endTime, $declaredWorkTime),
        );
    }

    /**
     * 保存済みの状態から組み直す。
     *
     * ここでは勤務時間を導出し直さず、保存されている値をそのまま採る。
     * 導出は create / update の責務で、保存時点で既に適用済みだからである
     * (再計算すると、休憩控除のような規則を後から足したときに過去の記録まで
     * 遡って書き換わってしまう)。
     */
    public static function reconstitute(
        int $id,
        ?int $userId,
        ?int $customerId,
        ?int $projectId,
        string $title,
        ?string $description,
        \DateTimeImmutable $date,
        ?TimeOfDay $startTime,
        ?TimeOfDay $endTime,
        WorkTime $workTime,
    ): self {
        return new self(
            $id,
            $userId,
            $customerId,
            $projectId,
            $title,
            $description,
            $date,
            $startTime,
            $endTime,
            $workTime,
        );
    }

    public function update(
        ?int $userId,
        ?int $customerId,
        ?int $projectId,
        string $title,
        ?string $description,
        \DateTimeImmutable $date,
        ?TimeOfDay $startTime,
        ?TimeOfDay $endTime,
        WorkTime $declaredWorkTime,
    ): void {
        $this->userId = $userId;
        $this->customerId = $customerId;
        $this->projectId = $projectId;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->workTime = self::resolveWorkTime($startTime, $endTime, $declaredWorkTime);
    }

    /**
     * 始業・終業が揃っていればそこから、そうでなければ申告値を採る。
     */
    private static function resolveWorkTime(
        ?TimeOfDay $startTime,
        ?TimeOfDay $endTime,
        WorkTime $declared,
    ): WorkTime {
        if ($startTime !== null && $endTime !== null) {
            return WorkTime::between($startTime, $endTime);
        }

        return $declared;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function customerId(): ?int
    {
        return $this->customerId;
    }

    public function projectId(): ?int
    {
        return $this->projectId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function startTime(): ?TimeOfDay
    {
        return $this->startTime;
    }

    public function endTime(): ?TimeOfDay
    {
        return $this->endTime;
    }

    public function workTime(): WorkTime
    {
        return $this->workTime;
    }
}
