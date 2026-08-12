<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolSupplyAllocation;
use App\Models\SchoolSupplyItem;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SchoolSupplyController extends Controller
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

        $gradeId = $request->grade_id;
        $townshipId = $request->input('township_id', '');

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = SchoolSupplyAllocation::with([
            'academicYear',
            'grade',
            'township',
            'item',
        ]);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $gradeId);
        }

        if ($townshipId !== '' && $townshipId !== null) {
            $query->where('township_id', $townshipId)
                ->where('row_type', 'township')
                ->whereNotIn('row_label', Township::EXCLUDED_NAMES);
        } else {
            // မြို့နယ်အားလုံး → ခရိုင်စုစုပေါင်း only
            $query->where(function ($q) {
                $q->where('row_type', 'total')
                    ->orWhereIn('row_label', Township::EXCLUDED_NAMES)
                    ->orWhere('row_label', 'like', '%ခရိုင်%စုစုပေါင်း%');
            });
        }

        if ($selectedYear?->isFuture()) {
            $allocations = collect();
        } else {
            $allocations = $query
                ->orderBy('grade_id')
                ->orderBy('row_type')
                ->orderBy('row_label')
                ->orderBy('school_supply_item_id')
                ->get();
        }

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('school-supplies.index', [
            'allocations' => $allocations,
            'years' => $years,
            'grades' => Grade::dropdownOptions(),
            'townships' => Township::dropdownOptions(),
            'items' => SchoolSupplyItem::dropdownOptions(),
            'yearId' => $yearId,
            'gradeId' => $gradeId,
            'townshipId' => $townshipId,
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
                    ->route('school-supplies.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        if ($request->filled('grade_id')) {
            $data['preselectedGradeId'] = $request->grade_id;
        }

        if ($request->filled('township_id')) {
            $data['preselectedTownshipId'] = $request->township_id;
        }

        return view('school-supplies.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        SchoolSupplyAllocation::create($this->validatedData($request));

        return redirect()
            ->route('school-supplies.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'grade_id' => $request->grade_id,
                'township_id' => $request->township_id,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ.');
    }

    public function edit(SchoolSupplyAllocation $schoolSupply)
    {
        $schoolSupply->load('item');

        return view('school-supplies.edit', $this->formData() + [
            'schoolSupply' => $schoolSupply,
        ]);
    }

    public function update(Request $request, SchoolSupplyAllocation $schoolSupply)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $schoolSupply->update($this->validatedData($request));

        return redirect()
            ->route('school-supplies.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'grade_id' => $request->grade_id,
                'township_id' => $request->township_id,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ.');
    }

    public function destroy(SchoolSupplyAllocation $schoolSupply)
    {
        $yearId = $schoolSupply->academic_year_id;
        $gradeId = $schoolSupply->grade_id;
        $townshipId = $schoolSupply->township_id;
        $schoolSupply->delete();

        return redirect()
            ->route('school-supplies.index', array_filter([
                'academic_year_id' => $yearId,
                'grade_id' => $gradeId,
                'township_id' => $townshipId,
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
            'grades' => Grade::dropdownOptions(),
            'townships' => Township::dropdownOptions(),
            'items' => SchoolSupplyItem::dropdownOptions(),
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
            'grade_id' => 'required|exists:grades,id',
            'township_id' => 'nullable|exists:townships,id',
            'school_supply_item_id' => 'required|exists:school_supply_items,id',

            'region' => 'nullable|string|max:255',
            'row_type' => 'nullable|in:township,total,box,loose',
            'row_label' => 'nullable|string|max:255',
            'school_count' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
            'remark' => 'nullable|string|max:255',
        ]);

        $item = SchoolSupplyItem::find($data['school_supply_item_id']);
        $schoolCount = (int) ($data['school_count'] ?? 0);
        $rate = (int) ($item?->rate ?? 0);

        $data['quantity'] = $rate * $schoolCount;

        if (empty($data['row_type'])) {
            $data['row_type'] = !empty($data['township_id']) ? 'township' : 'total';
        }

        if (empty($data['row_label']) && !empty($data['township_id'])) {
            $data['row_label'] = Township::find($data['township_id'])?->name;
        } elseif (empty($data['row_label']) && empty($data['township_id'])) {
            $data['row_label'] = 'ခရိုင်စုစုပေါင်း';
        }

        return $data;
    }
}
