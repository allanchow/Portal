<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHttpToCdnResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cdn_resources', function (Blueprint $table) {
            $table->tinyInteger('http')->unsigned()->default(0)->after('cname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cdn_resources', function (Blueprint $table) {
            $table->dropColumn('http');
        });
    }
}
