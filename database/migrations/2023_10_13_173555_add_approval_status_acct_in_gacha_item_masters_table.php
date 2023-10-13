<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalStatusAcctInGachaItemMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gacha_item_masters', function (Blueprint $table) {
            $table->integer('approval_status_acct')->length(5)->nullable()->unsigned()->after('approval_status');
        });
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->integer('approval_status_acct')->length(5)->nullable()->unsigned()->after('approval_status');
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
            $table->dropColumn('approval_status_acct');
        });
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('approval_status_acct');
        });
    }
}
