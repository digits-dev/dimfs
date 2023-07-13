<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubclassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subclasses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('classes_id',false,true)->length(10);
            $table->string('subclass_description',50)->unique();
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
        Schema::dropIfExists('subclasses');
    }
}
