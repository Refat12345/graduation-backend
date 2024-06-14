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
        Schema::create('patient_transfer_requests', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('patientID');
            $table->unsignedBigInteger('centerPatientID');
            $table->unsignedBigInteger('destinationCenterID');
            $table->unsignedBigInteger('requestID');
            $table->foreign('patientID')->references('id')->on('users');
            $table->foreign('centerPatientID')->references('id')->on('medical_centers');
            $table->foreign('destinationCenterID')->references('id')->on('medical_centers');
            $table->foreign('requestID')->references('id')->on('requests');
            $table->unsignedBigInteger('valid')->default(0);

            $table->timestamps();

           $table->index(['patientID', 'centerPatientID', 'destinationCenterID'], 'patientTransfer');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_transfer_requests');
    }
};
