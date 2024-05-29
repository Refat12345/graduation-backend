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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patientID');
            $table->unsignedBigInteger('doctorID');
            $table->foreign('patientID')->references('id')->on('users');
            $table->foreign('doctorID')->references('id')->on('users');
            $table->timestamps();


           

           $table->index(['patientID', 'doctorID'], 'presc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
