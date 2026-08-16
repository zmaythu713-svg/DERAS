<?php

namespace App\Http\Controllers;

use App\Models\AllocationPlan;
use App\Models\BookName;
use App\Models\Category;
use App\Models\Grade;
use App\Models\PreviousYearBalance;
use App\Models\SchoolCount;
use App\Models\SchoolSupplyAllocation;
use App\Models\SchoolSupplyItem;
use App\Models\Stock;
use App\Models\SupplyItem;
use App\Models\TeacherGuide;
use App\Models\Township;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceLookupController extends Controller
{
    /**
     * Subjects for a grade, optionally filtered by category.
     */
    public function subjects(Request $request, Grade $grade): JsonResponse
    {
        $query = $grade->bookNames()
            ->where('book_names.is_active', true)
            ->orderBy('book_names.name');

        if ($request->filled('category_id')) {
            $query->wherePivot('category_id', $request->category_id);
        } elseif ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->wherePivot('category_id', $category->id);
            }
        }

        $subjects = $query->get(['book_names.id', 'book_names.name']);

        return response()->json($subjects);
    }

    /**
     * Auto-fill တစ်အိတ်ပါယူနစ် + ထုတ်ပေးသည့်အုပ်ရေ from ခွဲတမ်းတွက်ချက်မှု.
     */
    public function allocationForTextbook(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'township_id' => 'required|exists:townships,id',
            'grade_id' => 'required|exists:grades,id',
            'book_name_id' => 'required|exists:book_names,id',
        ]);

        $plan = AllocationPlan::with('townships.township')
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('grade_id', $data['grade_id'])
            ->where('book_name_id', $data['book_name_id'])
            ->first();

        if (!$plan) {
            return response()->json([
                'found' => false,
                'books_per_set' => null,
                'student_count' => null,
            ]);
        }

        $township = Township::findOrFail($data['township_id']);
        $key = match ($township->name) {
            'မြန်အောင်' => 'myanaung',
            'ကြံခင်း' => 'kyankhin',
            'အင်္ဂပူ' => 'ingapu',
            default => null,
        };

        $issued = $key ? max(0, $plan->townshipComputedValue($key, 'allocation')) : 0;

        return response()->json([
            'found' => true,
            'books_per_set' => (int) $plan->books_per_package,
            'student_count' => $issued,
        ]);
    }

    /**
     * Previous year balance (ယခင်နှစ်လက်ကျန်) for ခွဲတမ်းတွက်ချက်မှု Auto-fill.
     * Reads from previous_year_balances (seeded / rollover carry-forward).
     */
    public function previousYearBalance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'township_id' => 'nullable|exists:townships,id',
            'grade_id' => 'required|exists:grades,id',
            'book_name_id' => 'required|exists:book_names,id',
        ]);

        $query = PreviousYearBalance::query()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('grade_id', $data['grade_id'])
            ->where('book_name_id', $data['book_name_id']);

        if (!empty($data['township_id'])) {
            $row = (clone $query)->where('township_id', $data['township_id'])->first();

            if ($row) {
                return response()->json([
                    'found' => true,
                    'previous_balance' => (int) $row->balance,
                ]);
            }

            // Fallback: existing stock row for same keys
            $stock = Stock::query()
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('township_id', $data['township_id'])
                ->where('grade_id', $data['grade_id'])
                ->where('book_name_id', $data['book_name_id'])
                ->first();

            if ($stock) {
                return response()->json([
                    'found' => true,
                    'previous_balance' => (int) ($stock->previous_balance ?? 0),
                ]);
            }

            // Fallback: prior academic year's stock leftover as carry-in
            $year = \App\Models\AcademicYear::find($data['academic_year_id']);
            $prevYear = $year
                ? \App\Models\AcademicYear::query()
                    ->where('start_year', ((int) $year->start_year) - 1)
                    ->orderByDesc('id')
                    ->first()
                : null;

            if ($prevYear) {
                $prevStock = Stock::query()
                    ->where('academic_year_id', $prevYear->id)
                    ->where('township_id', $data['township_id'])
                    ->where('grade_id', $data['grade_id'])
                    ->where('book_name_id', $data['book_name_id'])
                    ->first();

                if ($prevStock) {
                    // Carry remaining need as next previous balance when positive
                    $carry = max(
                        0,
                        (int) ($prevStock->previous_balance ?? 0)
                            + (int) ($prevStock->transferred ?? 0)
                            - (int) ($prevStock->enrolled_need ?? 0)
                    );
                    // Prefer explicit previous_balance if leftover formula is 0 but balance existed
                    $balance = $carry > 0
                        ? $carry
                        : (int) ($prevStock->required_qty ?? $prevStock->previous_balance ?? 0);

                    return response()->json([
                        'found' => true,
                        'previous_balance' => $balance,
                    ]);
                }
            }

            return response()->json([
                'found' => false,
                'previous_balance' => 0,
            ]);
        }

        $totals = [
            'myanaung' => 0,
            'kyankhin' => 0,
            'ingapu' => 0,
        ];

        $map = [
            'မြန်အောင်' => 'myanaung',
            'ကြံခင်း' => 'kyankhin',
            'အင်္ဂပူ' => 'ingapu',
        ];

        $rows = $query->with('township')->get();

        foreach ($rows as $row) {
            $key = $map[$row->township?->name] ?? null;
            if ($key) {
                $totals[$key] = (int) $row->balance;
            }
        }

        // Fallback to stocks.previous_balance if dedicated table empty
        if ($rows->isEmpty()) {
            $stocks = Stock::with('township')
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('grade_id', $data['grade_id'])
                ->where('book_name_id', $data['book_name_id'])
                ->get();

            foreach ($stocks as $stock) {
                $key = $map[$stock->township?->name] ?? null;
                if ($key) {
                    $totals[$key] = (int) $stock->previous_balance;
                }
            }

            return response()->json([
                'found' => $stocks->isNotEmpty(),
                'previous_balance' => $totals,
            ]);
        }

        return response()->json([
            'found' => true,
            'previous_balance' => $totals,
        ]);
    }

    /**
     * School count (ကျောင်းအရေအတွက်) — auto-fill from school_counts seeder.
     * Empty township_id = ခရိုင်စုစုပေါင်း.
     */
    public function schoolCount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'township_id' => 'nullable|exists:townships,id',
            'grade_id' => 'required|exists:grades,id',
        ]);

        $query = SchoolCount::query()->where('grade_id', $data['grade_id']);

        if (!empty($data['academic_year_id'])) {
            $query->where('academic_year_id', $data['academic_year_id']);
        }

        if (!empty($data['township_id'])) {
            $query->where('township_id', $data['township_id']);
        } else {
            $query->whereNull('township_id');
        }

        $row = $query->orderByDesc('id')->first();

        // Fallback: older allocation rows (before dedicated school_counts table)
        if (!$row) {
            $fallback = SchoolSupplyAllocation::query()
                ->where('grade_id', $data['grade_id'])
                ->when(
                    !empty($data['academic_year_id']),
                    fn ($q) => $q->where('academic_year_id', $data['academic_year_id'])
                )
                ->when(
                    !empty($data['township_id']),
                    fn ($q) => $q->where('township_id', $data['township_id'])->where('row_type', 'township'),
                    fn ($q) => $q->where(function ($inner) {
                        $inner->where('row_type', 'total')
                            ->orWhereIn('row_label', Township::EXCLUDED_NAMES);
                    })
                )
                ->orderByDesc('id')
                ->first();

            return response()->json([
                'found' => (bool) $fallback,
                'school_count' => $fallback?->school_count ?? 0,
            ]);
        }

        return response()->json([
            'found' => true,
            'school_count' => $row->school_count ?? 0,
        ]);
    }

    /**
     * ထုတ်ပေးမှု ဦးရေပေါင်း ← ခွဲတမ်းတွက်ချက်မှု အရေအတွက်
     */
    public function schoolSupplyQuantity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'township_id' => 'required|exists:townships,id',
            'grade_id' => 'required|exists:grades,id',
            'supply_item_id' => 'required|exists:supply_items,id',
        ]);

        $supplyItem = SupplyItem::find($data['supply_item_id']);
        if (!$supplyItem) {
            return response()->json(['found' => false, 'quantity' => 0]);
        }

        $schoolItemIds = $this->matchingSchoolSupplyItemIds($supplyItem->name);
        if ($schoolItemIds->isEmpty()) {
            return response()->json(['found' => false, 'quantity' => 0]);
        }

        $base = SchoolSupplyAllocation::query()
            ->where('grade_id', $data['grade_id'])
            ->where('township_id', $data['township_id'])
            ->whereIn('school_supply_item_id', $schoolItemIds)
            ->where('row_type', 'township');

        $row = (clone $base)
            ->when(
                !empty($data['academic_year_id']),
                fn ($q) => $q->where('academic_year_id', $data['academic_year_id'])
            )
            ->orderByDesc('id')
            ->first();

        // Same township/grade/item in another year (e.g. quota seeded for 2024-2025)
        if (!$row) {
            $row = $base->orderByDesc('id')->first();
        }

        return response()->json([
            'found' => (bool) $row,
            'quantity' => (int) ($row?->quantity ?? 0),
            'rate' => (int) ($row?->item?->rate ?? 0),
            'school_count' => (int) ($row?->school_count ?? 0),
        ]);
    }

    /**
     * Match supply_items ↔ school_supply_items by normalized name.
     */
    private function matchingSchoolSupplyItemIds(string $supplyItemName)
    {
        $target = $this->normalizeItemName($supplyItemName);

        return SchoolSupplyItem::query()
            ->get(['id', 'name'])
            ->filter(fn ($item) => $this->normalizeItemName($item->name) === $target)
            ->pluck('id')
            ->values();
    }

    private function normalizeItemName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/u', '', $name) ?? $name;
        $name = str_replace(['(', ')', '（', '）', '၊', ',', '.', '-', '–', '/', '\\'], '', $name);

        return $name;
    }

    /**
     * ဖြန့်ဝေရန်ခွဲတမ်း ← လက်ခံရရှိမှု (teacher_guides) district quotas.
     */
    public function teacherGuideReceipt(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_id' => 'required|exists:grades,id',
            'book_name_id' => 'required|exists:book_names,id',
            'guide_type' => 'required|in:ဆရာကိုင်,ဆရာလမ်းညွှန်',
        ]);

        $row = TeacherGuide::query()
            ->with('townshipAllocations.township')
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('grade_id', $data['grade_id'])
            ->where('book_name_id', $data['book_name_id'])
            ->where('guide_type', $data['guide_type'])
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            return response()->json([
                'found' => false,
                'kg_to_g12_quota' => 0,
                'g1_to_g5_quota' => 0,
                'total_quota' => 0,
                'remaining_total' => 0,
                'distributed_total' => 0,
                'township_issued' => [
                    'မြန်အောင်' => 0,
                    'ကြံခင်း' => 0,
                    'အင်္ဂပူ' => 0,
                ],
            ]);
        }

        $kg = (int) ($row->kg_to_g12_quota ?? 0);
        $g1 = (int) ($row->g1_to_g5_quota ?? 0);

        return response()->json([
            'found' => true,
            'id' => $row->id,
            'kg_to_g12_quota' => $kg,
            'g1_to_g5_quota' => $g1,
            'total_quota' => (int) ($row->total_quota ?? ($kg + $g1)),
            'remaining_total' => (int) ($row->remaining_total ?? 0),
            'distributed_total' => (int) ($row->distributed_total ?? 0),
            'kg_g12_myanaung_qty' => (int) ($row->kg_g12_myanaung_qty ?? 0),
            'kg_g12_kyankhin_qty' => (int) ($row->kg_g12_kyankhin_qty ?? 0),
            'kg_g12_ingapu_qty' => (int) ($row->kg_g12_ingapu_qty ?? 0),
            'g1_g5_myanaung_qty' => (int) ($row->g1_g5_myanaung_qty ?? 0),
            'g1_g5_kyankhin_qty' => (int) ($row->g1_g5_kyankhin_qty ?? 0),
            'g1_g5_ingapu_qty' => (int) ($row->g1_g5_ingapu_qty ?? 0),
            'total_myanaung_qty' => (int) ($row->total_myanaung_qty ?? 0),
            'total_kyankhin_qty' => (int) ($row->total_kyankhin_qty ?? 0),
            'total_ingapu_qty' => (int) ($row->total_ingapu_qty ?? 0),
            'township_issued' => [
                'မြန်အောင်' => (int) ($row->total_myanaung_qty ?? 0),
                'ကြံခင်း' => (int) ($row->total_kyankhin_qty ?? 0),
                'အင်္ဂပူ' => (int) ($row->total_ingapu_qty ?? 0),
            ],
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'slug', 'name_en', 'name_mm']);

        return response()->json($categories);
    }
}
