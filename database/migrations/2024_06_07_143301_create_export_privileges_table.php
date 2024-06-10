<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportPrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_privileges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cms_privileges_id')->length(10)->unsigned()->nullable();
            $table->integer('cms_moduls_id')->length(10)->unsigned()->nullable();            
            $table->string('table_name',50)->nullable();
            $table->longText('report_header')->nullable();
            $table->longText('report_query')->nullable();
            $table->string('status', 10)->default('ACTIVE')->nullable();
            $table->integer('created_by')->length(10)->unsigned()->nullable();
            $table->integer('updated_by')->length(10)->unsigned()->nullable();
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
        Schema::dropIfExists('export_privileges');
    }
}
