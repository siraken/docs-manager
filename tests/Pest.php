<?php

use App\Infrastructure\Persistence\Eloquent\Models\Account;
use App\Infrastructure\Persistence\Eloquent\Models\Assignment;
use App\Infrastructure\Persistence\Eloquent\Models\Contract;
use App\Infrastructure\Persistence\Eloquent\Models\Course;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use App\Infrastructure\Persistence\Eloquent\Models\JournalEntry;
use App\Infrastructure\Persistence\Eloquent\Models\OrderHeader;
use App\Infrastructure\Persistence\Eloquent\Models\Report;
use App\Infrastructure\Persistence\Eloquent\Models\Submission;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature テストは Laravel の TestCase を通す。TestCase::setUp() で
| withoutVite() を呼んでいるため、テスト前に pnpm build しなくても
| @vite ディレクティブが解決できる。
|
| Unit テストはフレームワークを起動しない素の PHPUnit TestCase のまま。
| ドメイン層は Laravel に依存しないので、その多くは Unit で書ける。
|
*/

uses(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
|
| DB を触る Feature テストはすべて sqlite の :memory: 上で動く
| (接続先は phpunit.xml で指定)。
|
*/

uses(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Pest ではテストファイル内で定義した関数もグローバルになるため、
| 複数のテストファイルで使うフィクスチャはここに集約する。
|
| フィクスチャは Eloquent モデルを直接使う。ユースケース経由にすると
| 「準備」と「検証対象」が同じ経路になり、リグレッションを検出できなくなるため。
|
*/

/**
 * テスト用ユーザーを作る。
 */
function createUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => Hash::make('secret123'),
    ], $attributes));
}

/**
 * ログイン済みセッションを張ったテストケースを返す。
 *
 * このアプリは Illuminate\Auth を使わず session('user_id'/'name'/'email') を
 * 手で組み立てているため、actingAs ではなく withSession で再現する。
 */
