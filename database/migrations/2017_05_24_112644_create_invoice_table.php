<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->date('invoice_date');
            $table->integer('org_id')->unsigned();
            $table->integer('fee_id')->unsigned();
            $table->string('remark');
            $table->char('currency', 3);
            $table->double('fee', 10, 2)->unsigned();
            $table->timestamps();
        });
        Schema::table('invoice', function (Blueprint $table) {
            $table->primary(['invoice_date', 'org_id']);
            $table->foreign('org_id', 'invoice_organization_ibfk_1')->references('id')->on('organization')->onUpdate('NO ACTION')->onDelete('RESTRICT');
            $table->foreign('fee_id', 'invoice_services_fees_ibfk_1')->references('id')->on('services_fees')->onUpdate('NO ACTION')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invoice');
    }
}
