<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logging', function (Blueprint $table) {
            $table->id();
            $table->string('operation');
            $table->string('destinationOfOperation');
            $table->text('oldData')->nullable();
            $table->text('newData')->nullable();
         
       
            $table->unsignedBigInteger('affectedUserID')->nullable();
            $table->unsignedBigInteger('affectorUserID')->nullable();
            $table->unsignedBigInteger('sessionID')->nullable();
            $table->foreign('affectedUserID')->references('id')->on('users');
            $table->foreign('affectorUserID')->references('id')->on('users');
            $table->foreign('sessionID')->references('id')->on('dialysis_sessions');


            $table->unsignedBigInteger('centerID');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->unsignedBigInteger('valid')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loggings');
    }
};
