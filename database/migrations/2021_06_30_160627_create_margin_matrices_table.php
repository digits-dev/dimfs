<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarginMatricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('margin_matrices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('brands_id',false,true)->length(10)->nullable();
            $table->string('margin_category',50)->nullable();
            $table->text('margin_categories_id')->nullable();
            $table->decimal('max',8,2)->nullable();
            $table->decimal('min',8,2)->nullable();
            $table->decimal('store_margin_percentage',8,2)->nullable();
            $table->enum('status',['ACTIVE','INACTIVE'])->default('ACTIVE');
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
        Schema::dropIfExists('margin_matrices');
    }
}
