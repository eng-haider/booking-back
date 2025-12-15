<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'icon',
    ];

    /**
     * Get the venues that have this amenity.
     */
    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'amenity_venue');
    }
}
