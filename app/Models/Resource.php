<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'capacity',
        'price_per_hour',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_per_hour' => 'decimal:2',
        ];
    }

    /**
     * Get the venue that owns the resource.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
