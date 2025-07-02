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
}
