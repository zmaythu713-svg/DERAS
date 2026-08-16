<?php

namespace App\Models;

use App\Support\TownshipKeys;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllocationPlan extends Model
{
    protected $fillable = [
        'academic_year_id',
        'grade_id',
        'book_name_id',

        'sequence_no',

        'received_books',
        'books_per_package',

        'remark',
    ];

    protected function ratio(): Attribute
    {
        return Attribute::get(function () {
            $eligible = $this->eligible_students_total;

            return $eligible > 0 ? ((float) $this->received_books / $eligible) : 0;
        });
    }

    protected function eligibleStudentsTotal(): Attribute
    {
        return Attribute::get(fn () => $this->sumTownshipValues('students'));
    }

    protected function allocatedBooksTotal(): Attribute
    {
        return Attribute::get(fn () => $this->sumTownshipValues('allocation'));
    }

    protected function studentCountTotal(): Attribute
    {
        return Attribute::get(fn () => $this->sumTownshipValues('total_students'));
    }

    protected function transferableBooksTotal(): Attribute
    {
        return Attribute::get(fn () => $this->sumTownshipValues('transferable'));
    }

    protected function availableTotal(): Attribute
    {
        return Attribute::get(fn () => $this->sumTownshipValues('final'));
    }

    protected function surplusShortageTotal(): Attribute
    {
        return Attribute::get(fn () => $this->sumTownshipValues('difference'));
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function bookName()
    {
        return $this->belongsTo(BookName::class);
    }

    public function townships(): HasMany
    {
        return $this->hasMany(AllocationPlanTownship::class);
    }

    /**
     * Merged view: computed fields from detail + input fields from townships.
     */
    public function detailCompat(): ?object
    {
        $this->loadMissing(['townships.township']);

        if ($this->townships->isEmpty()) {
            return null;
        }

        $merged = (object) [];

        foreach (TownshipKeys::slugs() as $slug) {
            $row = $this->townshipForSlug($slug);
            $merged->{"{$slug}_previous"} = (int) ($row?->getAttribute('previous') ?? 0);
            $merged->{"{$slug}_total_students"} = (int) ($row?->total_students ?? 0);
            $merged->{"{$slug}_transferable"} = (int) ($row?->transferable ?? 0);

            $eligible = $this->townshipComputedValue($slug, 'students');
            $allocation = $this->townshipComputedValue($slug, 'allocation');
            $unit = max(1, (int) $this->books_per_package);

            $merged->{"{$slug}_students"} = $eligible;
            $merged->{"{$slug}_allocation"} = $allocation;
            $merged->{"{$slug}_package"} = intdiv($allocation, $unit);
            $merged->{"{$slug}_loose"} = $allocation % $unit;
            $merged->{"{$slug}_final"} = $this->townshipComputedValue($slug, 'final');
            $merged->{"{$slug}_difference"} = $this->townshipComputedValue($slug, 'difference');
        }

        $merged->total_difference = array_sum(array_map(
            fn (string $slug) => $this->townshipComputedValue($slug, 'difference'),
            TownshipKeys::slugs()
        ));

        return $merged;
    }

    /**
     * @param  array<string, int>  $data  Keys like myanaung_previous, kyankhin_total_students, etc.
     */
    public function syncTownshipInputs(array $data): void
    {
        $townships = Township::query()
            ->whereIn('name', TownshipKeys::names())
            ->get()
            ->keyBy('name');

        foreach (TownshipKeys::nameToSlugMap() as $name => $slug) {
            $township = $townships->get($name);
            if (!$township) {
                continue;
            }

            $this->townships()->updateOrCreate(
                ['township_id' => $township->id],
                [
                    'previous' => (int) ($data["{$slug}_previous"] ?? 0),
                    'total_students' => (int) ($data["{$slug}_total_students"] ?? 0),
                    'transferable' => (int) ($data["{$slug}_transferable"] ?? 0),
                ]
            );
        }

        $this->unsetRelation('townships');
    }

    public function townshipForSlug(string $slug): ?AllocationPlanTownship
    {
        if (!$this->relationLoaded('townships')) {
            $this->load('townships.township');
        }

        $name = TownshipKeys::slugToName($slug);

        return $this->townships->first(
            fn (AllocationPlanTownship $row) => $row->township?->name === $name
        );
    }

    protected function sumTownshipValues(string $field): int
    {
        return array_sum(array_map(
            fn (string $slug) => $this->townshipComputedValue($slug, $field),
            TownshipKeys::slugs()
        ));
    }

    public function townshipComputedValue(string $slug, string $field): int
    {
        $row = $this->townshipForSlug($slug);
        $previous = (int) ($row?->getAttribute('previous') ?? 0);
        $students = (int) ($row?->total_students ?? 0);
        $transferable = (int) ($row?->transferable ?? 0);
        $eligible = $students - $previous - $transferable;

        if ($field === 'students') {
            return $eligible;
        }
        if ($field === 'total_students') {
            return $students;
        }
        if ($field === 'transferable') {
            return $transferable;
        }

        $allocation = (int) round($this->ratio * $eligible);
        if ($allocation < 0) {
            $allocation = 0;
        }
        $final = $previous + $allocation + $transferable;

        return match ($field) {
            'allocation' => $allocation,
            'final' => $final,
            'difference' => $final - $students,
            default => 0,
        };
    }
}
