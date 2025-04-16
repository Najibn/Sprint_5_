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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('name', ['Fire Extinguisher', 'Smoke Detector', 'Fire Alarm'])->default('Fire Extinguisher');
            $table->enum('type', ['water', 'foam', 'CO2', 'DCP']);
            $table->string('type_capacity');
            $table->string('serial_number')->unique();
            $table->enum('status', ['Active', 'Expired', 'Needs Maintenance']);
            $table->string('location');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
