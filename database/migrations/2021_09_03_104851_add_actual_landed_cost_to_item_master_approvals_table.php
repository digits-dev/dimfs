<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActualLandedCostToItemMasterApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->decimal('actual_landed_cost', 18, 2)->nullable()->after('landed_cost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('actual_landed_cost');
        });
    }
}
