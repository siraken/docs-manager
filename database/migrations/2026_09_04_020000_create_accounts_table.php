<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 勘定科目マスタ。
 *
 * 仕訳帳は in-house-timecard-app の CakePHP 時代のテンプレート
 * (resources/views/Posts/*.ctp) を参考に作り直したもの。参考実装は科目を
 * 自由入力の文字列で持っていたため「現金」と「げんきん」が別の科目として
 * 記録され、集計のしようがなかった。マスタにして仕訳から id で参照する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            // 科目コード。任意だが、付ければ試算表の並び順に使われる
            $table->string('code', 16)->nullable()->unique();
            $table->string('name');
            // asset / liability / equity / revenue / expense
            $table->string('type', 16)->index();
            // 使わなくなった科目は削除ではなく無効化する
            // (過去の仕訳が参照しているため消せない)
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
