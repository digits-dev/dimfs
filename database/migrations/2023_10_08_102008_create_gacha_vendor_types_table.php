<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGachaVendorTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gacha_vendor_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vendor_type_code')->length(50)->nullable();
            $table->string('vendor_type_description')->length(200)->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->nullable()->default('ACTIVE');
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
        Schema::dropIfExists('gacha_vendor_types');
    }
}
