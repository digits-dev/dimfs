<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompatibilityToItemMasterApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->string('model_number',60)->nullable()->after('supplier_item_code');
            $table->text('compatibility')->nullable()->after('model');
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
            $table->dropColumn('model_number');
            $table->dropColumn('compatibility');
        });
    }
}
