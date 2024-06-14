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
        Schema::create('prescription_medicines', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->text('details');
            $table->string('status');
            $table->timestamp('dateOfEnd')->nullable();
            $table->timestamp('dateOfStart')->nullable();
            $table->unsignedBigInteger('prescriptionID');
            $table->unsignedBigInteger('medicineID');
            $table->foreign('prescriptionID')->references('id')->on('prescriptions');
            $table->foreign('medicineID')->references('id')->on('medicines');
            $table->unsignedBigInteger('valid')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_medicines');
    }
};
