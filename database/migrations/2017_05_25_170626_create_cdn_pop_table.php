<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdnPopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdn_pop', function (Blueprint $table) {
            $table->string('pop_hostname');
            $table->string('ip');
            $table->text('deployment_ips');
            $table->tinyInteger('status')->comment('[0=Inactive|1=Active]');
            $table->dateTime('dns_updated_at');
            $table->timestamps();
        });
        Schema::table('cdn_pop', function (Blueprint $table) {
            $table->primary('pop_hostname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cdn_pop');
    }
}
