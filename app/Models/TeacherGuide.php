<?php

namespace App\Models;

use App\Support\TownshipKeys;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherGuide extends Model
{
    protected $fillable = [
        'academic_year_id',
        'grade_id',
        'book_name_id',

        'group_no',
        'group_title',
        'guide_type',
        'sequence_no',

        'kg_to_g12_quota',
        'g1_to_g5_quota',

        'remark',
    ];

    protected $casts = [
        'kg_to_g12_quota' => 'integer',
        'g1_to_g5_quota' => 'integer',
    ];

    protected function totalQuota(): Attribute
    {
        return Attribute::get(fn () => (int) $this->kg_to_g12_quota
            + (int) $this->g1_to_g5_quota);
    }

    protected function totalMyanaungQty(): Attribute
    {
        return Attribute::get(fn () => $this->kg_g12_myanaung_qty
            + $this->g1_g5_myanaung_qty);
    }

    protected function totalKyankhinQty(): Attribute
    {
        return Attribute::get(fn () => $this->kg_g12_kyankhin_qty
            + $this->g1_g5_kyankhin_qty);
    }

    protected function totalIngapuQty(): Attribute
    {
        return Attribute::get(fn () => $this->kg_g12_ingapu_qty
            + $this->g1_g5_ingapu_qty);
    }

    protected function distributedTotal(): Attribute
    {
        return Attribute::get(fn () => $this->total_myanaung_qty
            + $this->total_kyankhin_qty
            + $this->total_ingapu_qty);
    }

    protected function remainingTotal(): Attribute
    {
        return Attribute::get(fn () => $this->total_quota - $this->distributed_total);
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

    public function townshipAllocations(): HasMany
    {
        return $this->hasMany(TeacherGuideTownshipAllocation::class);
    }

    protected function kgG12MyanaungQty(): Attribute
    {
        return Attribute::get(fn () => $this->townshipFieldQty('myanaung', 'kg_g12_qty'));
    }

    protected function kgG12KyankhinQty(): Attribute
    {
        return Attribute::get(fn () => $this->townshipFieldQty('kyankhin', 'kg_g12_qty'));
    }

    protected function kgG12IngapuQty(): Attribute
    {
        return Attribute::get(fn () => $this->townshipFieldQty('ingapu', 'kg_g12_qty'));
    }

    protected function g1G5MyanaungQty(): Attribute
    {
        return Attribute::get(fn () => $this->townshipFieldQty('myanaung', 'g1_g5_qty'));
    }

    protected function g1G5KyankhinQty(): Attribute
    {
        return Attribute::get(fn () => $this->townshipFieldQty('kyankhin', 'g1_g5_qty'));
    }

    protected function g1G5IngapuQty(): Attribute
    {
        return Attribute::get(fn () => $this->townshipFieldQty('ingapu', 'g1_g5_qty'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function syncTownshipQtys(array $data): void
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

            $this->townshipAllocations()->updateOrCreate(
                ['township_id' => $township->id],
                [
                    'kg_g12_qty' => (int) ($data["kg_g12_{$slug}_qty"] ?? 0),
                    'g1_g5_qty' => (int) ($data["g1_g5_{$slug}_qty"] ?? 0),
                ]
            );
        }

        $this->unsetRelation('townshipAllocations');
    }

    protected function townshipFieldQty(string $slug, string $field): int
    {
        $allocation = $this->townshipAllocationFor($slug);

        return (int) ($allocation?->{$field} ?? 0);
    }

    protected function townshipAllocationFor(string $slug): ?TeacherGuideTownshipAllocation
    {
        if (!$this->relationLoaded('townshipAllocations')) {
            $this->load('townshipAllocations.township');
        }

        $name = TownshipKeys::slugToName($slug);

        return $this->townshipAllocations->first(
            fn (TeacherGuideTownshipAllocation $row) => $row->township?->name === $name
        );
    }
}
