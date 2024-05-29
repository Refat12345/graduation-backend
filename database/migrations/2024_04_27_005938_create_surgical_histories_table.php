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
        Schema::create('surgical_histories', function (Blueprint $table) {
            $table->id(); 
            $table->string('surgeryName');
            $table->date('surgeryDate');
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
        Schema::dropIfExists('surgical_histories');
    }
};
