<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Quota extends Model
{
    protected $fillable = [
        'academic_year_id',
        'township_id',
    ];

    public const LINE_FIELDS = [
        'primary_public' => ['primary', 'public'],
        'primary_monk' => ['primary', 'monk'],
        'primary_private' => ['primary', 'private'],
        'middle_public' => ['middle', 'public'],
        'middle_monk' => ['middle', 'monk'],
        'middle_private' => ['middle', 'private'],
        'high_public' => ['high', 'public'],
        'high_monk' => ['high', 'monk'],
        'high_private' => ['high', 'private'],
        'agriculture' => ['agriculture', ''],
    ];

    protected static function booted(): void
    {
        static::saved(function (Quota $quota) {
            if (!empty($quota->pendingLineValues)) {
                $quota->syncLines($quota->pendingLineValues);
                $quota->pendingLineValues = null;
            }
        });
    }

    /** @var array<string,int>|null */
    public ?array $pendingLineValues = null;

    public function lines(): HasMany
    {
        return $this->hasMany(QuotaLine::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function township(): BelongsTo
    {
        return $this->belongsTo(Township::class);
    }

    public function syncLines(array $values): void
    {
        $now = now();
        $rows = [];

        foreach (self::LINE_FIELDS as $field => [$level, $ownership]) {
            $rows[] = [
                'quota_id' => $this->id,
                'school_level' => $level,
                'ownership' => $ownership,
                'quantity' => (int) ($values[$field] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('quota_lines')->where('quota_id', $this->id)->delete();
        DB::table('quota_lines')->insert($rows);
        $this->unsetRelation('lines');
    }

    public static function createWithLines(array $header, array $lineValues): self
    {
        $quota = static::create($header);
        $quota->syncLines($lineValues);

        return $quota->fresh(['lines', 'academicYear', 'township']);
    }

    public function updateWithLines(array $header, array $lineValues): self
    {
        $this->update($header);
        $this->syncLines($lineValues);

        return $this->fresh(['lines', 'academicYear', 'township']);
    }

    private function lineQty(string $level, ?string $ownership): int
    {
        if ($this->relationLoaded('lines')) {
            $line = $this->lines->first(
                fn (QuotaLine $l) => $l->school_level === $level
                    && (string) $l->ownership === (string) ($ownership ?? '')
            );

            return (int) ($line?->quantity ?? 0);
        }

        return (int) $this->lines()
            ->where('school_level', $level)
            ->where('ownership', $ownership ?? '')
            ->value('quantity');
    }

    protected function primaryPublic(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('primary', 'public'));
    }

    protected function primaryMonk(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('primary', 'monk'));
    }

    protected function primaryPrivate(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('primary', 'private'));
    }

    protected function middlePublic(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('middle', 'public'));
    }

    protected function middleMonk(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('middle', 'monk'));
    }

    protected function middlePrivate(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('middle', 'private'));
    }

    protected function highPublic(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('high', 'public'));
    }

    protected function highMonk(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('high', 'monk'));
    }

    protected function highPrivate(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('high', 'private'));
    }

    protected function agriculture(): Attribute
    {
        return Attribute::get(fn () => $this->lineQty('agriculture', ''));
    }

    protected function primaryTotal(): Attribute
    {
        return Attribute::get(fn () => $this->primary_public + $this->primary_monk + $this->primary_private);
    }

    protected function middleTotal(): Attribute
    {
        return Attribute::get(fn () => $this->middle_public + $this->middle_monk + $this->middle_private);
    }

    protected function highTotal(): Attribute
    {
        return Attribute::get(fn () => $this->high_public + $this->high_monk + $this->high_private);
    }

    protected function grandPublic(): Attribute
    {
        return Attribute::get(fn () => $this->primary_public + $this->middle_public + $this->high_public);
    }

    protected function grandMonk(): Attribute
    {
        return Attribute::get(fn () => $this->primary_monk + $this->middle_monk + $this->high_monk);
    }

    protected function grandPrivate(): Attribute
    {
        return Attribute::get(fn () => $this->primary_private + $this->middle_private + $this->high_private);
    }

    protected function grandTotal(): Attribute
    {
        return Attribute::get(fn () => $this->grand_public + $this->grand_monk + $this->grand_private);
    }

    protected function totalWithAgriculture(): Attribute
    {
        return Attribute::get(fn () => $this->grand_total + $this->agriculture);
    }

    protected function distributionTotal(): Attribute
    {
        return Attribute::get(fn () => $this->total_with_agriculture);
    }
}
