<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRmaMarginCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rma_margin_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subclasses_id',false,true)->length(10);
            $table->string('margin_category_code',3);
            $table->string('margin_category_description',30);
            $table->enum('status',['ACTIVE','INACTIVE'])->default('ACTIVE');
            $table->integer('created_by',false,true)->length(10);
            $table->integer('updated_by',false,true)->length(10)->nullable();
            $table->unique(['subclasses_id', 'margin_category_code','margin_category_description'],'rma_margin_categories_unique');
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
        Schema::dropIfExists('rma_margin_categories');
    }
}
