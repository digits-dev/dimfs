<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGachaItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gacha_item_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->json('history_json')->nullable();
            $table->decimal('old_current_srp', 18, 2)->nullable();
            $table->decimal('new_current_srp', 18, 2)->nullable();
            $table->decimal('old_store_cost', 18, 2)->nullable();
            $table->decimal('new_store_cost', 18, 2)->nullable();
            $table->decimal('old_sc_margin', 18, 2)->nullable();
            $table->decimal('new_sc_margin', 18, 2)->nullable();
            $table->decimal('old_lc_per_pc', 18, 2)->nullable();
            $table->decimal('new_lc_per_pc', 18, 2)->nullable();
            $table->decimal('old_lc_margin_per_pc', 18, 2)->nullable();
            $table->decimal('new_lc_margin_per_pc', 18, 2)->nullable();
            $table->decimal('old_lc_per_carton', 18, 2)->nullable();
            $table->decimal('new_lc_per_carton', 18, 2)->nullable();
            $table->decimal('old_lc_margin_per_carton', 18, 2)->nullable();
            $table->decimal('new_lc_margin_per_carton', 18, 2)->nullable();
            $table->decimal('old_supplier_cost', 18, 2)->nullable();
            $table->decimal('new_supplier_cost', 18, 2)->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->nullable()->default('ACTIVE');
            $table->integer('created_by')->length(10)->unsigned()->nullable();
            $table->integer('updated_by')->length(10)->unsigned()->nullable();
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
        Schema::dropIfExists('gacha_item_histories');
    }
}