function actingAsUser(User $user): TestCase
{
    return test()->withSession([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
}

/**
 * 顧客を 1 件作る。
 */
function createCustomer(string $name = '株式会社テスト'): Customer
{
    return Customer::create([
        'name' => $name,
        'is_company' => 1,
        'email' => 'client@example.com',
        'phone' => '03-0000-0000',
        'post_code' => '100-0001',
        'address' => '千代田1-1',
        'city' => '千代田区',
        'state' => '東京都',
        'country' => '日本',
        'note' => null,
    ]);
}

/**
 * 発注書ヘッダーを 1 件作る。
 */
function createHeader(array $attributes = []): OrderHeader
{
    return OrderHeader::create(array_merge([
        'customer_id' => 1,
        'responsible' => '担当者',
        'honor_title' => '御中',
        'issued_date' => '2026-09-01',
        'exp_date' => '2026-09-30',
        'order_no' => 'NO-001',
        'title' => 'テスト発注',
        'subtotal_price' => 1000,
        'tax_price' => 100,
        'total_price' => 1100,
        'remarks' => '備考',
        'is_issued' => 0,
        'is_ordered' => 0,
        'is_deleted' => 0,
        'is_converted' => 0,
    ], $attributes));
}

/**
 * 発注書フォームの POST ペイロード。明細は配列で送られる。
 *
 * 金額 (price[]) は送っても使われない。保存される金額はサーバー側で
 * 数量・単価・税区分から計算し直すため。
 */
function orderPayload(array $overrides = []): array
{
    return array_merge([
        'customer_id' => 1,
        'responsible' => '担当者',
        'honor_title' => '御中',
        'issued_date' => '2026-09-01',
        'exp_date' => '2026-09-30',
        'order_no' => 'NO-001',
        'title' => 'テスト発注',
        'remarks' => '備考',
        'item_name' => ['商品A', '商品B'],
        'qty' => [1, 2],
        'unit' => ['個', '式'],
        'cost' => [1000, 250],
        'tax' => [1, 1],
    ], $overrides);
}

/**
 * 勤務報告を 1 件作る。
 *
 * work_minutes を直接指定する点に注意。ユースケース経由だと始業・終業から
 * 計算し直されてしまい、「準備」と「検証対象」が同じ経路になる。
 */
function createReport(array $attributes = []): Report
{
    return Report::create(array_merge([
        'user_id' => null,
        'customer_id' => null,
        'project_id' => null,
        'title' => '実装作業',
        'description' => null,
        'date' => '2026-09-04',
        'start_time' => '09:00',
        'end_time' => '18:00',
        'work_minutes' => 540,
    ], $attributes));
}

/**
 * 勤務報告フォームの POST ペイロード。
 *
 * 勤務時間 (work_time) は送っても、始業・終業が揃っていれば使われない。
 * 保存される値はサーバー側で計算し直すため。
 */
function reportPayload(array $overrides = []): array
{
    return array_merge([
        'title' => '実装作業',
        'date' => '2026-09-04',
        'start_time' => '09:00',
        'end_time' => '18:00',
        'work_time' => '9',
        'description' => '詳細',
    ], $overrides);
}

/**
 * 契約を 1 件作る。
 */
function createContract(array $attributes = []): Contract
{
    return Contract::create(array_merge([
        'name' => '保守契約',
        'contract_no' => 'CT-001',
        'customer_id' => null,
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
        'description' => null,
    ], $attributes));
}

/**
 * 契約フォームの POST ペイロード。
 */
function contractPayload(array $overrides = []): array
{
    return array_merge([
        'name' => '保守契約',
        'contract_no' => 'CT-001',
        'start_date' => '2026-09-01',
        'end_date' => '2027-08-31',
        'description' => '月額保守',
    ], $overrides);
}

/**
 * 勘定科目を 1 件作る。
 */
function createAccount(string $name = '現金', string $type = 'asset', array $attributes = []): Account
{
    return Account::create(array_merge([
        'code' => null,
        'name' => $name,
        'type' => $type,
        'is_active' => true,
        'note' => null,
    ], $attributes));
}

/**
 * 借方・貸方の勘定科目を作って返す。仕訳のテストの下ごしらえ。
 *
 * @return array{0: Account, 1: Account} [借方科目, 貸方科目]
 */
function createAccountPair(): array
{
    return [createAccount('現金', 'asset'), createAccount('売上', 'revenue')];
}

/**
 * 仕訳を 1 件作る。
 */
function createJournalEntry(int $debitId, int $creditId, array $attributes = []): JournalEntry
{
    return JournalEntry::create(array_merge([
        'date' => '2026-09-04',
        'debit_account_id' => $debitId,
        'credit_account_id' => $creditId,
        'amount' => 1000,
        'description' => '売上の計上',
        'note' => null,
    ], $attributes));
}

/**
 * 仕訳フォームの POST ペイロード。
 */
function journalPayload(int $debitId, int $creditId, array $overrides = []): array
{
    return array_merge([
        'date' => '2026-09-04',
        'debit_account_id' => $debitId,
        'credit_account_id' => $creditId,
        'amount' => 1000,
        'description' => '売上の計上',
        'note' => null,
    ], $overrides);
}

/**
 * 講座を 1 件作る。
 */
function createCourse(string $title = 'Laravel 入門', int $exp = 50, array $attributes = []): Course
{
    return Course::create(array_merge([
        'title' => $title,
        'description' => null,
        'exp' => $exp,
        'is_published' => true,
    ], $attributes));
}

/**
 * 受講記録を 1 件作る。
 *
 * 状態と日付を直接指定する点に注意。ユースケース経由だと整合が
 * 調整されてしまい、「準備」と「検証対象」が同じ経路になる。
 */
function createEnrollment(int $userId, int $courseId, array $attributes = []): Enrollment
{
    return Enrollment::create(array_merge([
        'user_id' => $userId,
        'course_id' => $courseId,
        'status' => 'completed',
        'started_at' => '2026-09-01',
        'completed_at' => '2026-09-30',
        'note' => null,
    ], $attributes));
}

/**
 * 受講記録フォームの POST ペイロード。
 */
function enrollmentPayload(int $userId, int $courseId, array $overrides = []): array
{
    return array_merge([
        'user_id' => $userId,
        'course_id' => $courseId,
        'status' => 'completed',
        'started_at' => '2026-09-01',
        'completed_at' => '2026-09-30',
        'note' => null,
    ], $overrides);
}

/**
 * 課題を 1 件作る。
 */
function createAssignment(int $courseId, array $attributes = []): Assignment
{
    return Assignment::create(array_merge([
        'course_id' => $courseId,
        'title' => '演習 1',
        'description' => null,
        'due_on' => '2026-09-30',
    ], $attributes));
}

/**
 * 提出物を 1 件作る。
 *
 * 状態と日付を直接指定する点に注意。ユースケース経由だと整合が
 * 調整されてしまい、「準備」と「検証対象」が同じ経路になる。
 */
function createSubmission(int $assignmentId, int $userId, array $attributes = []): Submission
{
    return Submission::create(array_merge([
        'assignment_id' => $assignmentId,
        'user_id' => $userId,
        'status' => 'submitted',
        'submitted_at' => '2026-09-20',
        'body' => '提出内容',
        'feedback' => null,
    ], $attributes));
}

/**
 * 提出物フォームの POST ペイロード。
 */
function submissionPayload(int $assignmentId, int $userId, array $overrides = []): array
{
    return array_merge([
        'assignment_id' => $assignmentId,
        'user_id' => $userId,
        'status' => 'submitted',
        'submitted_at' => '2026-09-20',
        'body' => '提出内容',
        'feedback' => null,
    ], $overrides);
}

/**
 * 有効な形式のウォレットアドレス (0x + 40 桁)。
 */
function walletAddress(string $suffix = '1'): string
{
    return '0x' . str_pad($suffix, 40, '0', STR_PAD_LEFT);
}
