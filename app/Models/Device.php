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
    ];

    public function sensorLogs(): HasMany
    {
        return $this->hasMany(SensorLog::class);
    }
}
