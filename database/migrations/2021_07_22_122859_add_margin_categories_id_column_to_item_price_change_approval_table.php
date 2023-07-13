<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMarginCategoriesIdColumnToItemPriceChangeApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_price_change_approvals', function (Blueprint $table) {
            $table->integer('margin_categories_id',false,true)->length(10)->unsigned()->nullable();
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
            $table->dropColumn('margin_categories_id');
        });
    }
}
