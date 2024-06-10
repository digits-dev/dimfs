<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActionTypesToExportPrivilegeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_privileges', function (Blueprint $table) {
            $table->integer('action_types_id')->length(10)->unsigned()->nullable()->after('cms_moduls_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_privileges', function (Blueprint $table) {
            $table->dropColumn('action_types_id');
        });
    }
}
