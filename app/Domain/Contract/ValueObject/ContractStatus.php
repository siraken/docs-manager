<?php

declare(strict_types=1);

namespace App\Domain\Contract\ValueObject;

/**
 * 契約の状態。カラムとしては持たず、契約期間と基準日から導出する。
 *
 * 移植元の一覧は列見出しが「契約期間」なのに開始日しか出しておらず、
 * しかも中身は存在しないカラム ($contract['pid']) を引いていた。
 * 期間から状態を導けば、保存された日付と表示が食い違いようがない。
 */
enum ContractStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => '開始前',
            self::Active => '契約中',
            self::Expired => '終了',
        };
    }
}
