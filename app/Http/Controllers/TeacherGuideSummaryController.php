<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\TeacherGuide;
use App\Models\TeacherGuideSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherGuideSummaryController extends Controller
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
        $guideType = $request->string('guide_type')->toString();
        $search = trim($request->string('search')->toString());

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = TeacherGuideSummary::query()
            ->with(['academicYear', 'grade', 'bookName']);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($guideType !== '') {
            $query->where('guide_type', $guideType);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('group_title', 'like', "%{$search}%")
                    ->orWhereHas(
                        'bookName',
                        fn ($bookQuery) => $bookQuery->where('name', 'like', "%{$search}%")
                    );
            });
        }

        if ($selectedYear?->isFuture()) {
            $query->whereRaw('0 = 1');
        }

        $summaries = $query
            ->orderBy('group_no')
            ->orderBy('sequence_no')
            ->paginate(config('deras.pagination_per_page'))
            ->withQueryString();

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('teacher-guide-summaries.index', [
            'summaries' => $summaries,
            'years' => $years,
            'grades' => Grade::dropdownOptions(),
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
                    ->route('teacher-guide-summaries.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
        }

        return view('teacher-guide-summaries.create', $this->formOptions() + [
            'preselectedYearId' => $request->academic_year_id,
            'preselectedGradeId' => $request->grade_id,
            'preselectedGuideType' => $request->guide_type,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request);

        if (empty($data['group_no'])) {
            $data['group_no'] = (TeacherGuideSummary::max('group_no') ?? 0) + 1;
        }
        if (empty($data['sequence_no'])) {
            $data['sequence_no'] = (TeacherGuideSummary::max('sequence_no') ?? 0) + 1;
        }

        TeacherGuideSummary::create($data);

        return redirect()
            ->route('teacher-guide-summaries.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
            ]))
            ->with('success', 'စာရင်းချုပ်အသစ် ဖန်တီးပြီးပါပြီ။');
    }

    public function edit(TeacherGuideSummary $teacherGuideSummary): View
    {
        $teacherGuideSummary->load('bookName');

        return view('teacher-guide-summaries.edit', array_merge(
            $this->formOptions($teacherGuideSummary->academic_year_id),
            ['teacherGuideSummary' => $teacherGuideSummary]
        ));
    }

    public function update(Request $request, TeacherGuideSummary $teacherGuideSummary): RedirectResponse
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request, $teacherGuideSummary);
        $teacherGuideSummary->update($data);

        return redirect()
            ->route('teacher-guide-summaries.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
            ]))
            ->with('success', 'စာရင်းချုပ် ပြင်ဆင်ပြီးပါပြီ။');
    }

    public function destroy(TeacherGuideSummary $teacherGuideSummary): RedirectResponse
    {
        $yearId = $teacherGuideSummary->academic_year_id;
        $teacherGuideSummary->delete();

        return redirect()
            ->route('teacher-guide-summaries.index', array_filter([
                'academic_year_id' => $yearId,
            ]))
            ->with('success', 'စာရင်းချုပ် ဖျက်ပြီးပါပြီ။');
    }

    private function formOptions(?int $keepYearId = null): array
    {
        $years = AcademicYear::where('is_active', true)
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get()
            ->filter(fn (AcademicYear $year) => $year->allowsDataEntry()
                || ($keepYearId !== null && (int) $year->id === (int) $keepYearId))
            ->values();

        return [
            'years' => $years,
            'currentYearId' => AcademicYear::current()->value('id'),
            'grades' => Grade::dropdownOptions(),
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

    private function validatedData(
        Request $request,
        ?TeacherGuideSummary $teacherGuideSummary = null
    ): array {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'book_name_id' => ['required', 'exists:book_names,id'],
            'guide_type' => [
                'required',
                Rule::in(['ဆရာကိုင်', 'ဆရာလမ်းညွှန်']),
            ],
            'previous_balance' => ['nullable', 'integer', 'min:0'],
            'fiscal_year_quota' => ['nullable', 'integer', 'min:0'],
            'distributed_books' => ['nullable', 'integer', 'min:0'],
            'remark' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['previous_balance'] = (int) ($data['previous_balance'] ?? 0);
        $quota = TeacherGuide::query()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('grade_id', $data['grade_id'])
            ->where('book_name_id', $data['book_name_id'])
            ->where('guide_type', $data['guide_type'])
            ->orderByDesc('id')
            ->first();

        if (!$quota) {
            throw ValidationException::withMessages([
                'book_name_id' => 'ဤဘာသာအတွက် ဆရာလမ်းညွှန် လက်ခံရရှိမှု မှတ်တမ်း မရှိသေးပါ။',
            ]);
        }

        // ဘဏ္ဍာရေးနှစ်ခွဲတမ်း ← လက်ခံရရှိမှု ခရိုင်ရရှိခွဲတမ်း (total_quota)
        $data['fiscal_year_quota'] = (int) ($quota->total_quota ?? $data['fiscal_year_quota'] ?? 0);

        // ဖြန့်ဝေပြီးအုပ်ရေ ← ဖြန့်ဝေရန်ခွဲတမ်း distributed_total
        $data['distributed_books'] = (int) ($quota->distributed_total ?? $data['distributed_books'] ?? 0);

        $gradeName = Grade::where('id', $data['grade_id'])->value('name');
        $data['group_title'] = $quota->group_title
            ?? ($gradeName . "\n(" . $data['guide_type'] . ')');

        $data['teacher_guide_id'] = $quota->id;

        if ($quota) {
            $data['group_no'] = $quota->group_no;
            $data['sequence_no'] = $quota->sequence_no;
        }

        return $data;
    }

}
