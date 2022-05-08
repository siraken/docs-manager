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
            $table->string('rel_id');
            $table->string('dir');
            $table->string('purpose');
            $table->string('apply_person');
            $table->date('apply_date');
            $table->date('date_from');
            $table->date('date_to');
            $table->date('pay_date');
            $table->string('trans_fee');
            $table->string('acm_fee');
            $table->string('gas_fee');
            $table->string('dinner_fee');
            $table->string('lunch_fee');
            $table->string('daily_pay');
            $table->string('total_fee');
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
