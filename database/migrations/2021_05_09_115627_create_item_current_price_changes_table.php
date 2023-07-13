<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemCurrentPriceChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_current_price_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned()->nullable();
            $table->tinyInteger('is_updated',false,true)->length(3)->unsigned()->default(0);
            $table->decimal('current_srp', 18, 2)->nullable();
            $table->decimal('price_change', 18, 2)->nullable();
            $table->date('effective_date')->nullable();
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
        Schema::dropIfExists('item_current_price_changes');
    }
}
