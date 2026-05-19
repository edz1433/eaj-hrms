<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ipcr extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'folder_id', 'dpcr_id', 'pr_number', 'dp_pr_number', 'mfo', 'percent', 'year', 'status'
    ];
}
