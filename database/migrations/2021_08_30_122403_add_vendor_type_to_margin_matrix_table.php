<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorTypeToMarginMatrixTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('margin_matrices', function (Blueprint $table) {
            $table->integer('vendor_types_id',false,true)->length(10)->unsigned()->nullable()->after('margin_categories_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('margin_matrices', function (Blueprint $table) {
            $table->dropColumn('vendor_types_id');
        });
    }
}
