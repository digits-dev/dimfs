<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrandMarketingColumnToItemMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->integer('brand_marketings_id')->length(10)->unsigned()->nullable()->after('brand_directions_id');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->integer('brand_marketings_id')->length(10)->unsigned()->nullable()->after('brand_directions_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn('brand_marketings_id');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('brand_marketings_id');
        });


    }
}
