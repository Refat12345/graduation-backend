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
      

        Schema::create('requests', function (Blueprint $table) {
            $table->id(); 
            $table->string('requestStatus');
            $table->text('cause');
           
            $table->unsignedBigInteger('center_id');
            $table->foreign('center_id')->references('id')->on('medical_centers');
            $table->unsignedBigInteger('valid')->default(0);

            $table->timestamps();

            $table->index('requestStatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
