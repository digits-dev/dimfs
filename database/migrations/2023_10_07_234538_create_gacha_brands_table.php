<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGachaBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gacha_brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_description')->nullable();
            $table->integer('gacha_brand_statuses_id')->length(10)->unsigned()->nullable();
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
        Schema::dropIfExists('gacha_brands');
    }
}
