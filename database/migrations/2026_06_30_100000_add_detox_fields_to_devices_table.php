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
        Schema::table('devices', function (Blueprint $table) {
            $table->string('detoxification_method')->nullable();
            $table->double('concentration')->nullable();
            $table->double('slice_thickness')->nullable();
            $table->double('yam_weight')->nullable();
            $table->double('water_volume')->nullable();
            $table->string('sensor_mode')->default('Menetap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'detoxification_method',
                'concentration',
                'slice_thickness',
                'yam_weight',
                'water_volume',
                'sensor_mode'
            ]);
        });
    }
};
