<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $grades = $query->ordered()->paginate(config('deras.pagination_per_page'))->withQueryString();

        return view('grades.index', compact('grades'));
    }

    public function create()
    {
        return view('grades.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:grades,name',
            'is_active' => 'required|boolean',
        ]);

        Grade::create($data);

        return redirect()->route('grades.index')
            ->with('success', 'အတန်း သိမ်းဆည်းပြီးပါပြီ။');
    }

    public function edit(Grade $grade)
    {
        return view('grades.edit', compact('grade'));
    }

    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'name' => 'required|unique:grades,name,' . $grade->id,
            'is_active' => 'required|boolean',
        ]);

        $grade->update($data);

        return redirect()->route('grades.index')
            ->with('success', 'အတန်း ပြင်ဆင်ပြီးပါပြီ။');
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
}
