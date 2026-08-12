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

{{-- ===== Row 1: ပညာသင်နှစ် | အတန်း | ဘာသာ ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-calendar-alt me-1"></i>ပညာသင်နှစ် <span class="text-danger">*</span>
        </label>
        <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
            <option value="">ပညာသင်နှစ်ရွေးချယ်ပါ</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}"
                    {{ (string) old('academic_year_id', $teacherGuide->academic_year_id ?? ($preselectedYearId ?? $currentYearId ?? '')) === (string) $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-graduation-cap me-1"></i>အတန်း <span class="text-danger">*</span>
        </label>
        <select name="grade_id" id="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
            <option value="">အတန်းရွေးချယ်ပါ</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}"
                    {{ (string) old('grade_id', $teacherGuide->grade_id ?? ($preselectedGradeId ?? '')) === (string) $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-book me-1"></i>ဘာသာ <span class="text-danger">*</span>
        </label>
        <select name="book_name_id" id="book_name_id" class="form-select @error('book_name_id') is-invalid @enderror" required
            data-placeholder="ဘာသာရပ်ရွေးချယ်ပါ">
            <option value="">ဘာသာရပ်ရွေးချယ်ပါ</option>
            @php $selectedBookId = old('book_name_id', $teacherGuide->book_name_id ?? ''); @endphp
            @if ($selectedBookId)
                @foreach ($bookNames as $bookName)
                    @if ((string) $bookName->id === (string) $selectedBookId)
                        <option value="{{ $bookName->id }}" selected>{{ $bookName->name }}</option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>
</div>

{{-- ===== Row 2: စာအုပ်အမည်အပြည့်အစုံ | အမျိုးအစား | KG-G12 ===== --}}
<div class="row mb-4" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-file-alt me-1"></i>စာအုပ်အမည်အပြည့်အစုံ <span class="text-danger">*</span>
        </label>
        <input type="text" name="group_title" class="form-control @error('group_title') is-invalid @enderror"
            value="{{ old('group_title', $teacherGuide->group_title ?? '') }}" placeholder="စာအုပ်အမည် ဖြည့်ပါ" required>
    </div>

    <div class="col-md-4 px-md-2">
        <label class="form-label">
            <i class="fas fa-tags me-1"></i>အမျိုးအစား <span class="text-danger">*</span>
        </label>
        <select name="guide_type" class="form-select @error('guide_type') is-invalid @enderror" required>
            <option value="ဆရာကိုင်"
                {{ old('guide_type', $teacherGuide->guide_type ?? ($preselectedGuideType ?? '')) === 'ဆရာကိုင်' ? 'selected' : '' }}>
                ဆရာကိုင်
            </option>
            <option value="ဆရာလမ်းညွှန်"
                {{ old('guide_type', $teacherGuide->guide_type ?? ($preselectedGuideType ?? '')) === 'ဆရာလမ်းညွှန်' ? 'selected' : '' }}>
                ဆရာလမ်းညွှန်
            </option>
        </select>
    </div>

    <div class="col-md-4 ps-md-3">
        <label class="form-label">
            <i class="fas fa-sort-numeric-up me-1"></i>KG to G-12 ခရိုင်ရရှိခွဲတမ်း
        </label>
        <input type="number" id="kg_to_g12_quota" name="kg_to_g12_quota" class="form-control"
            value="{{ \App\Support\FormValue::number(old('kg_to_g12_quota', $teacherGuide->kg_to_g12_quota ?? null)) }}" placeholder="0" min="0">
    </div>
</div>

{{-- ===== Row 3: G1-G5 | ပေါင်း | မှတ်ချက် ===== --}}
<div class="row mb-2" style="row-gap: 0; column-gap: 0;">
    <div class="col-md-4 pe-md-3">
        <label class="form-label">
            <i class="fas fa-sort-numeric-up me-1"></i>G-1 to G-5 ခရိုင်ရရှိခွဲတမ်း
        </label>
        <input type="number" id="g1_to_g5_quota" name="g1_to_g5_quota" class="form-control"
            value="{{ \App\Support\FormValue::number(old('g1_to_g5_quota', $teacherGuide->g1_to_g5_quota ?? null)) }}" placeholder="0" min="0">
    </div>

    @isset($teacherGuide)
        <div class="col-md-4 px-md-2">
            <label class="form-label">
                <i class="fas fa-calculator me-1"></i>၂မျိုးပေါင်း ခရိုင်ရရှိခွဲတမ်း
            </label>
            <input type="number" id="total_quota_display" class="form-control fw-bold"
                style="background-color: #f0faf4; color: #105c3a; border-color: #aad6bc;"
                value="{{ \App\Support\FormValue::number(old('total_quota', ($teacherGuide->kg_to_g12_quota ?? 0) + ($teacherGuide->g1_to_g5_quota ?? 0))) }}"
                disabled placeholder="0">
            <input type="hidden" id="total_quota" name="total_quota"
                value="{{ old('total_quota', ($teacherGuide->kg_to_g12_quota ?? 0) + ($teacherGuide->g1_to_g5_quota ?? 0)) }}">
        </div>

        <div class="col-md-4 ps-md-3">
            <label class="form-label">
                <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
            </label>
            <input type="text" name="remark" class="form-control"
                value="{{ old('remark', $teacherGuide->remark ?? '') }}"
                placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">
        </div>
    @else
        <input type="hidden" id="total_quota" name="total_quota" value="{{ old('total_quota', 0) }}">

        <div class="col-md-8 px-md-2">
            <label class="form-label">
                <i class="fas fa-comment-alt me-1"></i>မှတ်ချက်
            </label>
            <input type="text" name="remark" class="form-control"
                value="{{ old('remark', $teacherGuide->remark ?? '') }}"
                placeholder="မှတ်ချက်ရှိပါက ဖြည့်သွင်းပါ...">
        </div>
    @endisset
</div>

{{-- ===== Footer Buttons ===== --}}
<div class="tb-form-footer">
    <a href="{{ route('teacher-guides.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($teacherGuide) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($teacherGuide) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.DerasForm) {
            const gradeSelect = document.querySelector('#grade_id');
            const subjectSelect = document.querySelector('#book_name_id');
            const guideType = document.querySelector('[name="guide_type"]');

            async function reloadSubjects() {
                const gradeId = gradeSelect?.value;
                if (!gradeId) {
                    DerasForm.fillSelect(subjectSelect, [], '');
                    return;
                }
                const slug = guideType?.value === 'ဆရာကိုင်'
                    ? 'teacher_handbook'
                    : 'teacher_guide';
                const keep = subjectSelect?.value || '';
                try {
                    const subjects = await DerasForm.fetchJson(
                        '/grades/' + gradeId + '/subjects?category=' + encodeURIComponent(slug)
                    );
                    DerasForm.fillSelect(subjectSelect, subjects, keep);
                } catch (e) {
                    console.error(e);
                }
            }

            gradeSelect?.addEventListener('change', reloadSubjects);
            guideType?.addEventListener('change', reloadSubjects);
            if (gradeSelect?.value) {
                reloadSubjects();
            }
        }

        const kgToG12Input = document.getElementById('kg_to_g12_quota');
        const g1ToG5Input = document.getElementById('g1_to_g5_quota');
        const totalHiddenInput = document.getElementById('total_quota');
        const totalDisplayInput = document.getElementById('total_quota_display');

        function calculateTotalQuota() {
            const kgToG12 = parseInt(kgToG12Input.value, 10) || 0;
            const g1ToG5 = parseInt(g1ToG5Input.value, 10) || 0;
            const total = kgToG12 + g1ToG5;

            totalHiddenInput.value = total;

            if (totalDisplayInput) {
                totalDisplayInput.value = (window.DerasForm?.displayNumber || function (v) {
                    return Number(v) === 0 ? '' : v;
                })(total);
            }
        }

        if (kgToG12Input && g1ToG5Input) {
            kgToG12Input.addEventListener('input', calculateTotalQuota);
            g1ToG5Input.addEventListener('input', calculateTotalQuota);
            calculateTotalQuota();
        }
    });
</script>
