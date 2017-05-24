<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->tinyInteger('payment_terms')->unsigned();
            $table->char('currency', 3);
            $table->double('fee', 10, 2)->unsigned();
            $table->timestamps();
        });
        Schema::table('services_fees', function (Blueprint $table) {
            $table->foreign('service_id', 'services_fees_service_ibfk_1')->references('id')->on('services')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('services_fees');
    }
}
