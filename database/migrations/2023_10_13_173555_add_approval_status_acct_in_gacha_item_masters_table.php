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
            $table->timestamp('approved_at_acct')->nullable()->after('approved_at');
            $table->integer('approved_by_acct')->length(10)->unsigned()->nullable()->after('approved_at');
        });
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->integer('approval_status_acct')->length(5)->nullable()->unsigned()->after('approval_status');
            $table->timestamp('approved_at_acct')->nullable()->after('approved_at');
            $table->integer('approved_by_acct')->length(10)->unsigned()->nullable()->after('approved_at');
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
            $table->dropColumn('approved_at_acct');
            $table->dropColumn('approved_by_acct');
        });
        Schema::table('gacha_item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('approval_status_acct');
            $table->dropColumn('approved_at_acct');
            $table->dropColumn('approved_by_acct');
        });
    }
}
