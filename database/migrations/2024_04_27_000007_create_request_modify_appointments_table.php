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
        Schema::create('request_modify_appointments', function (Blueprint $table) {
            $table->id(); 
            $table->timestamp('newTime')->nullable();
            $table->unsignedBigInteger('requestID');
            $table->unsignedBigInteger('requesterID');
            $table->unsignedBigInteger('appointmentID');
            $table->foreign('requestID')->references('id')->on('requests');
            $table->foreign('requesterID')->references('id')->on('users');
            $table->foreign('appointmentID')->references('id')->on('appointments');
            $table->timestamps();

           $table->index('appointmentID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_modify_appointments');
    }
};
