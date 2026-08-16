<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TextName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        $users = $query->latest()->paginate(config('deras.pagination_per_page'))->withQueryString();

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
            // New accounts may only be Admin (Super is seeded / reserved).
            'role'     => 'required|in:admin',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => 'admin',
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
        $rules = [
            'name' => ['required', 'string', 'max:255', TextName::validationRule('အမည်')],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($adminUser->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // Existing Super Admin: role is locked (cannot demote/promote via form).
        // Admin accounts: role stays admin only (cannot promote to Super).
        if ($adminUser->isSuper()) {
            $rules['role'] = 'nullable|in:super';
        } else {
            $rules['role'] = 'required|in:admin';
        }

        $data = $request->validate($rules);

        if (!$adminUser->isSuper() && ($data['role'] ?? '') === 'super') {
            throw ValidationException::withMessages([
                'role' => 'Admin အကောင့်ကို Super Admin သို့ ပြောင်း၍မရပါ။',
            ]);
        }

        $adminUser->name = $data['name'];
        $adminUser->email = $data['email'];

        if ($adminUser->isSuper()) {
            // Keep Super role unchanged
            $adminUser->role = 'super';
        } else {
            $adminUser->role = 'admin';
        }

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
            return back()->with('error', 'ကိုယ့်အကောင့်ကို ဖျက်၍မရပါ။');
        }

        if ($adminUser->isSuper()) {
            return back()->with('error', 'Super Admin အကောင့်ကို ဖျက်၍မရပါ။');
        }

        $adminUser->delete();

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'အောင်မြင်စွာဖျက်လိုက်ပါပြီ');
    }
}
