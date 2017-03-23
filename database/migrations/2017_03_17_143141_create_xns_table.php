<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xns', function (Blueprint $table) {
            $table->integer('domain_id')->unsigned();
            $table->string('domain_name');
            $table->string('api_key', 64);
            $table->string('secret_key', 64);
            $table->timestamps();
            $table->primary('domain_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('xns');
    }
}
