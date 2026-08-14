@php
    $plan = $plan ?? ($allocationPlan ?? null);
    $isEdit = $plan !== null;
    $detail = $plan?->detailCompat();

    $districts = [
        'myanaung' => 'မြန်အောင်',
        'kyankhin' => 'ကြံခင်း',
        'ingapu'   => 'အင်္ဂပူ',
    ];

    foreach (array_keys($districts) as $district) {
        ${$district . 'Previous'}      = (int) old($district . '_previous', $detail?->{$district . '_previous'} ?? 0);
        ${$district . 'TotalStudents'} = (int) old($district . '_total_students', $detail?->{$district . '_total_students'} ?? 0);
        ${$district . 'Transferable'}  = (int) old($district . '_transferable', $detail?->{$district . '_transferable'} ?? 0);
        ${$district . 'Eligible'}      = ${$district . 'TotalStudents'} - (${$district . 'Previous'} + ${$district . 'Transferable'});
    }

    $eligibleTotal   = $myanaungEligible + $kyankhinEligible + $ingapuEligible;
    $receivedBooks   = (int) old('received_books', $plan?->received_books ?? 0);
    $booksPerPackage = (int) old('books_per_package', $plan?->books_per_package ?? 1);
    $ratio = $eligibleTotal > 0 ? $receivedBooks / $eligibleTotal : 0;

    foreach (array_keys($districts) as $district) {
        ${$district . 'Allocation'} = round($ratio * ${$district . 'Eligible'});
        ${$district . 'Package'}    = $booksPerPackage > 0 ? floor(${$district . 'Allocation'} / $booksPerPackage) : 0;
        ${$district . 'Loose'}      = $booksPerPackage > 0 ? ${$district . 'Allocation'} % $booksPerPackage : 0;
        ${$district . 'Final'}      = ${$district . 'Previous'} + ${$district . 'Allocation'} + ${$district . 'Transferable'};
        ${$district . 'Difference'} = ${$district . 'Final'} - ${$district . 'TotalStudents'};
    }

    $allocationTotal   = $myanaungAllocation + $kyankhinAllocation + $ingapuAllocation;
    $studentCountTotal = $myanaungTotalStudents + $kyankhinTotalStudents + $ingapuTotalStudents;
    $transferableTotal = $myanaungTransferable + $kyankhinTransferable + $ingapuTransferable;
    $availableTotal    = $myanaungFinal + $kyankhinFinal + $ingapuFinal;
    $differenceTotal   = $myanaungDifference + $kyankhinDifference + $ingapuDifference;
@endphp

