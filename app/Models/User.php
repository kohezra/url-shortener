<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the URLs for the user.
     */
    public function urls(): HasMany
    {
        return $this->hasMany(Url::class);
    }

    /**
     * Get the active URLs for the user.
     */
    public function activeUrls(): HasMany
    {
        return $this->hasMany(Url::class)->where('is_active', true);
    }

    /**
     * Generate a new API token for the user.
     */
    public function generateApiToken(): string
    {
        $token = Str::random(60);
        $this->update(['api_token' => $token]);
        return $token;
    }

    /**
     * Get total URLs count for the user.
     */
    public function getTotalUrlsAttribute(): int
    {
        return $this->urls()->count();
    }

    /**
     * Get total clicks count for all user URLs.
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->urls()
            ->withCount('clicks')
            ->get()
            ->sum('clicks_count');
    }

    /**
     * Get clicks today count for all user URLs.
     */
    public function getClicksTodayAttribute(): int
    {
        return Click::whereHas('url', function ($query) {
            $query->where('user_id', $this->id);
        })->whereDate('clicked_at', Carbon::today())->count();
    }

    /**
     * Get clicks this week count for all user URLs.
     */
    public function getClicksThisWeekAttribute(): int
    {
        return Click::whereHas('url', function ($query) {
            $query->where('user_id', $this->id);
        })->whereBetween('clicked_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
    }

    /**
     * Get clicks this month count for all user URLs.
     */
    public function getClicksThisMonthAttribute(): int
    {
        return Click::whereHas('url', function ($query) {
            $query->where('user_id', $this->id);
        })->whereBetween('clicked_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();
    }

    /**
     * Get top URLs by click count.
     */
    public function getTopUrlsAttribute()
    {
        return $this->urls()
            ->withCount('clicks')
            ->orderBy('clicks_count', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent URLs.
     */
    public function getRecentUrlsAttribute()
    {
        return $this->urls()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get active URLs count.
     */
    public function getActiveUrlsAttribute(): int
    {
        return $this->urls()->where('is_active', true)->count();
    }

    /**
     * Get expired URLs count.
     */
    public function getExpiredUrlsAttribute(): int
    {
        return $this->urls()
            ->where('expires_at', '<', Carbon::now())
            ->count();
    }

    /**
     * Get password protected URLs count.
     */
    public function getPasswordProtectedUrlsAttribute(): int
    {
        return $this->urls()
            ->whereNotNull('password_hash')
            ->count();
    }

    /**
     * Get the total number of clicks across all user's URLs
     */
    public function totalClicks()
    {
        return $this->urls()
            ->withCount('clicks')
            ->get()
            ->sum('clicks_count');
    }

    /**
     * Get the average clicks per URL for this user
     */
    public function averageClicks()
    {
        $totalUrls = $this->urls()->count();
        if ($totalUrls === 0) {
            return 0;
        }

        return round($this->totalClicks() / $totalUrls, 1);
    }

    /**
     * Get the total clicks for this user's URLs in the current month
     */
    public function clicksThisMonth()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return \DB::table('clicks')
            ->join('urls', 'clicks.url_id', '=', 'urls.id')
            ->where('urls.user_id', $this->id)
            ->whereBetween('clicks.clicked_at', [$startOfMonth, $endOfMonth])
            ->count();
    }

    /**
     * Get the user's most popular URL (by clicks)
     */
    public function mostPopularUrl()
    {
        return $this->urls()
            ->withCount('clicks')
            ->orderBy('clicks_count', 'desc')
            ->first();
    }

    /**
     * Get recent URLs created by this user
     */
    public function recentUrls($limit = 5)
    {
        return $this->urls()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get URLs created in the last N days
     */
    public function urlsInLastDays($days = 7)
    {
        return $this->urls()
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Get clicks in the last N days across all user's URLs
     */
    public function clicksInLastDays($days = 7)
    {
        $cutoffDate = now()->subDays($days);

        return \DB::table('clicks')
            ->join('urls', 'clicks.url_id', '=', 'urls.id')
            ->where('urls.user_id', $this->id)
            ->where('clicks.clicked_at', '>=', $cutoffDate)
            ->count();
    }
}
