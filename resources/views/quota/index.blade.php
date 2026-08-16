@extends('layouts.master')

@section('content')
    <div class="app-page-container">

        {{-- Filter Card --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-calculator text-amber-400"></i>
                    ခဲတံ၊ ဘောပင်၊ ဝတ်စုံအတွက် ကျောင်းသားဦးရေတွက်ချက်မှု
                </h5>
                <span class="text-xs bg-emerald-950/50 text-amber-300 px-3 py-1 rounded-full font-semibold">
                    {{ $academicYear }} ပညာသင်နှစ်
                    @if ($selectedYear?->is_current)
                        · Current
                    @endif
                </span>
            </div>

            <div class="modern-card-body">
                <!-- Filter & Actions -->
                <form method="GET" action="{{ route('quota.index') }}" id="quotaFilterForm" class="m-0 mb-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <div>
                            <label class="label-year-select" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                <span>ပညာသင်နှစ်</span>
                                @if ($selectedYear?->is_current)
                                    <span class="badge-active" style="font-size: 10px; padding: 2px 8px; white-space: nowrap; line-height: 1.4;">
                                        <i class="fas fa-star"></i> Current
                                    </span>
                                @endif
                            </label>
                            <select name="academic_year_id" id="filter_academic_year_id" class="modern-select" onchange="this.form.submit()">
                                @foreach ($years as $year)
                                    <option value="{{ $year->id }}"
                                        {{ (string) $academicYearId === (string) $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2 md:pt-0">
                            <button type="button" class="btn-modern-excel" onclick="exportQuotaTable()">
                                <i class="fas fa-file-excel"></i>
                                Excel ထုတ်ပါ
                            </button>
                            @if ($canCreate ?? true)
                                <a href="{{ route('quota.create', array_filter(['academic_year_id' => $academicYearId])) }}"
                                    class="btn-modern-primary">
                                    <i class="fas fa-plus"></i>
                                    ကျောင်းသားဦးရေတွက်ချက်ရန်
                                </a>
                            @else
                                <span class="btn-modern-primary"
                                    style="opacity: 0.45; cursor: not-allowed; pointer-events: none;"
                                    title="မရောက်သေးသောနှစ် — အချက်အလက် ထည့်မရပါ">
                                    <i class="fas fa-plus"></i>
                                    ကျောင်းသားဦးရေတွက်ချက်ရန်
                                </span>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @php
                    $calculatedRows = [];

                    $calculatedTotals = [
                        'primary_public' => 0,
                        'primary_monk' => 0,
                        'primary_private' => 0,
                        'primary_total' => 0,

                        'middle_public' => 0,
                        'middle_monk' => 0,
                        'middle_private' => 0,
                        'middle_total' => 0,

                        'high_public' => 0,
                        'high_monk' => 0,
                        'high_private' => 0,
                        'high_total' => 0,

                        'grand_public' => 0,
                        'grand_monk' => 0,
                        'grand_private' => 0,
                        'grand_total' => 0,

                        'agriculture' => 0,
                        'total_with_agriculture' => 0,
                        'distribution_total' => 0,
                    ];

                    foreach ($rows as $row) {
                        $primaryPublic = (int) ($row['primary_public'] ?? 0);
                        $primaryMonk = (int) ($row['primary_monk'] ?? 0);
                        $primaryPrivate = (int) ($row['primary_private'] ?? 0);

                        $middlePublic = (int) ($row['middle_public'] ?? 0);
                        $middleMonk = (int) ($row['middle_monk'] ?? 0);
                        $middlePrivate = (int) ($row['middle_private'] ?? 0);

                        $highPublic = (int) ($row['high_public'] ?? 0);
                        $highMonk = (int) ($row['high_monk'] ?? 0);
                        $highPrivate = (int) ($row['high_private'] ?? 0);

                        $agriculture = (int) ($row['agriculture'] ?? 0);

                        $primaryTotal = $primaryPublic + $primaryMonk + $primaryPrivate;

                        $middleTotal = $middlePublic + $middleMonk + $middlePrivate;

                        $highTotal = $highPublic + $highMonk + $highPrivate;

                        $grandPublic = $primaryPublic + $middlePublic + $highPublic;

                        $grandMonk = $primaryMonk + $middleMonk + $highMonk;

                        $grandPrivate = $primaryPrivate + $middlePrivate + $highPrivate;

                        $grandTotal = $grandPublic + $grandMonk + $grandPrivate;

                        $totalWithAgriculture = $grandTotal + $agriculture;

                        $distributionTotal = $totalWithAgriculture - $grandPrivate;

                        $calculatedRow = [
                            'id' => $row['id'],
                            'township' => $row['township'],

                            'primary_public' => $primaryPublic,
                            'primary_monk' => $primaryMonk,
                            'primary_private' => $primaryPrivate,
                            'primary_total' => $primaryTotal,

                            'middle_public' => $middlePublic,
                            'middle_monk' => $middleMonk,
                            'middle_private' => $middlePrivate,
                            'middle_total' => $middleTotal,

                            'high_public' => $highPublic,
                            'high_monk' => $highMonk,
                            'high_private' => $highPrivate,
                            'high_total' => $highTotal,

                            'grand_public' => $grandPublic,
                            'grand_monk' => $grandMonk,
                            'grand_private' => $grandPrivate,
                            'grand_total' => $grandTotal,

                            'agriculture' => $agriculture,
                            'total_with_agriculture' => $totalWithAgriculture,
                            'distribution_total' => $distributionTotal,
                        ];

                        $calculatedRows[] = $calculatedRow;

                        foreach ($calculatedTotals as $field => $value) {
                            $calculatedTotals[$field] += $calculatedRow[$field] ?? 0;
                        }
                    }
        @endphp

        @if (count($calculatedRows) === 0)
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

            <table id="quotaTable" class="modern-table quota-table" style="min-width: 1600px;">

                        <thead>

                            <tr>

                                <th rowspan="2">စဉ်</th>
                                <th rowspan="2">မြို့နယ်</th>

                                <th colspan="4" class="text-amber-300">မူလတန်း</th>
                                <th colspan="4" class="text-amber-300">အလယ်တန်း</th>
                                <th colspan="4" class="text-amber-300">အထက်တန်း</th>
                                <th colspan="4" class="text-amber-300">စုစုပေါင်း</th>

                                <th rowspan="2">စက်၊စိုက်၊မွေး</th>

                                <th rowspan="2">
                                    စုစုပေါင်း<br>
                                    စက်၊စိုက်၊မွေးအပါ
                                </th>

                                <th rowspan="2">
                                    ခဲတံ၊ ဘောပင်၊<br>
                                    ဝတ်စုံ ဖြန့်ဝေရန်<br>
                                    ကျောင်းသားဦးရေ
                                </th>

                                <th rowspan="2">
                                    လုပ်ဆောင်ချက်
                                </th>
                            </tr>

                            <tr>

                                @for ($i = 0; $i < 4; $i++)
                                    <th class="sub-header-border">
                                        အခြေခံ
                                    </th>

                                    <th class="sub-header-border">
                                        ဘက
                                    </th>

                                    <th class="sub-header-border">
                                        ကိုယ်ပိုင်
                                    </th>

                                    <th class="sub-header-border">
                                        ပေါင်း
                                    </th>
                                @endfor

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($calculatedRows as $row)
                                <tr>

                                    <td class="font-mono text-slate-500">{{ ($rows->firstItem() ?? 1) + $loop->index }}</td>

                                    <td class="whitespace-nowrap font-semibold text-slate-800">
                                        {{ $row['township'] }}
                                    </td>

                                    <td class="font-mono">{{ number_format($row['primary_public']) }}</td>
                                    <td class="font-mono">{{ number_format($row['primary_monk']) }}</td>
                                    <td class="font-mono">{{ number_format($row['primary_private']) }}</td>

                                    <td class="font-mono font-bold text-slate-800">
                                        {{ number_format($row['primary_total']) }}
                                    </td>

                                    <td class="font-mono">{{ number_format($row['middle_public']) }}</td>
                                    <td class="font-mono">{{ number_format($row['middle_monk']) }}</td>
                                    <td class="font-mono">{{ number_format($row['middle_private']) }}</td>

                                    <td class="font-mono font-bold text-slate-800">
                                        {{ number_format($row['middle_total']) }}
                                    </td>

                                    <td class="font-mono">{{ number_format($row['high_public']) }}</td>
                                    <td class="font-mono">{{ number_format($row['high_monk']) }}</td>
                                    <td class="font-mono">{{ number_format($row['high_private']) }}</td>

                                    <td class="font-mono font-bold text-slate-800">
                                        {{ number_format($row['high_total']) }}
                                    </td>

                                    <td class="font-mono">{{ number_format($row['grand_public']) }}</td>
                                    <td class="font-mono">{{ number_format($row['grand_monk']) }}</td>
                                    <td class="font-mono">{{ number_format($row['grand_private']) }}</td>

                                    <td class="font-mono font-bold text-slate-800">
                                        {{ number_format($row['grand_total']) }}
                                    </td>

                                    <td class="font-mono">
                                        {{ number_format($row['agriculture']) }}
                                    </td>

                                    <td class="font-mono font-bold text-slate-800">
                                        {{ number_format($row['total_with_agriculture']) }}
                                    </td>

                                    <td class="font-mono font-bold text-emerald-700">
                                        {{ number_format($row['distribution_total']) }}
                                    </td>

                                    <td class="whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('quota.edit', $row['id']) }}" class="btn-action-edit" title="ပြင်ဆင်ရန်">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            @if (auth()->user()?->canDeleteRecords())
                                                <form action="{{ route('quota.destroy', ['quotum' => $row['id']]) }}"
                                                    method="POST" class="d-inline m-0">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn-action-delete" title="ဖျက်ရန်">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                        <tfoot>

                            <tr class="table-success fw-bold">

                                <td colspan="2">
                                    စုစုပေါင်း
                                </td>

                                <td>{{ number_format($calculatedTotals['primary_public']) }}</td>
                                <td>{{ number_format($calculatedTotals['primary_monk']) }}</td>
                                <td>{{ number_format($calculatedTotals['primary_private']) }}</td>
                                <td>{{ number_format($calculatedTotals['primary_total']) }}</td>

                                <td>{{ number_format($calculatedTotals['middle_public']) }}</td>
                                <td>{{ number_format($calculatedTotals['middle_monk']) }}</td>
                                <td>{{ number_format($calculatedTotals['middle_private']) }}</td>
                                <td>{{ number_format($calculatedTotals['middle_total']) }}</td>

                                <td>{{ number_format($calculatedTotals['high_public']) }}</td>
                                <td>{{ number_format($calculatedTotals['high_monk']) }}</td>
                                <td>{{ number_format($calculatedTotals['high_private']) }}</td>
                                <td>{{ number_format($calculatedTotals['high_total']) }}</td>

                                <td>{{ number_format($calculatedTotals['grand_public']) }}</td>
                                <td>{{ number_format($calculatedTotals['grand_monk']) }}</td>
                                <td>{{ number_format($calculatedTotals['grand_private']) }}</td>
                                <td>{{ number_format($calculatedTotals['grand_total']) }}</td>

                                <td>{{ number_format($calculatedTotals['agriculture']) }}</td>

                                <td>
                                    {{ number_format($calculatedTotals['total_with_agriculture']) }}
                                </td>

                                <td>
                                    {{ number_format($calculatedTotals['distribution_total']) }}
                                </td>

                                <td>-</td>

                            </tr>

                        </tfoot>

        </table>

        </div>
        @include('partials.pagination', ['items' => $rows])
        @endif

        <style>
            .sub-header-border {
                border-left: 1px solid #dee2e6 !important;
                border-right: 1px solid #dee2e6 !important;
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>

        <script>
            async function exportQuotaTable() {
                const rows = @json($calculatedRows);
                const totals = @json($calculatedTotals);
                const academicYear = @json($academicYear ?? '2025-2026');

                const workbook = new ExcelJS.Workbook();
                const sheet = workbook.addWorksheet('Sheet1');

                sheet.columns = [{
                        width: 5
                    },
                    {
                        width: 14
                    },
                    {
                        width: 10
                    },
                    {
                        width: 8
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 8
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 8
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 8
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 10
                    },
                    {
                        width: 16
                    },
                    {
                        width: 18
                    },
                ];

                sheet.mergeCells('A1:U1');

                sheet.getCell('A1').value =
                    'ခဲတံ၊ ဘောပင်၊ ဝတ်စုံအတွက် ကျောင်းသားဦးရေတွက်ချက်မှု';

                sheet.getCell('A1').font = {
                    bold: true,
                    size: 14
                };

                sheet.getCell('A1').alignment = {
                    horizontal: 'center',
                    vertical: 'middle'
                };

                sheet.mergeCells('A3:U3');

                sheet.getCell('A3').value =
                    academicYear + ' ခုနှစ် အတွက် ကျောင်းသားစာရင်းအရ';

                sheet.getCell('A3').alignment = {
                    horizontal: 'center',
                    vertical: 'middle'
                };

                sheet.mergeCells('A4:A5');
                sheet.mergeCells('B4:B5');

                sheet.mergeCells('C4:F4');
                sheet.mergeCells('G4:J4');
                sheet.mergeCells('K4:N4');
                sheet.mergeCells('O4:R4');

                sheet.mergeCells('S4:S5');
                sheet.mergeCells('T4:T5');
                sheet.mergeCells('U4:U5');

                sheet.getRow(4).values = [
                    'စဉ်',
                    'မြို့နယ်',
                    'မူလတန်း', '', '', '',
                    'အလယ်တန်း', '', '', '',
                    'အထက်တန်း', '', '', '',
                    'စုစုပေါင်း', '', '', '',
                    'စက်၊စိုက်၊မွေး',
                    'စုစုပေါင်း စက်၊စိုက်၊မွေးအပါ',
                    'ခဲတံ၊ဘောပင် ဝတ်စုံ ဖြန့်ရန် ကျောင်းသားဦးရေ'
                ];

                sheet.getRow(5).values = [
                    '',
                    '',
                    'အခြေခံ', 'ဘက', 'ကိုယ်ပိုင်', 'ပေါင်း',
                    'အခြေခံ', 'ဘက', 'ကိုယ်ပိုင်', 'ပေါင်း',
                    'အခြေခံ', 'ဘက', 'ကိုယ်ပိုင်', 'ပေါင်း',
                    'အခြေခံ', 'ဘက', 'ကိုယ်ပိုင်', 'ပေါင်း',
                    '', '', ''
                ];

                let startRow = 6;

                rows.forEach((row, index) => {
                    sheet.getRow(startRow + index).values = [
                        index + 1,
                        row.township,

                        row.primary_public,
                        row.primary_monk,
                        row.primary_private,
                        row.primary_total,

                        row.middle_public,
                        row.middle_monk,
                        row.middle_private,
                        row.middle_total,

                        row.high_public,
                        row.high_monk,
                        row.high_private,
                        row.high_total,

                        row.grand_public,
                        row.grand_monk,
                        row.grand_private,
                        row.grand_total,

                        row.agriculture,
                        row.total_with_agriculture,
                        row.distribution_total,
                    ];
                });

                const totalRowNumber = startRow + rows.length;

                sheet.getRow(totalRowNumber).values = [
                    '',
                    'ပေါင်း',

                    totals.primary_public,
                    totals.primary_monk,
                    totals.primary_private,
                    totals.primary_total,

                    totals.middle_public,
                    totals.middle_monk,
                    totals.middle_private,
                    totals.middle_total,

                    totals.high_public,
                    totals.high_monk,
                    totals.high_private,
                    totals.high_total,

                    totals.grand_public,
                    totals.grand_monk,
                    totals.grand_private,
                    totals.grand_total,

                    totals.agriculture,
                    totals.total_with_agriculture,
                    totals.distribution_total,
                ];

                sheet.eachRow((row, rowNumber) => {
                    row.eachCell((cell) => {
                        cell.alignment = {
                            horizontal: 'center',
                            vertical: 'middle',
                            wrapText: true
                        };

                        cell.border = {
                            top: {
                                style: 'thin'
                            },
                            left: {
                                style: 'thin'
                            },
                            bottom: {
                                style: 'thin'
                            },
                            right: {
                                style: 'thin'
                            },
                        };

                        if (
                            rowNumber === 4 ||
                            rowNumber === 5 ||
                            rowNumber === totalRowNumber
                        ) {
                            cell.font = {
                                bold: true
                            };
                        }
                    });
                });

                const buffer = await workbook.xlsx.writeBuffer();

                saveAs(
                    new Blob([buffer]),
                    `quota_${academicYear}.xlsx`
                );
            }
        </script>

    </div>
@endsection

@push('styles')
    <style>
        body {
            background: #f4f6f8;
        }

        .quota-table {
            background: #ffffff;
        }

        .quota-table th {
            font-size: 15px !important;
            font-weight: 700;
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            white-space: nowrap;
            padding: 8px 10px;
        }

        .quota-table td {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            white-space: nowrap;
            padding: 8px 10px;
        }

        .quota-table tbody tr:hover {
            background: #f1f8f4;
        }

        .quota-table tfoot td {
            background: #d1e7dd !important;
        }
    </style>
@endpush
