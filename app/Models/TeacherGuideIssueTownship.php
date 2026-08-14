<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherGuideIssueTownship extends Model
{
    protected $fillable = [
        'teacher_guide_issue_id','township_id','issued_quantity',
    ];

    protected $casts = [
        'issued_quantity' => 'integer',
    ];

    protected function fullPackageCount(): Attribute
    {
        return Attribute::get(function () {
            $unit = (int) ($this->issue?->package_unit ?? 0);

            return $unit > 0 ? intdiv((int) $this->issued_quantity, $unit) : 0;
        });
    }

    protected function looseBookCount(): Attribute
    {
        return Attribute::get(function () {
            $unit = (int) ($this->issue?->package_unit ?? 0);

            return $unit > 0 ? (int) $this->issued_quantity % $unit : 0;
        });
    }

    public function issue(): BelongsTo { return $this->belongsTo(TeacherGuideIssue::class, 'teacher_guide_issue_id'); }
    public function township(): BelongsTo { return $this->belongsTo(Township::class); }
}
