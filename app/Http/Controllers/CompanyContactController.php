<?php

namespace App\Http\Controllers;

use App\Models\CompanyContact;
use App\Support\MyanmarPhone;
use App\Support\TextName;
use Illuminate\Http\Request;

class CompanyContactController extends Controller
{
    public function index(Request $request)
    {
        $query = CompanyContact::query();

        if ($request->search) {
            $query->where('company_name', 'like', "%{$request->search}%")
                ->orWhere('responsible_name', 'like', "%{$request->search}%");
        }

        $data = $query->orderBy('id', 'desc')->get();

        return view('company-contacts.index', compact('data'));
    }

    public function create()
    {
        return view('company-contacts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        CompanyContact::create($data);

        return redirect()->route('company-contacts.index')
            ->with('success', 'အောင်မြင်စွာဖန်တီးပြီးပါပြီ');
    }

    public function edit(CompanyContact $companyContact)
    {
        return view('company-contacts.edit', compact('companyContact'));
    }

    public function update(Request $request, CompanyContact $companyContact)
    {
        $companyContact->update($this->validated($request));

        return redirect()->route('company-contacts.index')
            ->with('success', 'အောင်မြင်စွာပြင်ဆင်ပြီးပါပြီ');
    }

    public function destroy(CompanyContact $companyContact)
    {
        $companyContact->delete();

        return redirect()->route('company-contacts.index')
            ->with('success', 'အောင်မြင်စွာဖျက်ပြီးပါပြီ');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'min:2', 'max:255', TextName::validationRule('ကုမ္ပဏီအမည်')],
            'lot' => ['required', 'string', 'min:1', 'max:255'],
            'responsible_name' => ['required', 'string', 'min:2', 'max:255', TextName::validationRule('တာဝန်ခံအမည်')],
            'phone' => ['required', 'string', 'max:30', MyanmarPhone::validationRule()],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'company_name.required' => 'ကုမ္ပဏီအမည် ဖြည့်သွင်းရန် လိုအပ်ပါသည်။',
            'company_name.min' => 'ကုမ္ပဏီအမည် အနည်းဆုံး ၂ လုံး ဖြည့်ပါ။',
            'lot.required' => 'Lot ဖြည့်သွင်းရန် လိုအပ်ပါသည်။',
            'responsible_name.required' => 'တာဝန်ခံအမည် ဖြည့်သွင်းရန် လိုအပ်ပါသည်။',
            'responsible_name.min' => 'တာဝန်ခံအမည် အနည်းဆုံး ၂ လုံး ဖြည့်ပါ။',
            'phone.required' => 'ဖုန်းနံပါတ် ဖြည့်သွင်းရန် လိုအပ်ပါသည်။',
        ]);

        $data['phone'] = MyanmarPhone::normalize($data['phone']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}