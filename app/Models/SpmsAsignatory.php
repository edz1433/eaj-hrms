<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmsAsignatory extends Model
{
    use HasFactory;
    protected $fillable = [
        'empid', 'pr_number', 'suffixes', 'designation', 'spms_type', 'label'
    ];
    public $timestamps = false;
}
