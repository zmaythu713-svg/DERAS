@extends('layouts.master')

@section('content')
    <div class="app-page-container">

        {{-- Filter Card --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-book-open text-amber-400"></i>
                    ပုံမှန်ဖြန့်ဝေစာရင်း
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('textbook.index') }}" id="textbookFilterForm" class="m-0">

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
                                <a href="{{ route('textbook.index') }}" class="btn-modern-secondary">
                                    <i class="fas fa-redo"></i>
                                    ပြန်လည်သတ်မှတ်
                                </a>
                            </div>
                        </div>

                        <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                            <button type="button" class="btn-modern-excel" onclick="exportTextbookTable()">
                                <i class="fas fa-file-excel"></i>
                                Excel ထုတ်ပါ
                            </button>
                            <button type="button" class="btn-modern-pdf" onclick="exportTextbookTable('pdf')">
                                <i class="fas fa-file-pdf"></i>
                                PDF ထုတ်ပါ
                            </button>
                            @if ($canCreate ?? true)
                                <a href="{{ route('textbook.create', array_filter([
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
                                @foreach ($townships as $t)
                                    <option value="{{ $t->id }}"
                                        {{ (string) $townshipId === (string) $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.getElementById('textbookFilterForm');
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
        @if (empty($blocks))
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
                <table id="textbookTable" class="modern-table textbook-table">

                    <thead>
                        <tr>
                            @foreach ($blocks as $block)
                                <th colspan="9" class="bg-emerald-950 text-amber-300 py-3 font-bold text-sm">
                                    {{ $block['academic_year'] }} ပညာသင်နှစ် အတွက်
                                    ကျောင်းသုံးပြဌာန်းစာအုပ်များ ဖြန့်ဝေထုတ်ပေးသည့်စာရင်း
                                </th>
                            @endforeach
                        </tr>

                        <tr>
                            @foreach ($blocks as $block)
                                <th colspan="9" class="bg-emerald-900 text-white font-semibold text-sm border-b border-emerald-950">
                                    {{ $block['township'] ?? '............... မြို့နယ်' }}
                                </th>
                            @endforeach
                        </tr>

                        <tr>
                            @foreach ($blocks as $block)
                                <th>စဉ်</th>
                                <th>အတန်း</th>
                                <th>အမျိုးအမည်</th>
                                <th>တစ်အိတ်ပါ<br>ယူနစ်</th>
                                <th>ထုတ်ပေးသည့်<br>အုပ်ရေ</th>
                                <th>အိတ်ပြည့်</th>
                                <th>အပြေ</th>
                                <th>မှတ်ချက်</th>
                                <th>လုပ်ဆောင်ချက်</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @for ($i = 0; $i < $maxRows; $i++)
                            <tr>
                                @foreach ($blocks as $block)
                                    @php $row = $block['rows'][$i] ?? null; @endphp

                                    @if ($row)
                                        <td class="font-mono text-slate-500">{{ $i + 1 }}</td>
                                        <td class="whitespace-nowrap font-medium text-slate-800">{{ $row['grade'] }}</td>
                                        <td class="text-left font-medium text-slate-800">{{ $row['book_name'] }}</td>
                                        <td class="font-mono text-slate-600">{{ number_format($row['books_per_set']) }}</td>
                                        <td class="font-mono font-semibold text-emerald-700">{{ number_format($row['student_count']) }}</td>
                                        @php
                                            $booksPerSet  = $row['books_per_set'];
                                            $studentCount = $row['student_count'];
                                            $result       = $booksPerSet > 0 ? intdiv($studentCount, $booksPerSet) : 0;
                                            $remain       = $booksPerSet > 0 ? $studentCount % $booksPerSet : 0;
                                        @endphp
                                        <td class="font-mono font-semibold text-slate-700">{{ number_format($result) }}</td>
                                        <td class="font-mono text-slate-600">{{ number_format($remain) }}</td>
                                        <td class="text-slate-500 text-xs">{{ $row['remark'] }}</td>
                                        <td class="whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('textbook.edit', $row['id']) }}"
                                                    class="btn-modern-warning" title="ပြင်ဆင်ပါ">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                @if (auth()->user()?->canDeleteRecords())
                                                    <form action="{{ route('textbook.destroy', $row['id']) }}"
                                                        method="POST" class="d-inline m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn-modern-danger" title="ဖျက်ပါ">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                        <td></td><td></td><td></td><td></td>
                                        <td></td><td></td><td></td><td></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>

                </table>
            </div>

            @include('partials.pagination', ['items' => $textbooks])
        @endif

        <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>
        <script>
            async function exportTextbookTable(format) {
                const blocks  = @json($blocks);
                const maxRows = @json($maxRows);
                const headerFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9EAD3' } };

                const workbook = new ExcelJS.Workbook();
                const sheet    = workbook.addWorksheet('Textbook');

                if (format === 'pdf') {
                    sheet.columns = [
                        { width: 8 }, { width: 16 }, { width: 16 }, { width: 14 },
                        { width: 32 }, { width: 16 }, { width: 18 }, { width: 12 }, { width: 12 }, { width: 18 },
                    ];

                    sheet.mergeCells('A1:J1');
                    sheet.getCell('A1').value = 'ကျောင်းသုံးပြဌာန်းစာအုပ်များ ဖြန့်ဝေထုတ်ပေးသည့်စာရင်း';
                    sheet.getCell('A1').font = { bold: true, size: 14 };
                    sheet.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };

                    sheet.getRow(2).values = [
                        'စဉ်', 'ပညာသင်နှစ်', 'မြို့နယ်', 'အတန်း', 'အမျိုးအမည်',
                        'တစ်အိတ်ပါယူနစ်', 'ထုတ်ပေးသည့်အုပ်ရေ', 'အိတ်ပြည့်', 'အပြေ', 'မှတ်ချက်',
                    ];

                    blocks.forEach(block => {
                        (block.rows || []).forEach((row, index) => {
                            const pkg = row.books_per_set > 0 ? Math.floor(row.student_count / row.books_per_set) : 0;
                            const loose = row.books_per_set > 0 ? row.student_count % row.books_per_set : 0;
                            sheet.addRow([
                                index + 1,
                                block.academic_year,
                                block.township ?? '',
                                row.grade,
                                row.book_name,
                                row.books_per_set,
                                row.student_count,
                                pkg,
                                loose,
                                row.remark ?? '',
                            ]);
                        });
                    });

                    sheet.eachRow((row, rowNumber) => {
                        row.eachCell((cell) => {
                            cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                            cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
                            if (rowNumber === 2) {
                                cell.font = { bold: true };
                                cell.fill = headerFill;
                            }
                        });
                    });
                    sheet.getRow(1).height = 35;
                    sheet.getRow(2).height = 35;
                } else {
                    sheet.columns = [
                        { width: 6  }, { width: 12 }, { width: 30 },
                        { width: 14 }, { width: 15 }, { width: 12 },
                        { width: 12 }, { width: 20 },
                    ];

                    let columnCount = Math.max(8, blocks.length * 8);
                    sheet.mergeCells(1, 1, 1, columnCount);
                    sheet.getCell(1, 1).value     = 'ကျောင်းသုံးပြဌာန်းစာအုပ်များ ဖြန့်ဝေထုတ်ပေးသည့်စာရင်း';
                    sheet.getCell(1, 1).font      = { bold: true, size: 14 };
                    sheet.getCell(1, 1).alignment = { horizontal: 'center', vertical: 'middle' };

                    let col = 1;
                    blocks.forEach(block => {
                        sheet.mergeCells(2, col, 2, col + 7);
                        sheet.getCell(2, col).value = block.academic_year + ' ပညာသင်နှစ်အတွက်';
                        sheet.mergeCells(3, col, 3, col + 7);
                        sheet.getCell(3, col).value = block.township ?? '............... မြို့နယ်';
                        col += 8;
                    });

                    col = 1;
                    blocks.forEach(block => {
                        const headers = ['စဉ်','အတန်း','အမျိုးအမည်','တစ်အိတ်ပါယူနစ်','ထုတ်ပေးသည့်အုပ်ရေ','အိတ်ပြည့်','အပြေ','မှတ်ချက်'];
                        headers.forEach((h, i) => { sheet.getCell(4, col + i).value = h; });
                        col += 8;
                    });

                    for (let i = 0; i < maxRows; i++) {
                        let rowData = [];
                        blocks.forEach(block => {
                            let row = block.rows[i];
                            if (row) {
                                let pkg   = row.books_per_set > 0 ? Math.floor(row.student_count / row.books_per_set) : 0;
                                let loose = row.books_per_set > 0 ? row.student_count % row.books_per_set : 0;
                                rowData.push(i + 1, row.grade, row.book_name, row.books_per_set, row.student_count, pkg, loose, row.remark ?? '');
                            } else {
                                rowData.push('', '', '', '', '', '', '', '');
                            }
                        });
                        sheet.addRow(rowData);
                    }

                    sheet.eachRow(row => {
                        row.eachCell(cell => {
                            cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                            cell.border    = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
                        });
                    });

                    sheet.addRow([]);
                    sheet.addRow(['', 'ပစ္စည်းထုတ်ပေးသူလက်မှတ် ............................', '', '', 'ပစ္စည်းလက်ခံသူလက်မှတ် ............................']);
                    sheet.addRow(['', 'အမည် ............................', '', '', 'အမည် ............................']);
                    sheet.addRow(['', 'မြို့နယ် ............................', '', '', 'မြို့နယ် ............................']);
                    sheet.addRow(['', 'ရာထူး ............................', '', '', 'ရာထူး ............................']);

                    let lastRows = sheet.lastRow.number - 3;
                    for (let i = lastRows; i <= sheet.lastRow.number; i++) {
                        sheet.getRow(i).eachCell(cell => {
                            cell.alignment = { horizontal: 'left', vertical: 'middle' };
                            cell.font      = { size: 12 };
                        });
                    }
                }

                await DerasPdf.downloadWorkbook(workbook, sheet, 'textbook_distribution.xlsx', format);
            }
        </script>
    </div>
@endsection

@push('styles')
    <style>
        body { background: #f4f6f8; }

        .textbook-table {
            font-size: 13px;
            background: #ffffff;
            color: #4f5870;
        }

        .textbook-table th,
        .textbook-table td {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            padding: 6px 8px;
        }

        .textbook-table thead th {
            background: #e5e5ea;
            font-weight: 700;
            color: #4f5870;
            white-space: nowrap;
        }

        .textbook-table tbody tr:hover { background: #f1f8f4; }

        .table td {
            font-size: 13px;
            vertical-align: middle;
        }
    </style>
@endpush
