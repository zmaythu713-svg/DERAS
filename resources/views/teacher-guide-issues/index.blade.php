@extends('layouts.master')

@section('content')
    @php
        $yearLabel = $selectedYear?->name ?? '';
    @endphp
    <div class="app-page-container">

        {{-- Main Filter & Action Card (allocation-plans style) --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="modern-card-header-title text-white text-lg font-bold">
                    <i class="fas fa-calculator text-amber-400"></i>
                    @if ($yearLabel)
                        {{ $yearLabel }} ပညာသင်နှစ်အတွက် ဆရာကိုင် / ဆရာလမ်းညွှန် စာအုပ်များ ဖြန့်ဝေထုတ်ပေးသည့်စာရင်း
                    @else
                        ဆရာကိုင် / ဆရာလမ်းညွှန် စာအုပ်များ ဖြန့်ဝေထုတ်ပေးသည့်စာရင်း
                    @endif
                </h5>
            </div>

            <div class="modern-card-body p-4 sm:p-6">
                <form method="GET" action="{{ route('teacher-guide-issues.index') }}" id="tgIssueFilterForm" class="m-0">

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
                                <a href="{{ route('teacher-guide-issues.index') }}" class="btn-modern-secondary">
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
                                <a href="{{ route('teacher-guide-issues.create', array_filter([
                                        'academic_year_id' => $yearId,
                                        'grade_id' => $gradeId,
                                        'guide_type' => $guideType,
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

                    {{-- Bottom: ပညာသင်နှစ် / အတန်း / အမျိုးအစား --}}
                    <div class="flex flex-wrap items-end gap-3 mt-4 pt-3 border-t border-slate-100">
                        <div style="flex: 2; min-width: 200px;">
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

                        <div style="flex: 2; min-width: 160px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">အမျိုးအစား</label>
                            <select name="guide_type" id="filter_guide_type" class="modern-select text-sm font-medium">
                                <option value="">အမျိုးအစားအားလုံး</option>
                                <option value="ဆရာကိုင်" {{ $guideType === 'ဆရာကိုင်' ? 'selected' : '' }}>ဆရာကိုင်</option>
                                <option value="ဆရာလမ်းညွှန်" {{ $guideType === 'ဆရာလမ်းညွှန်' ? 'selected' : '' }}>ဆရာလမ်းညွှန်</option>
                            </select>
                        </div>
                    </div>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.getElementById('tgIssueFilterForm');
                        if (!form) return;
                        ['filter_academic_year_id', 'filter_grade_id', 'filter_guide_type'].forEach(function (id) {
                            document.getElementById(id)?.addEventListener('change', function () {
                                form.submit();
                            });
                        });
                    });
                </script>
            </div>
        </div>

        @if ($issues->isEmpty())
            <div class="modern-card mt-4">
                <div class="modern-card-body p-5 text-center">
                    <i class="fas fa-inbox text-3xl mb-3 block" style="color: #94a3b8;"></i>
                    <p class="mb-0 font-medium text-base" style="color: #475569;">
                        {{ $emptyMessage ?? 'အချက်အလက်မရှိပါ' }}
                    </p>
                </div>
            </div>
        @else
        {{-- Table Section with #072a1e dark green header & customized column widths --}}
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped text-center align-middle issue-table">
                <thead style="background-color: #072a1e; color: #ffffff;">
                    <tr>
                        <th style="width: 50px; min-width: 50px; white-space: nowrap;">စဉ်</th>
                        <th style="min-width: 90px; white-space: nowrap;">အတန်း</th>
                        <th style="min-width: 220px; text-align: left; white-space: nowrap;">ဘာသာ</th>
                        <th style="min-width: 110px; white-space: nowrap;">အမျိုးအစား</th>
                        <th style="min-width: 120px; white-space: nowrap;">ခရိုင်ရုံးလက်ကျန်</th>
                        <th style="min-width: 120px; white-space: nowrap;">တစ်အိတ်ပါ Unit</th>
                        @foreach ($townships as $township)
                            <th style="min-width: 110px; white-space: nowrap;">{{ $township->name }} အုပ်ရေ</th>
                            <th style="min-width: 85px; white-space: nowrap;">အိတ်ပြည့်</th>
                            <th style="min-width: 85px; white-space: nowrap;">အပြေ</th>
                        @endforeach
                        <th style="min-width: 100px; white-space: nowrap;">လုပ်ဆောင်ချက်</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issues as $key => $issue)
                        <tr>
                            <td class="font-mono text-slate-500">{{ $key + 1 }}</td>
                            <td class="whitespace-nowrap font-medium text-slate-800">{{ $issue->grade?->name }}</td>
                            <td class="text-start font-medium text-slate-800" style="min-width: 220px;">{{ $issue->bookName?->name }}</td>
                            <td class="whitespace-nowrap font-medium text-slate-800">{{ $issue->guide_type }}</td>
                            <td class="font-mono text-slate-600">{{ number_format($issue->district_unit) }}</td>
                            <td class="font-mono text-slate-600">{{ number_format($issue->package_unit) }}</td>
                            @foreach ($townships as $township)
                                @php
                                    $detail = $issue->townshipIssues->firstWhere('township_id', $township->id);
                                    $tintClass = match ($township->name) {
                                        'မြန်အောင်' => 'col-tint-myanaung',
                                        'ကြံခင်း' => 'col-tint-kyankhin',
                                        'အင်္ဂပူ' => 'col-tint-ingapu',
                                        default => '',
                                    };
                                @endphp
                                <td class="font-mono text-slate-700 {{ $tintClass }}">{{ number_format($detail?->issued_quantity ?? 0) }}</td>
                                <td class="font-mono text-amber-700 font-semibold {{ $tintClass }}">{{ number_format($detail?->full_package_count ?? 0) }}</td>
                                <td class="font-mono text-emerald-700 font-semibold {{ $tintClass }}">{{ number_format($detail?->loose_book_count ?? 0) }}</td>
                            @endforeach
                            <td class="whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('teacher-guide-issues.edit', $issue) }}"
                                        class="btn-modern-warning" title="ပြင်ဆင်ပါ"><i class="fas fa-pen"></i></a>
                                    @if (auth()->user()?->canDeleteRecords())
                                        <form action="{{ route('teacher-guide-issues.destroy', $issue) }}" method="POST"
                                            class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-modern-danger" title="ဖျက်ပါ"><i class="fas fa-trash"></i></button>
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
            const sheet = workbook.addWorksheet('Teacher Guide Issues');

            const totalColumns = 6 + ({{ count($townships) }} * 3);

            function getColumnLetter(column) {
                let temp = '';
                while (column > 0) {
                    let rem = (column - 1) % 26;
                    temp = String.fromCharCode(65 + rem) + temp;
                    column = Math.floor((column - 1) / 26);
                }
                return temp;
            }

            const endColumn = getColumnLetter(totalColumns);

            sheet.mergeCells(`A1:${endColumn}1`);
            sheet.getCell('A1').value = @json(($yearLabel ? $yearLabel . ' ပညာသင်နှစ်အတွက် ' : '') . 'ဆရာကိုင် / ဆရာလမ်းညွှန် စာအုပ်များ ဖြန့်ဝေထုတ်ပေးသည့်စာရင်း');
            sheet.getCell('A1').font = { bold: true, size: 16 };
            sheet.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };
            sheet.getRow(1).height = 28;

            sheet.addRow([]);

            let columns = [
                { width: 8 },
                { width: 20 },
                { width: 25 },
                { width: 20 },
                { width: 18 },
                { width: 18 },
            ];

            @foreach ($townships as $township)
                columns.push({ width: 18 }, { width: 12 }, { width: 12 });
            @endforeach

            sheet.columns = columns;

            let headers = [
                'စဉ်',
                'အတန်း',
                'ဘာသာ',
                'အမျိုးအစား',
                'ခရိုင်ယခင်နှစ်လက်ကျန်',
                'တစ်အိတ်ပါ Unit'
            ];

            @foreach ($townships as $township)
                headers.push(
                    '{{ $township->name }} အုပ်ရေ',
                    'အိတ်ပြည့်',
                    'အပြေ'
                );
            @endforeach

            sheet.addRow(headers);

            @foreach ($issues as $issue)
                sheet.addRow([
                    {{ $issue->id }},
                    {!! json_encode($issue->grade?->name) !!},
                    {!! json_encode($issue->bookName?->name) !!},
                    {!! json_encode($issue->guide_type) !!},
                    {{ $issue->district_unit }},
                    {{ $issue->package_unit }},
                    @foreach ($townships as $township)
                        @php
                            $detail = $issue->townshipIssues->firstWhere('township_id', $township->id);
                        @endphp
                        {{ $detail?->issued_quantity ?? 0 }},
                        {{ $detail?->full_package_count ?? 0 }},
                        {{ $detail?->loose_book_count ?? 0 }},
                    @endforeach
                ]);
            @endforeach

            sheet.eachRow((row, rowNumber) => {
                row.eachCell((cell) => {
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    cell.border = {
                        top: { style: 'thin' },
                        left: { style: 'thin' },
                        bottom: { style: 'thin' },
                        right: { style: 'thin' }
                    };
                });

                if (rowNumber === 3) {
                    row.font = { bold: true };
                }
            });

            const buffer = await workbook.xlsx.writeBuffer();

            saveAs(
                new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }),
                'teacher-guide-issues.xlsx'
            );
        }
    </script>
@endsection

@push('styles')
    <style>
        body {
            background: #f4f6f8;
        }

        .issue-table {
            background: #ffffff;
            color: #334155;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .issue-table th {
            font-size: 15px !important;
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            padding: 10px 12px;
        }

        .issue-table td {
            font-size: 14px !important;
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
            padding: 10px 12px;
        }

        .issue-table thead th {
            background-color: #072a1e !important;
            color: #ffffff !important;
            font-weight: 700;
            white-space: nowrap;
            padding: 12px 14px;
            font-size: 15px !important;
        }

        .issue-table tbody tr:hover {
            background-color: #f0faf4 !important;
        }
    </style>
@endpush
