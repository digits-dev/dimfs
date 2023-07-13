<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moduls_id',false,true)->length(10);
            $table->string('module_name',50)->unique();

            $table->bigInteger('code_1',false,true)->length(18);
            $table->bigInteger('code_2',false,true)->length(18);
            $table->bigInteger('code_3',false,true)->length(18);
            $table->bigInteger('code_4',false,true)->length(18);
            $table->bigInteger('code_5',false,true)->length(18);
            $table->bigInteger('code_6',false,true)->length(18);
            $table->bigInteger('code_7',false,true)->length(18);
            $table->bigInteger('code_8',false,true)->length(18);
            $table->bigInteger('code_9',false,true)->length(18);

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
        Schema::dropIfExists('counters');
    }
}
