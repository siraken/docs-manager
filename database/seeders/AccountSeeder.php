<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\Account;
use Illuminate\Database\Seeder;

/**
 * 勘定科目マスタの初期値。
 *
 * 仕訳はこのマスタから科目を選ぶので、空のままだと 1 件も登録できない。
 * 小規模な会社で使う頻度の高いものだけを入れてある。足りないものは
 * 画面 (/accounts) から追加する。
 *
 * コードは日商簿記で一般的な採番に合わせている
 * (1xx 資産 / 2xx 負債 / 3xx 純資産 / 4xx 収益 / 5xx 費用)。
 */
class AccountSeeder extends Seeder
{
    /** @var list<array{code: string, name: string, type: string}> */
    private const ACCOUNTS = [
        // 資産
        ['code' => '101', 'name' => '現金', 'type' => 'asset'],
        ['code' => '102', 'name' => '普通預金', 'type' => 'asset'],
        ['code' => '103', 'name' => '売掛金', 'type' => 'asset'],
        ['code' => '104', 'name' => '前払費用', 'type' => 'asset'],
        ['code' => '105', 'name' => '仮払消費税', 'type' => 'asset'],
        ['code' => '106', 'name' => '工具器具備品', 'type' => 'asset'],

        // 負債
        ['code' => '201', 'name' => '買掛金', 'type' => 'liability'],
        ['code' => '202', 'name' => '未払金', 'type' => 'liability'],
        ['code' => '203', 'name' => '預り金', 'type' => 'liability'],
        ['code' => '204', 'name' => '仮受消費税', 'type' => 'liability'],

        // 純資産
        ['code' => '301', 'name' => '資本金', 'type' => 'equity'],
        ['code' => '302', 'name' => '繰越利益剰余金', 'type' => 'equity'],

        // 収益
        ['code' => '401', 'name' => '売上', 'type' => 'revenue'],
        ['code' => '402', 'name' => '雑収入', 'type' => 'revenue'],

        // 費用
        ['code' => '501', 'name' => '仕入', 'type' => 'expense'],
        ['code' => '502', 'name' => '外注費', 'type' => 'expense'],
        ['code' => '503', 'name' => '給料手当', 'type' => 'expense'],
        ['code' => '504', 'name' => '法定福利費', 'type' => 'expense'],
        ['code' => '505', 'name' => '旅費交通費', 'type' => 'expense'],
        ['code' => '506', 'name' => '通信費', 'type' => 'expense'],
        ['code' => '507', 'name' => '消耗品費', 'type' => 'expense'],
        ['code' => '508', 'name' => '支払手数料', 'type' => 'expense'],
        ['code' => '509', 'name' => '地代家賃', 'type' => 'expense'],
        ['code' => '510', 'name' => '広告宣伝費', 'type' => 'expense'],
        ['code' => '511', 'name' => '会議費', 'type' => 'expense'],
        ['code' => '512', 'name' => '雑費', 'type' => 'expense'],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            // 何度流しても増えないように、コードで突き合わせる
            Account::updateOrCreate(
                ['code' => $account['code']],
                ['name' => $account['name'], 'type' => $account['type'], 'is_active' => true],
            );
        }
    }
}
