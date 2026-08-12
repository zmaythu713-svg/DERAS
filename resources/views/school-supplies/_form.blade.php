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

    .calc-input {
        background-color: #f0faf4 !important;
        color: #105c3a !important;
        border-color: #aad6bc !important;
        font-weight: 700;
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
                    {{ (string) old('academic_year_id', $schoolSupply->academic_year_id ?? ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-map-marker-alt me-1"></i>မြို့နယ်
        </label>
        <select name="township_id" class="form-select">
            <option value="">မြို့နယ်ရွေးချယ်ပါ</option>
            @foreach ($townships as $township)
                <option value="{{ $township->id }}"
                    {{ old('township_id', $schoolSupply->township_id ?? '') == $township->id ? 'selected' : '' }}>
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
                    {{ old('grade_id', $schoolSupply->grade_id ?? '') == $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ===== Row 2: ပစ္စည်းများ | နှုန်း | ကျောင်းအရေအတွက် ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-box-open me-1"></i>ပစ္စည်းများ <span class="text-danger">*</span>
        </label>
        <select name="school_supply_item_id" id="school_supply_item_id" class="form-select" required>
            <option value="" data-rate="0">ပစ္စည်းရွေးချယ်ပါ</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}"
                    data-rate="{{ (int) $item->rate }}"
                    {{ old('school_supply_item_id', $schoolSupply->school_supply_item_id ?? '') == $item->id ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-percentage me-1"></i>နှုန်း
        </label>
        <input type="number" id="item_rate" class="form-control calc-input"
            value="{{ \App\Support\FormValue::number(old('item_rate', isset($schoolSupply) ? optional($schoolSupply->item)->rate : null)) }}"
            min="0" readonly placeholder="0">
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-school me-1"></i>ကျောင်းအရေအတွက်
        </label>
        <input type="number" name="school_count" id="school_count" class="form-control calc-input"
            value="{{ \App\Support\FormValue::number(old('school_count', $schoolSupply->school_count ?? null)) }}"
            min="0" readonly placeholder="0">
    </div>
</div>

{{-- ===== Row 3: အရေအတွက် | မှတ်ချက် ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-calculator me-1"></i>အရေအတွက်
        </label>
        <input type="number" name="quantity" id="quantity" class="form-control calc-input"
            value="{{ \App\Support\FormValue::number(old('quantity', $schoolSupply->quantity ?? null)) }}"
            min="0" readonly placeholder="0">
    </div>

    <div class="col-md-8 ps-md-2">
        <label class="form-label">
            <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
        </label>
        <input type="text" name="remark" class="form-control"
            value="{{ old('remark', $schoolSupply->remark ?? '') }}"
            placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">
    </div>
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('school-supplies.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($schoolSupply) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($schoolSupply) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemSelect = document.getElementById('school_supply_item_id');
        const rateInput = document.getElementById('item_rate');
        const countInput = document.getElementById('school_count');
        const quantityInput = document.getElementById('quantity');

        function recalcQuantity() {
            const rate = parseInt(rateInput.value || '0', 10) || 0;
            const count = parseInt(countInput.value || '0', 10) || 0;
            const qty = rate * count;
            quantityInput.value = (window.DerasForm?.displayNumber || function (v) {
                return Number(v) === 0 ? '' : v;
            })(qty);
        }

        function syncRateFromItem() {
            const selected = itemSelect.options[itemSelect.selectedIndex];
            const rate = selected ? (parseInt(selected.getAttribute('data-rate') || '0', 10) || 0) : 0;
            rateInput.value = itemSelect.value
                ? (window.DerasForm?.displayNumber || function (v) { return Number(v) === 0 ? '' : v; })(rate)
                : '';
            recalcQuantity();
        }

        itemSelect.addEventListener('change', syncRateFromItem);

        // Recalc when school count autofill updates the value
        const countObserver = new MutationObserver(recalcQuantity);
        countObserver.observe(countInput, { attributes: true, attributeFilter: ['value'] });
        countInput.addEventListener('input', recalcQuantity);
        countInput.addEventListener('change', recalcQuantity);

        // Patch value setter so autofill via .value= triggers recalc
        const nativeValueSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
        Object.defineProperty(countInput, 'value', {
            set: function (v) {
                nativeValueSetter.call(this, v);
                recalcQuantity();
            },
            get: function () {
                return Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').get.call(this);
            }
        });

        if (window.DerasForm) {
            DerasForm.wireSchoolCountAutofill({
                yearSelect: '[name="academic_year_id"]',
                townshipSelect: '[name="township_id"]',
                gradeSelect: '#grade_id',
                countInput: '#school_count',
            });
        }

        // Initial sync (edit form / old input)
        syncRateFromItem();
    });
</script>
