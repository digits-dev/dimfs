<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_masters_id',false,true)->length(10)->unsigned();
            $table->string('digits_code',8)->nullable();
            $table->string('upc_code',60)->nullable();
            $table->string('upc_code2',60)->nullable();
            $table->string('upc_code3',60)->nullable();
            $table->string('upc_code4',60)->nullable();
            $table->string('upc_code5',60)->nullable();
            $table->decimal('original_srp', 18, 2)->nullable();
            $table->decimal('current_srp', 18, 2)->nullable();
            $table->date('effective_date')->nullable();
            $table->decimal('dtp_rf', 18, 2)->nullable();
            $table->decimal('dtp_rf_percentage', 18, 2)->nullable();
            $table->decimal('dtp_districon', 18, 2)->nullable();
            $table->decimal('dtp_districon_percentage', 18, 2)->nullable();
            $table->decimal('landed_cost', 18, 2)->nullable();
            $table->decimal('working_landed_cost', 18, 2)->nullable();
            $table->integer('created_by',false,true)->length(10);
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
        Schema::dropIfExists('change_histories');
    }
}
