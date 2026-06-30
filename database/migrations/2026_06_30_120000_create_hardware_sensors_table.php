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
        Schema::create('hardware_sensors', function (Blueprint $table) {
            $table->id();
            $table->string('chip_identifier', 100)->unique();
            $table->string('name', 100);
            $table->unsignedBigInteger('assigned_device_id')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->foreign('assigned_device_id')
                ->references('id')
                ->on('devices')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_sensors');
    }
};
