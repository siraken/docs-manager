<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 講座 (社内研修の教材)。
 *
 * novalumo/e-learning の classes テーブル (title + exp) を引き継いだもの。
 * あちらはテーブルがあるだけで、モデルもコントローラもビューも無かった。
 *
 * テーブル名を classes から courses に変えたのは、class が PHP の予約語で
 * `App\Models\Class` のようなモデルを定義できないため。参考実装がモデルを
 * 作っていなかったのも、おそらくこれが理由。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            // 修了すると得られるポイント。参考実装の classes.exp に相当
            $table->unsignedInteger('exp')->default(0);
            // 下書きの講座は受講記録の選択肢に出さない
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
