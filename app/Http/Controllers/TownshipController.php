<?php

namespace App\Http\Controllers;

use App\Models\Township;
use App\Support\TextName;
use Illuminate\Http\Request;

class TownshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Township::real();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $townships = $query
            ->orderByRaw("FIELD(name, 'မြန်အောင်', 'ကြံခင်း', 'အင်္ဂပူ')")
            ->orderBy('name')
            ->get();

        return view('townships.index', compact('townships'));
    }

    public function create()
    {
        return view('townships.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:townships,name', TextName::validationRule('မြို့နယ်အမည်')],
            'is_active' => 'required|boolean',
        ]);

        Township::create([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('townships.index');
    }

    public function edit(Township $township)
    {
        return view('townships.edit', compact('township'));
    }

    public function update(Request $request, Township $township)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:townships,name,' . $township->id, TextName::validationRule('မြို့နယ်အမည်')],
            'is_active' => 'required|boolean',
        ]);

        $township->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('townships.index');
    }

    public function destroy(Township $township)
    {
        $township->delete();

        return redirect()->route('townships.index');
    }
}
