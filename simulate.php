<?php
/**
 * ============================================================
 * gadungGuard - Script Simulasi Status Sensor & AI
 * ============================================================
 * Gunakan script ini untuk mensimulasikan status AI di dashboard
 * tanpa harus mengubah air rendaman fisik secara nyata.
 * 
 * Cara Penggunaan di Terminal (Laragon):
 *   php simulate.php proses
 *   php simulate.php aman
 *   php simulate.php bahaya
 * ============================================================
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Device;
use App\Http\Controllers\Api\SensorController;

$mode = isset($argv[1]) ? strtolower($argv[1]) : 'proses';

// Ambil device aktif pertama
$device = Device::first();
if (!$device) {
    echo "ERROR: Tidak ada device/rendaman terdaftar di database.\n";
    echo "Silakan daftarkan rendaman terlebih dahulu di dashboard.\n";
    exit(1);
}

// Parameter simulasi berdasarkan hasil grid search model ML
switch ($mode) {
    case 'aman':
        echo "Mensimulasikan status: AMAN (Gadung Siap Konsumsi)\n";
        $ph = 5.28;
        $turb = 614.5;
        $tds = 655.3;
        $temp = 28.0;
        break;
        
    case 'proses':
        echo "Mensimulasikan status: PROSES (Detoksifikasi Sedang Berjalan)\n";
        $ph = 7.64;
        $turb = 800.0;
        $tds = 1100.0;
        $temp = 35.0;
        break;
        
    case 'bahaya':
    default:
        echo "Mensimulasikan status: BAHAYA (Air Bersih / Racun Belum Keluar)\n";
        $ph = 7.0;
        $turb = 0.0;
        $tds = 180.0;
        $temp = 26.0;
        break;
}

echo "Mengirim data ke SensorController API...\n";
echo "Parameter: pH = $ph | Kekeruhan = $turb NTU | TDS = $tds ppm | Suhu = $temp C\n";

// Buat request Laravel secara manual
$request = new \Illuminate\Http\Request();
$request->replace([
    'device_id' => $device->id,
    'ph_value' => $ph,
    'turbidity_value' => $turb,
    'tds_value' => $tds,
    'temperature_value' => $temp,
    'sensor_ph_detected' => true,
    'sensor_turbidity_detected' => true,
    'sensor_tds_detected' => true,
    'sensor_temp_detected' => true,
]);

$controller = new SensorController();
$response = $controller->store($request);
$result = json_decode($response->getContent(), true);

if ($response->getStatusCode() === 201) {
    echo "\nSUCCESS!\n";
    echo "Status AI terdeteksi: " . $result['safety_status_result'] . "\n";
    echo "HCN Air: " . ($result['hcn_air_ml'] ?? 'null') . " mg/L\n";
    echo "HCN Umbi: " . ($result['hcn_umbi_ml'] ?? 'null') . " mg/L\n";
    echo "Sumber Model: " . $result['prediction_source'] . "\n";
    echo "Dashboard akan langsung terupdate dalam 5 detik.\n";
} else {
    echo "\nFAILED!\n";
    print_r($result);
}
