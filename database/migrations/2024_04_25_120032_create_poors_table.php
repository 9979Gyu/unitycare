<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poors', function (Blueprint $table) {
            $table->id('poor_id');
            $table->unsignedBigInteger('disability_type');
            $table->foreign('disability_type')->references('dis_type_id')->on('disability_types')->onDelete('cascade');
            $table->integer('education_level');
            $table->string('instituition_name');
            $table->integer('employment_status');
            $table->integer('status');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poors');
    }
}