<style>
    /* ===== Green Focus ===== */
    .form-select:focus,
    .form-control:focus {
        border-color: #105c3a !important;
        box-shadow: 0 0 0 0.2rem rgba(16, 92, 58, 0.18) !important;
        outline: none !important;
    }

    /* ===== Uniform height & style for ALL fields ===== */
    .form-select,
    .form-control {
        height: 48px !important;
        font-size: 15px !important;
        border-radius: 8px !important;
        border: 1.5px solid #dee2e6 !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        padding: 0.5rem 0.75rem;
        width: 100% !important;
    }

    textarea.form-control {
        height: auto !important;
    }

    /* ===== Labels ===== */
    .form-label {
        font-size: 15px;
        font-weight: 600;
        color: #105c3a;
        margin-bottom: 10px;
        display: block;
    }

    .form-label i {
        color: #105c3a;
    }

    .form-label.text-secondary {
        color: #6b7280 !important;
    }

    /* ===== Section Dividers ===== */
    .section-title {
        font-size: 17px;
        font-weight: 700;
        color: #105c3a;
        letter-spacing: 0.03em;
        padding: 0 0 8px 0;
        margin: 24px 0 16px 0;
        border-bottom: 2px solid #d1fae5;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: #105c3a;
        font-size: 15px;
    }

    .section-title.secondary {
        color: #105c3a;
        border-bottom-color: #d1fae5;
    }

    .section-title.dark {
        color: #105c3a;
        border-bottom-color: #105c3a;
    }

    .section-title:first-child {
        margin-top: 0;
    }

    /* ===== Calculated (disabled) inputs ===== */
    .calc-input {
        background-color: #f0faf4 !important;
        color: #105c3a !important;
        border-color: #aad6bc !important;
        font-weight: 700;
        text-align: center;
    }

    /* ===== Buttons ===== */
    .btn-back {
        background: #ffffff;
        border: 1.5px solid #105c3a;
        color: #105c3a;
        border-radius: 8px;
        padding: 9px 24px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-back:hover {
        background: #051c14;
        color: #ffffff;
        text-decoration: none;
        border-color: #051c14;
    }

    .btn-save {
        background: #072a1e;
        border: none;
        color: #fff;
        border-radius: 8px;
        padding: 9px 24px;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(7, 42, 30, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .btn-save:hover {
        background: #051c14;
        color: #fff;
        box-shadow: 0 4px 14px rgba(7, 42, 30, 0.35);
        transform: translateY(-1px);
    }

    /* ===== Form Footer ===== */
    .tb-form-footer {
        background: #f8fbf9;
        border-top: 1px solid #d1fae5;
        margin: 24px -28px -24px -28px;
        padding: 16px 28px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

{{-- Validation Errors --}}
@if ($errors->any())
    <div class="gap-2 mb-4 border-0 alert rounded-3 d-flex align-items-start"
        style="background-color:#fef2f2; border-left:4px solid #dc3545 !important; padding:12px 16px;">
        <i class="mt-1 fas fa-exclamation-triangle text-danger"></i>
        <div>
            <strong class="text-danger" style="font-size:13.5px;">ဖြည့်သွင်းထားသော အချက်အလက်များ ပြန်စစ်ဆေးပါ</strong>
            <ul class="mt-1 mb-0 small text-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ===== Section 1: အခြေခံအချက်အလက် ===== --}}
<div class="section-title">
    <i class="fas fa-info-circle"></i> အခြေခံအချက်အလက်
</div>

{{-- Row 1: ပညာသင်နှစ် | အတန်း | ဘာသာရပ်အမည် (3 Columns) --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label for="academic_year_id" class="form-label">
            <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
        </label>
        <select id="academic_year_id" name="academic_year_id"
            class="form-select @error('academic_year_id') is-invalid @enderror" required>
            <option value="">ပညာသင်နှစ်ရွေးချယ်ပါ</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}"
                    {{ (string) old('academic_year_id', $plan?->academic_year_id ?? ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
        @error('academic_year_id')
            <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 px-md-2">
        <label for="grade_id" class="form-label">
            <i class="fas fa-graduation-cap me-1"></i>အတန်း <span class="text-danger">*</span>
        </label>
        <select id="grade_id" name="grade_id"
            class="form-select @error('grade_id') is-invalid @enderror" required>
            <option value="">အတန်းရွေးချယ်ပါ</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}"
                    {{ (string) old('grade_id', $plan?->grade_id ?? '') === (string) $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
        @error('grade_id')
            <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 ps-md-3">
        <label for="book_name_id" class="form-label">
            <i class="fas fa-book me-1"></i>ဘာသာရပ်အမည် <span class="text-danger">*</span>
        </label>
        <select id="book_name_id" name="book_name_id"
            class="form-select @error('book_name_id') is-invalid @enderror" required
            data-placeholder="ဘာသာရပ်အမည်ရွေးချယ်ပါ">
            <option value="">ဘာသာရပ်အမည်ရွေးချယ်ပါ</option>
            @php $selectedBookId = old('book_name_id', $plan?->book_name_id ?? ''); @endphp
            @if ($selectedBookId)
                @foreach ($bookNames as $book)
                    @if ((string) $book->id === (string) $selectedBookId)
                        <option value="{{ $book->id }}" selected>{{ $book->name }}</option>
                    @endif
                @endforeach
            @endif
        </select>
        @error('book_name_id')
            <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Row 2: ရရှိအုပ်ရေ | တစ်အိတ်ပါ Unit | အချိုး (3 Columns) --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label for="received_books" class="form-label">
            <i class="fas fa-boxes-stacked me-1"></i>ရရှိအုပ်ရေ <span class="text-danger">*</span>
        </label>
        <input type="number" id="received_books" name="received_books" min="0"
            class="form-control @error('received_books') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('received_books', $plan?->received_books)) }}" placeholder="0" required>
        @error('received_books')
            <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 px-md-2">
        <label for="books_per_package" class="form-label">
            <i class="fas fa-box me-1"></i>တစ်အိတ်ပါ Unit <span class="text-danger">*</span>
        </label>
        <input type="number" id="books_per_package" name="books_per_package" min="1"
            class="form-control @error('books_per_package') is-invalid @enderror"
            value="{{ old('books_per_package', $plan?->books_per_package ?? 1) }}" required>
        @error('books_per_package')
            <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    @if ($isEdit)
        <div class="col-md-4 ps-md-3">
            <label class="form-label text-secondary">
                <i class="fas fa-percent me-1"></i>အချိုး
            </label>
            <input type="text" class="form-control calc-input"
                value="{{ number_format($ratio, 2) }}" disabled>
        </div>
    @endif
</div>

{{-- ===== Input Groups: ယခင်နှစ်လက်ကျန် / ကျောင်းသား / လက်ဆင့်ကမ်း ===== --}}
@php
    $inputGroups = [
        [
            'title' => 'ယခင်နှစ်လက်ကျန်စာအုပ်',
            'icon' => 'fa-book-open',
            'suffix' => 'previous',
            'auto' => true,
            'hint' => '0',
        ],
        [
            'title' => 'ကျောင်းသားဦးရေ',
            'icon' => 'fa-users',
            'suffix' => 'total_students',
            'auto' => false,
        ],
        [
            'title' => 'လက်ဆင့်ကမ်း(ကျောင်းသားဆီမှပေးအပ်)',
            'icon' => 'fa-exchange-alt',
            'suffix' => 'transferable',
            'auto' => false,
        ],
    ];
@endphp

@foreach ($inputGroups as $group)
    <div class="section-title">
        <i class="fas {{ $group['icon'] }}"></i> {{ $group['title'] }}
    </div>
    <div class="row mb-4" style="row-gap: 0; column-gap: 0;">
        @php
            $districtKeys = array_keys($districts);
        @endphp
        @foreach ($districts as $district => $label)
            @php
                $fieldName = $district . '_' . $group['suffix'];
                $paddingClass = $district === $districtKeys[0] ? 'pe-md-3' : ($district === $districtKeys[count($districtKeys)-1] ? 'ps-md-3' : 'px-md-2');
            @endphp
            <div class="col-md-4 {{ $paddingClass }}">
                <label for="{{ $fieldName }}" class="form-label text-secondary">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $label }} <span class="text-danger">*</span>
                </label>
                <input type="number" id="{{ $fieldName }}" name="{{ $fieldName }}" min="0"
                    class="form-control @if(!empty($group['auto'])) calc-input @endif @error($fieldName) is-invalid @enderror"
                    value="{{ \App\Support\FormValue::number(old($fieldName, $detail?->{$fieldName})) }}"
                    placeholder="0"
                    required
                    @if (!empty($group['auto']) && !$isEdit) readonly @endif>
                @error($fieldName)
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>
        @endforeach
    </div>
@endforeach

{{-- ===== Calculated Sections (Edit only) ===== --}}
@if ($isEdit)
    @php
        $formulaGroups = [
            ['title' => 'ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီး ကျောင်းသားဦးရေ', 'icon' => 'fa-filter',       'values' => [$myanaungEligible, $kyankhinEligible, $ingapuEligible, $eligibleTotal]],
            ['title' => 'ခွဲတမ်းပေးရန် အုပ်အရေအတွက်',       'icon' => 'fa-chart-bar',    'values' => [$myanaungAllocation, $kyankhinAllocation, $ingapuAllocation, $allocationTotal]],
            ['title' => 'ခွဲတမ်းပေးရန် အိတ်ပြည့်',          'icon' => 'fa-box-open',     'values' => [$myanaungPackage, $kyankhinPackage, $ingapuPackage]],
            ['title' => 'ခွဲတမ်းပေးရန် အပြေအုပ်',           'icon' => 'fa-boxes-stacked','values' => [$myanaungLoose, $kyankhinLoose, $ingapuLoose]],
            ['title' => 'ယခင်နှစ်လက်ကျန် + ထုတ်ပေး + လက်ဆင့်ကမ်း',       'icon' => 'fa-layer-group',  'values' => [$myanaungFinal, $kyankhinFinal, $ingapuFinal, $availableTotal]],
            ['title' => 'ကျောင်းသားအရ အပိုအလို',            'icon' => 'fa-balance-scale','values' => [$myanaungDifference, $kyankhinDifference, $ingapuDifference, $differenceTotal]],
        ];
    @endphp

    @foreach ($formulaGroups as $group)
        <div class="section-title secondary">
            <i class="fas {{ $group['icon'] }}"></i> {{ $group['title'] }}
        </div>
        <div class="row mb-4" style="row-gap: 0; column-gap: 0;">
            @foreach ($group['values'] as $index => $value)
                @php
                    $cols   = count($group['values']) === 4 ? '3' : '4';
                    $labels = ['မြန်အောင်', 'ကြံခင်း', 'အင်္ဂပူ', 'စုစုပေါင်း'];
                    $paddingClass = count($group['values']) === 4
                        ? ($index === 0 ? 'pe-md-2' : ($index === 3 ? 'ps-md-2' : 'px-md-2'))
                        : ($index === 0 ? 'pe-md-3' : ($index === 2 ? 'ps-md-3' : 'px-md-2'));
                @endphp
                <div class="col-md-{{ $cols }} {{ $paddingClass }}">
                    <label class="form-label text-secondary">{{ $labels[$index] }}</label>
                    <input type="text" class="form-control calc-input"
                        value="{{ number_format($value) }}" disabled>
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- Grand Summary --}}
    <div class="section-title dark" style="margin-top: 2rem;">
        <i class="fas fa-calculator"></i> စုစုပေါင်း
    </div>
    <div class="row mb-4" style="row-gap: 1.2rem; column-gap: 0;">
        @foreach ([
            ['label' => 'ယခင်နှစ်လက်ကျန်စာအုပ်ဖယ်ပြီး ကျောင်းသားဦးရေ', 'value' => $eligibleTotal],
            ['label' => 'ခွဲတမ်းပေးရန် အုပ်အရေအတွက်',       'value' => $allocationTotal],
            ['label' => 'ကျောင်းသားဦးရေ',                    'value' => $studentCountTotal],
            ['label' => 'လက်ဆင့်ကမ်း',                       'value' => $transferableTotal],
            ['label' => 'ယခင်နှစ်လက်ကျန် + ထုတ်ပေး + လက်ဆင့်ကမ်း',       'value' => $availableTotal],
            ['label' => 'ကျောင်းသားအရ အပိုအလို',             'value' => $differenceTotal],
        ] as $idx => $item)
            @php
                $pos = $idx % 3;
                $paddingClass = $pos === 0 ? 'pe-md-3' : ($pos === 2 ? 'ps-md-3' : 'px-md-2');
            @endphp
            <div class="col-md-4 {{ $paddingClass }}">
                <label class="form-label text-secondary" style="margin-bottom: 10px;">{{ $item['label'] }}</label>
                <input type="text" class="form-control calc-input"
                    value="{{ number_format($item['value']) }}" disabled>
            </div>
        @endforeach
    </div>
@endif

{{-- ===== မှတ်ချက် ===== --}}
<div class="section-title">
    <i class="fas fa-comment-alt"></i> မှတ်ချက်
</div>
<div class="mb-4 row">
    <div class="col-12">
        <textarea id="remark" name="remark" rows="3"
            class="form-control @error('remark') is-invalid @enderror"
            placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">{{ old('remark', $plan?->remark ?? '') }}</textarea>
        @error('remark')
            <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('allocation-plans.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ $isEdit ? 'fa-pen' : 'fa-save' }}"></i>
        {{ $isEdit ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.DerasForm) return;

        DerasForm.wireGradeSubjects({
            gradeSelect: '#grade_id',
            subjectSelect: '#book_name_id',
            categorySlug: 'textbook',
            keepSelected: true,
        });

        const year = document.querySelector('#academic_year_id');
        const grade = document.querySelector('#grade_id');
        const subject = document.querySelector('#book_name_id');
        const isEdit = {{ $isEdit ? 'true' : 'false' }};

        async function fillPreviousBalances() {
            if (!year?.value || !grade?.value || !subject?.value) return;

            try {
                const params = new URLSearchParams({
                    academic_year_id: year.value,
                    grade_id: grade.value,
                    book_name_id: subject.value,
                });
                const data = await DerasForm.fetchJson('/lookups/previous-year-balance?' + params.toString());

                if (!data.found || typeof data.previous_balance !== 'object') {
                    return;
                }

                ['myanaung', 'kyankhin', 'ingapu'].forEach(function (key) {
                    const input = document.querySelector('[name="' + key + '_previous"]');
                    if (!input) return;
                    if (data.previous_balance[key] === undefined) return;
                    // On create always autofill; on edit only if empty
                    if (!isEdit || input.value === '' || Number(input.value) === 0) {
                        input.value = DerasForm.displayNumber(data.previous_balance[key]);
                    }
                });
            } catch (e) {
                console.error(e);
            }
        }

        [year, grade].forEach(function (el) {
            el?.addEventListener('change', fillPreviousBalances);
        });

        // Subject list reloads async after grade change — listen to subject change too
        subject?.addEventListener('change', fillPreviousBalances);

        // Initial fill when editing/create with preselected values
        fillPreviousBalances();
    });
</script>
