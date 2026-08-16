<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BookName;
use App\Models\Grade;
use App\Models\Textbook;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TextbookController extends Controller
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

        $query = Textbook::with(['year', 'township', 'grade', 'bookName']);

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
            $query->whereRaw('0 = 1');
        }

        $textbooks = $query
            ->orderBy('township_id')
            ->orderBy('id')
            ->paginate(config('deras.pagination_per_page'))
            ->withQueryString();

        $emptyMessage = 'အချက်အလက်မရှိပါ';
        if ($selectedYear?->isFuture()) {
            $emptyMessage = 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ';
        }

        $blocks = $textbooks->getCollection()->groupBy('township_id')->map(function ($items) {
            return [
                'academic_year' => $items->first()->year?->name,
                'township' => $items->first()->township?->name,
                'rows' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'grade' => $item->grade?->name ?? '',
                        'book_name' => $item->bookName?->name ?? '',
                        'books_per_set' => $item->books_per_set ?? 0,
                        'student_count' => $item->student_count ?? 0,
                        'book_count' => $item->book_count ?? '',
                        'remark' => $item->remark ?? '',
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        $maxRows = collect($blocks)->max(fn ($block) => count($block['rows'])) ?? 0;

        return view('textbook.index', [
            'blocks' => $blocks,
            'textbooks' => $textbooks,
            'maxRows' => $maxRows,
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
                    ->route('textbook.index', ['academic_year_id' => $year->id])
                    ->with('error', 'မရောက်သေးသောပညာသင်နှစ်ဖြစ်သဖြင့် အချက်အလက်ထည့်သွင်း၍မရနိုင်ပါ');
            }
            $data['preselectedYearId'] = $request->academic_year_id;
        }

        if ($request->filled('township_id')) {
            $data['preselectedTownshipId'] = $request->township_id;
        }

        return view('textbook.create', $data);
    }

    public function store(Request $request)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        Textbook::create($this->validatedData($request));

        return redirect()
            ->route('textbook.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'township_id' => $request->township_id,
            ]))
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ');
    }

    public function edit(Textbook $textbook)
    {
        return view('textbook.edit', $this->formData() + [
            'textbook' => $textbook,
        ]);
    }

    public function update(Request $request, Textbook $textbook)
    {
        $this->assertYearAllowsDataEntry($request->input('academic_year_id'));

        $textbook->update($this->validatedData($request, $textbook));

        return redirect()
            ->route('textbook.index', array_filter([
                'academic_year_id' => $request->academic_year_id,
                'township_id' => $request->township_id,
            ]))
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ');
    }

    public function destroy(Textbook $textbook)
    {
        $yearId = $textbook->academic_year_id;
        $townshipId = $textbook->township_id;
        $textbook->delete();

        return redirect()
            ->route('textbook.index', array_filter([
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

    private function validatedData(Request $request, ?Textbook $textbook = null): array
    {
        return $request->validate(
            [
                'academic_year_id' => 'required|exists:academic_years,id',
                'township_id' => 'required|exists:townships,id',
                'grade_id' => 'required|exists:grades,id',
                'book_name_id' => [
                    'required',
                    'exists:book_names,id',
                    Rule::unique('textbooks', 'book_name_id')
                        ->ignore($textbook?->id)
                        ->where(fn ($q) => $q
                            ->where('academic_year_id', $request->academic_year_id)
                            ->where('township_id', $request->township_id)
                            ->where('grade_id', $request->grade_id)
                        ),
                ],
                'books_per_set' => 'nullable|integer|min:0',
                'student_count' => 'nullable|integer|min:0',
                'book_count' => 'nullable|string|max:255',
                'remark' => 'nullable|string|max:255',
            ],
            [
                'book_name_id.unique' => 'ဤမြို့နယ်အတွက် ရွေးထားသော အတန်းနှင့် ဘာသာရပ် ပေါင်းစည်းမှု ရှိပြီးသားဖြစ်ပါသည်။',
            ]
        );
    }
}
