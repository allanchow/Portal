<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdnSsl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdn_ssl', function (Blueprint $table) {
            $table->integer('resource_id')->unsigned();
            $table->char('type', 2);
            $table->text('cert');
            $table->text('key');
            $table->tinyInteger('status'); //0=Suspended, 1=Pending, 2=Active
            $table->timestamps();
        });
        Schema::table('cdn_ssl', function (Blueprint $table) {
            $table->foreign('resource_id', 'cdn_ssl_resource_ibfk_1')->references('id')->on('cdn_resources')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cdn_ssl');
    }
}
