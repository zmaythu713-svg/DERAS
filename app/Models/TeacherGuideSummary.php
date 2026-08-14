<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherGuideSummary extends Model
{
    protected $fillable = [
        'teacher_guide_id',
        'academic_year_id',
        'grade_id',
        'book_name_id',
        'group_no',
        'group_title',
        'guide_type',
        'sequence_no',
        'previous_balance',
        'fiscal_year_quota',
        'distributed_books',
        'remark',
    ];

    protected $casts = [
        'group_no' => 'integer',
        'sequence_no' => 'integer',
        'previous_balance' => 'integer',
        'fiscal_year_quota' => 'integer',
        'distributed_books' => 'integer',
    ];

    protected function totalBooks(): Attribute
    {
        return Attribute::get(fn () => (int) $this->previous_balance
            + (int) $this->fiscal_year_quota);
    }

    protected function remainingBooks(): Attribute
    {
        return Attribute::get(fn () => $this->total_books - (int) $this->distributed_books);
    }

    public function teacherGuide(): BelongsTo
    {
        return $this->belongsTo(TeacherGuide::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function bookName(): BelongsTo
    {
        return $this->belongsTo(BookName::class);
    }
}
