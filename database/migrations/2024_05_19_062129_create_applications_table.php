<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->dateTime('applied_date');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            $table->dateTime('approved_at')->nullable();
            $table->integer('approval_status');
            $table->unsignedBigInteger('offer_id');
            $table->foreign('offer_id')->references('offer_id')->on('job_offers')->onDelete('cascade');
            $table->unsignedBigInteger('poor_id');
            $table->foreign('poor_id')->references('poor_id')->on('poors')->onDelete('cascade');
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
        Schema::dropIfExists('applications');
    }
}
