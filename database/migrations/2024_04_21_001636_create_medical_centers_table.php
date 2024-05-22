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
        Schema::create('medical_centers', function (Blueprint $table) {
            $table->id(); 
            $table->string('centerName')->unique();
            $table->text('description')->nullable();
            $table->string('charityName')->nullable();
            $table->timestamps();
            
            $table->index('centerName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_centers');
    }
};
