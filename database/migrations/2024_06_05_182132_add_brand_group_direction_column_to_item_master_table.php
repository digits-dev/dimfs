<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrandGroupDirectionColumnToItemMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->integer('brand_groups_id')->length(10)->unsigned()->nullable()->after('brands_id');
            $table->integer('brand_directions_id')->length(10)->unsigned()->nullable()->after('brand_groups_id');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->integer('brand_groups_id')->length(10)->unsigned()->nullable()->after('brands_id');
            $table->integer('brand_directions_id')->length(10)->unsigned()->nullable()->after('brand_groups_id');
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
            $table->dropColumn('brand_groups_id');
            $table->dropColumn('brand_directions_id');          
        });
        
        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('brand_groups_id');
            $table->dropColumn('brand_directions_id');
        });
    }
}
