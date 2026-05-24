<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'qr_token',
        'selected_action',
        'status',
        'reason',
        'latitude',
        'longitude',
        'accuracy',
        'matched_score',
        'device_info',
        'ip_address',
        'logged_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'decimal:2',
        'matched_score' => 'decimal:6',
        'logged_at' => 'datetime',
    ];
}
