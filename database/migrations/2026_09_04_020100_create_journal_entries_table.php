<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 仕訳帳。単一仕訳 (借方 1 科目・貸方 1 科目) を 1 行で表す。
 *
 * **金額の列は 1 つしかない。** 参考実装 (CakePHP 時代の posts) は
 * debit_price と credit_price を別々に持ち、画面でも別々に入力させていたが、
 * 単一仕訳なら両者は必ず一致する。2 つ持つと貸借がずれた帳簿を作れてしまい、
 * しかも検出する術が無かった。
 *
 * 参考実装から引き継いだ列: date / description (摘要) / note (その他)。
 * 捨てた列: debit_name / credit_name (文字列の科目名 → account_id に置換)、
 * debit_price / credit_price (→ amount に一本化)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->unsignedBigInteger('debit_account_id')->index();
            $table->unsignedBigInteger('credit_account_id')->index();
            // 円の整数。Money と同じで小数は持たない
            $table->unsignedBigInteger('amount');
            $table->string('description');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
