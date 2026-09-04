<?php

declare(strict_types=1);

namespace App\Domain\Learning\ValueObject;

/**
 * 受講の状態。
 *
 * 参考にした e-learning は records テーブルが id と timestamps しか持たず、
 * 状態という概念自体が無かった。
 */
enum EnrollmentStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => '未受講',
            self::InProgress => '受講中',
            self::Completed => '完了',
        };
    }

    /** 完了した受講だけが exp を与える */
    public function earnsExperience(): bool
    {
        return $this === self::Completed;
    }

    public static function fromNullable(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::NotStarted;
        }

        return self::tryFrom((string) $value) ?? self::NotStarted;
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
