<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMenuPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_keys',
    ];

    protected $casts = [
        'menu_keys' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
