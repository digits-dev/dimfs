<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRmaItemMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rma_item_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('digits_code',8)->nullable();
            $table->string('upc_code',60)->nullable();
            $table->string('upc_code2',60)->nullable();
            $table->string('upc_code3',60)->nullable();
            $table->string('upc_code4',60)->nullable();
            $table->string('upc_code5',60)->nullable();
            $table->string('supplier_item_code',60)->nullable();
            $table->string('item_description',100)->nullable();

            $table->integer('brands_id',false,true)->length(10)->unsigned();
            $table->integer('rma_categories_id',false,true)->length(10)->unsigned();
            $table->integer('rma_classes_id',false,true)->length(10)->unsigned();
            // $table->integer('rma_subcategories_id',false,true)->length(10)->unsigned();
            $table->integer('rma_subclasses_id',false,true)->length(10)->unsigned();
            $table->integer('rma_store_categories_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('rma_margin_categories_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('warehouse_categories_id',false,true)->length(10)->unsigned();
            $table->string('model',50)->nullable();
            $table->integer('rma_model_specifics_id',false,true)->length(10)->unsigned();
            $table->integer('colors_id',false,true)->length(10)->unsigned();
            $table->string('actual_color',50)->nullable();

            $table->integer('vendors_id',false,true)->length(10)->unsigned();
            $table->integer('vendor_types_id',false,true)->length(10)->unsigned();
            $table->integer('incoterms_id',false,true)->length(10)->unsigned();
            $table->integer('inventory_types_id',false,true)->length(10)->unsigned();

            $table->string('serialized',50)->nullable();
            $table->tinyInteger('has_serial',false,true)->length(3)->unsigned()->default(0);
            $table->tinyInteger('imei_code1',false,true)->length(3)->unsigned()->default(0);
            $table->tinyInteger('imei_code2',false,true)->length(3)->unsigned()->default(0);

            $table->integer('serialized_by',false,true)->length(10)->unsigned()->nullable();
            $table->dateTime('serialized_at')->nullable();
            
            $table->integer('sku_statuses_id',false,true)->length(10)->unsigned();
            $table->integer('sku_legends_id',false,true)->length(10)->unsigned();

            $table->decimal('original_srp', 18, 2)->nullable();
            $table->decimal('current_srp', 18, 2)->nullable();
            $table->decimal('promo_srp', 18, 2)->nullable();
            
            $table->decimal('price_change', 18, 2)->nullable();
            $table->date('effective_date')->nullable();

            $table->decimal('moq', 18, 2)->nullable();
            $table->integer('currencies_id',false,true)->length(10)->unsigned();
            $table->decimal('purchase_price', 18, 2)->nullable();

            $table->decimal('cost_factor', 18, 2)->nullable();

            $table->decimal('dtp_rf', 18, 2)->nullable();
            $table->decimal('dtp_rf_percentage', 18, 2)->nullable();
            $table->decimal('dtp_dcon', 18, 2)->nullable();
            $table->decimal('dtp_dcon_percentage', 18, 2)->nullable();
            $table->decimal('landed_cost', 18, 2)->nullable();
            $table->decimal('working_landed_cost', 18, 2)->nullable();
            $table->decimal('working_dtp_rf', 18, 2)->nullable();
            $table->decimal('working_dtp_rf_percentage', 18, 2)->nullable();
            $table->integer('warranties_id',false,true)->length(10)->unsigned();
            $table->integer('warranty_duration',false,true)->length(10)->unsigned();
            $table->integer('approval_status',false,true)->length(10)->unsigned();
            $table->integer('approved_by',false,true)->length(10)->unsigned()->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->date('initial_wrr_date')->nullable();
            $table->date('latest_wrr_date')->nullable();

            $table->integer('approver_privileges_id',false,true)->length(10)->unsigned()->nullable();
            $table->integer('encoder_privileges_id',false,true)->length(10)->unsigned()->nullable();

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
        Schema::dropIfExists('rma_item_masters');
    }
}
