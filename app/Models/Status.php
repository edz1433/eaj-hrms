<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['status_name', 'sector', 'sort_order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForSector($query, string $sector)
    {
        return $query->where(function ($q) use ($sector) {
            $q->where('sector', $sector)->orWhere('sector', 'both');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sector')->orderBy('sort_order');
    }
}
