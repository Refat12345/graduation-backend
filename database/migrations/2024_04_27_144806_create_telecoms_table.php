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
        Schema::create('telecoms', function (Blueprint $table) {
            $table->id(); 
            $table->string('system');
            $table->string('value')->unique();
            $table->string('use')->nullable();
            $table->unsignedBigInteger('userID')->nullable();
            $table->unsignedBigInteger('centerID')->nullable();
            $table->unsignedBigInteger('patientCompanionID')->nullable();
            $table->foreign('patientCompanionID')->references('id')->on('patient_companions');
            $table->foreign('userID')->references('id')->on('users');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->timestamps();

            $table->index('value');
            $table->index(['system', 'use']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telecoms');
    }
};
