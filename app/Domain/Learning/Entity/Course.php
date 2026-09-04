<?php

declare(strict_types=1);

namespace App\Domain\Learning\Entity;

use App\Domain\Learning\ValueObject\ExperiencePoint;

/**
 * 講座。社内研修の教材 1 つ分。
 *
 * 参考にした e-learning の classes テーブル (title + exp) を引き継いでいる。
 * あちらはテーブルがあるだけで、モデルもコントローラもビューも無かった。
 *
 * テーブル名を classes から courses に変えたのは、class が PHP の予約語で
 * `App\Models\Class` のようなモデルを定義できないため。参考実装がモデルを
 * 作っていなかったのも、おそらくこれが理由。
 */
final class Course
{
    private function __construct(
        private ?int $id,
        private string $title,
        private ?string $description,
        private ExperiencePoint $exp,
        private bool $isPublished,
    ) {
    }

    public static function create(
        string $title,
        ?string $description,
        ExperiencePoint $exp,
        bool $isPublished,
    ): self {
        return new self(null, $title, $description, $exp, $isPublished);
    }

    public static function reconstitute(
        int $id,
        string $title,
        ?string $description,
        ExperiencePoint $exp,
        bool $isPublished,
    ): self {
        return new self($id, $title, $description, $exp, $isPublished);
    }

    public function update(
        string $title,
        ?string $description,
        ExperiencePoint $exp,
        bool $isPublished,
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->exp = $exp;
        $this->isPublished = $isPublished;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function exp(): ExperiencePoint
    {
        return $this->exp;
    }

    /** 公開中か。下書きの講座は受講記録の選択肢に出さない */
    public function isPublished(): bool
    {
        return $this->isPublished;
    }
}
