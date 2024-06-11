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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->timestamp('shiftStart')->nullable();
            $table->timestamp('shiftEnd')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('centerID');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->timestamps();

           $table->index('centerID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
