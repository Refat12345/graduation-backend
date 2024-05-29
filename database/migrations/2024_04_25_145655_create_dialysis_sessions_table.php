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
        Schema::create('dialysis_sessions', function (Blueprint $table) {
            $table->id(); 
            $table->timestamp('sessionStartTime')->nullable();
            $table->timestamp('sessionEndTime')->nullable();
            $table->float('weightBeforeSession');
            $table->float('weightAfterSession');
            $table->float('totalWithdrawalRate');
            $table->float('withdrawalRateHourly');
            $table->float('pumpSpeed');
            $table->string('filterColor');
            $table->string('filterType');
            $table->string('vascularConnection');
            $table->float('naConcentration');
            $table->integer('venousPressure');
            $table->string('status');
            $table->date('sessionDate');
            $table->unsignedBigInteger('patientID');
            $table->unsignedBigInteger('nurseID');
            $table->unsignedBigInteger('doctorID')->nullable();
            $table->unsignedBigInteger('centerID');
            $table->foreign('patientID')->references('id')->on('users');
            $table->foreign('nurseID')->references('id')->on('users');
            $table->foreign('doctorID')->references('id')->on('users');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->timestamps();

           $table->index(['centerID', 'patientID', 'doctorID', 'nurseID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialysis_sessions');
    }
};
