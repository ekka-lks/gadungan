<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SensorLog;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        // ──────────────────────────────────────────────────────────────
        // LANGKAH 1: Validasi payload dari ESP32
        // ──────────────────────────────────────────────────────────────
        $request->validate([
            'device_id'         => 'required|exists:devices,id',
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

        $ph         = (float) $request->ph_value;
        $turbidity  = (float) $request->turbidity_value;
        $tds        = (float) $request->tds_value;
        $temp       = (float) $request->temperature_value;
        $hcn        = $request->has('hcn_estimated') ? (float) $request->hcn_estimated : null;

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 2: Rule-based AI server — validasi & klasifikasi status
        // Ini merupakan lapisan validasi kedua (server-side) di atas
        // model AI lokal yang sudah berjalan di ESP32.
        //
        // THRESHOLD FISIKOKIMIA (berbasis literatur detoksifikasi gadung):
        //   BAHAYA  → kondisi ekstrem, HCN masih sangat tinggi
        //   PROSES  → sedang dalam proses perendaman / pencucian HCN
        //   AMAN    → semua parameter dalam rentang aman konsumsi
        // ──────────────────────────────────────────────────────────────
        $status = self::classifyStatus($ph, $turbidity, $tds, $hcn);

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 3: Susun rekomendasi aksi berbasis status
        // ──────────────────────────────────────────────────────────────
        $recommendation = self::buildRecommendation($status, $ph, $turbidity, $tds, $hcn);

        // ──────────────────────────────────────────────────────────────
        // LANGKAH 6: Simpan ke database (transparansi komunitas)
        // ──────────────────────────────────────────────────────────────
        $log = SensorLog::create([
            'device_id'         => $request->device_id,
            'ph_value'          => round($ph,        2),
            'turbidity_value'   => round($turbidity, 2),
            'tds_value'         => round($tds,       2),
            'temperature_value' => round($temp,      2),
            'hcn_estimated'     => $hcn !== null ? round($hcn, 4) : null,
            'safety_status'     => $status,
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
                 . ' Jangan konsumsi gadung sebelum status berubah menjadi Aman.';
        }

        if ($status === 'Proses') {
            $hints = [];
            if ($turbidity > 300) $hints[] = 'ganti air lebih sering (tiap 6 jam)';
            if ($tds > 400)       $hints[] = 'gunakan air mengalir jika memungkinkan';
            if ($ph < 6.5)        $hints[] = 'pantau pH mendekati netral';
            $hints_str = !empty($hints) ? ' Saran: ' . implode('; ', $hints) . '.' : '';
            return 'Proses detoksifikasi berjalan.' . $hints_str
                 . ' Lanjutkan perendaman dan pantau setiap 8–12 jam.';
        }

        return 'Air rendaman dalam kondisi aman. Gadung siap ditiriskan dan diolah lebih lanjut.'
             . ' Konfirmasi secara fisik sebelum diproses ke tahap memasak.';
    }
}
