<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 課題。講座に紐づく提出物の題目。
 *
 * novalumo/e-learning の tasks テーブルに相当するが、あちらは id と
 * timestamps しか持たず、コントローラは view を返すだけ、ビューは
 * settings 画面の丸写しだった。手掛かりはサイドバーの「提出物」という
 * 項目名だけで、設計は実質すべて新規。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            // 期限を設けない課題もある
            $table->date('due_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
