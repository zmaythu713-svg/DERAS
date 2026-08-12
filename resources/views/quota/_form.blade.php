@csrf
@php $quota = $quota ?? new \App\Models\Quota(); @endphp

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

@php
    // col-md-4 = 3 columns (create), col-md-3 = 4 columns (edit with total)
    $colClass = isset($quota) ? 'col-md-3' : 'col-md-4';
@endphp

{{-- ===== BASIC INFO SECTION ===== --}}
<div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
    <div class="card-header py-3 px-4 d-flex align-items-center gap-2"
        style="background-color: #e8f5ef; border-left: 4px solid #105c3a;">
        <i class="fas fa-info-circle" style="color: #105c3a;"></i>
        <h6 class="mb-0 fw-bold" style="color: #105c3a;">အခြေခံအချက်အလက်</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">

            {{-- Academic Year --}}
            <div class="col-md-6">
                <label for="academic_year_id" class="form-label">
                    <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
                </label>
                <select id="academic_year_id" name="academic_year_id"
                    class="form-select @error('academic_year_id') is-invalid @enderror" required>
                    <option value="">-- ရွေးချယ်ပါ --</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}"
                            {{ (string) old('academic_year_id', $quota->academic_year_id ?? ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
                @error('academic_year_id')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            {{-- Township --}}
            <div class="col-md-6">
                <label for="township_id" class="form-label">
                    <i class="fas fa-map-marker-alt me-1"></i>မြို့နယ် <span class="text-danger">*</span>
                </label>
                <select id="township_id" name="township_id"
                    class="form-select @error('township_id') is-invalid @enderror" required>
                    <option value="">-- ရွေးချယ်ပါ --</option>
                    @foreach ($townships as $township)
                        <option value="{{ $township->id }}"
                            {{ old('township_id', $quota->township_id ?? '') == $township->id ? 'selected' : '' }}>
                            {{ $township->name }}
                        </option>
                    @endforeach
                </select>
                @error('township_id')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>

{{-- ===== မူလတန်း SECTION ===== --}}
<div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
    <div class="card-header py-3 px-4 d-flex align-items-center gap-2"
        style="background-color: #e8f5ef; border-left: 4px solid #105c3a;">
        <i class="fas fa-school" style="color: #105c3a;"></i>
        <h6 class="mb-0 fw-bold" style="color: #105c3a;">မူလတန်း</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-building me-1"></i>အခြေခံ</label>
                <input type="number" name="primary_public" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('primary_public', $quota->primary_public)) }}" placeholder="0" min="0">
            </div>
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-vihara me-1"></i>ဘက</label>
                <input type="number" name="primary_monk" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('primary_monk', $quota->primary_monk)) }}" placeholder="0" min="0">
            </div>
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-user-graduate me-1"></i>ကိုယ်ပိုင်</label>
                <input type="number" name="primary_private" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('primary_private', $quota->primary_private)) }}" placeholder="0" min="0">
            </div>
            @isset($quota)
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calculator me-1"></i>မူလတန်း ပေါင်း</label>
                    <input type="number" class="form-control fw-bold"
                        style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                        value="{{ $quota->primary_public + $quota->primary_monk + $quota->primary_private }}" disabled>
                </div>
            @endisset
        </div>
    </div>
</div>

{{-- ===== အလယ်တန်း SECTION ===== --}}
<div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
    <div class="card-header py-3 px-4 d-flex align-items-center gap-2"
        style="background-color: #e8f5ef; border-left: 4px solid #105c3a;">
        <i class="fas fa-graduation-cap" style="color: #105c3a;"></i>
        <h6 class="mb-0 fw-bold" style="color: #105c3a;">အလယ်တန်း</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-building me-1"></i>အခြေခံ</label>
                <input type="number" name="middle_public" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('middle_public', $quota->middle_public)) }}" placeholder="0" min="0">
            </div>
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-vihara me-1"></i>ဘက</label>
                <input type="number" name="middle_monk" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('middle_monk', $quota->middle_monk)) }}" placeholder="0" min="0">
            </div>
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-user-graduate me-1"></i>ကိုယ်ပိုင်</label>
                <input type="number" name="middle_private" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('middle_private', $quota->middle_private)) }}" placeholder="0" min="0">
            </div>
            @isset($quota)
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calculator me-1"></i>အလယ်တန်း ပေါင်း</label>
                    <input type="number" class="form-control fw-bold"
                        style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                        value="{{ $quota->middle_public + $quota->middle_monk + $quota->middle_private }}" disabled>
                </div>
            @endisset
        </div>
    </div>
</div>

