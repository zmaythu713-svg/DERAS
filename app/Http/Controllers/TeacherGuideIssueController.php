<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\TeacherGuide;
use App\Models\TeacherGuideIssue;
use App\Models\Township;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherGuideIssueController extends Controller
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

        $query = TeacherGuideIssue::with(['academicYear', 'grade', 'bookName', 'townshipIssues.township']);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($guideType) {
            $query->where('guide_type', $guideType);
        }

        if ($request->filled('township_id')) {
            $townshipId = $request->integer('township_id');
            $query->whereHas(
                'townshipIssues',
                fn ($sub) => $sub->where('township_id', $townshipId)
            );
        }

        if ($search !== '') {
            $query->whereHas(
                'bookName',
                fn ($sub) => $sub->where('name', 'like', "%{$search}%")
            );
        }

        if ($selectedYear?->isFuture()) {
            $issues = collect();
        } else {
            $issues = $query->orderBy('group_no')->orderBy('sequence_no')->get();
        }

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('teacher-guide-issues.index', [
            'issues' => $issues,
            'years' => $years,
            'grades' => Grade::dropdownOptions(),
            'townships' => Township::dropdownOptions(),
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
                    ->route('teacher-guide-issues.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
        }

        return view('teacher-guide-issues.create', $this->formData() + [
            'preselectedYearId' => $request->academic_year_id,
            'preselectedGradeId' => $request->grade_id,
            'preselectedGuideType' => $request->guide_type,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            if (empty($data['issue']['group_no'])) {
                $last = TeacherGuideIssue::orderByDesc('id')->first();
                $data['issue']['group_no'] = $last ? $last->group_no + 1 : 1;
            }

            if (empty($data['issue']['sequence_no'])) {
                $data['issue']['sequence_no'] = TeacherGuideIssue::where(
                    'group_no',
                    $data['issue']['group_no']
                )->count() + 1;
            }

            $issue = TeacherGuideIssue::create($data['issue']);

            foreach ($data['townships'] as $townshipId => $values) {
                $issue->townshipIssues()->create(
                    ['township_id' => $townshipId] + $values
                );
            }
        });

        return redirect()
            ->route('teacher-guide-issues.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
            ]))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ.');
    }

    public function edit(TeacherGuideIssue $teacherGuideIssue): View
    {
        $teacherGuideIssue->load(['townshipIssues', 'bookName']);

        return view('teacher-guide-issues.edit', $this->formData($teacherGuideIssue->academic_year_id) + [
            'teacherGuideIssue' => $teacherGuideIssue,
        ]);
    }

    public function update(Request $request, TeacherGuideIssue $teacherGuideIssue): RedirectResponse
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request, $teacherGuideIssue);
        DB::transaction(function () use ($teacherGuideIssue, $data) {
            $teacherGuideIssue->update($data['issue']);
            foreach ($data['townships'] as $townshipId => $values) {
                $teacherGuideIssue->townshipIssues()->updateOrCreate(['township_id' => $townshipId], $values);
            }
        });

        return redirect()
            ->route('teacher-guide-issues.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
            ]))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ.');
    }

    public function destroy(TeacherGuideIssue $teacherGuideIssue): RedirectResponse
    {
        $yearId = $teacherGuideIssue->academic_year_id;
        $teacherGuideIssue->delete();

        return redirect()
            ->route('teacher-guide-issues.index', array_filter([
                'academic_year_id' => $yearId,
            ]))
            ->with('success', 'အောင်မြင်စွာဖျက်ပြီးပါပြီ.');
    }

    private function formData(?int $keepYearId = null): array
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
            'townships' => Township::dropdownOptions(),
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

    private function validatedData(Request $request, ?TeacherGuideIssue $issue = null): array
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_id' => 'required|exists:grades,id',
            'book_name_id' => [
                'required',
                'exists:book_names,id',
                Rule::unique('teacher_guide_issues')->where(
                    fn ($q) => $q
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('grade_id', $request->grade_id)
                        ->where('guide_type', $request->guide_type)
                )->ignore($issue?->id),
            ],
            'group_no' => 'nullable|integer|min:1',
            'guide_type' => 'required|in:ဆရာကိုင်,ဆရာလမ်းညွှန်',
            'sequence_no' => 'nullable|integer|min:1',
            'district_unit' => 'nullable|integer|min:0',
            'package_unit' => 'required|integer|min:1',
            'remark' => 'nullable|string|max:1000',
            'township_values' => 'required|array',
            'township_values.*.issued_quantity' => 'nullable|integer|min:0',
            'township_values.*.full_package_count' => 'nullable|integer|min:0',
            'township_values.*.loose_book_count' => 'nullable|integer|min:0',
        ]);

        $quota = TeacherGuide::query()
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('grade_id', $validated['grade_id'])
            ->where('book_name_id', $validated['book_name_id'])
            ->where('guide_type', $validated['guide_type'])
            ->orderByDesc('id')
            ->first();

        $validated['district_unit'] = $quota
            ? (int) ($quota->remaining_total ?? 0)
            : (int) ($validated['district_unit'] ?? 0);

        $gradeName = Grade::where('id', $validated['grade_id'])->value('name');
        $validated['group_title'] = $quota?->group_title
            ?? ($gradeName . "\n(" . $validated['guide_type'] . ')');

        if ($quota) {
            $validated['group_no'] = $quota->group_no;
            $validated['sequence_no'] = $quota->sequence_no;
        }

        $issuedByTownshipName = [
            'မြန်အောင်' => (int) ($quota?->total_myanaung_qty ?? 0),
            'ကြံခင်း' => (int) ($quota?->total_kyankhin_qty ?? 0),
            'အင်္ဂပူ' => (int) ($quota?->total_ingapu_qty ?? 0),
        ];

        $packageUnit = (int) ($validated['package_unit'] ?? 0);
        $townships = [];

        foreach (Township::dropdownOptions() as $township) {
            $issued = $issuedByTownshipName[$township->name]
                ?? (int) ($validated['township_values'][$township->id]['issued_quantity'] ?? 0);

            $packages = $packageUnit > 0 ? intdiv($issued, $packageUnit) : 0;
            $loose = $packageUnit > 0 ? $issued % $packageUnit : 0;

            $townships[$township->id] = [
                'issued_quantity' => $issued,
                'full_package_count' => $packages,
                'loose_book_count' => $loose,
            ];
        }

        return [
            'issue' => collect($validated)->except('township_values')->all(),
            'townships' => $townships,
        ];
    }
}
