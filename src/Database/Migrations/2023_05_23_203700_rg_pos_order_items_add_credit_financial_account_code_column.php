<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RgPosOrderItemsAddCreditFinancialAccountCodeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('tenant')->hasColumn('rg_pos_order_items', 'credit_financial_account_code'))
        {
            Schema::connection('tenant')->table('rg_pos_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('credit_financial_account_code')->nullable()->after('item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->table('rg_pos_order_items', function (Blueprint $table) {
            $table->dropColumn('credit_financial_account_code');
        });
    }
}
