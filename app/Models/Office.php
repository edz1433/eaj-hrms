<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    // Uses default (HRMS) DB connection — removed external payroll dependency
    protected $fillable = [
        'office_name',
        'office_abbr',
        'office_code',
        'office_head_id',
        'oic_id',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function head()
    {
        return $this->belongsTo(Employee::class, 'office_head_id');
    }

    public function oic()
    {
        return $this->belongsTo(Employee::class, 'oic_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────
    public function employees()
    {
        return $this->hasMany(Employee::class, 'emp_dept', 'id');
    }

    public function getOfficeAbbrAttribute($value): string
    {
        return $value ?? $this->office_name;
    }
}
