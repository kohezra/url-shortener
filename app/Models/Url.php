<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Url extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'original_url',
        'short_code',
        'user_id',
        'title',
        'description',
        'expires_at',
        'is_active',
        'password_hash'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the user that owns the URL.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clicks for the URL.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Check if URL is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if URL is password protected.
     */
    public function isPasswordProtected(): bool
    {
        return !empty($this->password_hash);
    }

    /**
     * Check if URL is accessible.
     */
    public function isAccessible(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Get total clicks count.
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->clicks()->count();
    }

    /**
     * Get unique visitors count.
     */
    public function getUniqueClicksAttribute(): int
    {
        return $this->clicks()->distinct('ip_address')->count('ip_address');
    }

    /**
     * Get clicks today count.
     */
    public function getClicksTodayAttribute(): int
    {
        return $this->clicks()->whereDate('clicked_at', Carbon::today())->count();
    }

    /**
     * Get clicks this week count.
     */
    public function getClicksThisWeekAttribute(): int
    {
        return $this->clicks()->whereBetween('clicked_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
    }

    /**
     * Get clicks this month count.
     */
    public function getClicksThisMonthAttribute(): int
    {
        return $this->clicks()->whereBetween('clicked_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();
    }

    /**
     * Get short URL.
     */
    public function getShortUrlAttribute(): string
    {
        return url($this->short_code);
    }
}
