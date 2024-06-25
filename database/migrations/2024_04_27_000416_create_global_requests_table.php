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
            $table->string('content');
            $table->unsignedBigInteger('requestID');
            $table->unsignedBigInteger('requesterID');
            $table->foreign('requestID')->references('id')->on('requests');
            $table->foreign('requesterID')->references('id')->on('users');


            $table->unsignedBigInteger('requestable_id'); // Polymorphic relation id
            $table->string('requestable_type'); // Polymorphic relation type


            $table->timestamps();
            $table->unsignedBigInteger('valid')->default(0);

            
         //  $table->index(['requesterID', 'reciverID']);
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
