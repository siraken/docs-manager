<?php

declare(strict_types=1);

namespace App\Domain\Learning\Entity;

/**
 * 課題。講座に紐づく提出物の題目。
 *
 * 参考にした novalumo/e-learning の tasks テーブルは id と timestamps しか
 * 持っておらず、コントローラは view を返すだけ、ビューは settings 画面の
 * 丸写しだった。手掛かりはサイドバーの「提出物」という項目名だけで、
 * 設計は実質すべて新規。
 */
final class Assignment
{
    private function __construct(
        private ?int $id,
        private int $courseId,
        private string $title,
        private ?string $description,
        private ?\DateTimeImmutable $dueOn,
    ) {
    }

    public static function create(
        int $courseId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueOn,
    ): self {
        return new self(null, $courseId, $title, $description, $dueOn);
    }

    public static function reconstitute(
        int $id,
        int $courseId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueOn,
    ): self {
        return new self($id, $courseId, $title, $description, $dueOn);
    }

    public function update(
        int $courseId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueOn,
    ): void {
        $this->courseId = $courseId;
        $this->title = $title;
        $this->description = $description;
        $this->dueOn = $dueOn;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function courseId(): int
    {
        return $this->courseId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /** 提出期限。設けない課題もある */
    public function dueOn(): ?\DateTimeImmutable
    {
        return $this->dueOn;
    }

    /**
     * その日付が期限を過ぎているか。期限が無ければ常に false。
     *
     * 期限当日は遅れていないものとして扱う。
     */
    public function isOverdueOn(\DateTimeImmutable $date): bool
    {
        return $this->dueOn !== null && $date->setTime(0, 0) > $this->dueOn;
    }
}
