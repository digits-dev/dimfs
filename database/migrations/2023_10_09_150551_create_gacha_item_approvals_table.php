<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGachaItemApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gacha_item_master_approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jan_no')->length(120)->nullable();
            $table->string('digits_code')->length(10)->nullable();
            $table->string('item_no')->length(120)->nullable();
            $table->string('sap_no')->length(10)->nullable();
            $table->date('initial_wrr_date')->nullable();
            $table->date('latest_wrr_date')->nullable();
            $table->integer('gacha_brands_id')->length(10)->unsigned()->nullable();
            $table->integer('gacha_sku_statuses_id')->length(10)->unsigned()->nullable();
            $table->string('item_description')->nullable();
            $table->string('gacha_models')->nullable();
            $table->integer('gacha_wh_categories_id')->length(10)->unsigned()->nullable();
            $table->decimal('msrp', 18, 2)->unsigned()->nullable();
            $table->decimal('current_srp', 18, 2)->unsigned()->nullable();
            $table->decimal('no_of_tokens', 18, 2)->unsigned()->nullable();
            $table->decimal('store_cost', 18, 2)->unsigned()->nullable();
            $table->decimal('sc_margin', 18, 2)->unsigned()->nullable();
            $table->decimal('lc_per_pc', 18, 2)->unsigned()->nullable();
            $table->decimal('lc_margin_per_pc', 18, 2)->unsigned()->nullable();
            $table->decimal('lc_per_carton', 18, 2)->unsigned()->nullable();
            $table->decimal('lc_margin_per_carton', 18, 2)->unsigned()->nullable();
            $table->decimal('dp_ctn', 18, 2)->unsigned()->nullable();
            $table->decimal('pcs_dp', 18, 2)->unsigned()->nullable();
            $table->decimal('moq', 18, 2)->unsigned()->nullable();
            $table->decimal('no_of_assort', 18, 2)->unsigned()->nullable();
            $table->integer('gacha_countries_id')->length(10)->unsigned()->nullable();
            $table->integer('gacha_incoterms_id')->length(10)->unsigned()->nullable();
            $table->integer('currencies_id')->length(10)->unsigned()->nullable();
            $table->decimal('supplier_cost', 18, 2)->unsigned()->nullable();
            $table->integer('gacha_uoms_id')->length(10)->unsigned()->nullable();
            $table->integer('gacha_inventory_types_id')->length(10)->unsigned()->nullable();
            $table->integer('gacha_vendor_types_id')->length(10)->unsigned()->nullable();
            $table->integer('gacha_vendor_groups_id')->length(10)->unsigned()->nullable();
            $table->string('age_grade')->length(10)->nullable();
            $table->string('battery')->length(50)->nullable();
            $table->integer('approved_by')->length(10)->unsigned()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->integer('created_by')->length(10)->unsigned()->nullable();
            $table->integer('updated_by')->length(10)->unsigned()->nullable();
            $table->timestamps();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->nullable()->default('ACTIVE');
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
        Schema::dropIfExists('gacha_item_master_approvals');
    }
}
