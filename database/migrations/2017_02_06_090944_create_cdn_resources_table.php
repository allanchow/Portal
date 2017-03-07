<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdnResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdn_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->unsigned();
            $table->string('cdn_hostname');
            $table->text('origin');
            $table->string('host_header');
            $table->integer('max_age')->unsigned();
            $table->text('file_type');
            $table->string('cname');
            $table->tinyInteger('status'); //0=Suspended, 1=Pending, 2=Active
            $table->tinyInteger('update_status'); //0=No update, 1=Updating, 2=Deleting
            $table->tinyInteger('force_update'); //0=No , 1=Yes
            $table->timestamps();
        });
        Schema::table('cdn_resources', function (Blueprint $table) {
            $table->foreign('org_id', 'cdn_resource_organization_ibfk_1')->references('id')->on('organization')->onUpdate('NO ACTION')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cdn_resources');
    }
}
