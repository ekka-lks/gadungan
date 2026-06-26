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
        Schema::create('sensor_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel devices
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            
            // Data sensor
            $table->float('ph_value');
            $table->float('turbidity_value');
            $table->float('tds_value');
            $table->float('temperature_value');
            
            // Hasil analisis sistem
            $table->enum('safety_status', ['Aman', 'Proses', 'Bahaya']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_logs');
    }
};
