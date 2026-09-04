<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 勤務報告。in-house-timecard-app から移植したもの。
 *
 * 移植元との差分:
 *  - client_id を customer_id にして customers を指す
 *  - project_id を追加。移植元の登録フォームには案件のセレクトがあったが
 *    name 属性が空で送信されず、テーブルにも列が無かった
 *  - work_time (float の時間) を work_minutes (整数の分) に置き換え。
 *    金額を円の整数で持つのと同じ理由で、丸め誤差を持ち込まないため
 *  - title を必須、description を text に
 *
 * 一覧は年月で絞り込むので date に索引を張る。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('work_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
