<?php

declare(strict_types=1);

namespace App\Domain\Learning\Entity;

use App\Domain\Learning\ValueObject\SubmissionStatus;
use App\Domain\Shared\Exception\InvalidValueException;

/**
 * 提出物。「誰がどの課題を出して、どう評価されたか」。
 *
 * 受講記録 (Enrollment) と同じく、**状態と日付・講評は必ず噛み合う**。
 * 未提出なら提出日を持たず、提出される前に講評は書けない。
 */
final class Submission
{
    private function __construct(
        private ?int $id,
        private int $assignmentId,
        private int $userId,
        private SubmissionStatus $status,
        private ?\DateTimeImmutable $submittedAt,
        private ?string $body,
        private ?string $feedback,
    ) {
    }

    public static function create(
        int $assignmentId,
        int $userId,
        SubmissionStatus $status,
        ?\DateTimeImmutable $submittedAt,
        ?string $body,
        ?string $feedback,
    ): self {
        [$submittedAt, $feedback] = self::reconcile($status, $submittedAt, $feedback);

        return new self(null, $assignmentId, $userId, $status, $submittedAt, $body, $feedback);
    }

    /**
     * 保存済みの状態から組み直す。
     *
     * 整合の調整は通さない。規則を後から足したときに過去の行が読めなくなり、
     * 一覧ごと開けなくなるのを防ぐ。
     */
    public static function reconstitute(
        int $id,
        int $assignmentId,
        int $userId,
        SubmissionStatus $status,
        ?\DateTimeImmutable $submittedAt,
        ?string $body,
        ?string $feedback,
    ): self {
        return new self($id, $assignmentId, $userId, $status, $submittedAt, $body, $feedback);
    }

    public function update(
        int $assignmentId,
        int $userId,
        SubmissionStatus $status,
        ?\DateTimeImmutable $submittedAt,
        ?string $body,
        ?string $feedback,
    ): void {
        [$submittedAt, $feedback] = self::reconcile($status, $submittedAt, $feedback);

        $this->assignmentId = $assignmentId;
        $this->userId = $userId;
        $this->status = $status;
        $this->submittedAt = $submittedAt;
        $this->body = $body;
        $this->feedback = $feedback;
    }

    /**
     * 状態に合わせて提出日と講評を整える。
     *
     * - 未提出: 提出日も講評も持たない
     * - 提出済み: 提出日が要る。講評はまだ持たない
     * - 差し戻し / 合格: 提出日が要る。講評を持てる
     *
     * @return array{0: ?\DateTimeImmutable, 1: ?string} [提出日, 講評]
     * @throws InvalidValueException
     */
    private static function reconcile(
        SubmissionStatus $status,
        ?\DateTimeImmutable $submittedAt,
        ?string $feedback,
    ): array {
        if (!$status->requiresSubmittedAt()) {
            return [null, null];
        }

        if ($submittedAt === null) {
            throw new InvalidValueException(
                sprintf('「%s」にするには提出日を入力してください。', $status->label()),
            );
        }

        // 提出される前に評価はできない
        return [$submittedAt, $status->acceptsFeedback() ? $feedback : null];
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignmentId(): int
    {
        return $this->assignmentId;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function status(): SubmissionStatus
    {
        return $this->status;
    }

    public function submittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function body(): ?string
    {
        return $this->body;
    }

    public function feedback(): ?string
    {
        return $this->feedback;
    }

    /**
     * 期限に遅れて出したか。
     *
     * 未提出のものは「遅れた」とは呼ばない（まだ出していないだけ）。
     * 期限切れかどうかは一覧側が今日の日付で別途判定する。
     */
    public function isLate(Assignment $assignment): bool
    {
        return $this->submittedAt !== null && $assignment->isOverdueOn($this->submittedAt);
    }
}
