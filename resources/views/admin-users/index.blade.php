@extends('layouts.master')

@section('content')
    <div class="app-page-container">

        <style>
            .badge-role-super {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                padding: 5px 14px !important;
                border-radius: 9999px !important;
                font-size: 12.5px !important;
                font-weight: 600 !important;
                background-color: #f3e8ff !important;
                color: #6b21a8 !important;
                border: 1px solid #d8b4fe !important;
                white-space: nowrap !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }

            .badge-role-admin {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                padding: 5px 14px !important;
                border-radius: 9999px !important;
                font-size: 12.5px !important;
                font-weight: 600 !important;
                background-color: #e0f2fe !important;
                color: #0369a1 !important;
                border: 1px solid #7dd3fc !important;
                white-space: nowrap !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }
        </style>

        {{-- Main Filter & Title Card --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-users text-amber-400"></i>
                    စီမံခန့်ခွဲသူများ စာရင်း
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('admin-users.index') }}" class="m-0">
                    <div class="flex flex-wrap items-end gap-3">
                        <div style="flex: 2; min-width: 200px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">စီမံခန့်ခွဲသူ ရှာဖွေရန်</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search" style="color: #16a34a;"></i>
                                </span>
                                <input type="text" name="search" class="modern-input text-sm font-medium" style="padding-left: 2.25rem;"
                                    placeholder="အမည် သို့မဟုတ် အီးမေးလ် ရှာဖွေရန်..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                            <button type="submit" class="btn-modern-primary">
                                <i class="fas fa-search"></i>
                                ရှာဖွေရန်
                            </button>

                            <a href="{{ route('admin-users.index') }}" class="btn-modern-secondary">
                                <i class="fas fa-redo"></i>
                                ပြန်လည်သတ်မှတ်
                            </a>

                            @if (auth()->user()?->role === 'super')
                                <a href="{{ route('admin-users.create') }}" class="btn-modern-primary">
                                    <i class="fas fa-plus"></i>
                                    ဖန်တီးပါ
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Success / Error Alerts --}}
        @if (session('success'))
            <div class="alert alert-success border-0 rounded-3 my-3 py-2 px-3 shadow-sm" style="background-color: #d1fae5; color: #065f46; font-size: 14px;">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-3 my-3 py-2 px-3 shadow-sm" style="background-color: #fef2f2; color: #991b1b; font-size: 14px;">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Data Table --}}
        <div class="modern-table-container mt-4">
            <table class="modern-table">
                <thead style="background-color: #072a1e; color: #ffffff;">
                    <tr>
                        <th style="width: 80px;">စဉ်</th>
                        <th class="text-left px-6">အမည်</th>
                        <th class="text-left px-6">အီးမေးလ်</th>
                        <th style="width: 180px;">အမျိုးအစား</th>
                        @if (auth()->user()?->role === 'super')
                            <th style="width: 180px;">လုပ်ဆောင်ချက်</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $key => $user)
                        <tr>
                            <td class="font-mono text-slate-500">{{ ($users->firstItem() ?? 1) + $key }}</td>
                            <td class="font-semibold text-slate-800 text-left px-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full {{ $user->role === 'super' ? 'bg-purple-100 border-purple-300 text-purple-700' : 'bg-sky-100 border-sky-300 text-sky-700' }} border flex items-center justify-center font-bold text-xs">
                                        {{ mb_substr($user->name ?? 'A', 0, 1) }}
                                    </div>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="text-slate-600 text-left px-6 font-mono text-xs">
                                {{ $user->email }}
                            </td>
                            <td>
                                @if ($user->role === 'super')
                                    <span class="badge-role-super">
                                        <i class="fas fa-user-shield"></i>
                                        Super Admin
                                    </span>
                                @else
                                    <span class="badge-role-admin">
                                        <i class="fas fa-user-cog"></i>
                                        Admin
                                    </span>
                                @endif
                            </td>
                            @if (auth()->user()?->role === 'super')
                                <td>
                                    <div class="inline-flex items-center gap-2 justify-center">
                                        <a href="{{ route('admin-users.edit', $user->id) }}" class="btn-modern-warning" title="ပြင်ဆင်ပါ">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if ($user->role === 'super' || auth()->id() == $user->id)
                                            <button type="button" class="btn-modern-danger" style="opacity: 0.45; cursor: not-allowed;" title="ဖျက်၍မရပါ (Super Admin)" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('admin-users.destroy', $user->id) }}" method="POST" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn-modern-danger" title="ဖျက်ပါ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->role === 'super' ? 5 : 4 }}" class="text-slate-400 py-8 text-center font-medium">
                                <i class="fas fa-user-slash text-3xl mb-2 block text-slate-300"></i>
                                အချက်အလက် မရှိသေးပါ။
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $users])

    </div>
@endsection
