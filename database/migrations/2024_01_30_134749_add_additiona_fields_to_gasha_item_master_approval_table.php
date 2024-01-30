<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionaFieldsToGashaItemMasterApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->integer('gacha_categories_id')->length(10)->unsigned()->nullable()->after('gacha_brands_id');
            $table->integer('gacha_product_types_id')->length(10)->unsigned()->nullable()->after('gacha_wh_categories_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('gacha_categories_id');
            $table->dropColumn('gacha_product_types_id');
        });
    }
}
