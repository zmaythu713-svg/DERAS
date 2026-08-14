<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherGuideIssue extends Model
{
    protected $fillable = [
        'teacher_guide_id',
        'academic_year_id','grade_id','book_name_id','group_no','group_title',
        'guide_type','sequence_no','district_unit','package_unit','remark',
    ];

    protected $casts = [
        'group_no' => 'integer','sequence_no' => 'integer',
        'district_unit' => 'integer','package_unit' => 'integer',
    ];

    public function teacherGuide(): BelongsTo { return $this->belongsTo(TeacherGuide::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function bookName(): BelongsTo { return $this->belongsTo(BookName::class); }
    public function townshipIssues(): HasMany { return $this->hasMany(TeacherGuideIssueTownship::class); }
}
