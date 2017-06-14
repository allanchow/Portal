<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDnsSwitchedToCdnResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cdn_resources', function (Blueprint $table) {
            $table->tinyInteger('dns_switched')->after('cname')->default(0);
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
            $table->dropColumn('dns_switched');
        });
    }
}
