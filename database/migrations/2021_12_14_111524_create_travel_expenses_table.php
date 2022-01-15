<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('rel_id');
            $table->string('dir');
            $table->string('purpose');
            $table->string('apply_person');
            $table->date('apply_date');
            $table->date('date_from');
            $table->date('date_to');
            $table->date('pay_date');
            $table->integer('trans_fee');
            $table->integer('acm_fee');
            $table->integer('gas_fee');
            $table->integer('dinner_fee');
            $table->integer('lunch_fee');
            $table->integer('daily_pay');
            $table->integer('total_fee');
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
        Schema::dropIfExists('travel_expenses');
    }
}
