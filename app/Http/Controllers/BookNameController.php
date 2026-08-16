<?php

namespace App\Http\Controllers;

use App\Models\BookName;
use Illuminate\Http\Request;

class BookNameController extends Controller
{
    public function index(Request $request)
    {
        $query = BookName::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $bookNames = $query->latest()->paginate(config('deras.pagination_per_page'))->withQueryString();

        return view('book-names.index', compact('bookNames'));
    }

    public function create()
    {
        return view('book-names.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:book_names,name',
            'is_active' => 'required|boolean',
        ]);

        BookName::create($request->only('name', 'is_active'));

        return redirect()->route('book-names.index');
    }

    public function edit(BookName $bookName)
    {
        return view('book-names.edit', compact('bookName'));
    }

    public function update(Request $request, BookName $bookName)
    {
        $request->validate([
            'name' => 'required|unique:book_names,name,' . $bookName->id,
            'is_active' => 'required|boolean',
        ]);

        $bookName->update($request->only('name', 'is_active'));

        return redirect()->route('book-names.index');
    }

    public function destroy(BookName $bookName)
    {
        $bookName->delete();

        return redirect()->route('book-names.index');
    }
}
