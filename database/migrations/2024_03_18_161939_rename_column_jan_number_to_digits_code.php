<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnJanNumberToDigitsCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gacha_item_edit_histories', function (Blueprint $table) {
            $table->renameColumn('jan_number', 'digits_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gacha_item_edit_histories', function (Blueprint $table) {
            $table->renameColumn('digits_code', 'jan_number');
        });
    }
}