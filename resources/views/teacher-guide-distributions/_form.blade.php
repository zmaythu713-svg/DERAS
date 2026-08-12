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

{{-- ===== Validation Errors ===== --}}
@if ($errors->any())
    <div class="alert alert-danger rounded-3 mb-4 py-2 px-3" style="font-size:14px;">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (isset($teacherGuide))
    {{-- ===== Edit Mode Summary Info Box ===== --}}
    <div class="p-3 mb-4 rounded-3" style="background-color: #f8fbf9; border: 1.5px solid #d1fae5;">
        <div class="row g-3">
            <div class="col-md-3">
                <strong style="color:#105c3a;"><i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ်:</strong>
                <span class="ms-1 fw-bold">{{ $teacherGuide->academicYear?->name }}</span>
            </div>
            <div class="col-md-3">
                <strong style="color:#105c3a;"><i class="fas fa-graduation-cap me-1"></i>အတန်း:</strong>
                <span class="ms-1 fw-bold">{{ $teacherGuide->grade?->name }}</span>
            </div>
            <div class="col-md-3">
                <strong style="color:#105c3a;"><i class="fas fa-book me-1"></i>ဘာသာရပ်:</strong>
                <span class="ms-1 fw-bold">{{ $teacherGuide->bookName?->name }}</span>
            </div>
            <div class="col-md-3">
                <strong style="color:#105c3a;"><i class="fas fa-tags me-1"></i>အမျိုးအစား:</strong>
                <span class="ms-1 fw-bold">{{ $teacherGuide->guide_type }}</span>
            </div>
        </div>
    </div>
