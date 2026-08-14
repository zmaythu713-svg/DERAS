<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AllocationPlan;
use App\Models\PreviousYearBalance;
use App\Models\Township;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AcademicYearRollover
{
    private const TOWNSHIP_KEYS = [
        'မြန်အောင်' => 'myanaung',
        'ကြံခင်း' => 'kyankhin',
        'အင်္ဂပူ' => 'ingapu',
    ];

    /**
     * Close current year and open the next year, carrying previous balances.
     *
     * Carry source priority:
     * 1) Closed year's allocation plan leftover (previous + allocation surplus path via detail previous fields for next seed)
     * 2) Closed year's previous_year_balances (if no allocation leftover)
     *
     * For textbooks allocation form, leftover used as next year's ယခင်နှစ်လက်ကျန်
     * is taken from closed year allocation_plan_details.*_previous when present,
     * otherwise from positive *_difference (surplus), else 0.
     */
    public function rollover(?AcademicYear $from = null, ?int $toStartYear = null): AcademicYear
    {
        return DB::transaction(function () use ($from, $toStartYear) {
            $from = $from ?: AcademicYear::current()->first();

            if (!$from) {
                throw new InvalidArgumentException('လက်ရှိ ပညာသင်နှစ် မတွေ့ပါ။');
            }

            if ($from->isClosed()) {
                throw new InvalidArgumentException('ဤပညာသင်နှစ်ကို ပိတ်ပြီးသားဖြစ်ပါသည်။');
            }

            $startYear = $toStartYear ?: ((int) ($from->start_year ?: $this->parseStart($from->name)) + 1);
            $newName = AcademicYear::makeName($startYear);

            if (AcademicYear::where('name', $newName)->where('status', AcademicYear::STATUS_ACTIVE)->exists()) {
                $existing = AcademicYear::where('name', $newName)->first();
                if ($existing && $existing->is_current) {
                    throw new InvalidArgumentException("{$newName} သည် လက်ရှိနှစ်အဖြစ် ရှိပြီးသားဖြစ်ပါသည်။");
                }
            }

            $to = AcademicYear::updateOrCreate(
                ['name' => $newName],
                [
                    'start_year' => $startYear,
                    'end_year' => $startYear + 1,
                    'is_active' => true,
                    'is_current' => false,
                    'status' => AcademicYear::STATUS_ACTIVE,
                ]
            );

            $this->carryBalances($from, $to);

            AcademicYear::query()->update(['is_current' => false]);

            $from->update([
                'is_current' => false,
                'status' => AcademicYear::STATUS_CLOSED,
            ]);

            $to->update([
                'is_current' => true,
                'status' => AcademicYear::STATUS_ACTIVE,
                'is_active' => true,
            ]);

            return $to->fresh();
        });
    }

    private function carryBalances(AcademicYear $from, AcademicYear $to): void
    {
        $townshipMap = Township::whereIn('name', array_keys(self::TOWNSHIP_KEYS))
            ->get()
            ->keyBy('name');

        $plans = AllocationPlan::with(['townships.township'])
            ->where('academic_year_id', $from->id)
            ->get();

        if ($plans->isNotEmpty()) {
            foreach ($plans as $plan) {
                $compat = $plan->detailCompat();
                if (!$compat) {
                    continue;
                }

                foreach (self::TOWNSHIP_KEYS as $townshipName => $key) {
                    $township = $townshipMap->get($townshipName);
                    if (!$township) {
                        continue;
                    }

                    // Ending leftover for next year:
                    // prefer positive difference (surplus), else keep previous remaining stock concept.
                    $difference = (int) ($compat->{"{$key}_difference"} ?? 0);
                    $previous = (int) ($compat->{"{$key}_previous"} ?? 0);
                    $balance = $difference > 0 ? $difference : max(0, $previous);

                    PreviousYearBalance::updateOrCreate(
                        [
                            'academic_year_id' => $to->id,
                            'township_id' => $township->id,
                            'grade_id' => $plan->grade_id,
                            'book_name_id' => $plan->book_name_id,
                        ],
                        ['balance' => $balance]
                    );
                }
            }

            return;
        }

        // Fallback: copy previous_year_balances rows as-is into next year
        PreviousYearBalance::where('academic_year_id', $from->id)
            ->get()
            ->each(function (PreviousYearBalance $row) use ($to) {
                PreviousYearBalance::updateOrCreate(
                    [
                        'academic_year_id' => $to->id,
                        'township_id' => $row->township_id,
                        'grade_id' => $row->grade_id,
                        'book_name_id' => $row->book_name_id,
                    ],
                    ['balance' => $row->balance]
                );
            });
    }

    private function parseStart(string $name): int
    {
        if (preg_match('/^(\d{4})/', $name, $m)) {
            return (int) $m[1];
        }

        return (int) date('Y');
    }
}
