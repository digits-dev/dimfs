<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalStatusInGachaItemMastesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gacha_item_masters', function (Blueprint $table) {
            $table->integer('approval_status')->length(5)->unsigned()->nullable()->after('id');
        });
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->integer('approval_status')->length(5)->unsigned()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gacha_item_masters', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
}
