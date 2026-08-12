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

    /* ===== Labels ===== */
    .form-label {
        font-size: 15px !important;
        font-weight: 600 !important;
        color: #105c3a !important;
        margin-bottom: 8px !important;
        display: block !important;
    }

    .form-label i {
        color: #105c3a !important;
    }

    /* ===== Status Switch ===== */
    .status-switch {
        position: relative;
        width: 74px;
        height: 36px;
        border-radius: 999px;
        background: gray;
        cursor: pointer;
        transition: .25s ease;
        user-select: none;
    }
    .status-switch.active {
        background: var(--deras-leaf);
    }
    .status-knob {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 30px;
        height: 30px;
        background: #fff;
        border-radius: 50%;
        transition: .25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,.25);
    }
    .status-switch.active .status-knob {
        transform: translateX(38px);
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
        margin: 28px -28px -24px -28px;
        padding: 16px 28px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

{{-- Row 1: ကုမ္ပဏီအမည် & Lot --}}
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label"><i class="fas fa-building me-1"></i> ကုမ္ပဏီအမည် <span class="text-danger">*</span></label>
        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
            value="{{ old('company_name', $companyContact->company_name ?? '') }}"
            placeholder="ကုမ္ပဏီအမည် ထည့်သွင်းပါ"
            required minlength="2" maxlength="255"
            data-validate="name" data-label="ကုမ္ပဏီအမည်">
        @error('company_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label"><i class="fas fa-hashtag me-1"></i> Lot <span class="text-danger">*</span></label>
        <input type="text" name="lot" class="form-control @error('lot') is-invalid @enderror"
            value="{{ old('lot', $companyContact->lot ?? '') }}"
            placeholder="Lot ထည့်သွင်းပါ"
            required maxlength="255" data-label="Lot">
        @error('lot')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Row 2: တာဝန်ခံအမည် & ဖုန်းနံပါတ် --}}
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label"><i class="fas fa-user-tie me-1"></i> တာဝန်ခံအမည် <span class="text-danger">*</span></label>
        <input type="text" name="responsible_name" class="form-control @error('responsible_name') is-invalid @enderror"
            value="{{ old('responsible_name', $companyContact->responsible_name ?? '') }}"
            placeholder="တာဝန်ခံအမည် ထည့်သွင်းပါ"
            required minlength="2" maxlength="255"
            data-validate="name" data-label="တာဝန်ခံအမည်">
        @error('responsible_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label"><i class="fas fa-phone me-1"></i> ဖုန်းနံပါတ် <span class="text-danger">*</span></label>
        <input type="text" name="phone" id="company_phone" class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', isset($companyContact) ? \App\Support\MyanmarPhone::format($companyContact->phone) : '09-') }}"
            placeholder="09-XXXXXXXXX"
            required data-validate="phone" data-label="ဖုန်းနံပါတ်"
            inputmode="tel" autocomplete="tel" maxlength="12">
        @error('phone')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        {{-- <small class="text-muted">ဂဏန်း <strong>၉ လုံး</strong> ပြည့်မှ သိမ်းဆည်းနိုင်ပါသည်။</small> --}}
        <div id="phone_digit_hint" class="small mt-1" style="color: #b45309;"></div>
    </div>
</div>

{{-- Status Toggle --}}
<div class="mb-4">
    <label class="form-label"><i class="fas fa-toggle-on me-1"></i> အခြေအနေ</label>
    <input type="hidden" name="is_active" id="is_active" value="{{ old('is_active', $companyContact->is_active ?? 1) }}">
    <div id="statusToggle" class="status-switch {{ old('is_active', $companyContact->is_active ?? 1) ? 'active' : '' }}">
        <div class="status-knob"></div>
    </div>
</div>

<div class="tb-form-footer">
    <a href="{{ route('company-contacts.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> နောက်သို့
    </a>
    <button type="submit" class="btn-save">
        <i class="fas {{ isset($companyContact) ? 'fa-pen' : 'fa-save' }}"></i>
        {{ isset($companyContact) ? 'ပြင်ဆင်ရန်' : 'သိမ်းဆည်းရန်' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const t = document.getElementById('statusToggle'), i = document.getElementById('is_active');
        if (t && i) {
            t.addEventListener('click', function() {
                this.classList.toggle('active');
                i.value = this.classList.contains('active') ? 1 : 0;
            });
        }

        const phoneInput = document.getElementById('company_phone');
        const hint = document.getElementById('phone_digit_hint');
        const myanmarMap = {'၀':'0','၁':'1','၂':'2','၃':'3','၄':'4','၅':'5','၆':'6','၇':'7','၈':'8','၉':'9'};

        // Block digits in name fields (company / responsible)
        document.querySelectorAll('[data-validate="name"]').forEach(function (el) {
            el.addEventListener('keydown', function (e) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                if (e.key.length === 1 && /[0-9၀-၉]/.test(e.key)) {
                    e.preventDefault();
                }
            });
            el.addEventListener('input', function () {
                var cleaned = this.value.replace(/[0-9၀-၉]/g, '');
                if (cleaned !== this.value) this.value = cleaned;
            });
            el.addEventListener('paste', function (e) {
                var text = '';
                try { text = (e.clipboardData || window.clipboardData).getData('text') || ''; } catch (err) { return; }
                if (/[0-9၀-၉]/.test(text)) {
                    e.preventDefault();
                    var insert = text.replace(/[0-9၀-၉]/g, '');
                    var start = this.selectionStart || 0;
                    var end = this.selectionEnd || 0;
                    this.value = this.value.slice(0, start) + insert + this.value.slice(end);
                }
            });
        });

        function toDigits(value) {
            return String(value || '')
                .replace(/[၀-၉]/g, function (d) { return myanmarMap[d] || d; })
                .replace(/\D+/g, '');
        }

        function formatPhone(value) {
            let digits = toDigits(value);
            if (digits.indexOf('959') === 0) digits = '0' + digits.slice(2);
            if (digits.charAt(0) === '9' && digits.indexOf('09') !== 0) digits = '0' + digits;
            if (digits.indexOf('09') !== 0) digits = '09' + digits.replace(/^0+/, '');
            digits = digits.slice(0, 11); // 09 + 9
            const rest = digits.slice(2);
            return rest ? ('09-' + rest) : '09-';
        }

        function updateHint(value) {
            if (!hint) return;
            const digits = toDigits(value);
            let local = digits;
            if (local.indexOf('09') === 0) local = local.slice(2);
            else if (local.charAt(0) === '9') local = local.slice(1);
            const count = Math.min(local.length, 9);
            if (count >= 9) {
                hint.style.color = '#15803d';
                hint.textContent = '';
            } else {
                hint.style.color = '#b45309';
                hint.textContent = '';
            }
        }

        if (phoneInput) {
            phoneInput.value = formatPhone(phoneInput.value || '09');
            updateHint(phoneInput.value);

            phoneInput.addEventListener('input', function () {
                const start = this.selectionStart;
                const before = this.value;
                this.value = formatPhone(this.value);
                updateHint(this.value);
                // Keep caret near end while typing digits
                if (document.activeElement === this) {
                    const delta = this.value.length - before.length;
                    const pos = Math.max(3, (start || 0) + delta);
                    try { this.setSelectionRange(pos, pos); } catch (e) {}
                }
            });

            phoneInput.addEventListener('blur', function () {
                this.value = formatPhone(this.value);
                updateHint(this.value);
            });

            const form = phoneInput.closest('form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    phoneInput.value = formatPhone(phoneInput.value);
                    const digits = toDigits(phoneInput.value);
                    if (!/^09\d{9}$/.test(digits)) {
                        e.preventDefault();
                        e.stopPropagation();
                        updateHint(phoneInput.value);
                        phoneInput.classList.add('is-invalid', 'deras-field-invalid');
                        phoneInput.focus();
                        if (hint) {
                            hint.style.color = '#dc2626';
                            hint.textContent = 'ဖုန်းနံပါတ် ဂဏန်း ၉ လုံး ပြည့်အောင် ဖြည့်ပါ။';
                        }
                    }
                }, true);
            }
        }
    });
</script>
