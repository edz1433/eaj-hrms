<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampBranch extends Model
{
    use HasFactory;

    protected $table    = 'camp_branches';
    protected $fillable = ['name', 'abbr', 'code'];
}
