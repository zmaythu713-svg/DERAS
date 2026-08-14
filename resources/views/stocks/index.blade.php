@extends('layouts.master')

@section('content')
    @php
        $yearLabel = $selectedYear?->name ?? 'ပညာသင်နှစ်';
    @endphp
    <div class="app-page-container">

        {{-- Filter Card: Title + Search --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-boxes text-amber-400"></i>
                    {{ $yearLabel }} ပညာသင်နှစ်အတွက် ယခင်နှစ်လက်ကျန် ၊ လက်ဆင့်ကမ်း နှင့် အပ်နှံပြီးကျောင်းသားစာရင်းအရ
                    လိုအပ်မှုကို (ခရိုင်ရရှိပြီးခွဲတမ်း) မှ ဖြန့်ဝေသည့်စာရင်း
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('stocks.index') }}" id="stockFilterForm" class="m-0">

                    {{-- Top: စာအုပ်ရှာဖွေရန် + actions --}}
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div class="flex flex-wrap items-end gap-3" style="flex: 1; min-width: 260px;">
                            <div style="flex: 1; min-width: 220px; max-width: 360px;">
                                <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">စာအုပ်ရှာဖွေရန်</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-search" style="color: #16a34a;"></i>
                                    </span>
                                    <input type="text" name="search" class="modern-input text-sm font-medium"
                                        style="padding-left: 2.25rem;"
                                        value="{{ $search ?? '' }}"
                                        placeholder="စာအုပ်ရှာရန်...">
                                </div>
                            </div>

                            <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                                <button type="submit" class="btn-modern-primary">
                                    <i class="fas fa-search"></i>
                                    စစ်ထုတ်ပါ
                                </button>
                                <a href="{{ route('stocks.index') }}" class="btn-modern-secondary">
                                    <i class="fas fa-redo"></i>
                                    ပြန်လည်သတ်မှတ်
                                </a>
                            </div>
                        </div>

                        <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                            <button type="button" class="btn-modern-excel" onclick="exportExcel()">
                                <i class="fas fa-file-excel"></i>
                                Excel ထုတ်ပါ
                            </button>
                            @if ($canCreate ?? true)
                                <a href="{{ route('stocks.create', array_filter([
                                        'academic_year_id' => $yearId,
                                        'township_id' => $townshipId,
                                    ])) }}"
                                    class="btn-modern-primary">
                                    <i class="fas fa-plus"></i>
                                    ဖန်တီးပါ
                                </a>
                            @else
                                <span class="btn-modern-primary"
                                    style="opacity: 0.45; cursor: not-allowed; pointer-events: none;"
                                    title="မရောက်သေးသောနှစ် — အချက်အလက် ထည့်မရပါ">
                                    <i class="fas fa-plus"></i>
                                    ဖန်တီးပါ
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom: ပညာသင်နှစ် / မြို့နယ် --}}
                    <div class="flex flex-wrap items-end gap-3 mt-4 pt-3 border-t border-slate-100">
                        <div style="flex: 2; min-width: 160px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a; display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                <span>ပညာသင်နှစ်</span>
                                @if ($selectedYear?->is_current)
                                    <span class="badge-active" style="font-size: 10px; padding: 2px 8px; white-space: nowrap; line-height: 1.4;">
                                        <i class="fas fa-star"></i> Current
                                    </span>
                                @endif
                            </label>
                            <select name="academic_year_id" id="filter_academic_year_id" class="modern-select text-sm font-medium">
                                @foreach ($years as $year)
                                    <option value="{{ $year->id }}"
                                        {{ (string) $yearId === (string) $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 2; min-width: 160px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">မြို့နယ်</label>
                            <select name="township_id" id="filter_township_id" class="modern-select text-sm font-medium">
                                <option value="">မြို့နယ်အားလုံး</option>
                                @foreach ($townships as $township)
                                    <option value="{{ $township->id }}"
                                        {{ (string) $townshipId === (string) $township->id ? 'selected' : '' }}>
                                        {{ $township->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.getElementById('stockFilterForm');
                        if (!form) return;
                        ['filter_academic_year_id', 'filter_township_id'].forEach(function (id) {
                            document.getElementById(id)?.addEventListener('change', function () {
                                form.submit();
                            });
                        });
                    });
                </script>
            </div>
        </div>

        {{-- Data / empty state --}}
        @if ($stocks->isEmpty())
            <div class="modern-card mt-4">
                <div class="modern-card-body p-5 text-center">
                    <i class="fas fa-inbox text-3xl mb-3 block" style="color: #94a3b8;"></i>
                    <p class="mb-0 font-medium text-base" style="color: #475569;">
                        {{ $emptyMessage ?? 'အချက်အလက်မရှိပါ' }}
                    </p>
                </div>
            </div>
        @else
            <div class="modern-table-container mt-4">
                <table class="modern-table stock-table">
                    <thead>
                        <tr>
                            <th>စဉ်</th>
                            <th>ပညာသင်နှစ်</th>
                            <th>မြို့နယ်</th>
                            <th>အတန်း</th>
                            <th>ဘာသာ</th>
                            <th>ယခင်နှစ်လက်ကျန်</th>
                            <th>လက်ဆင့်ကမ်း</th>
                            <th>အပ်နှံပြီးလိုအပ်မှု</th>
                            <th>လိုအပ်မှု</th>
                            <th>မှတ်ချက်</th>
                            <th>လုပ်ဆောင်ချက်</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($stocks as $key => $stock)
                            <tr>
                                <td class="font-mono text-slate-500">{{ $key + 1 }}</td>
                                <td class="whitespace-nowrap font-medium text-slate-800">{{ $stock->academicYear?->name }}</td>
                                <td class="whitespace-nowrap font-medium text-slate-800">{{ $stock->township?->name }}</td>
                                <td class="whitespace-nowrap font-medium text-slate-800">{{ $stock->grade?->name }}</td>
                                <td class="text-left font-medium text-slate-800">{{ $stock->bookName?->name }}</td>
                                <td class="font-mono text-slate-600">{{ number_format($stock->previous_balance) }}</td>
                                <td class="font-mono text-slate-600">{{ number_format($stock->transferred) }}</td>
                                <td class="font-mono text-slate-600">{{ number_format($stock->enrolled_need) }}</td>
                                <td class="font-mono font-semibold text-emerald-700">{{ number_format($stock->required_qty) }}</td>
                                <td class="text-slate-500 text-xs">{{ $stock->remark }}</td>
                                <td class="whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('stocks.edit', $stock->id) }}" class="btn-modern-warning" title="ပြင်ဆင်ပါ">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if (auth()->user()?->canDeleteRecords())
                                            <form action="{{ route('stocks.destroy', $stock->id) }}" method="POST" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-modern-danger" title="ဖျက်ပါ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>
    <script>
        async function exportExcel() {
            const workbook = new ExcelJS.Workbook();

            const sheet = workbook.addWorksheet('Stocks');

            sheet.columns = [{
                    width: 8
                },
                {
                    width: 18
                },
                {
                    width: 18
                },
                {
                    width: 15
                },
                {
                    width: 30
                },
                {
                    width: 16
                },
                {
                    width: 16
                },
                {
                    width: 18
                },
                {
                    width: 16
                },
                {
                    width: 25
                },
            ];

            sheet.mergeCells('A1:J1');

            sheet.getCell('A1').value =
                @json(($selectedYear?->name ?? '') . ' ပညာသင်နှစ်အတွက် ယခင်နှစ်လက်ကျန် ၊ လက်ဆင့်ကမ်း နှင့် အပ်နှံပြီးကျောင်းသားစာရင်းအရ လိုအပ်မှုကို (ခရိုင်ရရှိပြီးခွဲတမ်း) မှ ဖြန့်ဝေသည့်စာရင်း');

            sheet.getCell('A1').font = {
                bold: true,
                size: 16
            };

            sheet.getCell('A1').alignment = {
                horizontal: 'center',
                vertical: 'middle',
                wrapText: true
            };

            sheet.getRow(1).height = 40;

            sheet.addRow([]);

            sheet.addRow([
                'စဉ်',
                'ပညာသင်နှစ်',
                'မြို့နယ်',
                'အတန်း',
                'ဘာသာ',
                'ယခင်နှစ်လက်ကျန်',
                'လက်ဆင့်ကမ်း',
                'အပ်နှံပြီးလိုအပ်မှု',
                'လိုအပ်မှု',
                'မှတ်ချက်'
            ]);

            @foreach ($stocks as $key => $stock)
                sheet.addRow([
                    {{ $key + 1 }},
                    {!! json_encode($stock->academicYear?->name) !!},
                    {!! json_encode($stock->township?->name) !!},
                    {!! json_encode($stock->grade?->name) !!},
                    {!! json_encode($stock->bookName?->name) !!},
                    {{ $stock->previous_balance ?? 0 }},
                    {{ $stock->transferred ?? 0 }},
                    {{ $stock->enrolled_need ?? 0 }},
                    {{ $stock->required_qty ?? 0 }},
                    {!! json_encode($stock->remark) !!}
                ]);
            @endforeach

            sheet.eachRow((row, rowNumber) => {
                row.eachCell((cell) => {
                    cell.alignment = {
                        horizontal: 'center',
                        vertical: 'middle',
                        wrapText: true
                    };
                    cell.border = {
                        top: { style: 'thin' },
                        left: { style: 'thin' },
                        bottom: { style: 'thin' },
                        right: { style: 'thin' }
                    };
                });

                if (rowNumber == 3) {
                    row.font = { bold: true };
                }
            });

            sheet.getColumn(5).alignment = {
                horizontal: 'left',
                vertical: 'middle',
                wrapText: true
            };

            const buffer = await workbook.xlsx.writeBuffer();

            saveAs(
                new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }),
                'stocks.xlsx'
            );
        }
    </script>
@endsection

@push('styles')
    <style>
        body {
            background: #f4f6f8;
        }

        .stock-table {
            font-size: 13px;
            background: #ffffff;
            color: #4f5870;
        }

        .stock-table th,
        .stock-table td {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            padding: 6px 8px;
        }

        .stock-table thead th {
            background: #e5e5ea;
            font-weight: 700;
            white-space: nowrap;
        }

        .stock-table tbody tr:hover {
            background: #f1f8f4;
        }
    </style>
@endpush
