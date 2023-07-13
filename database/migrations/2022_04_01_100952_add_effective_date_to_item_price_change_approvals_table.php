<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEffectiveDateToItemPriceChangeApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_price_change_approvals', function (Blueprint $table) {
            $table->date('effective_date')->nullable()->after('working_landed_cost');
            $table->integer('is_updated',false,true)->length(10)->unsigned()->default(0)->nullable()->after('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_price_change_approvals', function (Blueprint $table) {
            $table->dropColumn('effective_date');
            $table->dropColumn('is_updated');
        });
    }
}
