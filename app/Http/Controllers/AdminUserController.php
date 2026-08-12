<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TextName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->get();

        return view('admin-users.index', compact('users'));
    }

    public function create()
    {
        return view('admin-users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255', TextName::validationRule('အမည်')],
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:super,admin',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ');
    }

    public function edit(User $adminUser)
    {
        return view('admin-users.edit', [
            'user' => $adminUser,
        ]);
    }

    public function update(Request $request, User $adminUser)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', TextName::validationRule('အမည်')],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($adminUser->id),
            ],

            'role' => 'required|in:super,admin',

            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $adminUser->name = $data['name'];
        $adminUser->email = $data['email'];
        $adminUser->role = $data['role'];

        if (!empty($data['password'])) {
            $adminUser->password = Hash::make($data['password']);
        }

        $adminUser->save();

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ');
    }

    public function destroy(User $adminUser)
    {
        if ($adminUser->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $adminUser->delete();

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'အောင်မြင်စွာဖျက်လိုက်ပါပြီ');
    }
}
