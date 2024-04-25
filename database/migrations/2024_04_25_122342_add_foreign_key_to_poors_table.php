<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToPoorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('poors', function (Blueprint $table) {
            // add fk constraint
            $table->unsignedBigInteger('education_level');
            $table->foreign('education_level')->references('edu_level_id')->on('education_levels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('poors', function (Blueprint $table) {
            //
        });
    }
}
