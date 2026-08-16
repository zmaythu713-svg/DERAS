<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\AcademicYearRollover;
use Illuminate\Http\Request;
use Throwable;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicYear::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $years = $query
            ->orderByDesc('start_year')
            ->orderByDesc('id')
            ->paginate(config('deras.pagination_per_page'))
            ->withQueryString();

        return view('academic-years.index', compact('years'));
    }

    public function create()
    {
        return view('academic-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:academic_years,name',
            'is_active' => 'required|boolean',
        ]);

        $startYear = null;
        $endYear = null;
        if (preg_match('/^(\d{4})\s*-\s*(\d{4})$/', $request->name, $m)) {
            $startYear = (int) $m[1];
            $endYear = (int) $m[2];
        }

        AcademicYear::create([
            'name' => $request->name,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'is_active' => $request->is_active,
            'is_current' => false,
            'status' => AcademicYear::STATUS_ACTIVE,
        ]);

        return redirect()->route('academic-years.index')
            ->with('success', 'ပညာသင်နှစ် သိမ်းဆည်းပြီးပါပြီ။');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|unique:academic_years,name,' . $academicYear->id,
            'is_active' => 'required|boolean',
        ]);

        $startYear = $academicYear->start_year;
        $endYear = $academicYear->end_year;
        if (preg_match('/^(\d{4})\s*-\s*(\d{4})$/', $request->name, $m)) {
            $startYear = (int) $m[1];
            $endYear = (int) $m[2];
        }

        $academicYear->update([
            'name' => $request->name,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('academic-years.index')
            ->with('success', 'ပညာသင်နှစ် ပြင်ဆင်ပြီးပါပြီ။');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_current) {
            return redirect()->route('academic-years.index')
                ->with('error', 'လက်ရှိ ပညာသင်နှစ်ကို ဖျက်မရပါ။');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'ပညာသင်နှစ် ဖျက်ပြီးပါပြီ။');
    }

    /**
     * Year rollover: close current year, open next, carry ယခင်နှစ်လက်ကျန်.
     */
    public function rollover(Request $request, AcademicYearRollover $rollover)
    {
        try {
            $from = $request->filled('academic_year_id')
                ? AcademicYear::findOrFail($request->academic_year_id)
                : null;

            $newYear = $rollover->rollover($from);

            return redirect()->route('academic-years.index')
                ->with(
                    'success',
                    "နှစ်သစ် {$newYear->name} ဖွင့်ပြီး ယခင်နှစ်လက်ကျန်များကို အလိုအလျောက် သယ်ဆောင်ပြီးပါပြီ။"
                );
        } catch (Throwable $e) {
            return redirect()->route('academic-years.index')
                ->with('error', $e->getMessage());
        }
    }
}
