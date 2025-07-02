<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Click extends Model
{
    use HasFactory;
    protected $fillable = [
        'url_id',
        'ip_address',
        'user_agent',
        'referer',
        'country',
        'city',
        'browser',
        'os',
        'device_type',
        'clicked_at'
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the URL that was clicked.
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope for filtering by device type.
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope for filtering by browser.
     */
    public function scopeByBrowser($query, $browser)
    {
        return $query->where('browser', $browser);
    }

    /**
     * Scope for today's clicks.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('clicked_at', Carbon::today());
    }

    /**
     * Scope for this week's clicks.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('clicked_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's clicks.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('clicked_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    /**
     * Parse user agent to extract browser and OS information.
     */
    public static function parseUserAgent($userAgent)
    {
        $browser = 'Unknown';
        $os = 'Unknown';
        $deviceType = 'desktop';

        if (!$userAgent) {
            return compact('browser', 'os', 'deviceType');
        }

        // Simple browser detection
        if (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        // Simple OS detection (order matters - check mobile first)
        if (preg_match('/iPhone/i', $userAgent)) {
            $os = 'iOS';
            $deviceType = 'mobile';
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $os = 'iOS';
            $deviceType = 'tablet';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
            $deviceType = 'mobile';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        }

        // Device type detection is already handled in OS detection above
        // No need for additional device type detection here since it's set in OS detection

        return compact('browser', 'os', 'deviceType');
    }
}
