<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id')->increment();
            $table->string('payer_name')->nullable();
            $table->double('amount');
            $table->string('currency');
            $table->integer('payment_status');
            $table->string('references')->nullable();
            $table->string('reference_no');

            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('payer_id')->nullable();
            $table->foreign('payer_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('transaction_type_id');
            $table->foreign('transaction_type_id')->references('transaction_type_id')->on('transaction_types')->onDelete('cascade');
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
        Schema::dropIfExists('transactions');
    }
}
