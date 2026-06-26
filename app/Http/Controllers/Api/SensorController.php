<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SensorLog;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk dari ESP32
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'ph_value' => 'required|numeric',
            'turbidity_value' => 'required|numeric',
            'tds_value' => 'required|numeric',
            'temperature_value' => 'required|numeric',
        ]);

        // 2. Logika Aturan (Rule-based AI) untuk menentukan status keamanan air
        $ph = $request->ph_value;
        $turbidity = $request->turbidity_value;
        $tds = $request->tds_value;

        // Contoh simulasi batas ambang aman/tidaknya rendaman gadung
        if ($turbidity > 400 || $tds > 500 || $ph < 6.0 || $ph > 8.5) {
            $status = 'Bahaya';
        } elseif ($turbidity > 150 || $tds > 250) {
            $status = 'Proses';
        } else {
            $status = 'Aman';
        }

        // 3. Simpan data ke database menggunakan Eloquent ORM
        $log = new SensorLog();
        $log->device_id = $request->device_id;
        $log->ph_value = $ph;
        $log->turbidity_value = $turbidity;
        $log->tds_value = $tds;
        $log->temperature_value = $request->temperature_value;
        $log->safety_status = $status;
        $log->save();

        // 4. Kirim respon balik berupa JSON ke ESP32
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'safety_status_result' => $status
        ], 201);
    }
}
