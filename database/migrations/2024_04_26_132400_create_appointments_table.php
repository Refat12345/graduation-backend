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
            $table->time('appointmentTimeStamp')->nullable();
            $table->string('day');
            $table->unsignedBigInteger('userID')->nullable();
            $table->unsignedBigInteger('shiftID');
            $table->unsignedBigInteger('chairID');
            $table->unsignedBigInteger('centerID');
            $table->time('start')->nullable();
           // $table->unsignedBigInteger('sessionID')->nullable();
           // $table->foreign('sessionID')->references('id')->on('dialysis_sessions');
            $table->unsignedBigInteger('nurseID')->nullable();
            $table->foreign('userID')->references('id')->on('users');
            $table->foreign('nurseID')->references('id')->on('users');
            $table->foreign('shiftID')->references('id')->on('shifts');
            $table->foreign('chairID')->references('id')->on('chairs');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->boolean('isValid')->default(true);
            $table->string('valid')->nullable();

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
