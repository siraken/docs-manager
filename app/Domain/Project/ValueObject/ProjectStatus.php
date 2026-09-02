<?php

declare(strict_types=1);

namespace App\Domain\Project\ValueObject;

/**
 * 案件の状態 (projects.status)。
 *
 * 移行前は「フォームの選択肢が 8 種類 (作業中〜見積中)」なのに
 * 「一覧の表示が 3 種類 (未着手 / 進行中 / 完了)」という食い違いがあり、
 * さらに一覧側は switch の誤用で status = 0 が「進行中」と表示されていた。
 * 実際に保存されうる値の範囲はフォームの 8 種類なので、そちらに統一し、
 * フォームと一覧の双方がこの enum を参照するようにしている。
 */
enum ProjectStatus: int
{
    case InProgress = 0;
    case Done = 1;
    case AwaitingReply = 2;
    case OnHold = 3;
    case Sounding = 4;
    case Maintenance = 5;
    case Cancelled = 6;
    case Estimating = 7;

    public static function fromNullable(mixed $value): self
    {
        if ($value === null || $value === '') {
            return self::InProgress;
        }

        // sqlite は integer カラムを文字列で返すことがあるため int に寄せる
        return self::tryFrom((int) $value) ?? self::InProgress;
    }

    public function label(): string
    {
        return match ($this) {
            self::InProgress => '作業中',
            self::Done => '完了',
            self::AwaitingReply => '連絡待ち',
            self::OnHold => '保留',
            self::Sounding => '打診中',
            self::Maintenance => 'メンテナンス',
            self::Cancelled => 'キャンセル',
            self::Estimating => '見積中',
        };
    }

    /** @return array<int, string> value => label */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
