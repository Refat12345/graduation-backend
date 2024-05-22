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
        Schema::create('blood_pressure_measurements', function (Blueprint $table) {
            $table->id();
            $table->integer('pressureValue');
            $table->integer('pulseValue');
            $table->timestamp('time')->nullable();
            $table->unsignedBigInteger('sessionID');
            $table->foreign('sessionID')->references('id')->on('dialysis_sessions');
            $table->timestamps();

            $table->index('sessionID');
        });
    }






    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_pressure_measurements');
    }
};
