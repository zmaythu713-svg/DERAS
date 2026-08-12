/**
 * DERAS global frontend form validation.
 * - Red border + red error text under invalid fields
 * - required / email / number / phone / person-name / password
 * - Words-only for data-validate="name"; digits for number/phone fields
 */
(function () {
    'use strict';

    var MSG = {
        required: 'ဖြည့်သွင်းရန် လိုအပ်ပါသည်။',
        email: 'အီးမေးလ်ပုံစံ မှန်ကန်စွာ ဖြည့်ပါ။',
        number: 'ဂဏန်းသာ ဖြည့်သွင်းပါ။',
        numberRequired: 'အနည်းဆုံး 0 ဖြည့်ပါ။',
        min: function (n) { return 'အနည်းဆုံးတန်ဖိုးမှာ ' + n + ' ဖြစ်ရပါမည်။'; },
        max: function (n) { return 'အများဆုံးတန်ဖိုးမှာ ' + n + ' ဖြစ်ရပါမည်။'; },
        minlength: function (n) { return 'အနည်းဆုံး ' + n + ' လုံး ဖြည့်ပါ။'; },
        maxlength: function (n) { return 'အများဆုံး ' + n + ' လုံးသာ ခွင့်ပြုပါသည်။'; },
        pattern: 'ပုံစံမှန်ကန်စွာ ဖြည့်သွင်းပါ။',
        phone: 'ဖုန်းနံပါတ်သည် 09 (သို့) ၀၉ ဖြင့် စပြီး ဂဏန်း ၉ လုံး ပြည့်အောင် ဖြည့်ရမည်။ ဥပမာ — 09-450708675',
        name: 'စကားလုံးသာ ဖြည့်ရမည် — ဂဏန်း ထည့်၍မရပါ။',
        passwordMatch: 'စကားဝှက်နှင့် မကိုက်ညီပါ။',
        select: 'ရွေးချယ်ရန် လိုအပ်ပါသည်။'
    };

    var MYANMAR_DIGIT_MAP = {
        '၀': '0', '၁': '1', '၂': '2', '၃': '3', '၄': '4',
        '၅': '5', '၆': '6', '၇': '7', '၈': '8', '၉': '9'
    };

    function isValidateableForm(form) {
        if (!(form instanceof HTMLFormElement)) return false;
        if (form.hasAttribute('data-no-validate')) return false;
        if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return false;

        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && String(methodInput.value).toUpperCase() === 'DELETE') return false;

        var action = (form.getAttribute('action') || '').toLowerCase();
        if (action.indexOf('logout') !== -1) return false;

        return true;
    }

    function fieldLabel(el) {
        if (el.getAttribute('data-label')) return el.getAttribute('data-label');
        if (el.id) {
            var lab = document.querySelector('label[for="' + el.id + '"]');
            if (lab) return lab.textContent.replace(/\s+/g, ' ').trim();
        }
        var wrap = el.closest('.mb-3, .mb-4, .form-section, .col-md-3, .col-md-4, .col-md-6, .col-md-12, .col-12, div');
        if (wrap) {
            var near = wrap.querySelector('label.form-label, label');
            if (near) return near.textContent.replace(/\s+/g, ' ').trim();
        }
        return el.getAttribute('placeholder') || el.name || 'အကွက်';
    }

    function clearFieldError(el) {
        el.classList.remove('deras-field-invalid', 'is-invalid');
        el.removeAttribute('aria-invalid');
        var parent = el.parentElement;
        if (!parent) return;

        var kids = parent.children;
        for (var i = kids.length - 1; i >= 0; i--) {
            if (kids[i].classList && kids[i].classList.contains('deras-field-error')) {
                parent.removeChild(kids[i]);
            }
        }

        var sib = el.nextElementSibling;
        while (sib) {
            var next = sib.nextElementSibling;
            if (sib.classList && sib.classList.contains('deras-field-error')) {
                sib.remove();
                break;
            }
            if (sib.classList && (sib.classList.contains('password-toggle-icon') || sib.tagName === 'I')) {
                sib = next;
                continue;
            }
            break;
        }
    }

    function showFieldError(el, message) {
        clearFieldError(el);
        el.classList.add('deras-field-invalid', 'is-invalid');
        el.setAttribute('aria-invalid', 'true');

        var err = document.createElement('div');
        err.className = 'deras-field-error';
        err.textContent = message;

        var anchor = el;
        var relativeWrap = el.closest('[style*="position: relative"], .position-relative');
        if (relativeWrap && relativeWrap.contains(el) && relativeWrap.parentElement) {
            anchor = relativeWrap;
        }
        if (anchor.parentNode) {
            if (anchor.nextSibling) {
                anchor.parentNode.insertBefore(err, anchor.nextSibling);
            } else {
                anchor.parentNode.appendChild(err);
            }
        }
    }

    function isEmpty(el) {
        if (el.tagName === 'SELECT') {
            return !el.value || el.value === '';
        }
        var type = (el.getAttribute('type') || '').toLowerCase();
        if (type === 'number') {
            if (el.validity && el.validity.badInput) return true;
            var raw = String(el.value == null ? '' : el.value).trim();
            if (raw === '') return true;
            if (typeof el.valueAsNumber === 'number' && Number.isNaN(el.valueAsNumber)) return true;
            return false;
        }
        return String(el.value == null ? '' : el.value).trim() === '';
    }

    function isOptionalEmptyPassword(el) {
        if (el.type !== 'password') return false;
        if (el.name !== 'password' && el.name !== 'password_confirmation') return false;
        if (el.hasAttribute('required')) return false;
        return isEmpty(el);
    }

    function isSkippedReadonly(el) {
        // Auto-calc readonly fields: skip when they already have a value.
        // Required readonly fields that are empty must still fail (important on Edit forms).
        // Password fields often use temporary readonly anti-autofill — still validate them.
        var type = (el.getAttribute('type') || '').toLowerCase();
        if (type === 'password') return false;
        if (!(el.readOnly || el.hasAttribute('readonly'))) return false;
        if (el.hasAttribute('required') && isEmpty(el)) return false;
        return true;
    }

    function isRemarkOrOptional(el) {
        var name = (el.name || '').toLowerCase();
        if (!name) return true;
        if (name === 'remark' || name === 'remarks' || name === 'note' || name === 'notes') return true;
        if (el.dataset.optional === '1' || el.hasAttribute('data-optional')) return true;
        return false;
    }

    function isNonTextControl(el) {
        var type = (el.getAttribute('type') || '').toLowerCase();
        return type === 'checkbox' || type === 'radio' || type === 'file'
            || type === 'image' || type === 'range' || type === 'color';
    }

    function isPhoneField(el, name, type) {
        return name === 'phone' || type === 'tel' || el.dataset.validate === 'phone';
    }

    function isPersonNameField(el, name) {
        if (el.dataset.allowDigits === '1' || el.dataset.validate === 'code') return false;
        if (el.dataset.validate === 'name') return true;
        if (name === 'company_name' || name === 'responsible_name') return true;
        // Person "name" on user/profile/township forms — not grade/book/academic-year
        if (name !== 'name') return false;
        var action = ((el.form && el.form.getAttribute('action')) || '').toLowerCase();
        if (action.indexOf('grade') !== -1) return false;
        if (action.indexOf('book-name') !== -1 || action.indexOf('book_name') !== -1) return false;
        if (action.indexOf('academic-year') !== -1 || action.indexOf('academic_year') !== -1) return false;
        return true;
    }

    function isNumberField(el, name, type) {
        if (isPhoneField(el, name, type)) return false;
        return type === 'number' || el.getAttribute('inputmode') === 'numeric';
    }

    function normalizePhoneDigits(value) {
        var digits = String(value).replace(/[၀-၉]/g, function (d) {
            return MYANMAR_DIGIT_MAP[d] || d;
        }).replace(/\D+/g, '');
        if (/^959\d+/.test(digits)) digits = '0' + digits.slice(2);
        if (/^9\d+/.test(digits) && digits.indexOf('09') !== 0) digits = '0' + digits;
        return digits;
    }

    function validateField(el) {
        if (el.disabled) return null;
        if (isSkippedReadonly(el)) return null;
        if (isRemarkOrOptional(el)) return null;
        if (el.type === 'hidden') return null;
        if (el.type === 'submit' || el.type === 'button' || el.type === 'reset') return null;
        if (el.name === '_token' || el.name === '_method') return null;

        var type = (el.getAttribute('type') || el.tagName).toLowerCase();
        var name = el.name || '';
        var value = String(el.value == null ? '' : el.value).trim();
        var required = el.hasAttribute('required');

        if (isOptionalEmptyPassword(el)) return null;
        if (name === 'password_confirmation' && !el.hasAttribute('required')) {
            var pwd = el.form && el.form.querySelector('[name="password"]');
            if (pwd && isEmpty(pwd) && isEmpty(el)) return null;
        }

        var phoneField = isPhoneField(el, name, type);
        var numberField = isNumberField(el, name, type);
        var nameField = isPersonNameField(el, name);

        if (isEmpty(el)) {
            if (!required) {
                if (isNonTextControl(el)) return null;
                if (numberField) return null;
                return null;
            }
            if (el.tagName === 'SELECT') return MSG.select;
            return MSG.required;
        }

        if (type === 'email' || name === 'email') {
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
            if (!emailRe.test(value)) return MSG.email;
        }

        if (nameField) {
            if (/[0-9၀-၉]/.test(value)) return MSG.name;
            var hasLetter = false;
            try {
                hasLetter = /\p{L}/u.test(value);
            } catch (e) {
                hasLetter = /[A-Za-z\u1000-\u109F]/.test(value);
            }
            if (!hasLetter) return MSG.name;
        }

        if (numberField) {
            // Strict numeric token (no letters / hyphens)
            if (!/^-?\d+(\.\d+)?$/.test(value)) return MSG.number;
            var num = Number(value);
            if (Number.isNaN(num)) return MSG.number;

            var allowNeg = el.dataset.allowNegative === '1';
            var minVal = (el.hasAttribute('min') && el.min !== '')
                ? Number(el.min)
                : (allowNeg ? null : 0);

            if (minVal !== null && !Number.isNaN(minVal) && num < minVal) {
                return MSG.min(minVal);
            }
            if (el.hasAttribute('max') && el.max !== '' && num > Number(el.max)) {
                return MSG.max(el.max);
            }

            // Default integers unless step allows decimals
            var step = el.getAttribute('step');
            if ((!step || step === '1') && !Number.isInteger(num)) {
                return MSG.number;
            }
        }

        if (el.hasAttribute('minlength')) {
            var minL = parseInt(el.getAttribute('minlength'), 10);
            if (!Number.isNaN(minL) && value.length < minL) return MSG.minlength(minL);
        }

        if (el.hasAttribute('maxlength')) {
            var maxL = parseInt(el.getAttribute('maxlength'), 10);
            if (!Number.isNaN(maxL) && value.length > maxL) return MSG.maxlength(maxL);
        }

        if (phoneField) {
            var digits = normalizePhoneDigits(value);
            if (!/^09\d{9}$/.test(digits)) return MSG.phone;
        }

        if (el.hasAttribute('pattern')) {
            try {
                var re = new RegExp('^(?:' + el.getAttribute('pattern') + ')$');
                if (!re.test(value)) {
                    return el.getAttribute('data-pattern-message') || MSG.pattern;
                }
            } catch (e) { /* ignore bad pattern */ }
        }

        if (name === 'password_confirmation' || el.dataset.match) {
            var matchName = el.dataset.match || 'password';
            var matchEl = el.form && el.form.querySelector('[name="' + matchName + '"]');
            if (matchEl && String(matchEl.value) !== String(el.value)) {
                return MSG.passwordMatch;
            }
        }

        if (name === 'password' && value !== '' && value.length < 8 && !el.hasAttribute('minlength')) {
            return MSG.minlength(8);
        }

        return null;
    }

    function getFields(form) {
        return Array.prototype.slice.call(
            form.querySelectorAll('input, select, textarea')
        ).filter(function (el) {
            if (el.type === 'hidden') return false;
            if (el.name === '_token' || el.name === '_method') return false;
            if (el.disabled) return false;
            if (isSkippedReadonly(el)) return false;
            if (isRemarkOrOptional(el)) return false;
            return true;
        });
    }

    function coerceEmptyNumbers(form) {
        var coerced = [];
        Array.prototype.forEach.call(
            form.querySelectorAll('input[type="number"], input[inputmode="numeric"]'),
            function (el) {
                if (el.disabled || isSkippedReadonly(el)) return;
                if (!el.name || el.name === '_token' || el.name === '_method') return;
                if (isPhoneField(el, el.name || '', (el.getAttribute('type') || '').toLowerCase())) return;
                if (el.validity && el.validity.badInput) {
                    el.value = '0';
                    coerced.push(el);
                    return;
                }
                if (String(el.value == null ? '' : el.value).trim() === '') {
                    el.value = '0';
                    coerced.push(el);
                }
            }
        );
        return coerced;
    }

    function restoreCoercedNumbers(els) {
        (els || []).forEach(function (el) {
            if (String(el.value) === '0') el.value = '';
        });
    }

    function blockDigitsOnNameField(el) {
        el.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            if (e.key.length === 1 && /[0-9၀-၉]/.test(e.key)) e.preventDefault();
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
    }

    function validateForm(form) {
        var coerced = coerceEmptyNumbers(form);

        var fields = getFields(form);
        var firstInvalid = null;
        var ok = true;

        fields.forEach(function (el) {
            clearFieldError(el);
            var err = validateField(el);
            if (err) {
                ok = false;
                showFieldError(el, err);
                if (!firstInvalid) firstInvalid = el;
            }
        });

        if (!ok) {
            restoreCoercedNumbers(coerced);
        }

        if (firstInvalid) {
            form.dataset.derasSuppressClear = '1';
            try {
                firstInvalid.focus({ preventScroll: false });
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch (e) {
                try { firstInvalid.focus(); } catch (e2) { /* ignore */ }
            }
            delete form.dataset.derasSuppressClear;
        }

        return ok;
    }

    function onSubmitCapture(e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!isValidateableForm(form)) return;
        if (form.dataset.derasConfirmed === '1') return;

        try {
            if (!validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof e.stopImmediatePropagation === 'function') {
                    e.stopImmediatePropagation();
                }
            }
        } catch (err) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }
            if (window.console && console.error) console.error('DERAS validation error:', err);
        }
    }

    function bindForm(form) {
        if (!isValidateableForm(form) || form.dataset.derasValidationBound === '1') return;
        form.dataset.derasValidationBound = '1';
        form.setAttribute('novalidate', 'novalidate');

        getFields(form).forEach(function (el) {
            var t = (el.getAttribute('type') || '').toLowerCase();
            var name = el.name || '';
            var phoneField = isPhoneField(el, name, t);
            var numberField = isNumberField(el, name, t);
            var nameField = isPersonNameField(el, name);

            if (nameField) {
                blockDigitsOnNameField(el);
            }

            if (numberField && el.dataset.allowNegative !== '1' && !el.hasAttribute('min')) {
                el.setAttribute('min', '0');
            }

            if (numberField && el.dataset.allowNegative !== '1') {
                el.addEventListener('keydown', function (e) {
                    if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
                        e.preventDefault();
                    }
                });
                el.addEventListener('paste', function (e) {
                    var text = '';
                    try {
                        text = (e.clipboardData || window.clipboardData).getData('text') || '';
                    } catch (err) { return; }
                    if (/[-eE+]/.test(text) || (text !== '' && Number(text) < 0)) {
                        e.preventDefault();
                    }
                });
            }

            var clearOnInteract = function () {
                if (form.dataset.derasSuppressClear === '1') return;
                if (el.classList.contains('deras-field-invalid') || el.classList.contains('is-invalid')) {
                    clearFieldError(el);
                }
            };
            el.addEventListener('focus', clearOnInteract);
            el.addEventListener('input', clearOnInteract);
            el.addEventListener('change', clearOnInteract);
        });
    }

    function boot() {
        document.querySelectorAll('form').forEach(bindForm);
    }

    document.addEventListener('submit', onSubmitCapture, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
    window.addEventListener('load', boot);

    window.DerasValidation = {
        validateForm: validateForm,
        bindForm: bindForm,
        clearFieldError: clearFieldError,
        showFieldError: showFieldError
    };
})();
