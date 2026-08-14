<?php

namespace App\Http\Controllers;

use App\Models\BookName;
use App\Models\Category;
use App\Models\Grade;
use App\Support\GradeSubjectMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeSubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::with(['bookNames' => function ($q) {
            $q->where('book_names.is_active', true)
                ->orderBy('book_names.name');
        }])->withCount('bookNames');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $grades = $query->ordered()->get();

        $categories = Category::where('is_active', true)
            ->orderByRaw("FIELD(slug, 'textbook', 'teacher_handbook', 'teacher_guide')")
            ->orderBy('id')
            ->get();

        return view('grade-subjects.index', [
            'grades' => $grades,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request)
    {
        $grades = Grade::activeOrdered()->get();
        $data = [
            'grades' => $grades,
            'grade' => null,
            'linkedByCategory' => [],
            'bookNames' => collect(),
            'bookNamesByCategory' => [],
            'categories' => Category::where('is_active', true)->orderBy('id')->get(),
            'fieldMap' => Category::fieldMap(),
        ];

        $gradeId = $request->input('grade_id', old('grade_id'));
        if ($gradeId) {
            $selectedGrade = Grade::find($gradeId);
            if ($selectedGrade) {
                $data = array_merge($data, $this->formData($selectedGrade));
                $data['grade'] = $selectedGrade;
                $data['grades'] = $grades;
                $data['linkedByCategory'] = old()
                    ? [
                        'textbook' => old('textbook_book_name_ids', []),
                        'teacher_handbook' => old('teacher_handbook_book_name_ids', []),
                        'teacher_guide' => old('teacher_guide_book_name_ids', []),
                    ]
                    : $this->linkedByCategoryFor($selectedGrade);
            }
        }

        return view('grade-subjects.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate(array_merge([
            'grade_id' => 'required|exists:grades,id',
        ], $this->subjectRules()));

        $grade = Grade::findOrFail($request->grade_id);
        $this->syncSubjectsByCategory($grade, $request);

        return redirect()->route('grade-subjects.index')
            ->with('success', 'အတန်း–ဘာသာရပ် တွဲချိတ်မှု သိမ်းဆည်းပြီးပါပြီ။');
    }

    public function edit(Grade $grade)
    {
        $data = $this->formData($grade);
        $data['grade'] = $grade;
        $data['linkedByCategory'] = $this->linkedByCategoryFor($grade);

        return view('grade-subjects.edit', $data);
    }

    public function update(Request $request, Grade $grade)
    {
        $request->validate($this->subjectRules());

        $this->syncSubjectsByCategory($grade, $request);

        return redirect()->route('grade-subjects.index')
            ->with('success', 'အတန်း–ဘာသာရပ် တွဲချိတ်မှု ပြင်ဆင်ပြီးပါပြီ။');
    }

    public function destroy(Grade $grade)
    {
        DB::table('grade_book_names')->where('grade_id', $grade->id)->delete();

        return redirect()->route('grade-subjects.index')
            ->with('success', 'အတန်း–ဘာသာရပ် တွဲချိတ်မှု ဖျက်ပြီးပါပြီ။');
    }

    private function subjectRules(): array
    {
        return [
            'textbook_book_name_ids' => 'nullable|array',
            'textbook_book_name_ids.*' => 'exists:book_names,id',
            'teacher_handbook_book_name_ids' => 'nullable|array',
            'teacher_handbook_book_name_ids.*' => 'exists:book_names,id',
            'teacher_guide_book_name_ids' => 'nullable|array',
            'teacher_guide_book_name_ids.*' => 'exists:book_names,id',
        ];
    }

    private function formData(Grade $grade): array
    {
        $categories = Category::where('is_active', true)->orderBy('id')->get();
        $allBooks = BookName::where('is_active', true)->orderBy('name')->get();

        // Full subject catalog in every category — user picks what to link
        $bookNamesByCategory = $categories->mapWithKeys(
            fn (Category $category) => [$category->slug => $allBooks->values()]
        )->all();

        return [
            'bookNames' => $allBooks,
            'bookNamesByCategory' => $bookNamesByCategory,
            'categories' => $categories,
            'fieldMap' => Category::fieldMap(),
        ];
    }

    /**
     * Default checked subjects: existing links, else GradeSubjectMap for that grade.
     */
    private function linkedByCategoryFor(Grade $grade): array
    {
        $linked = DB::table('grade_book_names')
            ->where('grade_id', $grade->id)
            ->get()
            ->groupBy('category_id');

        $result = [];
        $categories = Category::where('is_active', true)->get();
        $allBooks = BookName::where('is_active', true)->get()->keyBy('name');

        foreach ($categories as $category) {
            $ids = ($linked[$category->id] ?? collect())
                ->pluck('book_name_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($ids === []) {
                $ids = collect(GradeSubjectMap::subjectsFor($grade->name, $category->slug))
                    ->map(fn (string $name) => $allBooks->get($name)?->id)
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            }

            $result[$category->slug] = $ids;
        }

        return $result;
    }

    private function syncSubjectsByCategory(Grade $grade, Request $request): void
    {
        DB::table('grade_book_names')->where('grade_id', $grade->id)->delete();

        $map = [
            Category::TEXTBOOK => $request->input('textbook_book_name_ids', []),
            Category::TEACHER_HANDBOOK => $request->input('teacher_handbook_book_name_ids', []),
            Category::TEACHER_GUIDE => $request->input('teacher_guide_book_name_ids', []),
        ];

        $categories = Category::whereIn('slug', array_keys($map))->get()->keyBy('slug');
        $now = now();
        $rows = [];

        foreach ($map as $slug => $ids) {
            $category = $categories->get($slug);
            if (!$category) {
                continue;
            }

            foreach (array_unique(array_filter($ids)) as $bookId) {
                $rows[] = [
                    'grade_id' => $grade->id,
                    'book_name_id' => (int) $bookId,
                    'category_id' => $category->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows) {
            DB::table('grade_book_names')->insert($rows);
        }
    }
}
