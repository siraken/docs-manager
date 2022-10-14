<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('zipcode');
            $table->string('address1');
            $table->string('address2');
            $table->string('address3');
            $table->string('company_stamp');
            $table->string('president_stamp');
            $table->string('logo');
            $table->string('tel_no');
            $table->string('fax_no');
            $table->string('responsible');
            $table->string('bank1');
            $table->string('bank2');
            $table->string('bank3');
            $table->integer('tax_cfg');
            $table->integer('tax_round');
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
        Schema::dropIfExists('companies');
    }
}
