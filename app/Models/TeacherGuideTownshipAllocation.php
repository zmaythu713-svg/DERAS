<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherGuideTownshipAllocation extends Model
{
    protected $fillable = [
        'teacher_guide_id',
        'township_id',
        'kg_g12_qty',
        'g1_g5_qty',
    ];

    protected $casts = [
        'kg_g12_qty' => 'integer',
        'g1_g5_qty' => 'integer',
    ];

    public function teacherGuide(): BelongsTo
    {
        return $this->belongsTo(TeacherGuide::class);
    }

    public function township(): BelongsTo
    {
        return $this->belongsTo(Township::class);
    }
}
