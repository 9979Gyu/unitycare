<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id('offer_id');
            $table->string('description');
            $table->string('state');
            $table->string('city');
            $table->integer('postal_code');
            $table->integer('min_salary');
            $table->integer('max_salary');
            $table->integer('status');
            $table->unsignedBigInteger('job_id');
            $table->foreign('job_id')->references('job_id')->on('jobs')->onDelete('cascade');
            
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            $table->dateTime('approved_at')->nullable();
            $table->integer('approval_status');

            $table->unsignedBigInteger('job_type_id');
            $table->foreign('job_type_id')->references('job_type_id')->on('job_types')->onDelete('cascade');
            
            $table->unsignedBigInteger('shift_type_id');
            $table->foreign('shift_type_id')->references('shift_type_id')->on('shift_types')->onDelete('cascade');
            
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
        Schema::dropIfExists('job_offers');
    }
}
