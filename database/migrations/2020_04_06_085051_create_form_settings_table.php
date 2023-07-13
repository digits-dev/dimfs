<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cms_privileges_id',false,true)->length(10);
            $table->integer('cms_moduls_id',false,true)->length(10);
            $table->integer('action_types_id',false,true)->length(10);
            $table->string('privilege_name',50)->nullable();
            $table->string('module_name',20)->nullable();
            $table->string('form_setting',50)->unique();
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
        Schema::dropIfExists('form_settings');
    }
}
