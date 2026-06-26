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
        'safety_status',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
