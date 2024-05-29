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
        Schema::create('pharmacological_histories', function (Blueprint $table) {
            $table->id(); 
            $table->string('medicineName');
            $table->date('dateStart');
            $table->date('dateEnd')->nullable();
            $table->text('generalDetails');
            $table->unsignedBigInteger('medicalRecordID');
            $table->foreign('medicalRecordID')->references('id')->on('medical_records');
            $table->timestamps();

           $table->index('medicalRecordID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacological_histories');
    }
};
