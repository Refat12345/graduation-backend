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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->text('noteContent');
            $table->string('category');
            $table->string('type');
           // $table->date('date');
            $table->unsignedBigInteger('sessionID')->nullable();
            $table->unsignedBigInteger('senderID');
            $table->unsignedBigInteger('receiverID')->nullable();
            $table->unsignedBigInteger('centerID');
            $table->foreign('sessionID')->references('id')->on('dialysis_sessions');
            $table->foreign('senderID')->references('id')->on('users');
            $table->foreign('receiverID')->references('id')->on('users');
            $table->foreign('centerID')->references('id')->on('medical_centers');
            $table->unsignedBigInteger('valid')->default(0);

            $table->unsignedBigInteger('favorite')->nullable();
            $table->foreign('favorite')->references('id')->on('users');
           
            $table->timestamps();

            $table->index('centerID');
          

           $table->index(['senderID', 'receiverID'], 'req');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
