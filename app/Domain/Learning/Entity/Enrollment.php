<?php

declare(strict_types=1);

namespace App\Domain\Learning\Entity;

use App\Domain\Learning\ValueObject\EnrollmentStatus;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 受講記録。「誰がどの講座をどこまで進めたか」を 1 行で表す。
 *
 * 参考にした e-learning の records テーブルは id と timestamps しか無く、
 * 受講という概念そのものが未実装だった。ここが設計の中心になる。
 *
 * **状態と日付は必ず噛み合っている。** 完了なら完了日を持ち、未受講なら
 * 開始日も完了日も持たない。状態だけ直して日付が取り残される、という
 * ありがちな不整合を作れないようにしてある。
 */
final class Enrollment
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private int $courseId,
        private EnrollmentStatus $status,
        private ?\DateTimeImmutable $startedAt,
        private ?\DateTimeImmutable $completedAt,
        private ?string $note,
    ) {
    }

    public static function create(
        int $userId,
        int $courseId,
        EnrollmentStatus $status,
        ?\DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $completedAt,
        ?string $note,
    ): self {
        [$startedAt, $completedAt] = self::reconcile($status, $startedAt, $completedAt);

        return new self(null, $userId, $courseId, $status, $startedAt, $completedAt, $note);
    }

    /**
     * 保存済みの状態から組み直す。
     *
     * 整合の調整は通さない。規則を後から足したときに過去の行が読めなくなり、
     * 一覧ごと開けなくなるのを防ぐ。
     */
    public static function reconstitute(
        int $id,
        int $userId,
        int $courseId,
        EnrollmentStatus $status,
        ?\DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $completedAt,
        ?string $note,
    ): self {
        return new self($id, $userId, $courseId, $status, $startedAt, $completedAt, $note);
    }

    public function update(
        int $userId,
        int $courseId,
        EnrollmentStatus $status,
        ?\DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $completedAt,
        ?string $note,
    ): void {
        [$startedAt, $completedAt] = self::reconcile($status, $startedAt, $completedAt);

        $this->userId = $userId;
        $this->courseId = $courseId;
        $this->status = $status;
        $this->startedAt = $startedAt;
        $this->completedAt = $completedAt;
        $this->note = $note;
    }

    /**
     * 状態に合わせて日付を整える。
     *
     * - 未受講: 開始日も完了日も持たない
     * - 受講中: 完了日は持たない
     * - 完了: 完了日が要る
     *
     * @return array{0: ?\DateTimeImmutable, 1: ?\DateTimeImmutable} [開始日, 完了日]
     * @throws InvalidValueException
     */
    private static function reconcile(
        EnrollmentStatus $status,
        ?\DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $completedAt,
    ): array {
        if ($startedAt !== null && $completedAt !== null && $completedAt < $startedAt) {
            throw new InvalidValueException('完了日は受講開始日以降にしてください。');
        }

        return match ($status) {
            EnrollmentStatus::NotStarted => [null, null],
            EnrollmentStatus::InProgress => [$startedAt, null],
            EnrollmentStatus::Completed => [
                $startedAt,
                $completedAt ?? throw new InvalidValueException('完了にするには完了日を入力してください。'),
            ],
        };
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function courseId(): int
    {
        return $this->courseId;
    }

    public function status(): EnrollmentStatus
    {
        return $this->status;
    }

    public function startedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function completedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    /** ポイントが入るのは完了したときだけ */
    public function earnsExperience(): bool
    {
        return $this->status->earnsExperience();
    }
}
