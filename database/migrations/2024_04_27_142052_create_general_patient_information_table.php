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
        Schema::create('general_patient_informations', function (Blueprint $table) {
            $table->id(); 
            $table->string('maritalStatus');
            $table->string('nationality');
            $table->string('status');
            $table->text('reasonOfStatus')->nullable();
            $table->string('educationalLevel');
            $table->decimal('generalIncome', 8, 2);
            $table->string('incomeType');
            $table->string('sourceOfIncome');
            $table->text('workDetails')->nullable();
            $table->string('residenceType');
            $table->unsignedBigInteger('patientID');
            $table->foreign('patientID')->references('id')->on('users');
            $table->timestamps();
            
           $table->index('patientID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_patient_information');
    }
};
