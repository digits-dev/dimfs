<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModelSpecificsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_specifics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_specific_code',15)->unique();
            $table->string('model_specific_description',50)->unique();
            $table->enum('status',['ACTIVE','INACTIVE'])->default('ACTIVE');

            $table->string('month_1',50)->nullable();
            $table->string('month_2',50)->nullable();
            $table->string('month_3',50)->nullable();
            $table->string('month_4',50)->nullable();

            $table->string('month_5',50)->nullable();
            $table->string('month_6',50)->nullable();
            $table->string('month_7',50)->nullable();
            $table->string('month_8',50)->nullable();

            $table->string('month_9',50)->nullable();
            $table->string('month_10',50)->nullable();
            $table->string('month_11',50)->nullable();
            $table->string('month_12',50)->nullable();

            $table->string('month_13',50)->nullable();
            $table->string('month_14',50)->nullable();
            $table->string('month_15',50)->nullable();
            $table->string('month_16',50)->nullable();

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
        Schema::dropIfExists('model_specifics');
    }
}
