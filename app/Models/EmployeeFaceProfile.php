<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFaceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'qr_token',
        'face_embedding',
        'scan_count',
        'registered_by',
        'registered_at',
        'is_active',
    ];

    protected $casts = [
        'face_embedding' => 'encrypted:array',
        'is_active' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_ID');
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
