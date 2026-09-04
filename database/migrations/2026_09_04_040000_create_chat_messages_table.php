<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * チャットの発言。全員が読み書きする 1 つのルーム。
 *
 * novalumo/e-learning の chats テーブルを参考にしているが、あちらの
 * uid_to は投稿時に 999 が固定で入るだけで、取得も全件から新しい 15 件を
 * 返していた。画面の宛先一覧も「ユーザー１」が 3 つ並ぶ静的な HTML で、
 * 宛先を選ぶ手段が無かった。**実態は宛先の無い単一のルーム**だったので、
 * 使われていなかった uid_to は持たせていない。
 *
 * uid_from は user_id に改名 (from/to の対が無くなったため)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            // 参考実装は string(255) だった。改行を含む発言が入るので text
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
