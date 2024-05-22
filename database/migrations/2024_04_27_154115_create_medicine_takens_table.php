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
        Schema::create('medicine_takens', function (Blueprint $table) {
            $table->id();
            $table->integer('value');
            $table->unsignedBigInteger('sessionID');
            $table->unsignedBigInteger('medicineID');
            $table->foreign('sessionID')->references('id')->on('dialysis_sessions');
            $table->foreign('medicineID')->references('id')->on('medicines');
            $table->timestamps();

            $table->index('sessionID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_takens');
    }
};
