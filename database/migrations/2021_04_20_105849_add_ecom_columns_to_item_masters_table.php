<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEcomColumnsToItemMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->decimal('item_length', 8, 2)->nullable()->after('moq');
            $table->decimal('item_width', 8, 2)->nullable()->after('item_length');
            $table->decimal('item_height', 8, 2)->nullable()->after('item_width');
            $table->decimal('item_weight', 8, 2)->nullable()->after('item_height');
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
            $table->dropColumn('item_length');
            $table->dropColumn('item_width');
            $table->dropColumn('item_height');
            $table->dropColumn('item_weight');
        });
    }
}
