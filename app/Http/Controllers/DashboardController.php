<?php

namespace App\Http\Controllers;

use App\Models\Device;
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
     * API Endpoint to fetch historical & latest logs for a specific device.
     */
    public function getDeviceData(Device $device)
    {
        $logs = $device->sensorLogs()
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'device' => $device,
            'logs' => $logs
        ]);
    }
}
