<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppleLobsColumnToItemMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->integer('apple_lobs_id')->length(10)->unsigned()->nullable()->after('brands_id');
            $table->integer('apple_report_inclusion')->length(10)->nullable()->after('brands_id');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->integer('apple_lobs_id')->length(10)->unsigned()->nullable();
            $table->integer('apple_report_inclusion')->length(10)->nullable()->after('brands_id');
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
            $table->dropColumn('apple_lobs_id');
            $table->dropColumn('apple_report_inclusion');
        });

        Schema::table('item_master_approvals', function (Blueprint $table) {
            $table->dropColumn('apple_lobs_id');
            $table->dropColumn('apple_report_inclusion');
        });
    }
}
