<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 提出物。「誰がどの課題を出して、どう評価されたか」。
 *
 * 同じ提出者が同じ課題の提出物を 2 つ持たないよう複合ユニークを張る
 * (再提出は既存の行を更新する)。受講記録 (enrollments) と同じ考え方。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            // not_submitted / submitted / returned / approved
            $table->string('status', 16)->index();
            $table->date('submitted_at')->nullable();
            // 提出内容。テキストか、成果物へのリンクを想定
            $table->text('body')->nullable();
            // 講師の講評。差し戻し・合格のときだけ入る
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
