<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarrantyChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warranty_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('digits_code',8);
            $table->integer('warranties_id',false,true)->length(10)->unsigned();
            $table->integer('warranty_duration',false,true)->length(10)->unsigned();
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
        Schema::dropIfExists('warranty_changes');
    }
}
