<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgPosOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_pos_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            //>> default columns
            $table->softDeletes();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            //<< default columns

            //>> table columns
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('app_id');
            $table->string('document_name', 50)->default('Order');
            $table->string('number', 250);
            $table->date('date');
            $table->time('time');
            $table->unsignedBigInteger('debit_financial_account_code')->nullable();
            $table->unsignedBigInteger('credit_financial_account_code')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('currency', 3);
            $table->unsignedDecimal('total', 20, 5);
            $table->unsignedDecimal('taxable_amount', 20,5)->default(0);
            $table->unsignedDecimal('total_paid', 20, 5)->default(0);
            $table->boolean('balances_where_updated')->default(0);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('payment_mode', 50)->nullable();
            $table->string('status', 20)->nullable();
            $table->unsignedDecimal('cash_tendered', 20, 5);
            $table->unsignedDecimal('cash_change', 20, 5);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_pos_orders');
    }
}
