<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCampaignColumnToEcomPriceChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecom_price_changes', function (Blueprint $table) {
            $table->text('campaign')->nullable()->after('to_date');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('brands_id',false,true)->length(10)->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecom_price_changes', function (Blueprint $table) {
            $table->dropColumn('campaign');
            $table->dropColumn('item_masters_id');
            $table->dropColumn('brands_id');
        });
    }
}
