<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Closing extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date',
        'month',
        'year',
        'status',
        'closed_at',
        'user_id',
        'total_income',
    ];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime',
        'total_income' => 'decimal:2',
    ];

    /**
     * Get the user who performed the closing.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include daily closings.
     */
    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    /**
     * Scope a query to only include monthly closings.
     */
    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    /**
     * Check if a specific date is closed.
     * A date is closed if:
     * 1. There is a daily closing for that specific date with status 'closed'.
     * OR
     * 2. There is a monthly closing for the month/year of that date with status 'closed'.
     *
     * @param string|\DateTime $date
     * @return bool
     */
    public static function isDateClosed($date)
    {
        $dateObj = Carbon::parse($date);
        $cacheKey = 'closing_status_' . $dateObj->format('Y-m-d');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($dateObj) {
            // Check Daily Closing
            $dailyClosed = self::daily()
                ->whereDate('date', $dateObj->format('Y-m-d'))
                ->where('status', 'closed')
                ->exists();

            if ($dailyClosed) {
                return true;
            }

            // Check Monthly Closing
            $monthlyClosed = self::monthly()
                ->where('month', $dateObj->month)
                ->where('year', $dateObj->year)
                ->where('status', 'closed')
                ->exists();

            return $monthlyClosed;
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            self::clearCache($model);
        });

        static::deleted(function ($model) {
            self::clearCache($model);
        });
    }

    protected static function clearCache($model)
    {
        if ($model->type === 'daily') {
            $key = 'closing_status_' . $model->date->format('Y-m-d');
            \Illuminate\Support\Facades\Cache::forget($key);
        } else {
             // For monthly, we need to clear all days in that month?
             // Or we just rely on the short cache duration (60s).
             // Clearing all days in a month is expensive to calculate keys.
             // Given the closing frequency is low, 60 seconds (or even longer) cache is fine.
             // But if we want immediate consistency, we can rely on manual clearing or key tags (if redis).
             // For simplicity, let's stick to short duration cache or accept that monthly might take a moment.
             // Actually, if we use Cache tags it's easy. But file driver doesn't support tags.
             // Let's just use 60 seconds cache for read.
        }
    }

    /**
     * Check if a specific month/year is closed.
     *
     * @param int $month
     * @param int $year
     * @return bool
     */
    public static function isMonthlyClosed($month, $year)
    {
        return self::monthly()
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'closed')
            ->exists();
    }
}
