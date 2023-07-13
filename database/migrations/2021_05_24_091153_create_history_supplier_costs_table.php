<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistorySupplierCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_supplier_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('brands_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('categories_id',false,true)->length(10)->unsigned()->nullable();
            $table->decimal('supplier_cost', 18, 2)->nullable();
            $table->integer('currencies_id',false,true)->length(10)->nullable();
            $table->decimal('supplier_cost1', 18, 2)->nullable();
            $table->integer('currencies1_id',false,true)->length(10)->nullable();
            $table->decimal('supplier_cost2', 18, 2)->nullable();
            $table->integer('currencies2_id',false,true)->length(10)->nullable();
            $table->decimal('supplier_cost3', 18, 2)->nullable();
            $table->integer('currencies3_id',false,true)->length(10)->nullable();
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
        Schema::dropIfExists('history_supplier_costs');
    }
}
