<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDurationColumnsToItemMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->decimal('landed_cost_sea', 18, 2)->nullable()->after('landed_cost');
            $table->date('duration_from')->nullable()->after('working_landed_cost');
            $table->date('duration_to')->nullable()->after('duration_from');
            $table->integer('support_types_id',false,true)->length(10)->nullable()->after('duration_to');
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
            $table->dropColumn('landed_cost_sea');
            $table->dropColumn('duration_from');
            $table->dropColumn('duration_to');
            $table->dropColumn('support_types_id');
        });
    }
}
