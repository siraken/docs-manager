<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 契約。in-house-timecard-app から移植したもの。
 *
 * 移植元との差分:
 *  - client_id (整数・参照先なし) を customer_id にして customers を指す
 *  - contract_id を contract_no に改名 (id 系のカラム名は主キーと紛らわしく、
 *    実体は「契約番号」という業務上の識別子だった)
 *  - フォームが送っていた pid は、そもそもこのテーブルに無いカラムだった
 *    (案件フォームの丸写しで、送られた値は静かに捨てられていた)。復元しない
 *  - description を string から text へ (備考欄は 255 文字に収まらない)
 *
 * 外部キー制約は張らない。このアプリは Eloquent のリレーションを定義せず、
 * 関連の組み立てをリポジトリの仕事にしているため (AGENTS.md 参照)。
 * 絞り込みに使うカラムには索引だけ張る。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contract_no', 32)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
