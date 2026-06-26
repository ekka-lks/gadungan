<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Seed data dummy untuk tabel devices.
     */
    public function run(): void
    {
        $devices = [
            [
                'device_code' => 'DEV001',
                'location_name' => 'Bak Rendaman 1',
                'status' => 'active',
            ],
            [
                'device_code' => 'DEV002',
                'location_name' => 'Bak Rendaman 2',
                'status' => 'active',
            ],
            [
                'device_code' => 'DEV003',
                'location_name' => 'Bak Rendaman 3',
                'status' => 'maintenance',
            ],
        ];

        foreach ($devices as $device) {
            Device::firstOrCreate(
                ['device_code' => $device['device_code']],
                $device
            );
        }
    }
}
