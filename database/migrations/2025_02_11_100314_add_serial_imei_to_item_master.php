<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSerialImeiToItemMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->string('device_type',199)->nullable()->after('working_ecom_store_margin_percentage');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->string('device_type',199)->nullable()->after('working_ecom_store_margin_percentage');
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
            $table->dropColumn('device_type');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('device_type');
        });
    }
}
