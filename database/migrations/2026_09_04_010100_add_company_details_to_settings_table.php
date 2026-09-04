<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 自社情報に会社概要の項目を足す。
 *
 * in-house-timecard-app の company テーブルにあって settings に無かったもの。
 * bank (取引銀行) は発注書 PDF の振込先として印字する。移行前の PDF には
 * 振込先の記載が一切無かった。
 *
 * 既存の settings 行を壊さないよう、いずれも nullable。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->date('established')->nullable()->after('rep');
            // 資本金は円の整数。Money と同じで小数は持たない
            $table->unsignedBigInteger('capital')->nullable()->after('established');
            // 振込先は「銀行名 / 支店名 / 口座番号」を改行で並べるため text
            $table->text('bank')->nullable()->after('capital');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'established', 'capital', 'bank']);
        });
    }
};
