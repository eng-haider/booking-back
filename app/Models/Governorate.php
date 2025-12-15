<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the providers for the governorate.
     */
    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    /**
     * Scope a query to only include active governorates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
