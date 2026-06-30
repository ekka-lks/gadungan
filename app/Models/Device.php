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
    ];

    public function sensorLogs(): HasMany
    {
        return $this->hasMany(SensorLog::class);
    }
}
