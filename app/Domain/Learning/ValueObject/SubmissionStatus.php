<?php

declare(strict_types=1);

namespace App\Domain\Learning\ValueObject;

/**
 * 提出物の状態。
 *
 * 参考にした novalumo/e-learning は tasks テーブルが id と timestamps しか
 * 持たず、状態どころか課題そのものが未実装だった。手掛かりはビューの
 * サイドバーにあった「提出物」という項目名だけ。
 */
enum SubmissionStatus: string
{
    case NotSubmitted = 'not_submitted';
    case Submitted = 'submitted';
    case Returned = 'returned';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::NotSubmitted => '未提出',
            self::Submitted => '提出済み',
            self::Returned => '差し戻し',
            self::Approved => '合格',
        };
    }

    /** 提出日を持つ状態か。未提出だけが持たない */
    public function requiresSubmittedAt(): bool
    {
        return $this !== self::NotSubmitted;
    }

    /** 講師の講評を書ける状態か。提出される前に評価はできない */
    public function acceptsFeedback(): bool
    {
        return $this === self::Returned || $this === self::Approved;
    }

    public static function fromNullable(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::NotSubmitted;
        }

        return self::tryFrom((string) $value) ?? self::NotSubmitted;
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
