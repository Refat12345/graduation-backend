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
        Schema::create('user_shifts', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('shiftID');
            $table->unsignedBigInteger('userID');
            $table->foreign('shiftID')->references('id')->on('shifts');
            $table->foreign('userID')->references('id')->on('users');
            $table->timestamps();

           $table->index(['userID', 'shiftID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shifts');
    }
};
