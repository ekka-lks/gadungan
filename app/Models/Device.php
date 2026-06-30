<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'device_code',
        'location_name',
        'status',
        'detoxification_method',
        'concentration',
        'slice_thickness',
        'yam_weight',
        'water_volume',
        'sensor_mode',
        'process_stage',
    ];

    public function sensorLogs(): HasMany
    {
        return $this->hasMany(SensorLog::class);
    }

    /**
     * Get the hardware sensor physically assigned to this perendaman (Device).
     */
    public function hardwareSensor()
    {
        return $this->hasOne(HardwareSensor::class, 'assigned_device_id');
    }
}
