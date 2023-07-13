<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCmsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->string('first_name',50)->nullable()->after('id');
            $table->string('last_name',50)->nullable()->after('first_name');
            $table->integer('created_by',false,true)->length(10)->nullable()->after('status');
            $table->integer('updated_by',false,true)->length(10)->nullable()->after('created_by');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
}
