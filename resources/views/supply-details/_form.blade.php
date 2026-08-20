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
        <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
            <option value="">ပညာသင်နှစ်ရွေးချယ်ပါ</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}"
                    {{ (string) old('academic_year_id', $supplyDetail->academic_year_id ?? ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-map-marker-alt me-1"></i>မြို့နယ် <span class="text-danger">*</span>
        </label>
        <select name="township_id" class="form-select @error('township_id') is-invalid @enderror" required>
            <option value="">မြို့နယ်ရွေးချယ်ပါ</option>
            @foreach ($townships as $township)
                <option value="{{ $township->id }}"
                    {{ (string) old('township_id', $supplyDetail->township_id ?? ($preselectedTownshipId ?? '')) === (string) $township->id ? 'selected' : '' }}>
                    {{ $township->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-graduation-cap me-1"></i>အတန်း <span class="text-danger">*</span>
        </label>
        <select name="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
            <option value="">အတန်းရွေးချယ်ပါ</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}"
                    {{ (string) old('grade_id', $supplyDetail->grade_id ?? ($preselectedGradeId ?? '')) === (string) $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ===== Row 2: ပစ္စည်းအမျိုးအမည် | လက်ခံရှိမှုအရေအတွက် | ထုတ်ပေးမှု (ဦးရေပေါင်း) ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-box-open me-1"></i>ပစ္စည်းအမျိုးအမည် <span class="text-danger">*</span>
        </label>
        <select name="supply_item_id" class="form-select @error('supply_item_id') is-invalid @enderror" required>
            <option value="">ပစ္စည်းရွေးချယ်ပါ</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}"
                    {{ old('supply_item_id', $supplyDetail->supply_item_id ?? '') == $item->id ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-boxes me-1"></i>လက်ခံရှိမှုအရေအတွက် <span class="text-danger">*</span>
        </label>
        <input type="number" id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror"
            value="{{ old('unit', $supplyDetail->unit ?? '') }}" placeholder="1" min="1" required>
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-sort-numeric-up-alt me-1"></i>ထုတ်ပေးမှု (ဦးရေပေါင်း)
        </label>
        <input type="number" id="issued_total" name="issued_total"
            class="form-control calc-input @error('issued_total') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('issued_total', $supplyDetail->issued_total ?? null)) }}"
            min="0" readonly placeholder="0">
    </div>
</div>

{{-- ===== Row 3: Auto Calculated Fields (Edit Mode) & Remark ===== --}}
<div class="row mb-2" style="row-gap: 0; column-gap: 0;">
    @isset($supplyDetail)
        @php
            $unitValue = (int) old('unit', $supplyDetail->unit ?? 0);
            $issuedValue = (int) old('issued_total', $supplyDetail->issued_total ?? 0);
            $calculatedPackageCount = $unitValue > 0 ? intdiv($issuedValue, $unitValue) : 0;
            $calculatedLooseCount = $unitValue > 0 ? $issuedValue % $unitValue : 0;
        @endphp

        <div class="col-md-4 pe-md-3">
            <label class="form-label">
                <i class="fas fa-archive me-1"></i>ပုံး/အိတ်
            </label>
            <input type="number" id="calculated_package_count" class="form-control fw-bold"
                style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                value="{{ \App\Support\FormValue::number($calculatedPackageCount) }}" disabled placeholder="0">
        </div>

        <div class="col-md-4 px-md-2">
            <label class="form-label">
                <i class="fas fa-cubes me-1"></i>အပြေ
            </label>
            <input type="number" id="calculated_loose_count" class="form-control fw-bold"
                style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                value="{{ \App\Support\FormValue::number($calculatedLooseCount) }}" disabled placeholder="0">
        </div>

        <div class="col-md-4 ps-md-3">
            <label class="form-label">
                <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
            </label>
            <input type="text" name="remark" class="form-control @error('remark') is-invalid @enderror"
                value="{{ old('remark', $supplyDetail->remark ?? '') }}"
                placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">
        </div>
    @else
        <div class="col-12">
            <label class="form-label">
                <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
            </label>
            <input type="text" name="remark" class="form-control @error('remark') is-invalid @enderror"
                value="{{ old('remark', $supplyDetail->remark ?? '') }}"
                placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">
        </div>
    @endisset
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('supply-details.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($supplyDetail) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($supplyDetail) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

@isset($supplyDetail)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitInput = document.getElementById('unit');
            const issuedInput = document.getElementById('issued_total');
            const packageInput = document.getElementById('calculated_package_count');
            const looseInput = document.getElementById('calculated_loose_count');

            function calculatePackageAndLoose() {
                const unit = parseInt(unitInput.value, 10) || 0;
                const issuedTotal = parseInt(issuedInput.value, 10) || 0;
                const blankZero = window.DerasForm?.displayNumber || function (v) {
                    return Number(v) === 0 ? '' : v;
                };

                if (unit <= 0) {
                    packageInput.value = '';
                    looseInput.value = '';
                    return;
                }

                packageInput.value = blankZero(Math.floor(issuedTotal / unit));
                looseInput.value = blankZero(issuedTotal % unit);
            }

            unitInput.addEventListener('input', calculatePackageAndLoose);
            issuedInput.addEventListener('input', calculatePackageAndLoose);
            issuedInput.addEventListener('change', calculatePackageAndLoose);

            if (window.DerasForm) {
                DerasForm.wireSupplyIssuedFromQuota({
                    yearSelect: '[name="academic_year_id"]',
                    townshipSelect: '[name="township_id"]',
                    gradeSelect: '[name="grade_id"]',
                    itemSelect: '[name="supply_item_id"]',
                    issuedInput: '#issued_total',
                });
            }

            calculatePackageAndLoose();
        });
    </script>
@else
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.DerasForm) return;
            DerasForm.wireSupplyIssuedFromQuota({
                yearSelect: '[name="academic_year_id"]',
                townshipSelect: '[name="township_id"]',
                gradeSelect: '[name="grade_id"]',
                itemSelect: '[name="supply_item_id"]',
                issuedInput: '#issued_total',
            });
        });
    </script>
@endisset
