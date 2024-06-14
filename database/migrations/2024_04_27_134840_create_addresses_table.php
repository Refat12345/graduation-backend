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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id(); 
            $table->string('use')->nullable();
            $table->text('line');
            $table->unsignedBigInteger('userID')->nullable();
            $table->unsignedBigInteger('centerID')->nullable();
            $table->unsignedBigInteger('cityID')->nullable();
            $table->unsignedBigInteger('PatientCompanionID')->nullable();

            $table->foreign('PatientCompanionID')->references('id')->on('patient_companions');
            $table->foreign('userID')->references('id')->on('users');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->foreign('cityID')->references('id')->on('cities');
            $table->unsignedBigInteger('valid')->default(0);
            $table->timestamps();

           $table->index('cityID');
           $table->index('userID');

           $table->index('centerID');
           $table->index('PatientCompanionID');
         

       //  $table->index(['line', 'cityID', 'userID'], 'add');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
