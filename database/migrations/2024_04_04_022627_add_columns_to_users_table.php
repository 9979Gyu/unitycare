<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('username');
            $table->integer('contactNo');
            $table->string('address');
            $table->string('state');
            $table->string('city');
            $table->integer('postalCode');
            $table->integer('officeNo')->nullable()->change();
            $table->integer('ICNo')->nullable()->change();
            $table->integer('status');
            $table->unsignedBigInteger('roleID');
            $table->foreign('roleID')->references('roleID')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
