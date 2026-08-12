<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BookName;
use App\Models\Grade;
use App\Models\Stock;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
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
        $search = trim((string) $request->input('search', ''));

        $selectedYear = $yearId
            ? $years->firstWhere('id', (int) $yearId) ?? AcademicYear::find($yearId)
            : null;

        $canCreate = $selectedYear?->allowsDataEntry() ?? false;

        $query = Stock::with([
            'academicYear',
            'township',
            'grade',
            'bookName',
        ]);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('township_id')) {
            $query->where('township_id', $townshipId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    'bookName',
                    fn ($bookQuery) => $bookQuery->where('name', 'like', "%{$search}%")
                )->orWhereHas(
                    'grade',
                    fn ($gradeQuery) => $gradeQuery->where('name', 'like', "%{$search}%")
                )->orWhere('remark', 'like', "%{$search}%");
            });
        }

        if ($selectedYear?->isFuture()) {
            $stocks = collect();
        } else {
            $stocks = $query->latest('id')->get();
        }

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        return view('stocks.index', [
            'stocks' => $stocks,
            'years' => $years,
            'townships' => Township::dropdownOptions(),
            'yearId' => $yearId,
            'townshipId' => $townshipId,
            'search' => $search,
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
                    ->route('stocks.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        if ($request->filled('township_id')) {
            $data['preselectedTownshipId'] = $request->township_id;
        }

        return view('stocks.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        Stock::create($this->validatedData($request));

        return redirect()
            ->route('stocks.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'township_id' => $request->township_id,
            ]))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ');
    }

    public function edit(Stock $stock)
    {
        return view('stocks.edit', $this->formData() + [
            'stock' => $stock,
        ]);
    }

    public function update(Request $request, Stock $stock)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $stock->update($this->validatedData($request, $stock));

        return redirect()
            ->route('stocks.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'township_id' => $request->township_id,
            ]))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ');
    }

    public function destroy(Stock $stock)
    {
        $yearId = $stock->academic_year_id;
        $townshipId = $stock->township_id;
        $stock->delete();

        return redirect()
            ->route('stocks.index', array_filter([
                'academic_year_id' => $yearId,
                'township_id' => $townshipId,
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

    private function validatedData(Request $request, ?Stock $stock = null): array
    {
        return $request->validate(
            [
                'academic_year_id' => 'required|exists:academic_years,id',
                'township_id' => 'required|exists:townships,id',
                'grade_id' => 'required|exists:grades,id',
                'book_name_id' => [
                    'required',
                    'exists:book_names,id',
                    Rule::unique('stocks', 'book_name_id')
                        ->ignore($stock?->id)
                        ->where(fn ($q) => $q
                            ->where('academic_year_id', $request->academic_year_id)
                            ->where('township_id', $request->township_id)
                            ->where('grade_id', $request->grade_id)
                        ),
                ],
                'previous_balance' => 'nullable|integer|min:0',
                'transferred' => 'nullable|integer|min:0',
                'enrolled_need' => 'nullable|integer|min:0',
                'required_qty' => 'nullable|integer|min:0',
                'remark' => 'nullable|string|max:255',
            ],
            [
                'book_name_id.unique' => 'ဤမြို့နယ်အတွက် ရွေးထားသော အတန်းနှင့် ဘာသာရပ် ပေါင်းစည်းမှု ရှိပြီးသားဖြစ်ပါသည်။',
            ]
        );
    }
}
