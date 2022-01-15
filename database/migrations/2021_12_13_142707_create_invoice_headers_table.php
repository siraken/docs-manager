<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_headers', function (Blueprint $table) {
            $table->id();
            $table->string('destination');
            $table->string('honor_title');
            $table->string('responsible');
            $table->date('issued_date');
            $table->date('exp_date');
            $table->string('invoice_no');
            $table->string('title');
            $table->integer('price');
            $table->string('remarks');
            $table->integer('is_issued');
            $table->integer('is_paid');
            $table->integer('is_deleted');
            $table->integer('is_converted');
            $table->string('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_headers');
    }
}
