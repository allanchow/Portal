<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdnDailyReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdn_daily_report', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('resource_id')->unsigned();
            $table->date('report_date');
            $table->integer('http_byte')->unsigned();
            $table->integer('https_byte')->unsigned();
            $table->integer('total_byte')->unsigned();
            $table->integer('total_req')->unsigned();
            $table->timestamps();
        });
        Schema::table('cdn_daily_report', function (Blueprint $table) {
            //$table->foreign('resource_id', 'cdn_daily_report_resource_ibfk_1')->references('id')->on('cdn_resources')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->unique(['resource_id', 'report_date'], 'cdn_daily_report_id_date_uk_1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cdn_daily_report');
    }
}
