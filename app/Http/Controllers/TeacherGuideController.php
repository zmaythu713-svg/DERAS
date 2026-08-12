<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BookName;
use App\Models\Grade;
use App\Models\TeacherGuide;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherGuideController extends Controller
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
        $guideType = $request->guide_type;

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = TeacherGuide::with(['academicYear', 'grade', 'bookName']);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $gradeId);
        }

        if ($request->filled('guide_type')) {
            $query->where('guide_type', $guideType);
        }

        if ($selectedYear?->isFuture()) {
            $teacherGuides = collect();
        } else {
            $teacherGuides = $query
                ->orderBy('group_no')
                ->orderBy('sequence_no')
                ->get();
        }

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('teacher-guides.index', [
            'teacherGuides' => $teacherGuides,
            'years' => $years,
            'grades' => Grade::dropdownOptions(),
            'yearId' => $yearId,
            'gradeId' => $gradeId,
            'guideType' => $guideType,
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
                    ->route('teacher-guides.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        if ($request->filled('grade_id')) {
            $data['preselectedGradeId'] = $request->grade_id;
        }

        if ($request->filled('guide_type')) {
            $data['preselectedGuideType'] = $request->guide_type;
        }

        return view('teacher-guides.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request);
        $data['group_no'] = (TeacherGuide::max('group_no') ?? 0) + 1;
        $data['sequence_no'] = (TeacherGuide::max('sequence_no') ?? 0) + 1;

        TeacherGuide::create($data);

        return redirect()
            ->route('teacher-guides.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'grade_id' => $request->grade_id,
                'guide_type' => $request->guide_type,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ.');
    }

    public function edit(TeacherGuide $teacherGuide)
    {
        return view('teacher-guides.edit', $this->formData() + [
            'teacherGuide' => $teacherGuide,
        ]);
    }

    public function update(Request $request, TeacherGuide $teacherGuide)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $teacherGuide->update($this->validatedData($request, $teacherGuide));

        return redirect()
            ->route('teacher-guides.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'grade_id' => $request->grade_id,
                'guide_type' => $request->guide_type,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ.');
    }

    public function destroy(TeacherGuide $teacherGuide)
    {
        $yearId = $teacherGuide->academic_year_id;
        $gradeId = $teacherGuide->grade_id;
        $guideType = $teacherGuide->guide_type;
        $teacherGuide->delete();

        return redirect()
            ->route('teacher-guides.index', array_filter([
                'academic_year_id' => $yearId,
                'grade_id' => $gradeId,
                'guide_type' => $guideType,
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
            'bookNames' => BookName::where('is_active', true)->orderBy('name')->get(),
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

    private function validatedData(Request $request, ?TeacherGuide $teacherGuide = null): array
    {
        return $request->validate(
            [
                'academic_year_id' => 'required|exists:academic_years,id',
                'grade_id' => 'required|exists:grades,id',
                'book_name_id' => [
                    'required',
                    'exists:book_names,id',
                    Rule::unique('teacher_guides', 'book_name_id')
                        ->ignore($teacherGuide?->id)
                        ->where(fn ($q) => $q
                            ->where('academic_year_id', $request->academic_year_id)
                            ->where('grade_id', $request->grade_id)
                            ->where('guide_type', $request->guide_type)
                        ),
                ],
                'group_no' => 'nullable|integer|min:1',
                'sequence_no' => 'nullable|integer|min:1',
                'group_title' => 'required|string|max:255',
                'guide_type' => 'required|in:ဆရာကိုင်,ဆရာလမ်းညွှန်',
                'kg_to_g12_quota' => 'nullable|integer|min:0',
                'g1_to_g5_quota' => 'nullable|integer|min:0',
                'total_quota' => 'nullable|integer|min:0',
                'remark' => 'nullable|string|max:255',
            ],
            [
                'book_name_id.unique' => 'ရွေးထားသော အတန်း၊ ဘာသာရပ်နှင့် ဆရာကိုင်/ဆရာလမ်းညွှန် ပေါင်းစည်းမှု ရှိပြီးသားဖြစ်ပါသည်။',
            ]
        );
    }
}
