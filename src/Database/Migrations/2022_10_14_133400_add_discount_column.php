<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('tenant')->hasColumn('rg_pos_orders', 'discount'))
        {
            //do nothing
        }
        else
        {
            Schema::connection('tenant')->table('rg_pos_orders', function (Blueprint $table) {
                $table->unsignedDecimal('discount', 20, 5)->nullable()->default(0)->after('total');
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
        //do nothing
    }
}
