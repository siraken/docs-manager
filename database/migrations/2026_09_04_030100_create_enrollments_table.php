<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 受講記録。「誰がどの講座をどこまで進めたか」。
 *
 * novalumo/e-learning の records テーブルに相当するが、あちらは id と
 * timestamps しか持たず、受講という概念そのものが未実装だった。
 * ここは実質すべて新規設計。
 *
 * 同じ受講者が同じ講座の記録を 2 つ持たないよう複合ユニークを張る
 * (受け直しは既存の記録を更新する)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('course_id')->index();
            // not_started / in_progress / completed
            $table->string('status', 16)->index();
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
