@csrf

@php
    $isEdit = isset($teacherGuideIssue) && $teacherGuideIssue->exists;
    $existing = $isEdit ? $teacherGuideIssue->townshipIssues->keyBy('township_id') : collect();
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

{{-- ===== Basic Info Section ===== --}}
<div class="row mb-4">
    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
        </label>
        <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
            <option value="">-- ရွေးချယ်ပါ --</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}"
                    {{ (string) old('academic_year_id', isset($teacherGuideIssue) ? $teacherGuideIssue->academic_year_id : ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
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
                    {{ (string) old('grade_id', isset($teacherGuideIssue) ? $teacherGuideIssue->grade_id : ($preselectedGradeId ?? '')) === (string) $grade->id ? 'selected' : '' }}>
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
            @php $selectedGuideType = old('guide_type', isset($teacherGuideIssue) ? $teacherGuideIssue->guide_type : ($preselectedGuideType ?? 'ဆရာလမ်းညွှန်')); @endphp
            <option value="ဆရာလမ်းညွှန်"
                {{ $selectedGuideType == 'ဆရာလမ်းညွှန်' ? 'selected' : '' }}>
                ဆရာလမ်းညွှန်
            </option>
            <option value="ဆရာကိုင်"
                {{ $selectedGuideType == 'ဆရာကိုင်' ? 'selected' : '' }}>
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
            @if ($isEdit && $teacherGuideIssue->bookName)
                <option value="{{ $teacherGuideIssue->book_name_id }}" selected>
                    {{ $teacherGuideIssue->bookName->name }}
                </option>
            @endif
        </select>
        @error('book_name_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label">
            <i class="fas fa-warehouse me-1"></i>ခရိုင်ရုံးလက်ကျန် <span class="text-danger">*</span>
        </label>
        <input type="number" name="district_unit" id="district_unit" class="form-control @error('district_unit') is-invalid @enderror"
            value="{{ \App\Support\FormValue::number(old('district_unit', $teacherGuideIssue->district_unit ?? null)) }}"
            required min="0" readonly placeholder="0"
            style="background-color: #eff6ff; color: #1e40af; border-color: #bfdbfe; cursor: default; font-weight: 600;">
        @error('district_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">
            <i class="fas fa-box me-1"></i>တစ်အိတ်ပါ Unit <span class="text-danger">*</span>
        </label>
        <input type="number" id="package_unit" name="package_unit" class="form-control @error('package_unit') is-invalid @enderror"
            value="{{ old('package_unit', $teacherGuideIssue->package_unit ?? '') }}" placeholder="1" required min="1">
        @error('package_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">
            <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
        </label>
        <input type="text" name="remark" class="form-control @error('remark') is-invalid @enderror"
            value="{{ old('remark', $teacherGuideIssue->remark ?? '') }}" placeholder="မှတ်ချက်ဖြည့်ပါ">
        @error('remark')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Townships Section ===== --}}
<div class="section-title">
    <i class="fas fa-map-marked-alt me-2"></i>မြို့နယ်အလိုက် ဖြန့်ဝေမှု
</div>

<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle text-center" style="border-radius: 8px; overflow: hidden;">
        <thead style="background-color: #072a1e; color: #ffffff;">
            <tr>
                <th style="width: 25%; font-weight: 700;">မြို့နယ်</th>
                <th style="font-weight: 700;">ထုတ်ပေးသည့်အုပ်ရေ</th>
                <th style="font-weight: 700;">အိတ်ပြည့်</th>
                <th style="font-weight: 700;">အပြေ (အုပ်)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($townships as $township)
                @php
                    $detail = $existing->get($township->id);
                @endphp
                <tr>
                    <td class="fw-bold" style="color: #105c3a;">
                        {{ $township->name }}
                    </td>
                    <td>
                        <input type="number" id="issued_{{ $township->id }}"
                            name="township_values[{{ $township->id }}][issued_quantity]"
                            class="form-control text-center issued-input fw-bold" data-id="{{ $township->id }}"
                            value="{{ \App\Support\FormValue::number(old('township_values.' . $township->id . '.issued_quantity', $detail->issued_quantity ?? null)) }}"
                            min="0" required readonly placeholder="0"
                            style="background-color: #eff6ff; color: #1e40af; border-color: #bfdbfe; cursor: default;">
                    </td>
                    <td>
                        <input type="number" id="package_{{ $township->id }}"
                            name="township_values[{{ $township->id }}][full_package_count]"
                            class="form-control text-center fw-bold"
                            style="background-color: #fef3c7; color: #92400e; border-color: #fde68a;"
                            value="{{ \App\Support\FormValue::number(old('township_values.' . $township->id . '.full_package_count', $detail->full_package_count ?? null)) }}"
                            placeholder="0" min="0" readonly>
                    </td>
                    <td>
                        <input type="number" id="loose_{{ $township->id }}"
                            name="township_values[{{ $township->id }}][loose_book_count]"
                            class="form-control text-center fw-bold"
                            style="background-color: #d1fae5; color: #065f46; border-color: #a7f3d0;"
                            value="{{ \App\Support\FormValue::number(old('township_values.' . $township->id . '.loose_book_count', $detail->loose_book_count ?? null)) }}"
                            placeholder="0" min="0" readonly>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('teacher-guide-issues.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ $isEdit ? 'fa-pen' : 'fa-save' }}"></i>
        {{ $isEdit ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const townshipMap = @json($townships->mapWithKeys(fn ($t) => [$t->name => $t->id]));

        if (window.DerasForm) {
            DerasForm.wireTeacherGuideIssueForm({
                yearSelect: '#academic_year_id',
                gradeSelect: '#grade_id',
                guideTypeSelect: '#guide_type',
                bookSelect: '#book_name_id',
                districtInput: '#district_unit',
                packageUnitInput: '#package_unit',
                townshipMap: townshipMap,
            });
        }
    });
</script>
