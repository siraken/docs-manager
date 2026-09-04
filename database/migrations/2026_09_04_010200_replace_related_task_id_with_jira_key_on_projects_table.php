<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 案件の related_task_id を jira_key に置き換える。
 *
 * related_task_id は二重に死んでいた列だった。
 *  - 指す先の tasks テーブルはマイグレーションごと存在しない (App\Models\Task を
 *    削除したときに判明している)
 *  - Domain から TypeScript の型まで全層を通っているのに、フォームにも一覧にも
 *    出ないため値を入れる手段が無い
 *
 * in-house-timecard-app の projects.pid は同じ「案件に紐づく外部の識別子」を
 * 表しつつ、一覧から Jira へリンクする導線として実際に使われていた。整数では
 * NOVA-123 のようなキーを持てないので、文字列の jira_key に入れ替える。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('jira_key', 32)->nullable()->after('client_id');
            $table->dropColumn('related_task_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('related_task_id')->nullable()->after('client_id');
            $table->dropColumn('jira_key');
        });
    }
};
