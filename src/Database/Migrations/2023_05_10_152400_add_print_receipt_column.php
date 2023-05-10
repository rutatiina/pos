<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrintReceiptColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('tenant')->hasColumn('rg_pos_order_settings', 'print_receipt'))
        {
            //do nothing
        }
        else
        {
            Schema::connection('tenant')->table('rg_pos_order_settings', function (Blueprint $table) {
                $table->boolean('print_receipt')->nullable()->default(0);
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
        Schema::connection('tenant')->table('rg_pos_order_settings', function (Blueprint $table) {
            $table->dropColumn('print_receipt');
        });
    }
}
