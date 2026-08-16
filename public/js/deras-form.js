/**
 * Shared cascading Grade → Subjects + resource auto-fill helpers.
 */
window.DerasForm = (function () {
    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function qsa(sel, root) {
        return Array.from((root || document).querySelectorAll(sel));
    }

    /** Keep zero blank so placeholder="0" can show on number inputs. */
    function displayNumber(value) {
        if (value === null || value === undefined || value === '') return '';
        var n = Number(value);
        if (!isNaN(n) && n === 0) return '';
        return value;
    }

    async function fetchJson(url) {
        const res = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            throw new Error('Request failed');
        }
        return res.json();
    }

    function fillSelect(select, items, selectedId) {
        if (!select) return;
        const placeholder = select.dataset.placeholder || 'ရွေးချယ်ပါ';
        const current = selectedId ?? select.value;
        select.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        select.appendChild(opt0);
        (items || []).forEach(function (item) {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            if (String(item.id) === String(current)) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    /**
     * Wire grade → subjects cascading.
     * Options: { gradeSelect, subjectSelect, categorySelect?, categorySlug?, keepSelected? }
     */
    function wireGradeSubjects(options) {
        const gradeSelect = typeof options.gradeSelect === 'string'
            ? qs(options.gradeSelect)
            : options.gradeSelect;
        const subjectSelect = typeof options.subjectSelect === 'string'
            ? qs(options.subjectSelect)
            : options.subjectSelect;
        const categorySelect = options.categorySelect
            ? (typeof options.categorySelect === 'string' ? qs(options.categorySelect) : options.categorySelect)
            : null;

        if (!gradeSelect || !subjectSelect) return;

        subjectSelect.dataset.placeholder = subjectSelect.dataset.placeholder || 'ဘာသာရပ်ရွေးချယ်ပါ';

        async function reload() {
            const gradeId = gradeSelect.value;
            if (!gradeId) {
                fillSelect(subjectSelect, [], '');
                subjectSelect.dispatchEvent(new Event('change'));
                return;
            }

            let url = '/grades/' + gradeId + '/subjects';
            const params = new URLSearchParams();
            if (categorySelect && categorySelect.value) {
                params.set('category_id', categorySelect.value);
            } else if (options.categorySlug) {
                params.set('category', options.categorySlug);
            }
            const qsStr = params.toString();
            if (qsStr) url += '?' + qsStr;

            try {
                const subjects = await fetchJson(url);
                fillSelect(subjectSelect, subjects, options.keepSelected ? subjectSelect.value : '');
                subjectSelect.dispatchEvent(new Event('change'));
            } catch (e) {
                console.error(e);
            }
        }

        gradeSelect.addEventListener('change', reload);
        if (categorySelect) {
            categorySelect.addEventListener('change', reload);
        }

        if (gradeSelect.value) {
            reload();
        }
    }

    /**
     * Auto-fill textbook fields from allocation plan.
     */
    function wireTextbookAllocationAutofill(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const township = qs(options.townshipSelect || '[name="township_id"]');
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const subject = qs(options.subjectSelect || '[name="book_name_id"]');
        const booksPerSet = qs(options.booksPerSetInput || '[name="books_per_set"]');
        const issuedQty = qs(options.issuedQtyInput || '[name="student_count"]');

        if (!year || !township || !grade || !subject) return;

        async function autofill() {
            if (!year.value || !township.value || !grade.value || !subject.value) {
                return;
            }

            const params = new URLSearchParams({
                academic_year_id: year.value,
                township_id: township.value,
                grade_id: grade.value,
                book_name_id: subject.value,
            });

            try {
                const data = await fetchJson('/lookups/allocation-for-textbook?' + params.toString());
                if (data.found) {
                    if (booksPerSet) {
                        booksPerSet.value = displayNumber(data.books_per_set ?? '');
                        booksPerSet.readOnly = true;
                        booksPerSet.classList.add('calc-input');
                    }
                    if (issuedQty) {
                        issuedQty.value = displayNumber(data.student_count ?? '');
                        issuedQty.readOnly = true;
                        issuedQty.classList.add('calc-input');
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }

        [year, township, grade, subject].forEach(function (el) {
            el.addEventListener('change', autofill);
        });
    }

    /**
     * Auto-fill previous balance (+ optional school count).
     */
    function wirePreviousBalanceAutofill(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const township = options.townshipSelect ? qs(options.townshipSelect) : null;
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const subject = qs(options.subjectSelect || '[name="book_name_id"]');
        const balanceInput = qs(options.balanceInput || '[name="previous_balance"]');

        if (!year || !grade || !subject || !balanceInput) return;

        async function autofill() {
            if (!year.value || !grade.value || !subject.value) return;
            if (township && !township.value) return;

            const params = new URLSearchParams({
                academic_year_id: year.value,
                grade_id: grade.value,
                book_name_id: subject.value,
            });
            if (township && township.value) {
                params.set('township_id', township.value);
            }

            try {
                const data = await fetchJson('/lookups/previous-year-balance?' + params.toString());
                var bal = 0;
                if (typeof data.previous_balance === 'number') {
                    bal = data.previous_balance;
                } else if (data.previous_balance && typeof data.previous_balance === 'object') {
                    bal = 0;
                }
                balanceInput.value = displayNumber(bal);
                if (options.readOnly !== false) {
                    balanceInput.readOnly = true;
                    balanceInput.classList.add('calc-input');
                }
                balanceInput.dispatchEvent(new Event('input', { bubbles: true }));
            } catch (e) {
                console.error(e);
            }
        }

        [year, grade, subject].forEach(function (el) {
            el.addEventListener('change', autofill);
        });
        if (township) township.addEventListener('change', autofill);

        if (year.value && grade.value && subject.value && (!township || township.value)) {
            autofill();
        }
    }

    /**
     * Stocks (ထပ်ဆောင်းဖြန့်ဝေ):
     * လိုအပ်မှု = အပ်နှံပြီးလိုအပ်မှု - (ယခင်နှစ်လက်ကျန် + လက်ဆင့်ကမ်း)
     */
    function wireStockRequirementCalc(options) {
        const previous = qs(options.previousInput || '[name="previous_balance"]');
        const transferred = qs(options.transferredInput || '[name="transferred"]');
        const enrolled = qs(options.enrolledInput || '[name="enrolled_need"]');
        const required = qs(options.requiredInput || '[name="required_qty"]');

        if (!previous || !transferred || !enrolled || !required) return;

        function num(el) {
            var raw = String(el.value || '').trim();
            if (raw === '') return 0;
            var v = parseInt(raw, 10);
            return isNaN(v) ? 0 : v;
        }

        function recalc() {
            var prev = num(previous);
            var hand = num(transferred);
            var need = num(enrolled);
            // လိုအပ်မှု = အပ်နှံပြီးလိုအပ်မှု - (ယခင်နှစ်လက်ကျန် + လက်ဆင့်ကမ်း)
            var result = need - (prev + hand);
            if (result < 0) result = 0;
            required.value = String(result);
            required.readOnly = true;
            required.classList.add('calc-input');
        }

        [previous, transferred, enrolled].forEach(function (el) {
            el.addEventListener('input', recalc);
            el.addEventListener('change', recalc);
            el.addEventListener('keyup', recalc);
            el.addEventListener('blur', recalc);
        });

        // Recalc after autofill / late subject load
        setTimeout(recalc, 0);
        setTimeout(recalc, 300);
        setTimeout(recalc, 800);
    }

    function wireSchoolCountAutofill(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const township = qs(options.townshipSelect || '[name="township_id"]');
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const countInput = qs(options.countInput || '[name="school_count"]');

        if (!grade || !countInput) return;

        async function autofill() {
            if (!grade.value) {
                countInput.value = '';
                return;
            }
            const params = new URLSearchParams({
                grade_id: grade.value,
            });
            if (township && township.value) {
                params.set('township_id', township.value);
            }
            if (year && year.value) {
                params.set('academic_year_id', year.value);
            }
            try {
                const data = await fetchJson('/lookups/school-count?' + params.toString());
                countInput.value = data.found ? displayNumber(data.school_count ?? 0) : '';
            } catch (e) {
                console.error(e);
            }
        }

        grade.addEventListener('change', autofill);
        if (township) township.addEventListener('change', autofill);
        if (year) year.addEventListener('change', autofill);

        // Prefill on load when grade already selected
        if (grade.value) {
            autofill();
        }
    }

    /**
     * Toggle ဆရာကိုင် / ဆရာလမ်းညွှန် field when category is teacher_guide.
     */
    function wireGuideTypeField(options) {
        const categorySelect = qs(options.categorySelect || '[name="category_id"]');
        const guideWrap = qs(options.guideWrap || '#guide_type_wrap');
        const teacherGuideSlug = options.teacherGuideSlug || 'teacher_guide';
        const categoryMeta = options.categoryMeta || {};

        if (!categorySelect || !guideWrap) return;

        function toggle() {
            const selected = categorySelect.options[categorySelect.selectedIndex];
            const slug = selected?.dataset?.slug
                || categoryMeta[categorySelect.value]
                || '';
            guideWrap.style.display = slug === teacherGuideSlug ? '' : 'none';
            const guideSelect = qs('[name="guide_type"]', guideWrap);
            if (guideSelect && slug !== teacherGuideSlug) {
                guideSelect.value = '';
            }
        }

        categorySelect.addEventListener('change', toggle);
        toggle();
    }

    function wireSupplyIssuedFromQuota(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const township = qs(options.townshipSelect || '[name="township_id"]');
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const item = qs(options.itemSelect || '[name="supply_item_id"]');
        const issuedInput = qs(options.issuedInput || '[name="issued_total"]');

        if (!township || !grade || !item || !issuedInput) return;

        async function autofill() {
            if (!township.value || !grade.value || !item.value) {
                return;
            }
            const params = new URLSearchParams({
                township_id: township.value,
                grade_id: grade.value,
                supply_item_id: item.value,
            });
            if (year && year.value) {
                params.set('academic_year_id', year.value);
            }
            try {
                const data = await fetchJson('/lookups/school-supply-quantity?' + params.toString());
                issuedInput.value = data.found ? displayNumber(data.quantity ?? 0) : '';
                issuedInput.dispatchEvent(new Event('input', { bubbles: true }));
                issuedInput.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (e) {
                console.error(e);
            }
        }

        [township, grade, item].forEach(function (el) {
            el.addEventListener('change', autofill);
        });
        if (year) year.addEventListener('change', autofill);

        if (township.value && grade.value && item.value) {
            autofill();
        }
    }

    /**
     * Teacher-guide distribution: cascade subjects by grade + guide_type,
     * and auto-fill district quotas from လက်ခံရရှိမှု.
     */
    function wireTeacherGuideDistributionForm(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const guideType = qs(options.guideTypeSelect || '[name="guide_type"]');
        const book = qs(options.bookSelect || '[name="book_name_id"]');
        const kgInput = qs(options.kgQuotaInput || '#kg_to_g12_quota');
        const g1Input = qs(options.g1QuotaInput || '#g1_to_g5_quota');
        const totalInput = qs(options.totalQuotaInput || '#total_quota');

        if (!grade || !guideType || !book) return;

        book.dataset.placeholder = book.dataset.placeholder || '-- ရွေးချယ်ပါ --';

        function categorySlug() {
            return guideType.value === 'ဆရာကိုင်' ? 'teacher_handbook' : 'teacher_guide';
        }

        async function reloadSubjects() {
            if (!grade.value) {
                fillSelect(book, [], '');
                book.dispatchEvent(new Event('change'));
                return;
            }
            try {
                const subjects = await fetchJson(
                    '/grades/' + grade.value + '/subjects?category=' + encodeURIComponent(categorySlug())
                );
                fillSelect(book, subjects, '');
                book.dispatchEvent(new Event('change'));
            } catch (e) {
                console.error(e);
            }
        }

        async function autofillQuotas() {
            if (!year || !year.value || !grade.value || !book.value || !guideType.value) {
                if (kgInput) kgInput.value = '';
                if (g1Input) g1Input.value = '';
                if (totalInput) totalInput.value = '';
                if (kgInput) kgInput.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }
            const params = new URLSearchParams({
                academic_year_id: year.value,
                grade_id: grade.value,
                book_name_id: book.value,
                guide_type: guideType.value,
            });
            try {
                const data = await fetchJson('/lookups/teacher-guide-receipt?' + params.toString());
                if (kgInput) kgInput.value = data.found ? displayNumber(data.kg_to_g12_quota ?? 0) : '';
                if (g1Input) g1Input.value = data.found ? displayNumber(data.g1_to_g5_quota ?? 0) : '';
                if (totalInput) {
                    totalInput.value = data.found
                        ? displayNumber(data.total_quota ?? ((data.kg_to_g12_quota || 0) + (data.g1_to_g5_quota || 0)))
                        : '';
                }
                if (kgInput) kgInput.dispatchEvent(new Event('input', { bubbles: true }));
            } catch (e) {
                console.error(e);
            }
        }

        grade.addEventListener('change', reloadSubjects);
        guideType.addEventListener('change', reloadSubjects);
        book.addEventListener('change', autofillQuotas);
        if (year) year.addEventListener('change', autofillQuotas);

        if (grade.value) {
            reloadSubjects();
        }
    }

    /**
     * Teacher-guide issues (ဖြန့်ဝေစာရင်း):
     * - subjects by grade + guide_type
     * - district_unit ← remaining_total from ဖြန့်ဝေရန်ခွဲတမ်း
     * - issued per township ← total_*_qty (နှစ်မျိုးပေါင်း)
     */
    function wireTeacherGuideIssueForm(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const guideType = qs(options.guideTypeSelect || '[name="guide_type"]');
        const book = qs(options.bookSelect || '[name="book_name_id"]');
        const districtInput = qs(options.districtInput || '[name="district_unit"]');
        const packageUnit = qs(options.packageUnitInput || '#package_unit');
        const townshipMap = options.townshipMap || {};

        if (!grade || !guideType || !book) return;

        book.dataset.placeholder = book.dataset.placeholder || '-- ရွေးချယ်ပါ --';

        function categorySlug() {
            return guideType.value === 'ဆရာကိုင်' ? 'teacher_handbook' : 'teacher_guide';
        }

        function recalcPackages() {
            const unit = parseInt(packageUnit?.value || '0', 10) || 0;
            Object.values(townshipMap).forEach(function (townshipId) {
                const issuedEl = document.getElementById('issued_' + townshipId);
                const packageEl = document.getElementById('package_' + townshipId);
                const looseEl = document.getElementById('loose_' + townshipId);
                if (!issuedEl || !packageEl || !looseEl) return;
                const issued = parseInt(issuedEl.value || '0', 10) || 0;
                if (unit <= 0) {
                    packageEl.value = '';
                    looseEl.value = '';
                    return;
                }
                packageEl.value = displayNumber(Math.floor(issued / unit));
                looseEl.value = displayNumber(issued % unit);
            });
        }

        async function reloadSubjects(keepSelected) {
            if (!grade.value) {
                fillSelect(book, [], '');
                book.dispatchEvent(new Event('change'));
                return;
            }
            try {
                const subjects = await fetchJson(
                    '/grades/' + grade.value + '/subjects?category=' + encodeURIComponent(categorySlug())
                );
                fillSelect(book, subjects, keepSelected ? book.value : '');
                book.dispatchEvent(new Event('change'));
            } catch (e) {
                console.error(e);
            }
        }

        async function autofillFromDistribution() {
            if (!year || !year.value || !grade.value || !book.value || !guideType.value) {
                if (districtInput) districtInput.value = '';
                Object.values(townshipMap).forEach(function (townshipId) {
                    const issuedEl = document.getElementById('issued_' + townshipId);
                    if (issuedEl) issuedEl.value = '';
                });
                recalcPackages();
                return;
            }

            const params = new URLSearchParams({
                academic_year_id: year.value,
                grade_id: grade.value,
                book_name_id: book.value,
                guide_type: guideType.value,
            });

            try {
                const data = await fetchJson('/lookups/teacher-guide-receipt?' + params.toString());
                if (districtInput) {
                    districtInput.value = data.found ? displayNumber(data.remaining_total ?? 0) : '';
                }

                const issuedByName = data.found ? (data.township_issued || {}) : {};
                Object.keys(townshipMap).forEach(function (name) {
                    const townshipId = townshipMap[name];
                    const issuedEl = document.getElementById('issued_' + townshipId);
                    if (issuedEl) {
                        issuedEl.value = displayNumber(issuedByName[name] ?? 0);
                    }
                });
                recalcPackages();
            } catch (e) {
                console.error(e);
            }
        }

        grade.addEventListener('change', function () { reloadSubjects(false); });
        guideType.addEventListener('change', function () { reloadSubjects(false); });
        book.addEventListener('change', autofillFromDistribution);
        if (year) year.addEventListener('change', autofillFromDistribution);
        if (packageUnit) packageUnit.addEventListener('input', recalcPackages);

        if (grade.value) {
            reloadSubjects(true);
        }
    }

    /**
     * Teacher-guide summaries (စာရင်းချုပ်):
     * - subjects by grade + guide_type
     * - fiscal_year_quota ← လက်ခံရရှိမှု total_quota (ခရိုင်ရရှိခွဲတမ်း)
     * - distributed_books ← ဖြန့်ဝေရန်ခွဲတမ်း distributed_total
     */
    function wireTeacherGuideSummaryForm(options) {
        const year = qs(options.yearSelect || '[name="academic_year_id"]');
        const grade = qs(options.gradeSelect || '[name="grade_id"]');
        const guideType = qs(options.guideTypeSelect || '[name="guide_type"]');
        const book = qs(options.bookSelect || '[name="book_name_id"]');
        const fiscalInput = qs(options.fiscalInput || '[name="fiscal_year_quota"]');
        const distributedInput = qs(options.distributedInput || '[name="distributed_books"]');
        const previousInput = qs(options.previousInput || '[name="previous_balance"]');
        const onRecalc = options.onRecalc || function () {};

        if (!grade || !guideType || !book) return;

        book.dataset.placeholder = book.dataset.placeholder || '-- ရွေးချယ်ပါ --';

        function categorySlug() {
            return guideType.value === 'ဆရာကိုင်' ? 'teacher_handbook' : 'teacher_guide';
        }

        async function reloadSubjects(keepSelected) {
            if (!grade.value) {
                fillSelect(book, [], '');
                book.dispatchEvent(new Event('change'));
                return;
            }
            try {
                const subjects = await fetchJson(
                    '/grades/' + grade.value + '/subjects?category=' + encodeURIComponent(categorySlug())
                );
                fillSelect(book, subjects, keepSelected ? book.value : '');
                book.dispatchEvent(new Event('change'));
            } catch (e) {
                console.error(e);
            }
        }

        async function autofillFromQuota() {
            if (!year || !year.value || !grade.value || !book.value || !guideType.value) {
                if (fiscalInput) fiscalInput.value = '';
                if (distributedInput) distributedInput.value = '';
                onRecalc();
                return;
            }

            const params = new URLSearchParams({
                academic_year_id: year.value,
                grade_id: grade.value,
                book_name_id: book.value,
                guide_type: guideType.value,
            });

            try {
                const data = await fetchJson('/lookups/teacher-guide-receipt?' + params.toString());
                if (fiscalInput) {
                    fiscalInput.value = data.found ? displayNumber(data.total_quota ?? 0) : '';
                }
                if (distributedInput) {
                    distributedInput.value = data.found ? displayNumber(data.distributed_total ?? 0) : '';
                }
                onRecalc();
            } catch (e) {
                console.error(e);
            }
        }

        grade.addEventListener('change', function () { reloadSubjects(false); });
        guideType.addEventListener('change', function () { reloadSubjects(false); });
        book.addEventListener('change', autofillFromQuota);
        if (year) year.addEventListener('change', autofillFromQuota);
        if (previousInput) previousInput.addEventListener('input', onRecalc);

        if (grade.value) {
            reloadSubjects(true);
        }
    }

    return {
        wireGradeSubjects: wireGradeSubjects,
        wireTextbookAllocationAutofill: wireTextbookAllocationAutofill,
        wirePreviousBalanceAutofill: wirePreviousBalanceAutofill,
        wireStockRequirementCalc: wireStockRequirementCalc,
        wireSchoolCountAutofill: wireSchoolCountAutofill,
        wireSupplyIssuedFromQuota: wireSupplyIssuedFromQuota,
        wireTeacherGuideDistributionForm: wireTeacherGuideDistributionForm,
        wireTeacherGuideIssueForm: wireTeacherGuideIssueForm,
        wireTeacherGuideSummaryForm: wireTeacherGuideSummaryForm,
        wireGuideTypeField: wireGuideTypeField,
        displayNumber: displayNumber,
        fillSelect: fillSelect,
        fetchJson: fetchJson,
    };
})();
