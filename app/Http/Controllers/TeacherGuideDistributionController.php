<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\TeacherGuide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherGuideDistributionController extends Controller
{
    public function index(Request $request): View
    {
        $years = AcademicYear::query()
            ->where('is_active', true)
            ->orderBy('start_year')
            ->orderBy('name')
            ->get();

        $currentYear = AcademicYear::current()->first();
        $yearId = $request->filled('academic_year_id')
            ? $request->integer('academic_year_id')
            : $currentYear?->id;

        $gradeId = $request->integer('grade_id') ?: null;
        $guideType = $request->input('guide_type');
        $search = trim((string) $request->input('search', ''));

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = TeacherGuide::query()
            ->with([
                'academicYear',
                'grade',
                'bookName',
                'townshipAllocations.township',
            ])
            ->whereHas('townshipAllocations');

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($guideType) {
            $query->where('guide_type', $guideType);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    'bookName',
                    fn ($bookQuery) => $bookQuery->where('name', 'like', "%{$search}%")
                )->orWhere('group_title', 'like', "%{$search}%");
            });
        }

        if ($selectedYear?->isFuture()) {
            $query->whereRaw('0 = 1');
        }

        $teacherGuides = $query
            ->orderBy('group_no')
            ->orderBy('sequence_no')
            ->paginate(config('deras.pagination_per_page'))
            ->withQueryString();

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        $grades = Grade::dropdownOptions();

        return view('teacher-guide-distributions.index', [
            'teacherGuides' => $teacherGuides,
            'years' => $years,
            'grades' => $grades,
            'yearId' => $yearId,
            'gradeId' => $gradeId,
            'guideType' => $guideType,
            'search' => $search,
            'selectedYear' => $selectedYear,
            'currentYear' => $currentYear,
            'canCreate' => $canCreate,
            'emptyMessage' => $emptyMessage,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->filled('academic_year_id')) {
            $year = AcademicYear::find($request->academic_year_id);
            if ($year && !$year->allowsDataEntry()) {
                return redirect()
                    ->route('teacher-guide-distributions.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
        }

        $years = AcademicYear::where('is_active', true)
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get()
            ->filter(fn (AcademicYear $year) => $year->allowsDataEntry())
            ->values();

        return view('teacher-guide-distributions.create', [
            'years' => $years,
            'currentYearId' => AcademicYear::current()->value('id'),
            'preselectedYearId' => $request->academic_year_id,
            'preselectedGradeId' => $request->grade_id,
            'preselectedGuideType' => $request->guide_type,
            'grades' => Grade::dropdownOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $year = AcademicYear::find($request->input('academic_year_id'));
        if (!$year || !$year->allowsDataEntry()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'academic_year_id' => 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ',
            ]);
        }

        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_id' => 'required|exists:grades,id',
            'book_name_id' => 'required|exists:book_names,id',

            'group_no' => 'nullable|integer|min:1',
            'group_title' => 'nullable|string|max:1000',
            'guide_type' => 'required|in:ဆရာကိုင်,ဆရာလမ်းညွှန်',
            'sequence_no' => 'nullable|integer|min:1',

            'kg_to_g12_quota' => 'nullable|integer|min:0',
            'g1_to_g5_quota' => 'nullable|integer|min:0',

            'kg_g12_myanaung_qty' => 'nullable|integer|min:0',
            'kg_g12_kyankhin_qty' => 'nullable|integer|min:0',
            'kg_g12_ingapu_qty' => 'nullable|integer|min:0',

            'g1_g5_myanaung_qty' => 'nullable|integer|min:0',
            'g1_g5_kyankhin_qty' => 'nullable|integer|min:0',
            'g1_g5_ingapu_qty' => 'nullable|integer|min:0',

            'remark' => 'nullable|string|max:1000',
        ]);

        // Prefer district quotas already saved in လက်ခံရရှိမှု
        $receipt = TeacherGuide::query()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('grade_id', $data['grade_id'])
            ->where('book_name_id', $data['book_name_id'])
            ->where('guide_type', $data['guide_type'])
            ->orderByDesc('id')
            ->first();

        if ($receipt) {
            $data['kg_to_g12_quota'] = (int) ($receipt->kg_to_g12_quota ?? 0);
            $data['g1_to_g5_quota'] = (int) ($receipt->g1_to_g5_quota ?? 0);
            $data['group_no'] = $receipt->group_no;
            $data['sequence_no'] = $receipt->sequence_no;
            $data['group_title'] = $receipt->group_title;
        } else {
            $data['group_no'] = (TeacherGuide::max('group_no') ?? 0) + 1;
            $data['sequence_no'] = (TeacherGuide::where('grade_id', $data['grade_id'])
                ->where('guide_type', $data['guide_type'])
                ->max('sequence_no') ?? 0) + 1;

            $gradeName = Grade::where('id', $data['grade_id'])->value('name');
            $data['group_title'] = $gradeName . "\n(" . $data['guide_type'] . ')';
        }

        $townshipQtys = $this->extractTownshipQtys($data);
        unset(
            $data['kg_g12_myanaung_qty'],
            $data['kg_g12_kyankhin_qty'],
            $data['kg_g12_ingapu_qty'],
            $data['g1_g5_myanaung_qty'],
            $data['g1_g5_kyankhin_qty'],
            $data['g1_g5_ingapu_qty']
        );

        $guide = TeacherGuide::updateOrCreate(
            [
                'academic_year_id' => $data['academic_year_id'],
                'grade_id' => $data['grade_id'],
                'book_name_id' => $data['book_name_id'],
                'guide_type' => $data['guide_type'],
            ],
            $data
        );

        $guide->syncTownshipQtys($townshipQtys);

        return redirect()
            ->route('teacher-guide-distributions.index')
            ->with('success', 'ဖြန့်ဝေရန်ခွဲတမ်းအသစ်ဖန်တီးပြီးပါပြီ');
    }

    public function edit(TeacherGuide $teacherGuideDistribution): View
    {
        $teacherGuideDistribution->load([
            'academicYear',
            'grade',
            'bookName',
            'townshipAllocations.township',
        ]);

        $years = AcademicYear::where('is_active', true)
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get()
            ->filter(fn (AcademicYear $year) => $year->allowsDataEntry()
                || (int) $year->id === (int) $teacherGuideDistribution->academic_year_id)
            ->values();

        return view('teacher-guide-distributions.edit', [
            'teacherGuide' => $teacherGuideDistribution,
            'years' => $years,
            'currentYearId' => AcademicYear::current()->value('id'),
            'grades' => Grade::dropdownOptions(),
        ]);
    }

    public function update(
        Request $request,
        TeacherGuide $teacherGuideDistribution
    ): RedirectResponse {
        $year = AcademicYear::find($request->input('academic_year_id'));
        if (!$year || !$year->allowsDataEntry()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'academic_year_id' => 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ',
            ]);
        }

        $data = $this->validatedData($request);
        $townshipQtys = $this->extractTownshipQtys($data);
        unset(
            $data['kg_g12_myanaung_qty'],
            $data['kg_g12_kyankhin_qty'],
            $data['kg_g12_ingapu_qty'],
            $data['g1_g5_myanaung_qty'],
            $data['g1_g5_kyankhin_qty'],
            $data['g1_g5_ingapu_qty']
        );

        $teacherGuideDistribution->update($data);
        $teacherGuideDistribution->syncTownshipQtys($townshipQtys);

        return redirect()
            ->route('teacher-guide-distributions.index')
            ->with('success', 'ဖြန့်ဝေရန်ခွဲတမ်း အချက်အလက် ပြင်ဆင်ပြီးပါပြီ။');
    }

    public function destroy(TeacherGuide $teacherGuideDistribution)
    {
        // Receipt + distribution share teacher_guides. Clearing township rows
        // removes distribution only — do not delete the receipt header.
        $teacherGuideDistribution->townshipAllocations()->delete();
        $teacherGuideDistribution->unsetRelation('townshipAllocations');

        return redirect()
            ->route('teacher-guide-distributions.index')
            ->with('success', 'ဖြန့်ဝေရန်ခွဲတမ်း ဖျက်ပြီးပါပြီ (လက်ခံရရှိမှု ထိန်းသိမ်းထားပါသည်).');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'kg_g12_myanaung_qty' => 'nullable|integer|min:0',
            'kg_g12_kyankhin_qty' => 'nullable|integer|min:0',
            'kg_g12_ingapu_qty' => 'nullable|integer|min:0',

            'g1_g5_myanaung_qty' => 'nullable|integer|min:0',
            'g1_g5_kyankhin_qty' => 'nullable|integer|min:0',
            'g1_g5_ingapu_qty' => 'nullable|integer|min:0',

            'remark' => 'nullable|string|max:1000',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, int|null>
     */
    private function extractTownshipQtys(array $data): array
    {
        return [
            'kg_g12_myanaung_qty' => $data['kg_g12_myanaung_qty'] ?? 0,
            'kg_g12_kyankhin_qty' => $data['kg_g12_kyankhin_qty'] ?? 0,
            'kg_g12_ingapu_qty' => $data['kg_g12_ingapu_qty'] ?? 0,
            'g1_g5_myanaung_qty' => $data['g1_g5_myanaung_qty'] ?? 0,
            'g1_g5_kyankhin_qty' => $data['g1_g5_kyankhin_qty'] ?? 0,
            'g1_g5_ingapu_qty' => $data['g1_g5_ingapu_qty'] ?? 0,
        ];
    }
}
