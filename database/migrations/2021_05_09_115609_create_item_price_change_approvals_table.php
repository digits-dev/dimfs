<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPriceChangeApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_price_change_approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('brands_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('categories_id',false,true)->length(10)->unsigned()->nullable();
            $table->decimal('store_cost', 18, 2)->nullable();
            $table->decimal('store_cost_percentage', 18, 2)->nullable();
            $table->decimal('working_store_cost', 18, 2)->nullable();
            $table->decimal('working_store_cost_percentage', 18, 2)->nullable();
            $table->decimal('landed_cost', 18, 2)->nullable();
            $table->decimal('working_landed_cost', 18, 2)->nullable();
            $table->integer('approval_status',false,true)->length(10)->nullable();
            $table->integer('approved_by',false,true)->length(10)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->integer('approver_privileges_id',false,true)->length(10)->nullable();
            $table->integer('encoder_privileges_id',false,true)->length(10)->nullable();
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
        Schema::dropIfExists('item_price_change_approvals');
    }
}
