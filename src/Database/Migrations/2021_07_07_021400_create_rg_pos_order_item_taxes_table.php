<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgPosOrderItemTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_pos_order_item_taxes', function (Blueprint $table) {
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
            $table->unsignedBigInteger('pos_order_id');
            $table->unsignedBigInteger('pos_order_item_id');
            $table->char('tax_code', 50);
            $table->unsignedDecimal('amount', 20,5);
            $table->unsignedDecimal('inclusive', 20,5);
            $table->unsignedDecimal('exclusive', 20,5);

            $table->index(['tenant_id', 'pos_order_id', 'pos_order_item_id', 'tax_code'], 'tenant_index');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_pos_order_item_taxes');
    }
}
