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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id(); 
            $table->date('dialysisStartDate');
            $table->float('dryWeight');
            $table->string('bloodType');
            $table->string('vascularEntrance');
            $table->boolean('kidneyTransplant');
            $table->text('causeRenalFailure');
            $table->unsignedBigInteger('userID')->nullable();
            $table->foreign('userID')->references('id')->on('users');
            $table->unsignedBigInteger('valid')->default(0);

            $table->timestamps();

            $table->index('userID');
        });




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
