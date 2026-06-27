<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom hcn_estimated ke tabel sensor_logs.
     * Kolom ini menyimpan estimasi kadar HCN (mg/L) yang dihitung
     * oleh model AI lokal di ESP32 sebelum data dikirim ke server.
     * Nullable karena data lama (sebelum firmware v2) tidak memiliki nilai ini.
     */
    public function up(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            // Estimasi kadar HCN dari model AI lokal ESP32 (mg/L)
            $table->float('hcn_estimated')->nullable()->default(null)->after('temperature_value');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropColumn('hcn_estimated');
        });
    }
};
