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
        Schema::create('user_centers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userID');
            $table->unsignedBigInteger('centerID');
            $table->foreign('userID')->references('id')->on('users');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->timestamps();
            $table->unsignedBigInteger('valid')->default(-1);


           $table->index('userID');
           $table->index('centerID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_centers');
    }
};
