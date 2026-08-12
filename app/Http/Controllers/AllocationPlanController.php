<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AllocationPlan;
use App\Models\BookName;
use App\Models\Grade;
use App\Services\AllocationCalculator;
use App\Services\TextbookFromAllocationSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AllocationPlanController extends Controller
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

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = AllocationPlan::with([
            'academicYear',
            'grade',
            'bookName',
            'detail',
        ]);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('book_name_id')) {
            $query->where('book_name_id', $request->book_name_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('bookName', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Future years: never show rows (and create is blocked).
        if ($selectedYear?->isFuture()) {
            $plans = collect();
        } else {
            $plans = $query->orderBy('sequence_no')->get();
        }

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('allocation-plans.index', [
            'plans' => $plans,
            'years' => $years,
            'grades' => Grade::dropdownOptions(),
            'bookNames' => BookName::where('is_active', true)->orderBy('name')->get(),
            'yearId' => $yearId,
            'gradeId' => $request->grade_id,
            'bookNameId' => $request->book_name_id,
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
                    ->route('allocation-plans.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသော ပညာသင်နှစ်အတွက် အချက်အလက် ထည့်သွင်း၍ မရပါ။');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        return view('allocation-plans.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $validated = $request->validate(
            [
                'academic_year_id' => [
                    'required',
                    'exists:academic_years,id',
                ],

                'grade_id' => [
                    'required',
                    'exists:grades,id',
                ],

                'book_name_id' => [
                    'required',
                    'exists:book_names,id',

                    Rule::unique(
                        'allocation_plans',
                        'book_name_id'
                    )->where(
                        function ($query) use ($request) {
                            return $query
                                ->where(
                                    'academic_year_id',
                                    $request->academic_year_id
                                )
                                ->where(
                                    'grade_id',
                                    $request->grade_id
                                );
                        }
                    ),
                ],

                'received_books' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'books_per_package' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'remark' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'myanaung_previous' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'kyankhin_previous' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'ingapu_previous' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'myanaung_total_students' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'kyankhin_total_students' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'ingapu_total_students' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'myanaung_transferable' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'kyankhin_transferable' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'ingapu_transferable' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ],
            [
                'book_name_id.unique' =>
                'ရွေးချယ်ထားသော ပညာသင်နှစ်၊ အတန်းနှင့် စာအုပ်အမည်အတွက် အချက်အလက်ရှိပြီးသားဖြစ်ပါသည်။',
            ]
        );

        $plan = null;

        DB::transaction(function () use ($validated, &$plan) {
            $computed = app(AllocationCalculator::class)->compute($validated);

            $lastSequenceNo = AllocationPlan::where(
                'academic_year_id',
                $validated['academic_year_id']
            )
                ->lockForUpdate()
                ->max('sequence_no');

            $sequenceNo = ((int) $lastSequenceNo) + 1;

            $plan = AllocationPlan::create(array_merge($computed['plan'], [
                'academic_year_id' => $validated['academic_year_id'],
                'grade_id' => $validated['grade_id'],
                'book_name_id' => $validated['book_name_id'],
                'sequence_no' => $sequenceNo,
                'remark' => $validated['remark'] ?? null,
            ]));

            $plan->detail()->create($computed['detail']);

            app(TextbookFromAllocationSync::class)->sync($plan->fresh('detail'));
        });

        return redirect()
            ->route('allocation-plans.index')
            ->with(
                'success',
                'အောင်မြင်စွာဖန်တီးပြီးပါပြီ (ပုံမှန်ဖြန့်ဝေစာရင်းသို့ အလိုအလျောက် ထည့်ပြီးပါပြီ)'
            );
    }

    public function edit(AllocationPlan $allocationPlan)
    {
        $allocationPlan->load('detail');

        return view(
            'allocation-plans.edit',
            $this->formData() + [
                'allocationPlan' => $allocationPlan,
            ]
        );
    }

    public function update(
        Request $request,
        AllocationPlan $allocationPlan
    ) {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $validated = $request->validate(
            [
                'academic_year_id' => [
                    'required',
                    'exists:academic_years,id',
                ],

                'grade_id' => [
                    'required',
                    'exists:grades,id',
                ],

                'book_name_id' => [
                    'required',
                    'exists:book_names,id',

                    Rule::unique(
                        'allocation_plans',
                        'book_name_id'
                    )
                        ->ignore($allocationPlan->id)
                        ->where(
                            function ($query) use ($request) {
                                return $query
                                    ->where(
                                        'academic_year_id',
                                        $request->academic_year_id
                                    )
                                    ->where(
                                        'grade_id',
                                        $request->grade_id
                                    );
                            }
                        ),
                ],

                'received_books' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'books_per_package' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'remark' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'myanaung_previous' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'kyankhin_previous' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'ingapu_previous' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'myanaung_total_students' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'kyankhin_total_students' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'ingapu_total_students' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'myanaung_transferable' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'kyankhin_transferable' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'ingapu_transferable' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ],
            [
                'book_name_id.unique' =>
                'ရွေးချယ်ထားသော ပညာသင်နှစ်၊ အတန်းနှင့် စာအုပ်အမည်အတွက် အချက်အလက်ရှိပြီးသားဖြစ်ပါသည်။',
            ]
        );

        DB::transaction(function () use (
            $validated,
            $allocationPlan
        ) {
            $computed = app(AllocationCalculator::class)->compute($validated);

            $allocationPlan->update(array_merge($computed['plan'], [
                'academic_year_id' => $validated['academic_year_id'],
                'grade_id' => $validated['grade_id'],
                'book_name_id' => $validated['book_name_id'],
                'remark' => $validated['remark'] ?? null,
            ]));

            $allocationPlan
                ->detail()
                ->updateOrCreate(
                    [
                        'allocation_plan_id' => $allocationPlan->id,
                    ],
                    $computed['detail']
                );

            app(TextbookFromAllocationSync::class)->sync($allocationPlan->fresh('detail'));
        });

        return redirect()
            ->route('allocation-plans.index')
            ->with(
                'success',
                'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ (ပုံမှန်ဖြန့်ဝေစာရင်းသို့ အလိုအလျောက် ပြန်ထုတ်ပြီးပါပြီ)'
            );
    }

    public function destroy(AllocationPlan $allocationPlan)
    {
        $allocationPlan->delete();

        return redirect()
            ->route('allocation-plans.index')
            ->with(
                'success',
                'အောင်မြင်စွာဖျက်ပြီးပါပြီ'
            );
    }

    private function formData()
    {
        $years = AcademicYear::where('is_active', true)
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get()
            // Future years cannot receive new data — hide from create/edit selects
            ->filter(fn (AcademicYear $year) => $year->allowsDataEntry())
            ->values();

        $currentYearId = AcademicYear::current()->value('id');

        return [
            'years' => $years,
            'currentYearId' => $currentYearId,

            'grades' => Grade::dropdownOptions(),

            'bookNames' => BookName::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    private function assertYearAllowsDataEntry(mixed $academicYearId): void
    {
        $year = AcademicYear::find($academicYearId);
        if (!$year || !$year->allowsDataEntry()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'academic_year_id' => 'မရောက်သေးသော ပညာသင်နှစ်အတွက် အချက်အလက် ထည့်သွင်း၍ မရပါ။',
            ]);
        }
    }
}
