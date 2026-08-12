<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolSupplyAllocation;
use App\Models\SchoolSupplyItem;
use App\Models\SupplyDetail;
use App\Models\SupplyItem;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplyDetailController extends Controller
{
    public function index(Request $request)
    {
        $years = AcademicYear::where('is_active', true)
            ->orderBy('start_year')
            ->orderBy('name')
            ->get();

        $currentYear = AcademicYear::current()->first();
        $yearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : $currentYear?->id;

        $townshipId = $request->township_id;
        $gradeId = $request->grade_id;

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = SupplyDetail::with([
            'academicYear',
            'township',
            'grade',
            'item',
        ]);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('township_id')) {
            $query->where('township_id', $townshipId);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $gradeId);
        }

        if ($selectedYear?->isFuture()) {
            $details = collect();
        } else {
            $details = $query
                ->orderBy('township_id')
                ->orderBy('grade_id')
                ->orderBy('sequence_no')
                ->get();
        }

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('supply-details.index', [
            'details' => $details,
            'years' => $years,
            'townships' => Township::dropdownOptions(),
            'grades' => Grade::dropdownOptions(),
            'yearId' => $yearId,
            'townshipId' => $townshipId,
            'gradeId' => $gradeId,
            'selectedYear' => $selectedYear,
            'currentYear' => $currentYear,
            'canCreate' => $canCreate,
            'emptyMessage' => $emptyMessage,
        ]);
    }

    public function create(Request $request)
    {
        $data = $this->formData();

        if ($request->filled('academic_year_id')) {
            $year = AcademicYear::find($request->academic_year_id);
            if ($year && !$year->allowsDataEntry()) {
                return redirect()
                    ->route('supply-details.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        if ($request->filled('township_id')) {
            $data['preselectedTownshipId'] = $request->township_id;
        }

        if ($request->filled('grade_id')) {
            $data['preselectedGradeId'] = $request->grade_id;
        }

        return view('supply-details.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request);
        $data['sequence_no'] = SupplyDetail::max('sequence_no') + 1;

        SupplyDetail::create($data);

        return redirect()
            ->route('supply-details.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'township_id' => $request->township_id,
                'grade_id' => $request->grade_id,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ.');
    }

    public function edit(SupplyDetail $supplyDetail)
    {
        return view('supply-details.edit', $this->formData() + [
            'supplyDetail' => $supplyDetail,
        ]);
    }

    public function update(Request $request, SupplyDetail $supplyDetail)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $supplyDetail->update($this->validatedData($request));

        return redirect()
            ->route('supply-details.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'township_id' => $request->township_id,
                'grade_id' => $request->grade_id,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ.');
    }

    public function destroy(SupplyDetail $supplyDetail)
    {
        $yearId = $supplyDetail->academic_year_id;
        $townshipId = $supplyDetail->township_id;
        $gradeId = $supplyDetail->grade_id;
        $supplyDetail->delete();

        return redirect()
            ->route('supply-details.index', array_filter([
                'academic_year_id' => $yearId,
                'township_id' => $townshipId,
                'grade_id' => $gradeId,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာဖျက်ပြီးပါပြီ.');
    }

    private function formData(): array
    {
        $years = AcademicYear::where('is_active', true)
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get()
            ->filter(fn (AcademicYear $year) => $year->allowsDataEntry())
            ->values();

        return [
            'years' => $years,
            'currentYearId' => AcademicYear::current()->value('id'),
            'townships' => Township::dropdownOptions(),
            'grades' => Grade::dropdownOptions(),
            'items' => SupplyItem::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function assertYearAllowsDataEntry(mixed $academicYearId): void
    {
        $year = AcademicYear::find($academicYearId);
        if (!$year || !$year->allowsDataEntry()) {
            throw ValidationException::withMessages([
                'academic_year_id' => 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ',
            ]);
        }
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'township_id' => 'required|exists:townships,id',
            'grade_id' => 'required|exists:grades,id',
            'supply_item_id' => 'required|exists:supply_items,id',
            'sequence_no' => 'nullable|integer|min:1',
            'unit' => 'required|integer|min:1',
            'issued_total' => 'nullable|integer|min:0',
            'package_count' => 'nullable|integer|min:0',
            'loose_count' => 'nullable|integer|min:0',
            'remark' => 'nullable|string|max:255',
        ]);

        $quotaQty = $this->quotaQuantityFor(
            $data['academic_year_id'] ?? null,
            $data['township_id'],
            $data['grade_id'],
            $data['supply_item_id']
        );
        if ($quotaQty !== null) {
            $data['issued_total'] = $quotaQty;
        }

        $unit = (int) ($data['unit'] ?? 0);
        $issued = (int) ($data['issued_total'] ?? 0);
        if ($unit > 0) {
            $data['package_count'] = intdiv($issued, $unit);
            $data['loose_count'] = $issued % $unit;
        }

        return $data;
    }

    private function quotaQuantityFor($yearId, $townshipId, $gradeId, $supplyItemId): ?int
    {
        $supplyItem = SupplyItem::find($supplyItemId);
        if (!$supplyItem) {
            return null;
        }

        $target = $this->normalizeItemName($supplyItem->name);
        $schoolItemIds = SchoolSupplyItem::query()
            ->get(['id', 'name'])
            ->filter(fn ($item) => $this->normalizeItemName($item->name) === $target)
            ->pluck('id');

        if ($schoolItemIds->isEmpty()) {
            return null;
        }

        $base = SchoolSupplyAllocation::query()
            ->where('grade_id', $gradeId)
            ->where('township_id', $townshipId)
            ->whereIn('school_supply_item_id', $schoolItemIds)
            ->where('row_type', 'township');

        $row = (clone $base)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->orderByDesc('id')
            ->first()
            ?? $base->orderByDesc('id')->first();

        return $row ? (int) $row->quantity : null;
    }

    private function normalizeItemName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/u', '', $name) ?? $name;
        $name = str_replace(['(', ')', '（', '）', '၊', ',', '.', '-', '–', '/', '\\'], '', $name);

        return $name;
    }
}
