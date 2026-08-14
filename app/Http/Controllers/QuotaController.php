<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Quota;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuotaController extends Controller
{
    public function index(Request $request)
    {
        $years = AcademicYear::where('is_active', true)
            ->orderBy('start_year')
            ->orderBy('name')
            ->get();

        $currentYear = AcademicYear::current()->first();
        $academicYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($currentYear?->id ?? $years->last()?->id);

        $selectedYear = $academicYearId
            ? $years->firstWhere('id', (int) $academicYearId) ?? AcademicYear::find($academicYearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $quotaQuery = Quota::with(['academicYear', 'township', 'lines'])
            ->where('academic_year_id', $academicYearId);

        if ($selectedYear?->isFuture()) {
            $quotas = collect();
        } else {
            $quotas = $quotaQuery->orderBy('id')->get();
        }

        $rows = $quotas->map(function ($quota) {
            return [
                'id' => $quota->id,
                'academic_year' => $quota->academicYear?->name,
                'township' => $quota->township?->name,

                'primary_public' => $quota->primary_public,
                'primary_monk' => $quota->primary_monk,
                'primary_private' => $quota->primary_private,
                'primary_total' => $quota->primary_total,

                'middle_public' => $quota->middle_public,
                'middle_monk' => $quota->middle_monk,
                'middle_private' => $quota->middle_private,
                'middle_total' => $quota->middle_total,

                'high_public' => $quota->high_public,
                'high_monk' => $quota->high_monk,
                'high_private' => $quota->high_private,
                'high_total' => $quota->high_total,

                'grand_public' => $quota->grand_public,
                'grand_monk' => $quota->grand_monk,
                'grand_private' => $quota->grand_private,
                'grand_total' => $quota->grand_total,

                'agriculture' => $quota->agriculture,
                'total_with_agriculture' => $quota->total_with_agriculture,
                'distribution_total' => $quota->distribution_total,
            ];
        });

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        $academicYear = $selectedYear?->name ?? '';

        return view('quota.index', [
            'rows' => $rows,
            'years' => $years,
            'academicYear' => $academicYear,
            'academicYearId' => $academicYearId,
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
                    ->route('quota.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        return view('quota.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $data = $this->validatedData($request);
        $header = [
            'academic_year_id' => $data['academic_year_id'],
            'township_id' => $data['township_id'],
        ];
        unset(
            $data['academic_year_id'],
            $data['township_id']
        );

        Quota::createWithLines($header, $data);

        return redirect()
            ->route('quota.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
            ]))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ');
    }

    public function edit($id)
    {
        $quota = Quota::with('lines')->findOrFail($id);

        return view('quota.edit', $this->formData() + [
            'quota' => $quota,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $quota = Quota::findOrFail($id);
        $data = $this->validatedData($request);
        $header = [
            'academic_year_id' => $data['academic_year_id'],
            'township_id' => $data['township_id'],
        ];
        unset($data['academic_year_id'], $data['township_id']);

        $quota->updateWithLines($header, $data);

        return redirect()
            ->route('quota.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
            ]))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ');
    }

    public function destroy($id)
    {
        $quota = Quota::findOrFail($id);
        $yearId = $quota->academic_year_id;
        $quota->delete();

        return redirect()
            ->route('quota.index', array_filter([
                'academic_year_id' => $yearId,
            ]))
            ->with('success', 'အောင်မြင်စွာဖျက်လိုက်ပါပြီ');
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

            'primary_public' => 'nullable|integer|min:0',
            'primary_monk' => 'nullable|integer|min:0',
            'primary_private' => 'nullable|integer|min:0',

            'middle_public' => 'nullable|integer|min:0',
            'middle_monk' => 'nullable|integer|min:0',
            'middle_private' => 'nullable|integer|min:0',

            'high_public' => 'nullable|integer|min:0',
            'high_monk' => 'nullable|integer|min:0',
            'high_private' => 'nullable|integer|min:0',

            'agriculture' => 'nullable|integer|min:0',
        ]);

        foreach ([
            'primary_public', 'primary_monk', 'primary_private',
            'middle_public', 'middle_monk', 'middle_private',
            'high_public', 'high_monk', 'high_private',
            'agriculture',
        ] as $field) {
            $data[$field] = (int) ($data[$field] ?? 0);
        }

        return $data;
    }
}
