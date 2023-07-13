<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToItemPriceChangeApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_price_change_approvals', function (Blueprint $table) {
            $table->decimal('current_srp', 18, 2)->nullable()->after('margin_categories_id');
            $table->decimal('promo_srp', 18, 2)->nullable()->after('current_srp');
            $table->decimal('landed_cost_sea', 18, 2)->nullable()->after('landed_cost');
            $table->date('duration_from')->nullable()->after('working_landed_cost');
            $table->date('duration_to')->nullable()->after('duration_from');
            $table->integer('support_types_id',false,true)->length(10)->unsigned()->nullable()->after('duration_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_price_change_approvals', function (Blueprint $table) {
            $table->dropColumn('current_srp');
            $table->dropColumn('promo_srp');
            $table->dropColumn('duration_from');
            $table->dropColumn('duration_to');
            $table->dropColumn('landed_cost_sea');
            $table->dropColumn('support_types_id');
        });
    }
}
