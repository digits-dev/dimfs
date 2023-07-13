<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_settings', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('cms_moduls_id',false,true)->length(10);
            $table->integer('action_types_id',false,true)->length(10);

            $table->integer('approver_privileges_id',false,true)->length(10);
            $table->integer('encoder_privileges_id',false,true)->length(10);
            
            $table->tinyInteger('current_state',false,true)->length(3);
            $table->tinyInteger('next_state',false,true)->length(3);
           
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
        Schema::dropIfExists('workflow_settings');
    }
}
