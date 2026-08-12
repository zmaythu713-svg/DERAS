@csrf

@php
    $isEdit = isset($teacherGuideSummary) && $teacherGuideSummary->exists;
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

    /* ===== Section Header Title ===== */
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #105c3a;
        border-bottom: 2px solid #d1fae5;
        padding-bottom: 8px;
        margin-top: 20px;
        margin-bottom: 20px;
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

    .calc-blue-input {
        background-color: #eff6ff !important;
        color: #1e40af !important;
        border-color: #bfdbfe !important;
        font-weight: 700;
        cursor: default !important;
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
    <div class="alert alert-danger rounded-3 mb-4 py-2 px-3" style="font-size:14px;">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== Section: အခြေခံအချက်အလက် ===== --}}
<div class="section-title">
    <i class="fas fa-info-circle me-2"></i>အခြေခံအချက်အလက်
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
        </label>
        <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
            <option value="">-- ရွေးချယ်ပါ --</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}"
                    {{ (string) old('academic_year_id', isset($teacherGuideSummary) ? $teacherGuideSummary->academic_year_id : ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
        @error('academic_year_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-graduation-cap me-1"></i>အတန်း <span class="text-danger">*</span>
        </label>
        <select name="grade_id" id="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
            <option value="">-- ရွေးချယ်ပါ --</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}"
                    {{ (string) old('grade_id', isset($teacherGuideSummary) ? $teacherGuideSummary->grade_id : ($preselectedGradeId ?? '')) === (string) $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
        @error('grade_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-tags me-1"></i>အမျိုးအစား <span class="text-danger">*</span>
        </label>
        <select name="guide_type" id="guide_type" class="form-select @error('guide_type') is-invalid @enderror" required>
            @php $selectedGuideType = old('guide_type', isset($teacherGuideSummary) ? $teacherGuideSummary->guide_type : ($preselectedGuideType ?? 'ဆရာလမ်းညွှန်')); @endphp
            <option value="ဆရာလမ်းညွှန်"
                {{ $selectedGuideType === 'ဆရာလမ်းညွှန်' ? 'selected' : '' }}>
                ဆရာလမ်းညွှန်
            </option>
            <option value="ဆရာကိုင်"
                {{ $selectedGuideType === 'ဆရာကိုင်' ? 'selected' : '' }}>
                ဆရာကိုင်
            </option>
        </select>
        @error('guide_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-book me-1"></i>ဘာသာအမည် <span class="text-danger">*</span>
        </label>
        <select name="book_name_id" id="book_name_id" class="form-select @error('book_name_id') is-invalid @enderror" required
            data-placeholder="-- ရွေးချယ်ပါ --">
            <option value="">-- ရွေးချယ်ပါ --</option>
            @if ($isEdit && $teacherGuideSummary->bookName)
                <option value="{{ $teacherGuideSummary->book_name_id }}" selected>
                    {{ $teacherGuideSummary->bookName->name }}
                </option>
            @endif
        </select>
        @error('book_name_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Section: စာအုပ်စာရင်းတွက်ချက်မှု ===== --}}
<div class="section-title">
    <i class="fas fa-calculator me-2"></i>စာအုပ်စာရင်းတွက်ချက်မှု
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label">
            <i class="fas fa-history me-1"></i>ယခင်နှစ်လက်ကျန်
        </label>
        <input type="number" name="previous_balance" id="previous_balance" min="0" class="form-control calc-field"
            value="{{ \App\Support\FormValue::number(old('previous_balance', $teacherGuideSummary->previous_balance ?? null)) }}"
            placeholder="0">
    </div>

    <div class="col-md-4">
        <label class="form-label">
            <i class="fas fa-layer-group me-1"></i>ဘဏ္ဍာရေးနှစ်ခွဲတမ်း
        </label>
        <input type="number" name="fiscal_year_quota" id="fiscal_year_quota" min="0" class="form-control calc-field calc-blue-input"
            value="{{ \App\Support\FormValue::number(old('fiscal_year_quota', $teacherGuideSummary->fiscal_year_quota ?? null)) }}" readonly placeholder="0">
    </div>

    <div class="col-md-4">
        <label class="form-label">
            <i class="fas fa-plus-circle me-1"></i>စုစုပေါင်းအုပ်ရေ
        </label>
        <input type="number" id="total_books" class="form-control fw-bold"
            style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;" readonly placeholder="0">
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <label class="form-label">
            <i class="fas fa-truck-loading me-1"></i>ဖြန့်ဝေပြီးအုပ်ရေ
        </label>
        <input type="number" name="distributed_books" id="distributed_books" min="0" class="form-control calc-field calc-blue-input"
            value="{{ \App\Support\FormValue::number(old('distributed_books', $teacherGuideSummary->distributed_books ?? null)) }}" readonly placeholder="0">
    </div>

    <div class="col-md-6">
        <label class="form-label">
            <i class="fas fa-warehouse me-1"></i>လက်ကျန်
        </label>
        <input type="number" id="remaining_books" class="form-control fw-bold"
            style="background-color: #fff9e6; color: #b45309; border-color: #fde68a;" readonly placeholder="0">
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <label class="form-label">
            <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
        </label>
        <textarea name="remark" rows="3" class="form-control" placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">{{ old('remark', $teacherGuideSummary->remark ?? '') }}</textarea>
    </div>
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('teacher-guide-summaries.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($teacherGuideSummary) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($teacherGuideSummary) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previous = document.querySelector('[name="previous_balance"]');
        const quota = document.querySelector('[name="fiscal_year_quota"]');
        const distributed = document.querySelector('[name="distributed_books"]');
        const total = document.getElementById('total_books');
        const remaining = document.getElementById('remaining_books');

        function calculate() {
            const totalValue = Number(previous?.value || 0) + Number(quota?.value || 0);
            const remainingValue = totalValue - Number(distributed?.value || 0);
            const blankZero = window.DerasForm?.displayNumber || function (v) {
                return Number(v) === 0 ? '' : v;
            };
            if (total) total.value = blankZero(totalValue);
            if (remaining) remaining.value = blankZero(remainingValue);
        }

        if (window.DerasForm) {
            DerasForm.wireTeacherGuideSummaryForm({
                yearSelect: '#academic_year_id',
                gradeSelect: '#grade_id',
                guideTypeSelect: '#guide_type',
                bookSelect: '#book_name_id',
                fiscalInput: '#fiscal_year_quota',
                distributedInput: '#distributed_books',
                previousInput: '#previous_balance',
                onRecalc: calculate,
            });
        }

        calculate();
    });
</script>
