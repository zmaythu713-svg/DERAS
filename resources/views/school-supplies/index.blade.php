@extends('layouts.master')

@section('content')
    @php
        $yearLabel = $selectedYear?->name ?? '';
    @endphp
    <div class="app-page-container">

        {{-- Filter Card: Title + Search --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-boxes text-amber-400"></i>
                    @if ($yearLabel)
                        {{ $yearLabel }} ပညာသင်နှစ်အတွက် သင်ထောက်ကူပစ္စည်းများ ဖြန့်ဝေမည့် ခွဲတမ်းတွက်ချက်မှု
                    @else
                        သင်ထောက်ကူပစ္စည်းများ ဖြန့်ဝေမည့် ခွဲတမ်းတွက်ချက်မှု
                    @endif
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('school-supplies.index') }}" id="schoolSupplyFilterForm" class="m-0">
                    <div class="flex flex-wrap items-end justify-between gap-3">

                        <div class="flex flex-wrap items-end gap-3" style="flex: 1; min-width: 260px;">
                            <div style="min-width: 200px; max-width: 260px; flex: 1;">
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

                            <div style="min-width: 140px; max-width: 200px; flex: 1;">
                                <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">အတန်း</label>
                                <select name="grade_id" id="filter_grade_id" class="modern-select text-sm font-medium">
                                    <option value="">အတန်းအားလုံး</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}"
                                            {{ (string) $gradeId === (string) $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="min-width: 140px; max-width: 200px; flex: 1;">
                                <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">မြို့နယ်</label>
                                <select name="township_id" id="filter_township_id" class="modern-select text-sm font-medium">
                                    <option value="" {{ $townshipId === '' || $townshipId === null ? 'selected' : '' }}>
                                        မြို့နယ်အားလုံး
                                    </option>
                                    @foreach ($townships as $township)
                                        <option value="{{ $township->id }}"
                                            {{ (string) $townshipId === (string) $township->id ? 'selected' : '' }}>
                                            {{ $township->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-end gap-2 ms-auto" style="padding-bottom: 1px;">
                            <a href="{{ route('school-supplies.index') }}" class="btn-modern-secondary">
                                <i class="fas fa-redo"></i>
                                ပြန်လည်သတ်မှတ်
                            </a>
                            <button type="button" class="btn-modern-excel" onclick="exportSchoolSupplies()">
                                <i class="fas fa-file-excel"></i>
                                Excel ထုတ်ပါ
                            </button>
                            @if ($canCreate ?? true)
                                <a href="{{ route('school-supplies.create', array_filter([
                                        'academic_year_id' => $yearId,
                                        'grade_id' => $gradeId,
                                        'township_id' => $townshipId,
                                    ], fn ($v) => $v !== null && $v !== '')) }}"
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
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.getElementById('schoolSupplyFilterForm');
                        if (!form) return;
                        ['filter_academic_year_id', 'filter_grade_id', 'filter_township_id'].forEach(function (id) {
                            document.getElementById(id)?.addEventListener('change', function () {
                                form.submit();
                            });
                        });
                    });
                </script>
            </div>
        </div>

        @php
            $exportRows = [];

            foreach ($allocations as $key => $row) {
                $exportRows[] = [
                    'sequence' => ($allocations->firstItem() ?? 1) + $key,
                    'academic_year' => $row->academicYear?->name,
                    'grade' => $row->grade?->name,
                    'region' => $row->region,
                    'township' => in_array($row->row_label, \App\Models\Township::EXCLUDED_NAMES, true) || $row->row_type === 'total'
                        ? 'ခရိုင်စုစုပေါင်း'
                        : ($row->township?->name ?? $row->row_label),
                    'item' => $row->item?->name,
                    'rate' => $row->item?->rate,
                    'school_count' => $row->school_count,
                    'quantity' => $row->quantity,
                    'row_type' => $row->row_type,
                ];
            }
        @endphp

        @if ($allocations->isEmpty())
            <div class="modern-card mt-4">
                <div class="modern-card-body p-5 text-center">
                    <i class="fas fa-inbox text-3xl mb-3 block" style="color: #94a3b8;"></i>
                    <p class="mb-0 font-medium text-base" style="color: #475569;">
                        {{ $emptyMessage ?? 'အချက်အလက်မရှိပါ' }}
                    </p>
                </div>
            </div>
        @else
            {{-- Data Table (outside card, below) --}}
            <div class="modern-table-container mt-4">
                <table class="modern-table school-supplies-table">
                    <thead>
                        <tr>
                            <th>စဉ်</th>
                            <th>ပညာသင်နှစ်</th>
                            <th>အတန်း</th>
                            <th>မြို့နယ်များ</th>
                            <th>ပစ္စည်းများ</th>
                            <th>နှုန်း</th>
                            <th>ကျောင်းအရေအတွက်</th>
                            <th>အရေအတွက်</th>
                            <th>လုပ်ဆောင်ချက်</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($allocations as $key => $row)
                            <tr>
                                <td class="font-mono text-slate-500">{{ ($allocations->firstItem() ?? 1) + $key }}</td>
                                <td class="whitespace-nowrap font-medium text-slate-800">{{ $row->academicYear?->name }}</td>
                                <td class="whitespace-nowrap font-medium text-slate-800">{{ $row->grade?->name }}</td>
                                <td class="whitespace-nowrap font-medium text-slate-800">
                                    @if ($row->row_type === 'total' || in_array($row->row_label, \App\Models\Township::EXCLUDED_NAMES, true))
                                        ခရိုင်စုစုပေါင်း
                                    @else
                                        {{ $row->township?->name ?? $row->row_label }}
                                    @endif
                                </td>
                                <td class="text-left font-medium text-slate-800">{{ $row->item?->name }}</td>
                                <td class="font-mono text-slate-600">{{ number_format($row->item?->rate ?? 0) }}</td>
                                <td class="font-mono text-slate-600">{{ number_format($row->school_count ?? 0) }}</td>
                                <td class="font-mono font-semibold text-emerald-700">{{ number_format(($row->item?->rate ?? 0) * ($row->school_count ?? 0)) }}</td>
                                <td class="whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('school-supplies.edit', $row->id) }}" class="btn-modern-warning" title="ပြင်ဆင်ပါ">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if (auth()->user()?->canDeleteRecords())
                                            <form action="{{ route('school-supplies.destroy', $row->id) }}" method="POST" class="d-inline m-0">
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
            @include('partials.pagination', ['items' => $allocations])
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>
    <script>
        async function exportSchoolSupplies() {
            const rows = @json($exportRows);
            const selectedYear = @json($selectedYear?->name ?? '');

            const workbook = new ExcelJS.Workbook();
            const sheet = workbook.addWorksheet('School Supplies');

            sheet.views = [{ showGridLines: true }];

            sheet.columns = [
                { width: 8 }, { width: 18 }, { width: 15 }, { width: 20 },
                { width: 25 }, { width: 12 }, { width: 18 }, { width: 18 },
            ];

            sheet.mergeCells('A1:H1');
            sheet.getCell('A1').value =
                (selectedYear ? selectedYear + ' ' : '') +
                'ပညာသင်နှစ်အတွက် သင်ထောက်ကူပစ္စည်းများ ဖြန့်ဝေမည့် ခွဲတမ်းတွက်ချက်မှု';
            sheet.getCell('A1').font = { bold: true, size: 14 };
            sheet.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };

            sheet.getRow(3).values = ['စဉ်','ပညာသင်နှစ်','အတန်း','မြို့နယ်များ','ပစ္စည်းများ','နှုန်း','ကျောင်းအရေအတွက်','အရေအတွက်'];

            rows.forEach((row, index) => {
                sheet.getRow(index + 4).values = [
                    row.sequence, row.academic_year, row.grade, row.township,
                    row.item, row.rate, row.school_count, (row.rate || 0) * (row.school_count || 0),
                ];
            });

            sheet.eachRow((row, rowNumber) => {
                row.eachCell((cell) => {
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    cell.border = { top:{style:'thin'}, left:{style:'thin'}, bottom:{style:'thin'}, right:{style:'thin'} };
                    if (rowNumber === 3) {
                        cell.font = { bold: true };
                        cell.fill = { type:'pattern', pattern:'solid', fgColor:{argb:'FFD9EAD3'} };
                    }
                });
            });

            sheet.getRow(1).height = 35;
            sheet.getRow(3).height = 35;

            const buffer = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buffer]), `school_supplies_${selectedYear || 'all'}.xlsx`);
        }
    </script>
@endsection

@push('styles')
    <style>
        body {
            background: #f4f6f8;
        }

        .school-supplies-table {
            font-size: 13px;
            background: #ffffff;
            color: #4f5870;
        }

        .school-supplies-table th,
        .school-supplies-table td {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            padding: 6px 8px;
        }

        .school-supplies-table thead th {
            background: #e5e5ea;
            font-weight: 700;
            white-space: nowrap;
        }

        .school-supplies-table tbody tr:hover {
            background: #f1f8f4;
        }
    </style>
@endpush
