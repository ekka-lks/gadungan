<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom status keberadaan sensor.
     * Setiap sensor bisa ada (true) atau tidak terdeteksi (false).
     */
    public function up(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->boolean('sensor_ph_detected')->default(true)->after('hcn_estimated');
            $table->boolean('sensor_turbidity_detected')->default(true)->after('sensor_ph_detected');
            $table->boolean('sensor_tds_detected')->default(true)->after('sensor_turbidity_detected');
            $table->boolean('sensor_temp_detected')->default(true)->after('sensor_tds_detected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'sensor_ph_detected',
                'sensor_turbidity_detected',
                'sensor_tds_detected',
                'sensor_temp_detected',
            ]);
        });
    }
};
