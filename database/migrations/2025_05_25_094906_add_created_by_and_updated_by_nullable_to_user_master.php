<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByAndUpdatedByNullableToUserMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_master', function (Blueprint $table) {
        $table->unsignedBigInteger('created_by')->nullable()->after('is_admin');
        $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

        $table->foreign('created_by')->references('id')->on('user_master')->onDelete('set null');
        $table->foreign('updated_by')->references('id')->on('user_master')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_master', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        $table->dropForeign(['updated_by']);
        $table->dropColumn(['created_by', 'updated_by']);
        });
    }
}
