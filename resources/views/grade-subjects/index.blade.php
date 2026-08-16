@extends('layouts.master')

@section('content')
    @php
        $categoryOrder = ['textbook', 'teacher_handbook', 'teacher_guide'];
        $orderedCategories = collect($categoryOrder)
            ->map(fn ($slug) => $categories->firstWhere('slug', $slug))
            ->filter();
    @endphp

    <div class="app-page-container">

        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-link text-amber-400"></i>
                    အတန်း–ဘာသာရပ် စာရင်း
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('grade-subjects.index') }}" class="m-0">
                    <div class="flex flex-wrap items-end gap-3">
                        <div style="flex: 2; min-width: 200px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">အတန်း ရှာဖွေရန်</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search" style="color: #16a34a;"></i>
                                </span>
                                <input type="text" name="search" class="modern-input text-sm font-medium" style="padding-left: 2.25rem;"
                                    placeholder="အတန်းရှာဖွေရန်..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                            <button type="submit" class="btn-modern-primary">
                                <i class="fas fa-search"></i>
                                ရှာဖွေရန်
                            </button>

                            <a href="{{ route('grade-subjects.index') }}" class="btn-modern-secondary">
                                <i class="fas fa-redo"></i>
                                ပြန်လည်သတ်မှတ်
                            </a>

                            <a href="{{ route('grade-subjects.create') }}" class="btn-modern-primary">
                                <i class="fas fa-plus"></i>
                                ဖန်တီးပါ
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modern-table-container mt-4">
            <table class="modern-table" style="min-width: 1100px;">
                <thead style="background-color: #072a1e; color: #ffffff;">
                    <tr>
                        <th style="width: 50px;">စဉ်</th>
                        <th style="width: 110px;">အတန်းအမည်</th>
                        @foreach ($orderedCategories as $category)
                            <th>{{ $category->name_mm }}</th>
                        @endforeach
                        <th style="width: 130px;">အခြေအနေ</th>
                        <th style="width: 100px;">လုပ်ဆောင်ချက်</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($grades as $key => $grade)
                        @php
                            $grouped = $grade->bookNames->groupBy(fn ($s) => (int) $s->pivot->category_id);
                        @endphp
                        <tr>
                            <td class="font-mono text-slate-500">{{ ($grades->firstItem() ?? 1) + $key }}</td>
                            <td class="font-semibold text-slate-800 text-center">{{ $grade->name }}</td>

                            @foreach ($orderedCategories as $category)
                                @php
                                    $subjects = ($grouped[$category->id] ?? collect())->unique('id')->values();
                                @endphp
                                <td class="text-left align-top" style="padding: 10px 14px;">
                                    @if ($subjects->isNotEmpty())
                                        <ul style="margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:4px;">
                                            @foreach ($subjects as $subject)
                                                <li style="display:flex; align-items:baseline; gap:6px; font-size:13px; color:#1e293b; line-height:1.5;">
                                                    <span style="color:#16a34a; font-size:10px; flex-shrink:0;">●</span>
                                                    <span>{{ $subject->name }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span style="color:#94a3b8; font-size:18px; display:block; text-align:center;">—</span>
                                    @endif
                                </td>
                            @endforeach

                            <td>
                                @if ($grade->is_active)
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
                                    <a href="{{ route('grade-subjects.edit', $grade->id) }}" class="btn-modern-warning" title="ပြင်ဆင်ရန်">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    @if (auth()->user()?->canDeleteRecords())
                                        <form action="{{ route('grade-subjects.destroy', $grade->id) }}" method="POST"
                                            class="d-inline m-0"
                                            onsubmit="return confirm('ဤအတန်း၏ ဘာသာရပ် တွဲချိတ်မှုအားလုံး ဖျက်မလား?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-modern-danger" title="ဖျက်ရန်">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $orderedCategories->count() }}" class="text-slate-400 py-8 text-center font-medium">
                                <i class="fas fa-inbox text-3xl mb-2 block text-slate-300"></i>
                                အချက်အလက် မရှိသေးပါ။ အရင် အတန်းများ စာမျက်နှာမှ အတန်း ဖန်တီးပါ။
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $grades])

    </div>
@endsection