@else
    {{-- ===== Create Mode Select Options ===== --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label">
                <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
            </label>
            <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                <option value="">-- ရွေးချယ်ပါ --</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}"
                        {{ (string) old('academic_year_id', isset($teacherGuide) ? $teacherGuide->academic_year_id : ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
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
                        {{ (string) old('grade_id', isset($teacherGuide) ? $teacherGuide->grade_id : ($preselectedGradeId ?? '')) === (string) $grade->id ? 'selected' : '' }}>
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
                @php $selectedGuideType = old('guide_type', isset($teacherGuide) ? $teacherGuide->guide_type : ($preselectedGuideType ?? 'ဆရာလမ်းညွှန်')); @endphp
                <option value="ဆရာလမ်းညွှန်" {{ $selectedGuideType === 'ဆရာလမ်းညွှန်' ? 'selected' : '' }}>ဆရာလမ်းညွှန်</option>
                <option value="ဆရာကိုင်" {{ $selectedGuideType === 'ဆရာကိုင်' ? 'selected' : '' }}>ဆရာကိုင်</option>
            </select>
            @error('guide_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">
                <i class="fas fa-book me-1"></i>ဘာသာရပ်အမည် <span class="text-danger">*</span>
            </label>
            <select name="book_name_id" id="book_name_id" class="form-select @error('book_name_id') is-invalid @enderror" required
                data-placeholder="-- ရွေးချယ်ပါ --">
                <option value="">-- ရွေးချယ်ပါ --</option>
            </select>
            @error('book_name_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endif

{{-- ===== Section: ခရိုင်ရရှိခွဲတမ်း ===== --}}
<div class="section-title">
    <i class="fas fa-layer-group me-2"></i>ခရိုင်ရရှိခွဲတမ်း
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-calculator me-1"></i>KG to G-12 ခရိုင်ရရှိခွဲတမ်း</label>
        <input type="number" id="kg_to_g12_quota" name="kg_to_g12_quota" class="form-control calc-blue-input"
            value="{{ \App\Support\FormValue::number(old('kg_to_g12_quota', $teacherGuide->kg_to_g12_quota ?? null)) }}"
            min="0" readonly placeholder="0">
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-calculator me-1"></i>G-1 to G-5 ခရိုင်ရရှိခွဲတမ်း</label>
        <input type="number" id="g1_to_g5_quota" name="g1_to_g5_quota" class="form-control calc-blue-input"
            value="{{ \App\Support\FormValue::number(old('g1_to_g5_quota', $teacherGuide->g1_to_g5_quota ?? null)) }}"
            min="0" readonly placeholder="0">
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-plus-circle me-1"></i>၂ မျိုးပေါင်း ခရိုင်ရရှိခွဲတမ်း</label>
        <input type="number" id="total_quota" name="total_quota" class="form-control fw-bold"
            style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
            value="{{ \App\Support\FormValue::number(old('total_quota', $teacherGuide->total_quota ?? null)) }}"
            placeholder="0" min="0" readonly>
    </div>
</div>

{{-- ===== Section: KG to G-12 မြို့နယ်များသို့ ခွဲဝေ ===== --}}
<div class="section-title">
    <i class="fas fa-map-marked-alt me-2"></i>KG to G-12 မြို့နယ်များသို့ ခွဲဝေ
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-building me-1"></i>မြန်အောင်</label>
        <input type="number" id="kg_g12_myanaung_qty" name="kg_g12_myanaung_qty" class="form-control @error('kg_g12_myanaung_qty') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('kg_g12_myanaung_qty', $teacherGuide->kg_g12_myanaung_qty ?? null)) }}" placeholder="0" min="0">
        @error('kg_g12_myanaung_qty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-building me-1"></i>ကြံခင်း</label>
        <input type="number" id="kg_g12_kyankhin_qty" name="kg_g12_kyankhin_qty" class="form-control @error('kg_g12_kyankhin_qty') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('kg_g12_kyankhin_qty', $teacherGuide->kg_g12_kyankhin_qty ?? null)) }}" placeholder="0" min="0">
        @error('kg_g12_kyankhin_qty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-building me-1"></i>အင်္ဂပူ</label>
        <input type="number" id="kg_g12_ingapu_qty" name="kg_g12_ingapu_qty" class="form-control @error('kg_g12_ingapu_qty') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('kg_g12_ingapu_qty', $teacherGuide->kg_g12_ingapu_qty ?? null)) }}" placeholder="0" min="0">
        @error('kg_g12_ingapu_qty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Section: G-1 to G-5 မြို့နယ်များသို့ ခွဲဝေ ===== --}}
<div class="section-title">
    <i class="fas fa-map-marked-alt me-2"></i>G-1 to G-5 မြို့နယ်များသို့ ခွဲဝေ
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-building me-1"></i>မြန်အောင်</label>
        <input type="number" id="g1_g5_myanaung_qty" name="g1_g5_myanaung_qty" class="form-control @error('g1_g5_myanaung_qty') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('g1_g5_myanaung_qty', $teacherGuide->g1_g5_myanaung_qty ?? null)) }}" placeholder="0" min="0">
        @error('g1_g5_myanaung_qty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-building me-1"></i>ကြံခင်း</label>
        <input type="number" id="g1_g5_kyankhin_qty" name="g1_g5_kyankhin_qty" class="form-control @error('g1_g5_kyankhin_qty') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('g1_g5_kyankhin_qty', $teacherGuide->g1_g5_kyankhin_qty ?? null)) }}" placeholder="0" min="0">
        @error('g1_g5_kyankhin_qty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-building me-1"></i>အင်္ဂပူ</label>
        <input type="number" id="g1_g5_ingapu_qty" name="g1_g5_ingapu_qty" class="form-control @error('g1_g5_ingapu_qty') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('g1_g5_ingapu_qty', $teacherGuide->g1_g5_ingapu_qty ?? null)) }}" placeholder="0" min="0">
        @error('g1_g5_ingapu_qty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Section: တွက်ချက်ထားသော စုစုပေါင်း ===== --}}
<div class="section-title">
    <i class="fas fa-chart-pie me-2"></i>တွက်ချက်ထားသော စုစုပေါင်း
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-poll me-1"></i>မြန်အောင် ပေါင်း</label>
        <input type="number" id="total_myanaung_qty" name="total_myanaung_qty" class="form-control"
            value="{{ \App\Support\FormValue::number(old('total_myanaung_qty', $teacherGuide->total_myanaung_qty ?? null)) }}" min="0" readonly placeholder="0">
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-poll me-1"></i>ကြံခင်း ပေါင်း</label>
        <input type="number" id="total_kyankhin_qty" name="total_kyankhin_qty" class="form-control"
            value="{{ \App\Support\FormValue::number(old('total_kyankhin_qty', $teacherGuide->total_kyankhin_qty ?? null)) }}" min="0" readonly placeholder="0">
    </div>

    <div class="col-md-4">
        <label class="form-label"><i class="fas fa-poll me-1"></i>အင်္ဂပူ ပေါင်း</label>
        <input type="number" id="total_ingapu_qty" name="total_ingapu_qty" class="form-control"
            value="{{ \App\Support\FormValue::number(old('total_ingapu_qty', $teacherGuide->total_ingapu_qty ?? null)) }}" min="0" readonly placeholder="0">
    </div>

    <div class="col-md-6 mt-3">
        <label class="form-label"><i class="fas fa-truck-loading me-1"></i>ဖြန့်ဝေမှု စုစုပေါင်း</label>
        <input type="number" id="distributed_total" name="distributed_total" class="form-control fw-bold"
            style="background-color: #f0faf4; color: #105c3a;"
            value="{{ \App\Support\FormValue::number(old('distributed_total', $teacherGuide->distributed_total ?? null)) }}" min="0" readonly placeholder="0">
    </div>

    <div class="col-md-6 mt-3">
        <label class="form-label"><i class="fas fa-warehouse me-1"></i>ခရိုင်ရုံးလက်ကျန်</label>
        <input type="number" id="remaining_total" name="remaining_total" class="form-control fw-bold"
            style="background-color: #fff9e6; color: #b45309;"
            value="{{ \App\Support\FormValue::number(old('remaining_total', $teacherGuide->remaining_total ?? null)) }}" min="0" readonly placeholder="0">
    </div>
</div>

{{-- ===== Section: မှတ်ချက် ===== --}}
<div class="row mb-4">
    <div class="col-md-12">
        <label class="form-label"><i class="fas fa-comment-alt me-1"></i>မှတ်ချက်</label>
        <textarea name="remark" class="form-control @error('remark') is-invalid @enderror"
            rows="3" placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">{{ old('remark', $teacherGuide->remark ?? '') }}</textarea>
        @error('remark')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('teacher-guide-distributions.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($teacherGuide) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($teacherGuide) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @unless(isset($teacherGuide))
            if (window.DerasForm) {
                DerasForm.wireTeacherGuideDistributionForm({
                    yearSelect: '[name="academic_year_id"]',
                    gradeSelect: '#grade_id',
                    guideTypeSelect: '#guide_type',
                    bookSelect: '#book_name_id',
                    kgQuotaInput: '#kg_to_g12_quota',
                    g1QuotaInput: '#g1_to_g5_quota',
                    totalQuotaInput: '#total_quota',
                });
            }
        @endunless

        const fieldIds = [
            'kg_to_g12_quota',
            'g1_to_g5_quota',

            'kg_g12_myanaung_qty',
            'kg_g12_kyankhin_qty',
            'kg_g12_ingapu_qty',

            'g1_g5_myanaung_qty',
            'g1_g5_kyankhin_qty',
            'g1_g5_ingapu_qty'
        ];

        const numberValue = (id) => {
            return Number(document.getElementById(id)?.value || 0);
        };

        const calculate = () => {
            const totalQuota =
                numberValue('kg_to_g12_quota') +
                numberValue('g1_to_g5_quota');

            const myanaung =
                numberValue('kg_g12_myanaung_qty') +
                numberValue('g1_g5_myanaung_qty');

            const kyankhin =
                numberValue('kg_g12_kyankhin_qty') +
                numberValue('g1_g5_kyankhin_qty');

            const ingapu =
                numberValue('kg_g12_ingapu_qty') +
                numberValue('g1_g5_ingapu_qty');

            const distributedTotal = myanaung + kyankhin + ingapu;
            const remainingTotal = totalQuota - distributedTotal;

            const blankZero = window.DerasForm?.displayNumber || function (v) {
                return Number(v) === 0 ? '' : v;
            };

            const totalQuotaEl = document.getElementById('total_quota');
            if (totalQuotaEl) totalQuotaEl.value = blankZero(totalQuota);

            const totalMyanaungEl = document.getElementById('total_myanaung_qty');
            if (totalMyanaungEl) totalMyanaungEl.value = blankZero(myanaung);

            const totalKyankhinEl = document.getElementById('total_kyankhin_qty');
            if (totalKyankhinEl) totalKyankhinEl.value = blankZero(kyankhin);

            const totalIngapuEl = document.getElementById('total_ingapu_qty');
            if (totalIngapuEl) totalIngapuEl.value = blankZero(ingapu);

            const distTotalEl = document.getElementById('distributed_total');
            if (distTotalEl) distTotalEl.value = blankZero(distributedTotal);

            const remTotalEl = document.getElementById('remaining_total');
            if (remTotalEl) remTotalEl.value = blankZero(remainingTotal);
        };

        fieldIds.forEach((id) => {
            document.getElementById(id)?.addEventListener('input', calculate);
        });

        calculate();
    });
</script>
