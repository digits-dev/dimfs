<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColsInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->timestamp('updated_password_at')->useCurrent();
            $table->tinyInteger('waive_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn('updated_password_at');
            $table->dropColumn('waive_count');
        });

    }
}
