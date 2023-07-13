<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSizeToItemMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            
            $table->string('size',50)->nullable()->after('actual_color');
            $table->string('size_value',10)->nullable()->after('size');
            $table->integer('sizes_id',false,true)->length(10)->unsigned()->after('size_value');
            $table->integer('uoms_id',false,true)->length(10)->unsigned()->after('sizes_id');
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
            $table->dropColumn('size');
            $table->dropColumn('size_value');
            $table->dropColumn('sizes_id');
            $table->dropColumn('uoms_id');
        });
    }
}
