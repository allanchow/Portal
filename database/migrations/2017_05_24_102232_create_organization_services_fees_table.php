<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationServicesFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_services_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->unsigned();
            $table->integer('fee_id')->unsigned();
            $table->timestamps();
        });
        Schema::table('organization_services_fees', function (Blueprint $table) {
            $table->foreign('org_id', 'organization_services_fees_ibfk_1')->references('id')->on('organization')->onUpdate('NO ACTION')->onDelete('RESTRICT');
            $table->foreign('fee_id', 'organization_services_fees_ibfk_2')->references('id')->on('services_fees')->onUpdate('NO ACTION')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('organization_services_fees');
    }
}
