<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationPlanTownship extends Model
{
    protected $fillable = [
        'allocation_plan_id',
        'township_id',
        'previous',
        'total_students',
        'transferable',
    ];

    protected $casts = [
        'previous' => 'integer',
        'total_students' => 'integer',
        'transferable' => 'integer',
    ];

    public function allocationPlan(): BelongsTo
    {
        return $this->belongsTo(AllocationPlan::class);
    }

    public function township(): BelongsTo
    {
        return $this->belongsTo(Township::class);
    }
}
