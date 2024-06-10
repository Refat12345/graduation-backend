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
        Schema::create('analysis_types', function (Blueprint $table) {
            $table->id();
            $table->string('analysisName');
            $table->integer('recurrenceInterval');
            $table->string('unitOfMeasurement')->nullable();
            $table->timestamps();

           $table->index('analysisName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_types');
    }
};
