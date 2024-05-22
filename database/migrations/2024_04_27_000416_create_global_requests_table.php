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
        Schema::create('global_requests', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('direction');
            $table->unsignedBigInteger('requestID');
            $table->unsignedBigInteger('requesterID');
            $table->unsignedBigInteger('reciverID');
            $table->foreign('requestID')->references('id')->on('requests');
            $table->foreign('requesterID')->references('id')->on('users');
            $table->foreign('reciverID')->references('id')->on('users');
            $table->timestamps();
            
            $table->index(['requesterID', 'reciverID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_requests');
    }
};
