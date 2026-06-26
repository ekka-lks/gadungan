<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\SensorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorLogApiTest extends TestCase
{
    use RefreshDatabase;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat device dummy untuk setiap test
        $this->device = Device::create([
            'device_code' => 'DEV001',
            'location_name' => 'Bak Rendaman 1',
            'status' => 'active',
        ]);
    }

    /**
     * Test: data berhasil disimpan dengan status "Bahaya"
     * Kondisi: turbidity > 400 ATAU tds > 500 ATAU ph < 6.0 ATAU ph > 8.5
     */
    public function test_store_returns_bahaya_when_thresholds_exceeded(): void
    {
        $payload = [
            'device_id' => $this->device->id,
            'ph_value' => 7.2,
            'turbidity_value' => 450.5,
            'tds_value' => 520.0,
            'temperature_value' => 29.5,
        ];

        $response = $this->postJson('/api/sensor-logs', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'safety_status_result' => 'Bahaya',
            ]);

        $this->assertDatabaseHas('sensor_logs', [
            'device_id' => $this->device->id,
            'safety_status' => 'Bahaya',
        ]);
    }

    /**
     * Test: data berhasil disimpan dengan status "Proses"
     * Kondisi: turbidity > 150 ATAU tds > 250 (tapi tidak melebihi batas Bahaya)
     */
    public function test_store_returns_proses_when_moderate_values(): void
    {
        $payload = [
            'device_id' => $this->device->id,
            'ph_value' => 7.0,
            'turbidity_value' => 200.0,
            'tds_value' => 300.0,
            'temperature_value' => 28.0,
        ];

        $response = $this->postJson('/api/sensor-logs', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'safety_status_result' => 'Proses',
            ]);

        $this->assertDatabaseHas('sensor_logs', [
            'device_id' => $this->device->id,
            'safety_status' => 'Proses',
        ]);
    }

    /**
     * Test: data berhasil disimpan dengan status "Aman"
     * Kondisi: semua nilai sensor dalam batas aman
     */
    public function test_store_returns_aman_when_all_values_safe(): void
    {
        $payload = [
            'device_id' => $this->device->id,
            'ph_value' => 7.0,
            'turbidity_value' => 50.0,
            'tds_value' => 100.0,
            'temperature_value' => 27.0,
        ];

        $response = $this->postJson('/api/sensor-logs', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'safety_status_result' => 'Aman',
            ]);

        $this->assertDatabaseHas('sensor_logs', [
            'device_id' => $this->device->id,
            'safety_status' => 'Aman',
        ]);
    }

    /**
     * Test: pH terlalu rendah (< 6.0) menghasilkan "Bahaya"
     */
    public function test_store_returns_bahaya_when_ph_too_low(): void
    {
        $payload = [
            'device_id' => $this->device->id,
            'ph_value' => 5.5,
            'turbidity_value' => 50.0,
            'tds_value' => 100.0,
            'temperature_value' => 27.0,
        ];

        $response = $this->postJson('/api/sensor-logs', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'safety_status_result' => 'Bahaya',
            ]);
    }

    /**
     * Test: pH terlalu tinggi (> 8.5) menghasilkan "Bahaya"
     */
    public function test_store_returns_bahaya_when_ph_too_high(): void
    {
        $payload = [
            'device_id' => $this->device->id,
            'ph_value' => 9.0,
            'turbidity_value' => 50.0,
            'tds_value' => 100.0,
            'temperature_value' => 27.0,
        ];

        $response = $this->postJson('/api/sensor-logs', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'safety_status_result' => 'Bahaya',
            ]);
    }

    /**
     * Test: validasi gagal jika field wajib tidak ada
     */
    public function test_store_fails_validation_when_fields_missing(): void
    {
        $response = $this->postJson('/api/sensor-logs', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'device_id',
                'ph_value',
                'turbidity_value',
                'tds_value',
                'temperature_value',
            ]);
    }

    /**
     * Test: validasi gagal jika device_id tidak ada di database
     */
    public function test_store_fails_validation_when_device_not_exists(): void
    {
        $payload = [
            'device_id' => 9999,
            'ph_value' => 7.0,
            'turbidity_value' => 50.0,
            'tds_value' => 100.0,
            'temperature_value' => 27.0,
        ];

        $response = $this->postJson('/api/sensor-logs', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_id']);
    }
}
