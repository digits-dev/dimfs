<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryStoreCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_store_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('brands_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('categories_id',false,true)->length(10)->unsigned()->nullable();
            $table->decimal('store_cost', 18, 2)->nullable();
            $table->decimal('working_store_cost', 18, 2)->nullable();
            $table->integer('updated_by',false,true)->length(10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_store_costs');
    }
}
