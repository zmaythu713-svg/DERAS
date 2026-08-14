<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotaLine extends Model
{
    protected $fillable = [
        'quota_id',
        'school_level',
        'ownership',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }
}
