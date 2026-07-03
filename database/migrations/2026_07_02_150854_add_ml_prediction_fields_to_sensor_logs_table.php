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
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->float('hcn_air_ml')->nullable()->after('hcn_estimated');
            $table->float('hcn_umbi_ml')->nullable()->after('hcn_air_ml');
            $table->string('status_air_ml')->nullable()->after('safety_status');
            $table->string('status_gadung_ml')->nullable()->after('status_air_ml');
            $table->string('prediction_source')->default('rule-based')->after('status_gadung_ml');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'hcn_air_ml',
                'hcn_umbi_ml',
                'status_air_ml',
                'status_gadung_ml',
                'prediction_source'
            ]);
        });
    }
};
