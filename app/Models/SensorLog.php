<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorLog extends Model
{
    protected $fillable = [
        'device_id',
        'ph_value',
        'turbidity_value',
        'tds_value',
        'temperature_value',
        'hcn_estimated',
        'safety_status',
        'sensor_ph_detected',
        'sensor_turbidity_detected',
        'sensor_tds_detected',
        'sensor_temp_detected',
    ];

    protected $casts = [
        'sensor_ph_detected'        => 'boolean',
        'sensor_turbidity_detected' => 'boolean',
        'sensor_tds_detected'       => 'boolean',
        'sensor_temp_detected'      => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
