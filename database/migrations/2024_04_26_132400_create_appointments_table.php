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
        
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); 
            $table->timestamp('appointmentTimeStamp')->nullable();
            $table->unsignedBigInteger('userID');
            $table->unsignedBigInteger('shiftID');
            $table->unsignedBigInteger('chairID');
            $table->unsignedBigInteger('centerID');
            $table->unsignedBigInteger('sessionID')->nullable();
            $table->foreign('userID')->references('id')->on('users');
            $table->foreign('shiftID')->references('id')->on('shifts');
            $table->foreign('chairID')->references('id')->on('chairs');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->foreign('sessionID')->references('id')->on('dialysis_sessions');

            $table->timestamps();

           $table->index(['userID', 'shiftID', 'chairID', 'centerID']);
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
