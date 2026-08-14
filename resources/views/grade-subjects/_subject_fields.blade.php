{{-- Shared: 3 category subject checkbox sections (grade-specific lists) --}}
@php
    $linkedByCategory = $linkedByCategory ?? [];
    $fieldMap = $fieldMap ?? \App\Models\Category::fieldMap();
    $bookNamesByCategory = $bookNamesByCategory ?? [];
@endphp

<style>
    .subjects-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 4px;
    }
    .subject-section {
        background: #f0fdf4; border: 1.5px solid #bbf7d0;
        border-radius: 10px; padding: 12px; margin-bottom: 0;
        min-height: 0;
    }
    .subject-section.handbook { background: #eff6ff; border-color: #bfdbfe; }
    .subject-section.guide { background: #fff7ed; border-color: #fed7aa; }
    .subject-section-title {
        font-size: 13px; font-weight: 700; color: #065f46;
        margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
        flex-wrap: wrap;
    }
    .subject-section.handbook .subject-section-title { color: #1d4ed8; }
    .subject-section.guide .subject-section-title { color: #c2410c; }
    .subject-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px;
        max-height: 420px;
        overflow-y: auto;
        padding-right: 2px;
    }
    .subject-check-item {
        display: flex; align-items: center; gap: 7px;
        background: #fff; border: 1.5px solid #d1fae5;
        border-radius: 7px; padding: 6px 10px;
        cursor: pointer; transition: all 0.15s ease; user-select: none;
    }
    .subject-section.handbook .subject-check-item { border-color: #dbeafe; }
    .subject-section.guide .subject-check-item { border-color: #ffedd5; }
    .subject-check-item:hover { border-color: #34d399; background: #ecfdf5; }
    .subject-check-item input[type="checkbox"] { width: 14px; height: 14px; accent-color: #059669; cursor: pointer; flex-shrink: 0; }
    .subject-check-item label { font-size: 12.5px; font-weight: 500; color: #374151; cursor: pointer; margin: 0; line-height: 1.3; }
    .subject-check-item.checked { border-color: #059669; background: #ecfdf5; }
    .select-all-btn {
        background: none; border: 1px solid currentColor; border-radius: 5px;
        padding: 2px 8px; font-size: 11px; font-weight: 600; cursor: pointer; margin-left: auto;
    }
    @media (max-width: 992px) {
        .subjects-row { grid-template-columns: 1fr; }
        .subject-grid { max-height: none; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
        .subject-section { margin-bottom: 10px; }
    }
</style>

<div class="subjects-row">
@forelse ($categories as $category)
    @php
        $meta = $fieldMap[$category->slug] ?? null;
        if (!$meta) continue;
        $field = $meta['field'];
        $sectionClass = match ($category->slug) {
            'teacher_handbook' => 'handbook',
            'teacher_guide' => 'guide',
            default => '',
        };
        $selected = old($field, $linkedByCategory[$category->slug] ?? []);
        $sectionBooks = collect($bookNamesByCategory[$category->slug] ?? $bookNames ?? []);
    @endphp

    <div class="subject-section {{ $sectionClass }}" data-section="{{ $category->slug }}">
        <div class="subject-section-title">
            <i class="fas {{ $meta['icon'] }}"></i>
            {{ $meta['label'] }}
            @if ($sectionBooks->isNotEmpty())
                <button type="button" class="select-all-btn" onclick="toggleSectionAll(this)">အားလုံးရွေး</button>
            @endif
        </div>

        @if ($sectionBooks->isEmpty())
            <p class="text-slate-400 text-sm mb-0">ဤအတန်းအတွက် ဘာသာရပ် မရှိပါ။</p>
        @else
            <div class="subject-grid">
                @foreach ($sectionBooks as $book)
                    <div class="subject-check-item {{ in_array($book->id, $selected) ? 'checked' : '' }}"
                         onclick="toggleSubjectCheck(this, event)">
                        <input type="checkbox" name="{{ $field }}[]"
                            id="{{ $category->slug }}_book_{{ $book->id }}" value="{{ $book->id }}"
                            {{ in_array($book->id, $selected) ? 'checked' : '' }}
                            onclick="event.stopPropagation()">
                        <label for="{{ $category->slug }}_book_{{ $book->id }}">{{ $book->name }}</label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@empty
    <div class="alert alert-warning">Category မရှိသေးပါ။ CategorySeeder run လုပ်ပါ။</div>
@endforelse
</div>

<script>
    function toggleSubjectCheck(el, event) {
        if (event.target.tagName === 'INPUT' || event.target.tagName === 'LABEL') return;
        const cb = el.querySelector('input[type="checkbox"]');
        cb.checked = !cb.checked;
        el.classList.toggle('checked', cb.checked);
    }

    function toggleSectionAll(btn) {
        const section = btn.closest('.subject-section');
        const items = section.querySelectorAll('.subject-check-item');
        const checks = section.querySelectorAll('input[type="checkbox"]');
        const allChecked = [...checks].every(c => c.checked);
        checks.forEach(c => c.checked = !allChecked);
        items.forEach(el => el.classList.toggle('checked', !allChecked));
        btn.textContent = allChecked ? 'အားလုံးရွေး' : 'ရွေးချယ်မှုဖယ်ရှား';
    }
</script>
