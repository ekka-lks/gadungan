<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SensorLog;
use App\Models\HardwareSensor;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        // ──────────────────────────────────────────────────────────────
        // LANGKAH 1: Validasi payload dari ESP32
        // ──────────────────────────────────────────────────────────────
        $request->validate([
            'device_id'         => 'required|exists:devices,id',
            'chip_id'           => 'nullable|string|max:100',
            'ph_value'          => 'required|numeric|min:0|max:14',
            'turbidity_value'   => 'required|numeric|min:0',
            'tds_value'         => 'required|numeric|min:0',
            'temperature_value' => 'required|numeric',
            // HCN estimasi dari AI lokal ESP32 (opsional — nullable)
            'hcn_estimated'     => 'nullable|numeric|min:0',
            // Status deteksi sensor (opsional, default true)
            'sensor_ph_detected'        => 'nullable|boolean',
            'sensor_turbidity_detected' => 'nullable|boolean',
            'sensor_tds_detected'       => 'nullable|boolean',
            'sensor_temp_detected'      => 'nullable|boolean',
        ]);

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 1.5: Auto-register hardware sensor & smart routing
        // ──────────────────────────────────────────────────────────────
        $targetDeviceId = (int) $request->device_id;

        if ($request->filled('chip_id')) {
            $chipId = $request->chip_id;

            // Cari atau buat record hardware sensor
            $hwSensor = HardwareSensor::firstOrCreate(
                ['chip_identifier' => $chipId],
                [
                    'name' => 'Sensor-' . substr(str_replace(':', '', $chipId), -6),
                    'assigned_device_id' => $targetDeviceId,
                ]
            );

            // Update waktu terakhir sensor terlihat online
            $hwSensor->update(['last_seen_at' => now()]);

            // Smart routing: jika sensor sudah di-assign ke device lain via
            // halaman Process, gunakan assigned_device_id sebagai tujuan data
            if ($hwSensor->assigned_device_id) {
                $targetDeviceId = $hwSensor->assigned_device_id;
            }
        }

        $ph         = (float) $request->ph_value;
        $turbidity  = (float) $request->turbidity_value;
        $tds        = (float) $request->tds_value;
        $temp       = (float) $request->temperature_value;

        // Try ML prediction first
        $mlPrediction = self::callMlPrediction($ph, $turbidity, $tds, $temp);

        if ($mlPrediction) {
            $hcn              = $mlPrediction['hcn_air'];
            $status           = $mlPrediction['safety_status'];
            $predictionSource = 'ml';
            $hcnAirMl         = $mlPrediction['hcn_air'];
            $hcnUmbiMl        = $mlPrediction['hcn_umbi'];
            $statusAirMl      = $mlPrediction['status_air'];
            $statusGadungMl   = $mlPrediction['status_gadung'];
        } else {
            // Fallback to rule-based AI server
            $hcn = ($request->has('hcn_estimated') && $request->hcn_estimated !== null) 
                ? (float) $request->hcn_estimated 
                : self::estimateHcn($ph, $turbidity, $tds, $temp);
            $status           = self::classifyStatus($ph, $turbidity, $tds, $hcn);
            $predictionSource = 'rule-based';
            $hcnAirMl         = null;
            $hcnUmbiMl        = null;
            $statusAirMl      = null;
            $statusGadungMl   = null;
        }

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 3: Susun rekomendasi aksi berbasis status
        // ──────────────────────────────────────────────────────────────
        $recommendation = self::buildRecommendation($status, $ph, $turbidity, $tds, $hcn);

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 6: Simpan ke database (transparansi komunitas)
        // ──────────────────────────────────────────────────────────────
        $log = SensorLog::create([
            'device_id'         => $targetDeviceId,
            'ph_value'          => round($ph,        2),
            'turbidity_value'   => round($turbidity, 2),
            'tds_value'         => round($tds,       2),
            'temperature_value' => round($temp,      2),
            'hcn_estimated'     => $hcn !== null ? round($hcn, 4) : null,
            'safety_status'     => $status,
            'hcn_air_ml'        => $hcnAirMl !== null ? round($hcnAirMl, 4) : null,
            'hcn_umbi_ml'       => $hcnUmbiMl !== null ? round($hcnUmbiMl, 4) : null,
            'status_air_ml'     => $statusAirMl,
            'status_gadung_ml'  => $statusGadungMl,
            'prediction_source' => $predictionSource,
            // Status keberadaan sensor (dari ESP32 auto-detection)
            'sensor_ph_detected'        => $request->input('sensor_ph_detected', true),
            'sensor_turbidity_detected' => $request->input('sensor_turbidity_detected', true),
            'sensor_tds_detected'       => $request->input('sensor_tds_detected', true),
            'sensor_temp_detected'      => $request->input('sensor_temp_detected', true),
        ]);

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 4: Kembalikan response lengkap ke ESP32
        // (untuk ditampilkan di OLED dan log Serial)
        // ──────────────────────────────────────────────────────────────
        return response()->json([
            'status'                => 'success',
            'message'               => 'Data berhasil disimpan',
            'log_id'                => $log->id,
            'safety_status_result'  => $status,
            'hcn_estimated'         => $hcn,
            'prediction_source'     => $predictionSource,
            'hcn_air_ml'            => $hcnAirMl,
            'hcn_umbi_ml'           => $hcnUmbiMl,
            'status_air_ml'         => $statusAirMl,
            'status_gadung_ml'      => $statusGadungMl,
            'recommendation'        => $recommendation,
            'thresholds'            => [
                'ph_safe'           => '6.5 – 7.5',
                'turbidity_safe'    => '< 100 NTU',
                'tds_safe'          => '< 150 ppm',
                'hcn_safe'          => '< 0.5 mg/L (estimasi)',
            ],
        ], 201);
    }

    /**
     * Mengklasifikasikan status keamanan air rendaman gadung.
     * Menggabungkan parameter fisikokimia dan estimasi HCN dari ESP32.
     *
     * @param  float      $ph
     * @param  float      $turbidity  NTU
     * @param  float      $tds        ppm
     * @param  float|null $hcn        mg/L (dari AI lokal ESP32, nullable)
     * @return string     'Aman' | 'Proses' | 'Bahaya'
     */
    private static function classifyStatus(float $ph, float $turbidity, float $tds, ?float $hcn): string
    {
        // ── Kondisi BAHAYA: parameter melebihi batas kritis ──────────
        $bahaya_fisik = ($turbidity > 600 || $tds > 700 || $ph < 5.5 || $ph > 9.0);
        $bahaya_hcn   = ($hcn !== null && $hcn > 3.0);

        if ($bahaya_fisik || $bahaya_hcn) {
            return 'Bahaya';
        }

        // ── Kondisi PROSES: sedang dalam proses detoksifikasi ────────
        $proses_fisik = (
            ($turbidity > 100 && $turbidity <= 600) ||
            ($tds       > 150 && $tds       <= 700) ||
            ($ph < 6.5  && $ph >= 5.5) ||
            ($ph > 7.5  && $ph <= 9.0)
        );
        $proses_hcn   = ($hcn !== null && $hcn >= 0.5 && $hcn <= 3.0);

        if ($proses_fisik || $proses_hcn) {
            return 'Proses';
        }

        // ── Kondisi AMAN: semua dalam rentang normal ─────────────────
        return 'Aman';
    }

    /**
     * Menyusun kalimat rekomendasi tindakan untuk operator.
     */
    private static function buildRecommendation(
        string $status,
        float $ph,
        float $turbidity,
        float $tds,
        ?float $hcn
    ): string {
        if ($status === 'Bahaya') {
            $detail = [];
            if ($ph < 5.5 || $ph > 9.0)  $detail[] = "pH ekstrem ($ph)";
            if ($turbidity > 600)         $detail[] = "kekeruhan sangat tinggi ($turbidity NTU)";
            if ($tds > 700)               $detail[] = "TDS sangat tinggi ($tds ppm)";
            if ($hcn !== null && $hcn > 3.0) $detail[] = "estimasi HCN kritis ($hcn mg/L)";
            $detail_str = !empty($detail) ? ' Penyebab: ' . implode(', ', $detail) . '.' : '';
            return 'SEGERA ganti air rendaman!' . $detail_str
                 . ' Jangan konsumsi gadung sebelum status berubah menjadi Aman.'
                 . ' (⚠️ TENTANG LIMBAH: Air bekas rendaman gadung mengandung konsentrasi Asam Sianida (HCN) yang sangat tinggi akibat pelarutan racun dari umbi. Senyawa ini bersifat toksik akut yang dapat membunuh biota air secara instan dan mencemari air tanah. PANDUAN PENANGANAN: Jangan dibuang langsung ke selokan atau sungai. Tampung limbah di wadah terbuka di bawah matahari selama 24 jam agar HCN menguap aman, atau alirkan ke lubang resapan khusus jauh dari sumur).';
        }

        if ($status === 'Proses') {
            $hints = [];
            if ($turbidity > 300) $hints[] = 'ganti air lebih sering (tiap 6 jam)';
            if ($tds > 400)       $hints[] = 'gunakan air mengalir jika memungkinkan';
            if ($ph < 6.5)        $hints[] = 'pantau pH mendekati netral';
            $hints_str = !empty($hints) ? ' Saran: ' . implode('; ', $hints) . '.' : '';
            return 'Proses detoksifikasi berjalan.' . $hints_str
                 . ' Lanjutkan perendaman dan pantau setiap 8–12 jam.'
                 . ' (⚠️ TENTANG LIMBAH: Air limbah pada tahap ini masih membawa sisa-sisa racun sianida (HCN) yang terlarut bertahap dari umbi gadung. Meskipun kadarnya sedang, akumulasi limbah ini tetap berbahaya bagi ekosistem perairan. PANDUAN PENANGANAN: Tampung air limbah terlebih dahulu di wadah terbuka selama 24 jam agar kandungan racun menguap sebelum dibuang ke saluran pembuangan).';
        }

        return 'Air rendaman dalam kondisi aman. Gadung siap ditiriskan dan diolah lebih lanjut.'
             . ' Konfirmasi secara fisik sebelum diproses ke tahap memasak.'
             . ' (⚠️ TENTANG LIMBAH: Air rendaman pada tahap akhir memiliki kadar sianida (HCN) di bawah batas bahaya. Namun, air ini tetap mengandung sisa pati organik dari gadung yang dapat membusuk dan menimbulkan bau jika menggenang. PANDUAN PENANGANAN: Buang air rendaman ke tanah terbuka atau lubang resapan agar sisa pati dapat disaring dan diurai oleh tanah secara alami, tidak dibuang ke saluran air bersih).';
    }

    public function getConfig(Request $request)
    {
        $request->validate([
            'chip_id' => 'required|string',
        ]);

        $chipId = $request->query('chip_id');

        // Find or create the sensor mapping
        // e.g. for MAC address "XX:XX:XX:XX:XX:XX"
        $sensor = HardwareSensor::firstOrCreate(
            ['chip_identifier' => $chipId],
            ['name' => 'Sensor Kit ' . substr(str_replace(':', '', $chipId), -4)]
        );

        // Update last seen
        $sensor->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'success',
            'sensor_name' => $sensor->name,
            'device_id' => $sensor->assigned_device_id, // can be null
        ]);
    }

    /**
     * Mengestimasi kadar HCN (asam sianida) dalam air rendaman gadung.
     * Menggunakan model regresi proxy multi-variabel berbasis pengetahuan domain.
     */
    private static function estimateHcn(float $ph, float $turb, float $tds, float $temp): float
    {
        $contrib_tds  = 0.0005 * $tds;
        $contrib_turb = 0.0003 * $turb;
        $contrib_ph   = ($ph < 7.0) ? 0.08 * (7.0 - $ph) : 0.0;
        $contrib_temp = ($temp > 25.0) ? 0.003 * ($temp - 25.0) : 0.0;

        $hcn = $contrib_tds + $contrib_turb + $contrib_ph + $contrib_temp;
        return max(0.0, min(15.0, $hcn));
    }

    /**
     * Call Python ML prediction script.
     */
    private static function callMlPrediction(float $ph, float $turb, float $tds, float $temp): ?array
    {
        try {
            // Get python path from env, fallback to 'py -3.14' or 'python'
            $pythonPath = env('PYTHON_PATH', 'py -3.14');
            $scriptPath = base_path('app/PythonScript/predict.py');

            // Escape parameters to prevent shell injection
            $args = array_map('escapeshellarg', [$ph, $turb, $tds, $temp]);
            $command = "$pythonPath " . escapeshellarg($scriptPath) . " " . implode(' ', $args);

            // Execute command
            $output = [];
            $returnVar = 1;
            exec($command . ' 2>&1', $output, $returnVar);

            $outputStr = implode("\n", $output);

            if ($returnVar !== 0) {
                // If 'py -3.14' fails, try 'python' as a fallback
                if ($pythonPath === 'py -3.14') {
                    $command = "python " . escapeshellarg($scriptPath) . " " . implode(' ', $args);
                    $output = [];
                    exec($command . ' 2>&1', $output, $returnVar);
                    $outputStr = implode("\n", $output);
                }
            }

            if ($returnVar === 0) {
                $data = json_decode($outputStr, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['fallback_needed']) && !$data['fallback_needed']) {
                    return $data;
                }
            }

            \Log::warning("ML Prediction failed: " . $outputStr);
            return null;
        } catch (\Exception $e) {
            \Log::error("ML Execution error: " . $e->getMessage());
            return null;
        }
    }
}

