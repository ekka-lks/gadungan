<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\SensorLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SensorLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = Device::all();

        foreach ($devices as $device) {
            // Generate data over the last 24 hours (hourly)
            $now = Carbon::now();
            
            // Adjust starting parameters based on device to have variations
            $basePh = $device->device_code === 'DEV002' ? 5.8 : 7.2;
            $baseTurb = $device->device_code === 'DEV002' ? 480 : 380;
            $baseTds = $device->device_code === 'DEV002' ? 580 : 460;

            for ($i = 24; $i >= 0; $i--) {
                $time = $now->copy()->subHours($i);
                
                // Simulate progressive clean-up process of yam soaking
                // Values decrease gradually over time
                $progression = (24 - $i) / 24; // 0 to 1
                
                // pH starts slightly acidic or neutral, stabilized over time
                $ph = $basePh + ($progression * 0.8) + (sin($i) * 0.1);
                
                // Turbidity drops dramatically
                $turbidity = max(10, $baseTurb * (1 - $progression * 0.75) + (rand(-10, 10)));
                
                // TDS drops
                $tds = max(50, $baseTds * (1 - $progression * 0.65) + (rand(-15, 15)));
                
                // Temperature cycles sinusoidally (day/night)
                $temp = 26.5 + (sin($time->hour * pi() / 12) * 1.8) + (rand(-2, 2) / 10);

                // Classification logic (matching Controller logic)
                if ($turbidity > 400 || $tds > 500 || $ph < 6.0 || $ph > 8.5) {
                    $status = 'Bahaya';
                } elseif ($turbidity > 150 || $tds > 250) {
                    $status = 'Proses';
                } else {
                    $status = 'Aman';
                }

                SensorLog::create([
                    'device_id' => $device->id,
                    'ph_value' => round($ph, 1),
                    'turbidity_value' => round($turbidity, 1),
                    'tds_value' => round($tds, 1),
                    'temperature_value' => round($temp, 1),
                    'safety_status' => $status,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }
    }
}
