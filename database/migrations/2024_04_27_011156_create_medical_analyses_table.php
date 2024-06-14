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
        Schema::create('medical_analyses', function (Blueprint $table) {
            $table->id();
            $table->float('averageMin');
            $table->float('averageMax');
            $table->float('value');
            $table->date('analysisDate');
            $table->text('notes')->nullable();
            $table->string('quarter');
            $table->unsignedBigInteger('analysisTypeID');
            $table->unsignedBigInteger('userID');
            $table->foreign('analysisTypeID')->references('id')->on('analysis_types');
            $table->foreign('userID')->references('id')->on('users');
            $table->unsignedBigInteger('valid')->default(0);

            $table->timestamps();

           $table->index(['userID', 'analysisTypeID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_analyses');
    }
};
