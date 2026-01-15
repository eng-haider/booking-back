<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'title',
        'description',
        'discount_type', // 'percentage' or 'fixed'
        'discount_value',
        'min_booking_hours',
        'max_uses',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'terms_and_conditions',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_booking_hours' => 'integer',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the venue that owns this offer.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Scope to get only active offers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope to get available offers (active and not maxed out).
     */
    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            });
    }

    /**
     * Check if offer is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }

        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given price.
     */
    public function calculateDiscount(float $price): float
    {
        if ($this->discount_type === 'percentage') {
            return round($price * ($this->discount_value / 100), 2);
        }

        return min($this->discount_value, $price);
    }

    /**
     * Increment the used count.
     */
    public function incrementUsedCount(): void
    {
        $this->increment('used_count');
    }
}
