<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\HardwareSensor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main water quality dashboard.
     */
    public function index(Request $request)
    {
        $devices = Device::all();
        
        // Get device from query, or fall back to the first active device
        $deviceId = $request->query('device_id');
        $currentDevice = $deviceId 
            ? Device::find($deviceId) 
            : Device::first();

        // Get the latest 30 logs chronologically for the chart
        $logs = [];
        if ($currentDevice) {
            $logs = $currentDevice->sensorLogs()
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->reverse()
                ->values();
        }

        return view('dashboard', compact('devices', 'currentDevice', 'logs'));
    }

    /**
     * Display the sensory status page.
     */
    public function sensory(Request $request)
    {
        $devices = Device::all();
        
        // Get device from query, or fall back to the first active device
        $deviceId = $request->query('device_id');
        $currentDevice = $deviceId 
            ? Device::find($deviceId) 
            : Device::first();

        // Get the latest 30 logs chronologically for the chart/status
        $logs = [];
        if ($currentDevice) {
            $logs = $currentDevice->sensorLogs()
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->reverse()
                ->values();
        }

        return view('sensory', compact('devices', 'currentDevice', 'logs'));
    }

    /**
     * Display the new rendaman registration page.
     */
    public function rendaman()
    {
        $devices = Device::all();
        $nextCount = Device::count() + 1;
        $nextDeviceCode = 'GG-' . date('Y') . '-' . str_pad($nextCount, 3, '0', STR_PAD_LEFT);

        return view('rendaman', compact('devices', 'nextDeviceCode'));
    }

    public function getDeviceData(Device $device)
    {
        $date = request('date');
        $query = $device->sensorLogs();

        if ($date) {
            $logs = $query->whereDate('created_at', $date)
                ->orderBy('created_at', 'asc')
                ->get()
                ->values();
        } else {
            $logs = $query->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->reverse()
                ->values();
        }

        return response()->json([
            'device' => $device,
            'logs' => $logs,
            'server_time' => now()->toIso8601String()
        ]);
    }

    /**
     * Store a newly created device (rendaman) in storage.
     */
    public function storeDevice(Request $request)
    {
        $request->validate([
            'location_name'          => 'required|string|max:100',
            'detoxification_method'  => 'required|string|max:100',
            'concentration'          => 'nullable|numeric|min:0|max:100',
            'slice_thickness'        => 'nullable|numeric|min:0|max:100',
            'yam_weight'             => 'nullable|numeric|min:0|max:1000',
            'water_volume'           => 'nullable|numeric|min:0|max:10000',
            'sensor_mode'            => 'required|string|in:Menetap,Keliling',
        ]);

        // Auto-generate code: GG-[Year]-[3-digit Sequence]
        $year = date('Y');
        $count = Device::count() + 1;
        $device_code = 'GG-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $device = Device::create([
            'device_code'           => $device_code,
            'location_name'         => $request->location_name,
            'detoxification_method' => $request->detoxification_method,
            'concentration'         => $request->concentration,
            'slice_thickness'       => $request->slice_thickness,
            'yam_weight'            => $request->yam_weight,
            'water_volume'          => $request->water_volume,
            'sensor_mode'           => $request->sensor_mode,
            'status'                => 'active',
        ]);

        // Seed 15 initial log entries to populate charts immediately
        $now = \Carbon\Carbon::now();
        for ($i = 15; $i >= 0; $i--) {
            $time = $now->copy()->subHours($i);
            $progression = (15 - $i) / 15; // 0.0 -> 1.0

            // Start parameters
            $basePh   = 5.6;
            $baseTurb = 520;
            $baseTds  = 610;

            // pH: start acidic, progress towards neutral
            $ph = round($basePh + ($progression * (6.9 - $basePh)) + (sin($i) * 0.05), 2);
            $ph = max(4.5, min(9.0, $ph));

            // Turbidity: goes down over time
            $turbidity = round(max(8, $baseTurb * (1 - $progression * 0.72) + rand(-10, 10)), 1);

            // TDS: goes down over time
            $tds = round(max(40, $baseTds * (1 - $progression * 0.68) + rand(-15, 15)), 1);

            // Temperature: daily cycle
            $temp = round(26.5 + (sin($time->hour * M_PI / 12) * 1.5) + (rand(-2, 2) / 10), 1);

            $hcn = $this->estimateHcn($ph, $turbidity, $tds, $temp);
            $status = $this->classifyHcnStatus($ph, $turbidity, $tds, $hcn);

            \App\Models\SensorLog::create([
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

        // Determine where to redirect back to
        $redirectUrl = $request->input('redirect_to', '/');
        $separator = strpos($redirectUrl, '?') !== false ? '&' : '?';

        return redirect($redirectUrl . $separator . 'device_id=' . $device->id)
            ->with('success', 'Rendaman baru dengan kode ' . $device_code . ' berhasil didaftarkan.');
    }

    private function estimateHcn(float $ph, float $turb, float $tds, float $temp): float
    {
        $contrib_tds  = 0.0005 * $tds;
        $contrib_turb = 0.0003 * $turb;
        $contrib_ph   = ($ph < 7.0) ? 0.08 * (7.0 - $ph) : 0.0;
        $contrib_temp = ($temp > 25.0) ? 0.003 * ($temp - 25.0) : 0.0;

        $hcn = $contrib_tds + $contrib_turb + $contrib_ph + $contrib_temp;
        return max(0.0, min(15.0, $hcn));
    }

    private function classifyHcnStatus(float $ph, float $turbidity, float $tds, float $hcn): string
    {
        if ($turbidity > 600 || $tds > 700 || $ph < 5.5 || $ph > 9.0 || $hcn > 3.0) {
            return 'Bahaya';
        } elseif ($turbidity > 100 || $tds > 150 || ($ph < 6.5 && $ph >= 5.5) || ($ph > 7.5 && $ph <= 9.0) || ($hcn >= 0.5 && $hcn <= 3.0)) {
            return 'Proses';
        } else {
            return 'Aman';
        }
    }

    /**
     * Display the Kanban-based process board.
     */
    public function process()
    {
        // Load all devices with their hardware sensor and latest sensor log
        $devices = Device::with(['hardwareSensor', 'sensorLogs' => function($query) {
            $query->orderBy('created_at', 'desc')->take(1);
        }])->get();

        // Load all available physical sensors
        $hardwareSensors = HardwareSensor::all();
        
        $serverTime = now()->toIso8601String();

        return view('process', compact('devices', 'hardwareSensors', 'serverTime'));
    }

    /**
     * Update the process stage of a batch/device.
     */
    public function updateStage(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'stage' => 'required|in:soaking,rinsing,drying,completed',
        ]);

        $device = Device::find($request->device_id);
        $device->update(['process_stage' => $request->stage]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tahapan proses berhasil diperbarui ke ' . $request->stage,
            'device' => $device
        ]);
    }

    /**
     * Assign a hardware sensor to a specific perendaman batch (Device).
     */
    public function assignSensor(Request $request)
    {
        $request->validate([
            'sensor_id' => 'required|exists:hardware_sensors,id',
            'device_id' => 'nullable|exists:devices,id',
        ]);

        $sensor = HardwareSensor::find($request->sensor_id);
        
        // Detach the sensor from any existing batch (device)
        $sensor->update(['assigned_device_id' => $request->device_id]);

        return response()->json([
            'status' => 'success',
            'message' => $request->device_id 
                ? 'Sensor "' . $sensor->name . '" berhasil dipasang ke rendaman.' 
                : 'Sensor "' . $sensor->name . '" berhasil dilepas.',
            'sensor' => $sensor
        ]);
    }

    /**
     * Manually register a new hardware sensor from the Process page.
     */
    public function storeSensor(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'chip_identifier' => 'required|string|max:100|unique:hardware_sensors,chip_identifier',
        ]);

        $sensor = HardwareSensor::create([
            'name'            => $request->name,
            'chip_identifier' => $request->chip_identifier,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sensor "' . $sensor->name . '" berhasil didaftarkan.',
            'sensor'  => $sensor,
        ]);
    }

    /**
     * Delete a hardware sensor from the system.
     */
    public function deleteSensor($id)
    {
        $sensor = HardwareSensor::findOrFail($id);
        $name = $sensor->name;
        $sensor->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Sensor "' . $name . '" berhasil dihapus.',
        ]);
    }
}

