<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_key',
        'label',
        'group',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible'  => 'boolean',
        'sort_order'  => 'integer',
    ];
}
