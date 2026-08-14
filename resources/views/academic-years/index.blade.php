@extends('layouts.master')

@section('content')
    <div class="app-page-container">

        {{-- Main Filter & Title Card --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-calendar-alt text-amber-400"></i>
                    ပညာသင်နှစ်များ စာရင်း
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('academic-years.index') }}" class="m-0">
                    <div class="flex flex-wrap items-end gap-3">
                        <div style="flex: 2; min-width: 200px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">ပညာသင်နှစ် ရှာဖွေရန်</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search" style="color: #16a34a;"></i>
                                </span>
                                <input type="text" name="search" class="modern-input text-sm font-medium" style="padding-left: 2.25rem;"
                                    placeholder="ပညာသင်နှစ်ကို ရှာဖွေပါ..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                            <button type="submit" class="btn-modern-primary">
                                <i class="fas fa-search"></i>
                                ရှာဖွေရန်
                            </button>

                            <a href="{{ route('academic-years.index') }}" class="btn-modern-secondary">
                                <i class="fas fa-redo"></i>
                                ပြန်လည်သတ်မှတ်
                            </a>

                            <a href="{{ route('academic-years.create') }}" class="btn-modern-primary">
                                <i class="fas fa-plus"></i>
                                ဖန်တီးပါ
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Data Table (outside card, below) --}}
        <div class="modern-table-container mt-4">
            <table class="modern-table">
                <thead style="background-color: #072a1e; color: #ffffff;">
                    <tr>
                        <th style="width: 80px;">စဉ်</th>
                        <th>ပညာသင်နှစ်များ</th>
                        <th style="width: 140px;">လက်ရှိနှစ်</th>
                        <th style="width: 140px;">အခြေအနေ</th>
                        <th style="width: 180px;">လုပ်ဆောင်ချက်</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($years as $key => $year)
                        <tr>
                            <td class="font-mono text-slate-500">{{ $key + 1 }}</td>
                            <td class="font-bold text-slate-800 text-center">
                                {{ $year->name }}
                            </td>
                            <td>
                                @if ($year->is_current)
                                    <span class="badge-active">
                                        <i class="fas fa-star"></i>
                                        Current
                                    </span>
                                @else
                                    <span class="text-slate-400 text-sm">—</span>
                                @endif
                            </td>
                            <td>
                                @if (($year->status ?? 'active') === 'closed')
                                    <span class="badge-inactive">
                                        <i class="fas fa-lock"></i>
                                        ပိတ်ထားသည်
                                    </span>
                                @elseif ($year->is_active)
                                    <span class="badge-active">
                                        <i class="fas fa-check-circle"></i>
                                        အသုံးပြုနေသည်
                                    </span>
                                @else
                                    <span class="badge-inactive">
                                        <i class="fas fa-times-circle"></i>
                                        မသုံးတော့ပါ
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="inline-flex items-center gap-2 justify-center">
                                    <a href="{{ route('academic-years.edit', $year->id) }}" class="btn-modern-warning" title="ပြင်ဆင်ရန်">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    @if (auth()->user()?->canDeleteRecords())
                                        <form action="{{ route('academic-years.destroy', $year->id) }}" method="POST"
                                            class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn-modern-danger" title="ဖျက်ရန်">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-slate-400 py-8 text-center font-medium">
                                <i class="fas fa-inbox text-3xl mb-2 block text-slate-300"></i>
                                အချက်အလက် မရှိသေးပါ။
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