{{-- ===== အထက်တန်း SECTION ===== --}}
<div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
    <div class="card-header py-3 px-4 d-flex align-items-center gap-2"
        style="background-color: #e8f5ef; border-left: 4px solid #105c3a;">
        <i class="fas fa-university" style="color: #105c3a;"></i>
        <h6 class="mb-0 fw-bold" style="color: #105c3a;">အထက်တန်း</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-building me-1"></i>အခြေခံ</label>
                <input type="number" name="high_public" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('high_public', $quota->high_public)) }}" placeholder="0" min="0">
            </div>
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-vihara me-1"></i>ဘက</label>
                <input type="number" name="high_monk" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('high_monk', $quota->high_monk)) }}" placeholder="0" min="0">
            </div>
            <div class="{{ $colClass }}">
                <label class="form-label"><i class="fas fa-user-graduate me-1"></i>ကိုယ်ပိုင်</label>
                <input type="number" name="high_private" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('high_private', $quota->high_private)) }}" placeholder="0" min="0">
            </div>

            @isset($quota)
                @php
                    $primaryTotal = $quota->primary_public + $quota->primary_monk + $quota->primary_private;
                    $middleTotal  = $quota->middle_public + $quota->middle_monk + $quota->middle_private;
                    $highTotal    = $quota->high_public + $quota->high_monk + $quota->high_private;
                    $grandPublic  = $quota->primary_public + $quota->middle_public + $quota->high_public;
                    $grandMonk    = $quota->primary_monk + $quota->middle_monk + $quota->high_monk;
                    $grandPrivate = $quota->primary_private + $quota->middle_private + $quota->high_private;
                    $grandTotal   = $grandPublic + $grandMonk + $grandPrivate;
                    $totalWithAgriculture = $grandTotal + $quota->agriculture;
                    $distributionTotal    = $totalWithAgriculture - $grandPrivate;
                @endphp
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calculator me-1"></i>အထက်တန်း ပေါင်း</label>
                    <input type="number" class="form-control fw-bold"
                        style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                        value="{{ $highTotal }}" disabled>
                </div>
            @endisset
        </div>
    </div>
</div>

{{-- ===== စုစုပေါင်း (edit only) ===== --}}
@isset($quota)
    <div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
        <div class="card-header py-3 px-4 d-flex align-items-center gap-2" style="background-color: #105c3a;">
            <i class="fas fa-calculator text-white"></i>
            <h6 class="mb-0 fw-bold text-white">စုစုပေါင်း</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @foreach ([
                    ['label' => 'စုစုပေါင်း အခြေခံ', 'value' => $grandPublic, 'icon' => 'fa-building'],
                    ['label' => 'စုစုပေါင်း ဘက',    'value' => $grandMonk, 'icon' => 'fa-vihara'],
                    ['label' => 'စုစုပေါင်း ကိုယ်ပိုင်','value' => $grandPrivate, 'icon' => 'fa-user-graduate'],
                    ['label' => 'စုစုပေါင်း ပေါင်း', 'value' => $grandTotal, 'icon' => 'fa-calculator'],
                ] as $item)
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas {{ $item['icon'] }} me-1"></i>{{ $item['label'] }}</label>
                        <input type="number" class="form-control fw-bold"
                            style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                            value="{{ $item['value'] }}" disabled>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endisset

{{-- ===== အခြားအချက်အလက် ===== --}}
<div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
    <div class="card-header py-3 px-4 d-flex align-items-center gap-2"
        style="background-color: #e8f5ef; border-left: 4px solid #105c3a;">
        <i class="fas fa-clipboard-list" style="color: #105c3a;"></i>
        <h6 class="mb-0 fw-bold" style="color: #105c3a;">အခြားအချက်အလက်</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-tractor me-1"></i>စက်၊စိုက်၊မွေး</label>
                <input type="number" name="agriculture" class="form-control"
                    value="{{ \App\Support\FormValue::number(old('agriculture', $quota->agriculture)) }}" placeholder="0" min="0">
            </div>
            @isset($quota)
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-plus-circle me-1"></i>စုစုပေါင်း စက်၊စိုက်၊မွေးအပါ</label>
                    <input type="number" class="form-control fw-bold"
                        style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                        value="{{ $totalWithAgriculture }}" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-truck me-1"></i>ဖြန့်ဝေရန် ကျောင်းသားဦးရေ</label>
                    <input type="number" class="form-control fw-bold"
                        style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                        value="{{ $distributionTotal }}" disabled>
                </div>
            @endisset
        </div>
    </div>
</div>

{{-- ===== Form Footer ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('quota.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($quota) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($quota) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

