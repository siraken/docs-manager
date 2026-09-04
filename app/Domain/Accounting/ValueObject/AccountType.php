<?php

declare(strict_types=1);

namespace App\Domain\Accounting\ValueObject;

/**
 * 勘定科目の区分。
 *
 * 残高がどちらの側に立つか (normalBalance) はこの区分で決まる。資産と費用は
 * 借方が増加、負債・純資産・収益は貸方が増加する。残高試算表はこの規則で
 * 「借方残高 / 貸方残高」を振り分ける。
 *
 * 参考にした移植元 (in-house-timecard-app の CakePHP 時代の仕訳帳) は
 * 科目を自由入力の文字列で持っていたため区分の概念が無く、集計もできなかった。
 *
 * 並び順は貸借対照表・損益計算書の慣習に合わせてある (資産 → 負債 → 純資産
 * → 収益 → 費用)。
 */
enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset => '資産',
            self::Liability => '負債',
            self::Equity => '純資産',
            self::Revenue => '収益',
            self::Expense => '費用',
        };
    }

    /** 残高が立つ側。増加をどちらに記帳するかでもある */
    public function normalBalance(): BalanceSide
    {
        return match ($this) {
            self::Asset, self::Expense => BalanceSide::Debit,
            self::Liability, self::Equity, self::Revenue => BalanceSide::Credit,
        };
    }

    /** 試算表や一覧の並び順 */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Asset => 1,
            self::Liability => 2,
            self::Equity => 3,
            self::Revenue => 4,
            self::Expense => 5,
        };
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
