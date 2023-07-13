<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryUpcCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_upc_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('brands_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('categories_id',false,true)->length(10)->unsigned()->nullable();
            $table->string('upc_code', 100)->nullable();
            $table->string('supplier_item_code', 100)->nullable();
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
        Schema::dropIfExists('history_upc_codes');
    }
}
