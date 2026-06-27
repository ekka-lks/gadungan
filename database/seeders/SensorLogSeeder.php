<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\SensorLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SensorLogSeeder extends Seeder
{
    /**
     * Seed data dummy sensor logs untuk 24 jam terakhir.
     * Setiap device mensimulasikan proses detoksifikasi gadung dari BAHAYA → PROSES → AMAN.
     * Kolom hcn_estimated diisi sesuai model AI lokal ESP32 (proxy regresi).
     */
    public function run(): void
    {
        $devices = Device::all();

        foreach ($devices as $device) {
            $now = Carbon::now();

            // Variasi parameter awal tiap device
            $basePh   = $device->device_code === 'DEV002' ? 5.4 : 6.8;
            $baseTurb = $device->device_code === 'DEV002' ? 580 : 420;
            $baseTds  = $device->device_code === 'DEV002' ? 680 : 500;

            for ($i = 24; $i >= 0; $i--) {
                $time       = $now->copy()->subHours($i);
                $progression = (24 - $i) / 24; // 0.0 → 1.0 (makin tua makin bersih)

                // pH: mulai asam/netral, mendekati netral seiring waktu
                $ph = $basePh + ($progression * (7.2 - $basePh)) + (sin($i) * 0.08);
                $ph = round(max(4.5, min(9.0, $ph)), 2);

                // Turbidity: turun drastis seiring perendaman & penggantian air
                $turbidity = max(8, $baseTurb * (1 - $progression * 0.85) + rand(-12, 12));
                $turbidity = round($turbidity, 1);

                // TDS: turun lebih lambat (zat terlarut perlu waktu lebih lama)
                $tds = max(40, $baseTds * (1 - $progression * 0.75) + rand(-20, 20));
                $tds = round($tds, 1);

                // Suhu: siklus harian (siang lebih panas, malam lebih dingin)
                $temp = round(26.5 + (sin($time->hour * M_PI / 12) * 1.8) + (rand(-2, 2) / 10), 1);

                // ── Estimasi HCN menggunakan rumus yang sama dengan ESP32 ──
                $hcn = $this->estimateHCN($ph, $turbidity, $tds, $temp);

                // ── Klasifikasi status (threshold yang sama dengan SensorController) ──
                if ($turbidity > 600 || $tds > 700 || $ph < 5.5 || $ph > 9.0 || $hcn > 3.0) {
                    $status = 'Bahaya';
                } elseif ($turbidity > 100 || $tds > 150 || ($ph < 6.5 && $ph >= 5.5) || ($ph > 7.5 && $ph <= 9.0) || ($hcn >= 0.5 && $hcn <= 3.0)) {
                    $status = 'Proses';
                } else {
                    $status = 'Aman';
                }

                SensorLog::create([
                    'device_id'         => $device->id,
                    'ph_value'          => $ph,
                    'turbidity_value'   => $turbidity,
                    'tds_value'         => $tds,
                    'temperature_value' => $temp,
                    'hcn_estimated'     => round($hcn, 4),
                    'safety_status'     => $status,
                    'created_at'        => $time,
                    'updated_at'        => $time,
                ]);
            }
        }
    }

    /**
     * Estimasi kadar HCN — rumus proxy yang identik dengan GadungGuard.ino
     * Memastikan konsistensi antara ESP32 firmware dan seed data.
     */
    private function estimateHCN(float $ph, float $turb, float $tds, float $temp): float
    {
        $contrib_tds  = 0.0005 * $tds;
        $contrib_turb = 0.0003 * $turb;
        $contrib_ph   = ($ph < 7.0) ? 0.08 * (7.0 - $ph) : 0.0;
        $contrib_temp = ($temp > 25.0) ? 0.003 * ($temp - 25.0) : 0.0;

        $hcn = $contrib_tds + $contrib_turb + $contrib_ph + $contrib_temp;
        return max(0.0, min(15.0, $hcn));
    }
}
