<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HardwareSensor extends Model
{
    protected $fillable = [
        'chip_identifier',
        'name',
        'assigned_device_id',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the perendaman batch (Device) currently assigned to this physical sensor.
     */
    public function assignedDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'assigned_device_id');
    }
}
