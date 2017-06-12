<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDnsStatusToCdnPopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cdn_pop', function (Blueprint $table) {
            $table->tinyInteger('dns_status')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cdn_pop', function (Blueprint $table) {
            $table->dropColumn('dns_status');
        });
    }
}
