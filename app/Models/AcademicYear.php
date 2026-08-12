<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'is_active',
        'is_current',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];

    public function quotas()
    {
        return $this->hasMany(Quota::class);
    }

    public function previousYearBalances()
    {
        return $this->hasMany(PreviousYearBalance::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isCurrent(): bool
    {
        return (bool) $this->is_current;
    }

    /** Years after the active current year (e.g. 2026-2027 while current is 2025-2026). */
    public function isFuture(): bool
    {
        static $currentStart = false;
        if ($currentStart === false) {
            $currentStart = static::query()->where('is_current', true)->value('start_year');
            if ($currentStart === null) {
                $currentName = static::query()->where('is_current', true)->value('name');
                $currentStart = $currentName && preg_match('/^(\d{4})-/', $currentName, $m)
                    ? (int) $m[1]
                    : null;
            }
        }

        $thisStart = $this->start_year;
        if ($thisStart === null && $this->name && preg_match('/^(\d{4})-/', $this->name, $m)) {
            $thisStart = (int) $m[1];
        }

        if ($currentStart === null || $thisStart === null) {
            return false;
        }

        return (int) $thisStart > (int) $currentStart;
    }

    public function isPast(): bool
    {
        static $currentStart = false;
        if ($currentStart === false) {
            $currentStart = static::query()->where('is_current', true)->value('start_year');
            if ($currentStart === null) {
                $currentName = static::query()->where('is_current', true)->value('name');
                $currentStart = $currentName && preg_match('/^(\d{4})-/', $currentName, $m)
                    ? (int) $m[1]
                    : null;
            }
        }

        $thisStart = $this->start_year;
        if ($thisStart === null && $this->name && preg_match('/^(\d{4})-/', $this->name, $m)) {
            $thisStart = (int) $m[1];
        }

        if ($currentStart === null || $thisStart === null) {
            return false;
        }

        return (int) $thisStart < (int) $currentStart;
    }

    /** Data entry allowed for current + past years only. */
    public function allowsDataEntry(): bool
    {
        return !$this->isFuture();
    }

    public static function makeName(int $startYear): string
    {
        return $startYear . '-' . ($startYear + 1);
    }
}
