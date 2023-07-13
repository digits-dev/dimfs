<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreviousColumnsToItemMasterApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->decimal('prev_dtp_rf', 18, 2)->nullable();
            $table->decimal('prev_dtp_rf_percentage', 18, 4)->nullable();    
            $table->decimal('prev_working_dtp_rf', 18, 2)->nullable();
            $table->decimal('prev_working_dtp_rf_percentage', 18, 4)->nullable();
            $table->decimal('prev_landed_cost', 18, 2)->nullable();
            $table->decimal('prev_working_landed_cost', 18, 2)->nullable();
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
            $table->dropColumn('prev_dtp_rf');
            $table->dropColumn('prev_dtp_rf_percentage');
            $table->dropColumn('prev_working_dtp_rf');
            $table->dropColumn('prev_working_dtp_rf_percentage');
            $table->dropColumn('prev_landed_cost');
            $table->dropColumn('prev_working_landed_cost');
        });
    }
}
