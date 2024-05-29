<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGachaItemEditHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gacha_item_edit_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jan_number')->nullable();
            $table->decimal('old_lc_per_carton', 18, 2)->nullable();
            $table->decimal('new_lc_per_carton', 18, 2)->nullable();
            $table->decimal('old_lc_margin_per_carton', 18, 2)->nullable();
            $table->decimal('new_lc_margin_per_carton', 18, 2)->nullable();
            $table->decimal('old_lc_per_pc', 18, 2)->nullable();
            $table->decimal('new_lc_per_pc', 18, 2)->nullable();
            $table->decimal('old_lc_margin_per_pc', 18, 2)->nullable();
            $table->decimal('new_lc_margin_per_pc', 18, 2)->nullable();
            $table->decimal('old_sc_per_pc', 18, 2)->nullable();
            $table->decimal('new_sc_per_pc', 18, 2)->nullable();
            $table->decimal('old_sc_margin_per_pc', 18, 2)->nullable();
            $table->decimal('new_sc_margin_per_pc', 18, 2)->nullable();
            $table->decimal('old_supplier_cost', 18, 2)->nullable();
            $table->decimal('new_supplier_cost', 18, 2)->nullable();
            $table->integer('approved_by_acct')->length(10)->unsigned()->nullable();
            $table->timestamp('approved_at_acct')->nullable();
            $table->integer('created_by')->length(10)->unsigned()->nullable();
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
        Schema::dropIfExists('gacha_item_edit_histories');
    }
}