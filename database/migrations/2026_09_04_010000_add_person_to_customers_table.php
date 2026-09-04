<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 顧客に担当者名を足す。
 *
 * in-house-timecard-app の clients.person に相当する。docs-manager では
 * 担当者を発注書ごと (order_headers.responsible) にしか持っておらず、
 * 取引先を選んでも毎回手入力する必要があった。マスタに既定の担当者を持たせ、
 * 発注書フォームの初期値に使う。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('person')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('person');
        });
    }
};
