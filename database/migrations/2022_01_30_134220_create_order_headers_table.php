<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_headers', function (Blueprint $table) {
            $table->id();
            $table->string('destination');
            $table->string('responsible')->nullable();
            $table->string('honor_title')->nullable();
            $table->date('issued_date');
            $table->date('exp_date')->nullable();
            $table->string('order_no');
            $table->string('title')->nullable();
            $table->integer('price');
            $table->string('remarks')->nullable();
            $table->integer('is_issued')->nullable();
            $table->integer('is_deleted')->nullable();
            $table->integer('is_converted')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('order_headers');
    }
}
