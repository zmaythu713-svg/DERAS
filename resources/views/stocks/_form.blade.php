@csrf

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

{{-- ===== Row 1: ပညာသင်နှစ် | မြို့နယ် | အတန်း ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
        </label>
        <select name="academic_year_id" class="form-select" required>
            <option value="">ပညာသင်နှစ်ရွေးချယ်ပါ</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}"
                    {{ (string) old('academic_year_id', $stock->academic_year_id ?? ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-map-marker-alt me-1"></i>မြို့နယ် <span class="text-danger">*</span>
        </label>
        <select name="township_id" class="form-select" required>
            <option value="">မြို့နယ်ရွေးချယ်ပါ</option>
            @foreach ($townships as $township)
                <option value="{{ $township->id }}"
                    {{ (string) old('township_id', $stock->township_id ?? ($preselectedTownshipId ?? '')) === (string) $township->id ? 'selected' : '' }}>
                    {{ $township->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-graduation-cap me-1"></i>အတန်း <span class="text-danger">*</span>
        </label>
        <select name="grade_id" id="grade_id" class="form-select" required>
            <option value="">အတန်းရွေးချယ်ပါ</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}"
                    {{ old('grade_id', $stock->grade_id ?? '') == $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ===== Row 2: ဘာသာ | ယခင်ယခင်နှစ်လက်ကျန် | လက်ဆင့်ကမ်း ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-book me-1"></i>ဘာသာ <span class="text-danger">*</span>
        </label>
        <select name="book_name_id" id="book_name_id" class="form-select" required
            data-placeholder="ဘာသာရပ်ရွေးချယ်ပါ">
            <option value="">ဘာသာရပ်ရွေးချယ်ပါ</option>
            @php $selectedBookId = old('book_name_id', $stock->book_name_id ?? ''); @endphp
            @if ($selectedBookId)
                @foreach ($bookNames as $bookName)
                    @if ((string) $bookName->id === (string) $selectedBookId)
                        <option value="{{ $bookName->id }}" selected>{{ $bookName->name }}</option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-history me-1"></i>ယခင်နှစ်လက်ကျန်
        </label>
        <input type="number" name="previous_balance" class="form-control"
            value="{{ \App\Support\FormValue::number(old('previous_balance', $stock->previous_balance ?? null)) }}"
            placeholder="0" min="0">
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-exchange-alt me-1"></i>လက်ဆင့်ကမ်း
        </label>
        <input type="number" name="transferred" class="form-control"
            value="{{ \App\Support\FormValue::number(old('transferred', $stock->transferred ?? null)) }}"
            placeholder="0" min="0">
    </div>
</div>

{{-- ===== Row 3: အပ်နှံပြီးလိုအပ်မှု | လိုအပ်မှု | မှတ်ချက် ===== --}}
<div class="row mb-2" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-check-circle me-1"></i>အပ်နှံပြီးလိုအပ်မှု
        </label>
        <input type="number" name="enrolled_need" class="form-control"
            value="{{ \App\Support\FormValue::number(old('enrolled_need', $stock->enrolled_need ?? null)) }}"
            placeholder="0" min="0">
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-list-ol me-1"></i>လိုအပ်မှု
        </label>
        <input type="number" name="required_qty" class="form-control"
            value="{{ \App\Support\FormValue::number(old('required_qty', $stock->required_qty ?? null)) }}"
            placeholder="0" min="0">
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
        </label>
        <input type="text" name="remark" class="form-control"
            value="{{ old('remark', $stock->remark ?? '') }}"
            placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">
    </div>
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('stocks.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($stock) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($stock) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
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
        DerasForm.wirePreviousBalanceAutofill({
            yearSelect: '[name="academic_year_id"]',
            townshipSelect: '[name="township_id"]',
            gradeSelect: '#grade_id',
            subjectSelect: '#book_name_id',
            balanceInput: '[name="previous_balance"]',
        });
    });
</script>
