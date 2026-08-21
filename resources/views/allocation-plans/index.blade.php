@extends('layouts.master')

@section('content')
    <div class="app-page-container">

        <!-- Main Data Table Card -->
        <div class="modern-card">
            <!-- Card Header with solid #072a1e dark green background matching table header TH -->
            <div class="modern-card-header" style="background: #072a1e !important; background-image: none !important; color: #ffffff !important; border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                <h5 class="text-lg font-bold text-white modern-card-header-title">
                    <i class="fas fa-calculator text-amber-400"></i>
                    ခရိုင်ခွဲတမ်းတွက်ချက်မှုစာရင်း
                </h5>
            </div>

            <div class="p-4 modern-card-body sm:p-6">
                <form method="GET" action="{{ route('allocation-plans.index') }}" class="m-0">

                    {{-- Top: စာအုပ်ရှာဖွေရန် + actions --}}
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div class="flex flex-wrap items-end gap-3" style="flex: 1; min-width: 260px;">
                            <div style="flex: 1; min-width: 220px; max-width: 360px;">
                                <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">စာအုပ်ရှာဖွေရန်</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-search" style="color: #16a34a;"></i>
                                    </span>
                                    <input type="text" name="search" class="text-sm font-medium modern-input"
                                        style="padding-left: 2.25rem;"
                                        value="{{ request('search') }}"
                                        placeholder="စာအုပ်ရှာရန်...">
                                </div>
                            </div>

                            <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                                <button type="submit" class="btn-modern-primary">
                                    <i class="fas fa-search"></i>
                                    စစ်ထုတ်ပါ
                                </button>
                                <a href="{{ route('allocation-plans.index') }}" class="btn-modern-secondary">
                                    <i class="fas fa-redo"></i>
                                    ပြန်လည်သတ်မှတ်
                                </a>
                            </div>
                        </div>

                        <div class="flex items-end gap-2" style="padding-bottom: 1px;">
                            <button type="button" class="btn-modern-excel" onclick="exportAllocationPlan()">
                                <i class="fas fa-file-excel"></i>
                                Excel ထုတ်ပါ
                            </button>
                            <button type="button" class="btn-modern-pdf" onclick="exportAllocationPlan('pdf')">
                                <i class="fas fa-file-pdf"></i>
                                PDF ထုတ်ပါ
                            </button>
                            @if ($canCreate ?? true)
                                <a href="{{ route('allocation-plans.create', array_filter(['academic_year_id' => $yearId])) }}"
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

                    {{-- Bottom: ပညာသင်နှစ် / အတန်း / ဘာသာရပ် --}}
                    <div class="flex flex-wrap items-end gap-3 pt-3 mt-4 border-t border-slate-100">
                        <div style="flex: 2; min-width: 160px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a; display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                <span>ပညာသင်နှစ်</span>
                                @if ($selectedYear?->is_current)
                                    <span class="badge-active" style="font-size: 10px; padding: 2px 8px; white-space: nowrap; line-height: 1.4;">
                                        <i class="fas fa-star"></i> Current
                                    </span>
                                @endif
                            </label>
                            <select name="academic_year_id" id="filter_academic_year_id" class="text-sm font-medium modern-select">
                                @foreach ($years as $year)
                                    <option value="{{ $year->id }}" {{ (string) $yearId === (string) $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 2; min-width: 160px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">အတန်း</label>
                            <select name="grade_id" id="filter_grade_id" class="text-sm font-medium modern-select">
                                <option value="">အတန်းအားလုံး</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ (string) $gradeId === (string) $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 2; min-width: 160px;">
                            <label class="block text-sm font-extrabold mb-1.5" style="color: #105c3a;">ဘာသာရပ်</label>
                            <select name="book_name_id" id="filter_book_name_id" class="text-sm font-medium modern-select"
                                data-placeholder="ဘာသာရပ်အားလုံး">
                                <option value="">ဘာသာရပ်အားလုံး</option>
                                @if ($bookNameId)
                                    @foreach ($bookNames as $book)
                                        @if ((string) $book->id === (string) $bookNameId)
                                            <option value="{{ $book->id }}" selected>{{ $book->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.querySelector('form[action="{{ route('allocation-plans.index') }}"]');
                        const yearSelect = document.getElementById('filter_academic_year_id');
                        const gradeSelect = document.getElementById('filter_grade_id');
                        const subjectSelect = document.getElementById('filter_book_name_id');
                        if (!form || !window.DerasForm) return;

                        async function reloadSubjects(keepSelected) {
                            const gradeId = gradeSelect?.value;
                            const keep = keepSelected ? (subjectSelect?.value || '') : '';

                            if (!gradeId) {
                                DerasForm.fillSelect(subjectSelect, [], '');
                                return;
                            }

                            try {
                                const subjects = await DerasForm.fetchJson(
                                    '/grades/' + gradeId + '/subjects?category=textbook'
                                );
                                DerasForm.fillSelect(subjectSelect, subjects, keep);
                            } catch (e) {
                                console.error(e);
                            }
                        }

                        yearSelect?.addEventListener('change', function () {
                            if (gradeSelect) gradeSelect.value = '';
                            if (subjectSelect) DerasForm.fillSelect(subjectSelect, [], '');
                            form.submit();
                        });

                        gradeSelect?.addEventListener('change', async function () {
                            await reloadSubjects(false);
                            form.submit();
                        });

                        subjectSelect?.addEventListener('change', function () {
                            form.submit();
                        });

                        reloadSubjects(true);
                    });
                </script>
            </div>
        </div>

        @if ($plans->isEmpty())
            <div class="modern-card mt-4">
                <div class="modern-card-body p-5 text-center">
                    <i class="fas fa-inbox text-3xl mb-3 block" style="color: #94a3b8;"></i>
                    <p class="mb-0 font-medium text-base" style="color: #475569;">
                        {{ $emptyMessage ?? 'အချက်အလက်မရှိပါ' }}
                    </p>
                </div>
            </div>
        @else
        <div class="table-responsive">
                <table class="table text-center align-middle table-bordered table-striped township-cols" style="min-width:4200px;">
                    <thead style="background-color: #072a1e; color: #ffffff;">
                        <tr>
                            <th rowspan="2">
                                စဉ်
                            </th>

                            <th rowspan="2">
                                အတန်း
                            </th>

                            <th rowspan="2">
                                ဘာသာ
                            </th>

                            <th rowspan="2">
                                ရရှိအုပ်ရေ
                            </th>

                            <th rowspan="2">
                                တစ်အိတ်ပါ Unit
                            </th>

                            <th rowspan="2">
                                အချိုး
                            </th>

                            <th colspan="4">
                                ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီးကျောင်းသားဦးရေ
                            </th>

                            <th colspan="4">
                                ခွဲတမ်းပေးရန်အုပ်အရေအတွက်
                            </th>

                            <th colspan="3">
                                ခွဲတမ်းပေးရန်အိတ်
                            </th>

                            <th colspan="3">
                                ခွဲတမ်းပေးရန်အပြေအုပ်အရေအတွက်
                            </th>

                            <th colspan="3">
                                ယခင်နှစ်လက်ကျန်စာအုပ်
                            </th>

                            <th colspan="4">
                                ကျောင်းသားဦးရေ
                            </th>

                            <th colspan="4">
                                လက်ဆင့်ကမ်းစာအုပ်
                            </th>

                            <th colspan="4">
                                ယခင်နှစ်လက်ကျန် + ထုတ်ပေး + လက်ဆင့်ကမ်း
                            </th>

                            <th colspan="4">
                                ကျောင်းသားအရအပိုအလို
                            </th>

                            <th rowspan="2">
                                လုပ်ဆောင်ချက်
                            </th>
                        </tr>

                        <tr>
                            {{-- Student --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            <th>
                                စုစုပေါင်း
                            </th>

                            {{-- Allocation Book --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            <th>
                                စုစုပေါင်း
                            </th>

                            {{-- Package --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            {{-- Loose --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            {{-- Remaining --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            {{-- Student Count --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            <th>
                                စုစုပေါင်း
                            </th>

                            {{-- Transferable --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            <th>
                                စုစုပေါင်း
                            </th>

                            {{-- လက+ထုတ်ပေး+လက်ဆင့်ကမ်း --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            <th>
                                စုစုပေါင်း
                            </th>

                            {{-- ကျောင်းသားအရအပိုအလို --}}
                            <th>
                                မြန်အောင်
                            </th>

                            <th>
                                ကြံခင်း
                            </th>

                            <th>
                                အင်္ဂပူ
                            </th>

                            <th>
                                စုစုပေါင်း
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($plans as $key => $plan)
                            @php $detail = $plan->detailCompat(); @endphp
                            <tr>
                                <td>
                                    {{ ($plans->firstItem() ?? 1) + $key }}
                                </td>

                                <td>
                                    {{ $plan->grade->name ?? '' }}
                                </td>

                                <td>
                                    {{ $plan->bookName->name ?? '' }}
                                </td>

                                <td>
                                    {{ number_format($plan->received_books ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($plan->books_per_package ?? 0) }}
                                </td>

                                {{-- အချိုး = ရရှိအုပ်ရေ ÷ ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီးကျောင်းသားဦးရေပေါင်း --}}
                                <td>
                                    @php
                                        $eligibleStudents =
                                            ($detail->myanaung_total_students ?? 0) -
                                            (($detail->myanaung_previous ?? 0) +
                                                ($detail->myanaung_transferable ?? 0)) +
                                            (($detail->kyankhin_total_students ?? 0) -
                                                (($detail->kyankhin_previous ?? 0) +
                                                    ($detail->kyankhin_transferable ?? 0))) +
                                            (($detail->ingapu_total_students ?? 0) -
                                                (($detail->ingapu_previous ?? 0) +
                                                    ($detail->ingapu_transferable ?? 0)));
                                    @endphp

                                    {{ $eligibleStudents > 0 ? number_format(($plan->received_books ?? 0) / $eligibleStudents, 2) : 0 }}
                                </td>

                                {{-- ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီးကျောင်းသားဦးရေ = ကျောင်းသားဦးရေ - (ယခင်နှစ်လက်ကျန်စာအုပ် + လက်ဆင့်ကမ်းအသုံးပြုနိုင်) --}}
                                <td>
                                    {{ number_format(
                                        ($detail->myanaung_total_students ?? 0) -
                                            (($detail->myanaung_previous ?? 0) + ($detail->myanaung_transferable ?? 0)),
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        ($detail->kyankhin_total_students ?? 0) -
                                            (($detail->kyankhin_previous ?? 0) + ($detail->kyankhin_transferable ?? 0)),
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        ($detail->ingapu_total_students ?? 0) -
                                            (($detail->ingapu_previous ?? 0) + ($detail->ingapu_transferable ?? 0)),
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        ($detail->myanaung_total_students ?? 0) -
                                            (($detail->myanaung_previous ?? 0) + ($detail->myanaung_transferable ?? 0)) +
                                            (($detail->kyankhin_total_students ?? 0) -
                                                (($detail->kyankhin_previous ?? 0) + ($detail->kyankhin_transferable ?? 0))) +
                                            (($detail->ingapu_total_students ?? 0) -
                                                (($detail->ingapu_previous ?? 0) + ($detail->ingapu_transferable ?? 0))),
                                    ) }}
                                </td>

                                {{-- ခွဲတမ်းပေးရန်အုပ်အရေအတွက် = အချိုး × ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီးကျောင်းသားဦးရေ --}}
                                @php
                                    $eligibleMyanaung =
                                        ($detail->myanaung_total_students ?? 0) -
                                        (($detail->myanaung_previous ?? 0) +
                                            ($detail->myanaung_transferable ?? 0));

                                    $eligibleKyankhin =
                                        ($detail->kyankhin_total_students ?? 0) -
                                        (($detail->kyankhin_previous ?? 0) +
                                            ($detail->kyankhin_transferable ?? 0));

                                    $eligibleIngapu =
                                        ($detail->ingapu_total_students ?? 0) -
                                        (($detail->ingapu_previous ?? 0) +
                                            ($detail->ingapu_transferable ?? 0));

                                    $eligibleTotal = $eligibleMyanaung + $eligibleKyankhin + $eligibleIngapu;

                                    $ratio = $eligibleTotal > 0 ? ($plan->received_books ?? 0) / $eligibleTotal : 0;
                                @endphp

                                <td>
                                    {{ number_format($ratio * $eligibleMyanaung) }}
                                </td>

                                <td>
                                    {{ number_format($ratio * $eligibleKyankhin) }}
                                </td>

                                <td>
                                    {{ number_format($ratio * $eligibleIngapu) }}
                                </td>

                                <td>
                                    {{ number_format($ratio * $eligibleMyanaung + $ratio * $eligibleKyankhin + $ratio * $eligibleIngapu) }}
                                </td>

                                {{-- ခွဲတမ်းပေးရန်အိတ်ပြည့် = ခွဲတမ်းပေးရန်အုပ်အရေအတွက် ÷ တစ်အိတ်ပါယူနစ် --}}
                                @php
                                    $eligibleMyanaung =
                                        ($detail->myanaung_total_students ?? 0) -
                                        (($detail->myanaung_previous ?? 0) +
                                            ($detail->myanaung_transferable ?? 0));

                                    $eligibleKyankhin =
                                        ($detail->kyankhin_total_students ?? 0) -
                                        (($detail->kyankhin_previous ?? 0) +
                                            ($detail->kyankhin_transferable ?? 0));

                                    $eligibleIngapu =
                                        ($detail->ingapu_total_students ?? 0) -
                                        (($detail->ingapu_previous ?? 0) +
                                            ($detail->ingapu_transferable ?? 0));

                                    $eligibleTotal = $eligibleMyanaung + $eligibleKyankhin + $eligibleIngapu;

                                    $ratio = $eligibleTotal > 0 ? ($plan->received_books ?? 0) / $eligibleTotal : 0;

                                    $allocationMyanaung = $ratio * $eligibleMyanaung;
                                    $allocationKyankhin = $ratio * $eligibleKyankhin;
                                    $allocationIngapu = $ratio * $eligibleIngapu;

                                    $unit = $plan->books_per_package ?? 0;
                                @endphp

                                <td>
                                    {{ $unit > 0 ? floor($allocationMyanaung / $unit) : 0 }}
                                </td>

                                <td>
                                    {{ $unit > 0 ? floor($allocationKyankhin / $unit) : 0 }}
                                </td>

                                <td>
                                    {{ $unit > 0 ? floor($allocationIngapu / $unit) : 0 }}
                                </td>

                                {{-- ခွဲတမ်းပေးရန်အပြေအုပ်အရေအတွက် = ခွဲတမ်းပေးရန်အုပ်အရေအတွက် ÷ တစ်အိတ်ပါယူနစ် (စားကြွင်း) --}}
                                @php
                                    $eligibleMyanaung =
                                        ($detail->myanaung_total_students ?? 0) -
                                        (($detail->myanaung_previous ?? 0) +
                                            ($detail->myanaung_transferable ?? 0));

                                    $eligibleKyankhin =
                                        ($detail->kyankhin_total_students ?? 0) -
                                        (($detail->kyankhin_previous ?? 0) +
                                            ($detail->kyankhin_transferable ?? 0));

                                    $eligibleIngapu =
                                        ($detail->ingapu_total_students ?? 0) -
                                        (($detail->ingapu_previous ?? 0) +
                                            ($detail->ingapu_transferable ?? 0));

                                    $eligibleTotal = $eligibleMyanaung + $eligibleKyankhin + $eligibleIngapu;

                                    $ratio = $eligibleTotal > 0 ? ($plan->received_books ?? 0) / $eligibleTotal : 0;

                                    $allocationMyanaung = round($ratio * $eligibleMyanaung);
                                    $allocationKyankhin = round($ratio * $eligibleKyankhin);
                                    $allocationIngapu = round($ratio * $eligibleIngapu);

                                    $unit = $plan->books_per_package ?? 0;
                                @endphp

                                <td>
                                    {{ $unit > 0 ? $allocationMyanaung % $unit : 0 }}
                                </td>

                                <td>
                                    {{ $unit > 0 ? $allocationKyankhin % $unit : 0 }}
                                </td>

                                <td>
                                    {{ $unit > 0 ? $allocationIngapu % $unit : 0 }}
                                </td>

                                {{-- ယခင်နှစ်လက်ကျန်စာအုပ် --}}
                                <td>
                                    {{ number_format($detail->myanaung_previous ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($detail->kyankhin_previous ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($detail->ingapu_previous ?? 0) }}
                                </td>

                                {{-- ကျောင်းသားဦးရေ --}}
                                <td>
                                    {{ number_format($detail->myanaung_total_students ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($detail->kyankhin_total_students ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($detail->ingapu_total_students ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        ($detail->myanaung_total_students ?? 0) +
                                            ($detail->kyankhin_total_students ?? 0) +
                                            ($detail->ingapu_total_students ?? 0),
                                    ) }}
                                </td>

                                {{-- လက်ဆင့်ကမ်း(အသုံးပြုနိုင်) --}}
                                <td>
                                    {{ number_format($detail->myanaung_transferable ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($detail->kyankhin_transferable ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format($detail->ingapu_transferable ?? 0) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        ($detail->myanaung_transferable ?? 0) +
                                            ($detail->kyankhin_transferable ?? 0) +
                                            ($detail->ingapu_transferable ?? 0),
                                    ) }}
                                </td>

                                {{-- ယခင်နှစ်လက်ကျန် + ထုတ်ပေး + လက်ဆင့်ကမ်း --}}
                                {{-- Formula = ယခင်နှစ်လက်ကျန်စာအုပ် + ခွဲတမ်းပေးရန်အုပ်အရေအတွက် + လက်ဆင့်ကမ်းအသုံးပြုနိုင် --}}
                                @php
                                    $eligibleMyanaung =
                                        ($detail->myanaung_total_students ?? 0) -
                                        (($detail->myanaung_previous ?? 0) +
                                            ($detail->myanaung_transferable ?? 0));

                                    $eligibleKyankhin =
                                        ($detail->kyankhin_total_students ?? 0) -
                                        (($detail->kyankhin_previous ?? 0) +
                                            ($detail->kyankhin_transferable ?? 0));

                                    $eligibleIngapu =
                                        ($detail->ingapu_total_students ?? 0) -
                                        (($detail->ingapu_previous ?? 0) +
                                            ($detail->ingapu_transferable ?? 0));

                                    $eligibleTotal = $eligibleMyanaung + $eligibleKyankhin + $eligibleIngapu;

                                    $ratio = $eligibleTotal > 0 ? ($plan->received_books ?? 0) / $eligibleTotal : 0;

                                    $allocationMyanaung = round($ratio * $eligibleMyanaung);
                                    $allocationKyankhin = round($ratio * $eligibleKyankhin);
                                    $allocationIngapu = round($ratio * $eligibleIngapu);

                                    $finalMyanaung =
                                        ($detail->myanaung_previous ?? 0) +
                                        $allocationMyanaung +
                                        ($detail->myanaung_transferable ?? 0);

                                    $finalKyankhin =
                                        ($detail->kyankhin_previous ?? 0) +
                                        $allocationKyankhin +
                                        ($detail->kyankhin_transferable ?? 0);

                                    $finalIngapu =
                                        ($detail->ingapu_previous ?? 0) +
                                        $allocationIngapu +
                                        ($detail->ingapu_transferable ?? 0);
                                @endphp

                                <td>
                                    {{ number_format($finalMyanaung) }}
                                </td>

                                <td>
                                    {{ number_format($finalKyankhin) }}
                                </td>

                                <td>
                                    {{ number_format($finalIngapu) }}
                                </td>

                                <td>
                                    {{ number_format($finalMyanaung + $finalKyankhin + $finalIngapu) }}
                                </td>

                                {{-- ကျောင်းသားအရအပိုအလို = လကထုတ်ပေးလက်ဆင့်ကမ်း - ကျောင်းသားဦးရေ --}}
                                @php
                                    $eligibleMyanaung =
                                        ($detail->myanaung_total_students ?? 0) -
                                        (($detail->myanaung_previous ?? 0) +
                                            ($detail->myanaung_transferable ?? 0));

                                    $eligibleKyankhin =
                                        ($detail->kyankhin_total_students ?? 0) -
                                        (($detail->kyankhin_previous ?? 0) +
                                            ($detail->kyankhin_transferable ?? 0));

                                    $eligibleIngapu =
                                        ($detail->ingapu_total_students ?? 0) -
                                        (($detail->ingapu_previous ?? 0) +
                                            ($detail->ingapu_transferable ?? 0));

                                    $eligibleTotal = $eligibleMyanaung + $eligibleKyankhin + $eligibleIngapu;

                                    $ratio = $eligibleTotal > 0 ? ($plan->received_books ?? 0) / $eligibleTotal : 0;

                                    $allocationMyanaung = round($ratio * $eligibleMyanaung);
                                    $allocationKyankhin = round($ratio * $eligibleKyankhin);
                                    $allocationIngapu = round($ratio * $eligibleIngapu);

                                    $finalMyanaung =
                                        ($detail->myanaung_previous ?? 0) +
                                        $allocationMyanaung +
                                        ($detail->myanaung_transferable ?? 0);

                                    $finalKyankhin =
                                        ($detail->kyankhin_previous ?? 0) +
                                        $allocationKyankhin +
                                        ($detail->kyankhin_transferable ?? 0);

                                    $finalIngapu =
                                        ($detail->ingapu_previous ?? 0) +
                                        $allocationIngapu +
                                        ($detail->ingapu_transferable ?? 0);
                                @endphp

                                <td>
                                    {{ number_format($finalMyanaung - ($detail->myanaung_total_students ?? 0)) }}
                                </td>

                                <td>
                                    {{ number_format($finalKyankhin - ($detail->kyankhin_total_students ?? 0)) }}
                                </td>

                                <td>
                                    {{ number_format($finalIngapu - ($detail->ingapu_total_students ?? 0)) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $finalMyanaung -
                                            ($detail->myanaung_total_students ?? 0) +
                                            ($finalKyankhin - ($detail->kyankhin_total_students ?? 0)) +
                                            ($finalIngapu - ($detail->ingapu_total_students ?? 0)),
                                    ) }}
                                </td>

                                <td class="whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 justify-center">
                                        <a href="{{ route('allocation-plans.edit', $plan->id) }}"
                                            class="btn-modern-warning" title="ပြင်ဆင်ပါ">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if (auth()->user()?->canDeleteRecords())
                                            <form action="{{ route('allocation-plans.destroy', $plan->id) }}" method="POST"
                                                class="m-0 d-inline">
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
            @include('partials.pagination', ['items' => $plans])
        @endif
        @php
            $allocationFirstItem = $plans->firstItem() ?? 1;
            $allocationExportRows = collect($plans->items())->values()->map(function ($plan, $index) use ($allocationFirstItem) {
                $detail = $plan->detailCompat() ?? (object) [];
                $unit = max(0, (int) ($plan->books_per_package ?? 0));

                $eligibleM = (int) ($detail->myanaung_total_students ?? 0)
                    - ((int) ($detail->myanaung_previous ?? 0) + (int) ($detail->myanaung_transferable ?? 0));
                $eligibleK = (int) ($detail->kyankhin_total_students ?? 0)
                    - ((int) ($detail->kyankhin_previous ?? 0) + (int) ($detail->kyankhin_transferable ?? 0));
                $eligibleI = (int) ($detail->ingapu_total_students ?? 0)
                    - ((int) ($detail->ingapu_previous ?? 0) + (int) ($detail->ingapu_transferable ?? 0));
                $eligibleTotal = $eligibleM + $eligibleK + $eligibleI;

                $ratio = $eligibleTotal > 0 ? ((float) ($plan->received_books ?? 0) / $eligibleTotal) : 0;
                $allocM = (int) round($ratio * $eligibleM);
                $allocK = (int) round($ratio * $eligibleK);
                $allocI = (int) round($ratio * $eligibleI);

                $prevM = (int) ($detail->myanaung_previous ?? 0);
                $prevK = (int) ($detail->kyankhin_previous ?? 0);
                $prevI = (int) ($detail->ingapu_previous ?? 0);
                $studentsM = (int) ($detail->myanaung_total_students ?? 0);
                $studentsK = (int) ($detail->kyankhin_total_students ?? 0);
                $studentsI = (int) ($detail->ingapu_total_students ?? 0);
                $transferM = (int) ($detail->myanaung_transferable ?? 0);
                $transferK = (int) ($detail->kyankhin_transferable ?? 0);
                $transferI = (int) ($detail->ingapu_transferable ?? 0);

                $finalM = $prevM + $allocM + $transferM;
                $finalK = $prevK + $allocK + $transferK;
                $finalI = $prevI + $allocI + $transferI;

                $diffM = $finalM - $studentsM;
                $diffK = $finalK - $studentsK;
                $diffI = $finalI - $studentsI;

                return [
                    $allocationFirstItem + $index,
                    $plan->grade?->name ?? '',
                    $plan->bookName?->name ?? '',
                    (int) ($plan->received_books ?? 0),
                    $unit,
                    number_format($ratio, 2, '.', ''),
                    $eligibleM,
                    $eligibleK,
                    $eligibleI,
                    $eligibleTotal,
                    $allocM,
                    $allocK,
                    $allocI,
                    $allocM + $allocK + $allocI,
                    $unit > 0 ? intdiv($allocM, $unit) : 0,
                    $unit > 0 ? intdiv($allocK, $unit) : 0,
                    $unit > 0 ? intdiv($allocI, $unit) : 0,
                    $unit > 0 ? ($allocM % $unit) : 0,
                    $unit > 0 ? ($allocK % $unit) : 0,
                    $unit > 0 ? ($allocI % $unit) : 0,
                    $prevM,
                    $prevK,
                    $prevI,
                    $studentsM,
                    $studentsK,
                    $studentsI,
                    $studentsM + $studentsK + $studentsI,
                    $transferM,
                    $transferK,
                    $transferI,
                    $transferM + $transferK + $transferI,
                    $finalM,
                    $finalK,
                    $finalI,
                    $finalM + $finalK + $finalI,
                    $diffM,
                    $diffK,
                    $diffI,
                    $diffM + $diffK + $diffI,
                ];
            })->all();
        @endphp
        <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>
        <script>
            async function exportAllocationPlan(format) {
                const rows = @json($allocationExportRows);
                const workbook = new ExcelJS.Workbook();
                const sheet = workbook.addWorksheet('Allocation Plan');
                const colCount = 39;
                const headerFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9EAD3' } };

                sheet.columns = Array.from({ length: colCount }, (_, i) => ({
                    width: i === 0 ? 5 : (i === 1 ? 14 : (i === 2 ? 24 : 11)),
                }));

                sheet.mergeCells(1, 1, 1, colCount);
                sheet.getCell(1, 1).value = 'ခရိုင်ခွဲတမ်းတွက်ချက်မှုစာရင်း';
                sheet.getCell(1, 1).font = { bold: true, size: 14 };
                sheet.getCell(1, 1).alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                sheet.getRow(1).height = 28;

                sheet.mergeCells('A3:A4');
                sheet.mergeCells('B3:B4');
                sheet.mergeCells('C3:C4');
                sheet.mergeCells('D3:D4');
                sheet.mergeCells('E3:E4');
                sheet.mergeCells('F3:F4');
                sheet.mergeCells('G3:J3');
                sheet.mergeCells('K3:N3');
                sheet.mergeCells('O3:Q3');
                sheet.mergeCells('R3:T3');
                sheet.mergeCells('U3:W3');
                sheet.mergeCells('X3:AA3');
                sheet.mergeCells('AB3:AE3');
                sheet.mergeCells('AF3:AI3');
                sheet.mergeCells('AJ3:AM3');

                const topHeaders = [
                    [1, 'စဉ်'],
                    [2, 'အတန်း'],
                    [3, 'ဘာသာ'],
                    [4, 'ရရှိအုပ်ရေ'],
                    [5, 'တစ်အိတ်ပါ Unit'],
                    [6, 'အချိုး'],
                    [7, 'ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီးကျောင်းသားဦးရေ'],
                    [11, 'ခွဲတမ်းပေးရန်အုပ်အရေအတွက်'],
                    [15, 'ခွဲတမ်းပေးရန်အိတ်'],
                    [18, 'ခွဲတမ်းပေးရန်အပြေအုပ်အရေအတွက်'],
                    [21, 'ယခင်နှစ်လက်ကျန်စာအုပ်'],
                    [24, 'ကျောင်းသားဦးရေ'],
                    [28, 'လက်ဆင့်ကမ်း(အသုံးပြုနိုင်)'],
                    [32, 'ယခင်နှစ်လက်ကျန် + ထုတ်ပေး + လက်ဆင့်ကမ်း'],
                    [36, 'ကျောင်းသားအရအပိုအလို'],
                ];
                topHeaders.forEach(([col, text]) => {
                    sheet.getCell(3, col).value = text;
                });

                const subLabels = ['မြန်အောင်', 'ကြံခင်း', 'အင်္ဂပူ', 'စုစုပေါင်း'];
                const subGroups = [
                    [7, 4],
                    [11, 4],
                    [15, 3],
                    [18, 3],
                    [21, 3],
                    [24, 4],
                    [28, 4],
                    [32, 4],
                    [36, 4],
                ];
                subGroups.forEach(([start, count]) => {
                    for (let i = 0; i < count; i++) {
                        sheet.getCell(4, start + i).value = subLabels[i];
                    }
                });

                rows.forEach((rowValues) => {
                    sheet.addRow(rowValues);
                });

                sheet.eachRow((row, rowNumber) => {
                    for (let c = 1; c <= colCount; c++) {
                        const cell = row.getCell(c);
                        cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                        cell.border = {
                            top: { style: 'thin' },
                            left: { style: 'thin' },
                            bottom: { style: 'thin' },
                            right: { style: 'thin' },
                        };
                        if (rowNumber === 3 || rowNumber === 4) {
                            cell.font = { bold: true };
                            cell.fill = headerFill;
                        }
                    }
                });

                sheet.getRow(3).height = 34;
                sheet.getRow(4).height = 24;

                await DerasPdf.downloadWorkbook(workbook, sheet, 'ခွဲတမ်းတွက်ချက်မှု.xlsx', format);
            }
        </script>
    </div>
@endsection
