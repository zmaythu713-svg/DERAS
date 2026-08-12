<?php

namespace App\Http\Controllers;

use App\Models\BookName;
use App\Models\Category;
use App\Models\Grade;
use App\Support\GradeSubjectMap;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
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

        $grades = $query
            ->ordered()
            ->get();

        $categories = Category::where('is_active', true)
            ->orderByRaw("FIELD(slug, 'textbook', 'teacher_handbook', 'teacher_guide')")
            ->orderBy('id')
            ->get();

        return view('grades.index', [
            'grades' => $grades,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $hint = old('name') ? new Grade(['name' => old('name')]) : null;

        return view('grades.create', $this->formData($hint));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $grade = Grade::create($request->only('name', 'is_active'));
        $this->syncSubjectsByCategory($grade, $request);

        return redirect()->route('grades.index')
            ->with('success', 'အတန်းနှင့် ဘာသာရပ်များ သိမ်းဆည်းပြီးပါပြီ။');
    }

    public function edit(Grade $grade)
    {
        $data = $this->formData($grade);
        $data['grade'] = $grade;

        $linked = DB::table('grade_book_names')
            ->where('grade_id', $grade->id)
            ->get()
            ->groupBy('category_id');

        $data['linkedByCategory'] = [];
        foreach (Category::where('is_active', true)->get() as $category) {
            $data['linkedByCategory'][$category->slug] = ($linked[$category->id] ?? collect())
                ->pluck('book_name_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('grades.edit', $data);
    }

    public function update(Request $request, Grade $grade)
    {
        $request->validate($this->rules($grade));

        $grade->update($request->only('name', 'is_active'));
        $this->syncSubjectsByCategory($grade, $request);

        return redirect()->route('grades.index')
            ->with('success', 'အတန်းနှင့် ဘာသာရပ်များ ပြင်ဆင်ပြီးပါပြီ။');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();

        return redirect()->route('grades.index')
            ->with('success', 'အတန်း ဖျက်သိမ်းပြီးပါပြီ။');
    }

    public function getSubjects(Request $request, Grade $grade)
    {
        return app(ResourceLookupController::class)->subjects($request, $grade);
    }

    private function formData(?Grade $grade = null): array
    {
        $categories = Category::where('is_active', true)->orderBy('id')->get();
        $allBooks = BookName::where('is_active', true)->orderBy('name')->get();

        return [
            // Create: full catalog. Edit: only that grade's subjects per category.
            'bookNames' => $allBooks,
            'bookNamesByCategory' => $this->bookNamesByCategory($categories, $allBooks, $grade),
            'categories' => $categories,
            'fieldMap' => Category::fieldMap(),
        ];
    }

    /**
     * Per-category subject checklist for a grade (KG → only KG subjects, etc.).
     * Create form (no grade): full catalog in every section.
     */
    private function bookNamesByCategory(Collection $categories, Collection $allBooks, ?Grade $grade): array
    {
        if (!$grade || !$grade->name) {
            $list = $allBooks->values();

            return $categories->mapWithKeys(
                fn (Category $category) => [$category->slug => $list]
            )->all();
        }

        $linkedIdsByCategory = $grade->exists
            ? DB::table('grade_book_names')
                ->where('grade_id', $grade->id)
                ->get()
                ->groupBy('category_id')
                ->map(fn ($rows) => $rows->pluck('book_name_id')->map(fn ($id) => (int) $id)->all())
            : collect();

        $byName = $allBooks->keyBy('name');
        $byId = $allBooks->keyBy('id');
        $result = [];

        foreach ($categories as $category) {
            $names = GradeSubjectMap::subjectsFor($grade->name, $category->slug);
            $books = collect($names)
                ->map(fn (string $name) => $byName->get($name))
                ->filter();

            // Keep any custom links not in the canonical map
            foreach ($linkedIdsByCategory->get($category->id, []) as $bookId) {
                $book = $byId->get($bookId);
                if ($book && !$books->contains('id', $book->id)) {
                    $books->push($book);
                }
            }

            $result[$category->slug] = $books->sortBy('name', SORT_NATURAL)->values();
        }

        return $result;
    }

    private function rules(?Grade $grade = null): array
    {
        return [
            'name' => 'required|unique:grades,name' . ($grade ? ',' . $grade->id : ''),
            'is_active' => 'required|boolean',
            'textbook_book_name_ids' => 'nullable|array',
            'textbook_book_name_ids.*' => 'exists:book_names,id',
            'teacher_handbook_book_name_ids' => 'nullable|array',
            'teacher_handbook_book_name_ids.*' => 'exists:book_names,id',
            'teacher_guide_book_name_ids' => 'nullable|array',
            'teacher_guide_book_name_ids.*' => 'exists:book_names,id',
        ];
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
