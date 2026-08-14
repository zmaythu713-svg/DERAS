@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0 mx-auto" style="max-width: 1200px; border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-plus me-2"></i>အတန်း–ဘာသာရပ် တွဲချိတ်ရန်
                </h5>
            </div>

            <div class="card-body" style="padding: 24px 28px 0;">
                <style>
                    .form-select:focus, .form-control:focus {
                        border-color: #105c3a !important;
                        box-shadow: 0 0 0 0.2rem rgba(16, 92, 58, 0.18) !important;
                    }
                    .form-select, .form-control {
                        height: 48px !important; font-size: 15px !important;
                        border-radius: 8px !important; border: 1.5px solid #dee2e6 !important;
                        padding: 0.5rem 0.75rem; width: 100% !important;
                    }
                    .form-label { font-size: 15px; font-weight: 600; color: #105c3a; margin-bottom: 10px; display: block; }
                    .btn-back {
                        background: #ffffff; border: 1.5px solid #105c3a; color: #105c3a;
                        border-radius: 8px; padding: 9px 24px; font-size: 16px; font-weight: 600;
                        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
                    }
                    .btn-back:hover { background: #051c14; color: #ffffff; border-color: #051c14; text-decoration: none; }
                    .btn-save {
                        background: #072a1e; border: none; color: #fff; border-radius: 8px;
                        padding: 9px 24px; font-size: 16px; font-weight: 600; cursor: pointer;
                        display: inline-flex; align-items: center; gap: 6px;
                    }
                    .tb-form-footer {
                        background: #f8fbf9; border-top: 1px solid #d1fae5;
                        margin: 16px -28px 0; padding: 16px 28px;
                        display: flex; justify-content: flex-end; gap: 10px;
                    }
                    .subjects-title {
                        font-size: 15px; font-weight: 700; color: #105c3a;
                        margin: 16px 0 12px;
                    }
                </style>

                <form method="POST" action="{{ route('grade-subjects.store') }}" id="gradeSubjectForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-layer-group me-1"></i> အတန်း <span class="text-danger">*</span></label>
                        <select name="grade_id" id="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
                            <option value="">အတန်း ရွေးချယ်ပါ</option>
                            @foreach ($grades as $g)
                                <option value="{{ $g->id }}" @selected((string) old('grade_id', $grade?->id) === (string) $g->id)>
                                    {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('grade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        {{-- <small class="text-slate-500">အတန်း မရှိသေးရင် <a href="{{ route('grades.create') }}">အတန်းများ</a> မှ အရင် ဖန်တီးပါ။</small> --}}
                    </div>

                    @if ($grade)
                        <h6 class="subjects-title">
                            <i class="fas fa-books me-1"></i> ဘာသာရပ်များ — လိုအပ်သည်များကို ရွေးချယ်ပါ
                        </h6>
                        <p class="text-slate-500 text-sm mb-3">
                            {{-- အောက်တွင် ဘာသာရပ်စာရင်း အစုံ ပေါ်နေပါသည်။ ချိတ်ဆက်လိုသည်များကို အမှန်ခြစ်ပြီး သိမ်းဆည်းပါ။ --}}
                        </p>
                        @include('grade-subjects._subject_fields')
                    @else
                        <div class="alert alert-light border text-slate-600 mt-3">
                            {{-- အတန်း ရွေးချယ်ပါ — ရွေးပြီးသည်နှင့် ဘာသာရပ်စာရင်း အစုံ ပေါ်လာပြီး လိုအပ်သည်များကို ရွေးနိုင်ပါသည်။ --}}
                        </div>
                    @endif

                    <div class="tb-form-footer">
                        <a href="{{ route('grade-subjects.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> နောက်သို့
                        </a>
                        <button type="submit" class="btn-save" {{ $grade ? '' : 'disabled' }}>
                            <i class="fas fa-save"></i> သိမ်းဆည်းရန်
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const select = document.getElementById('grade_id');
            if (!select) return;
            select.addEventListener('change', function () {
                const base = @json(route('grade-subjects.create'));
                const id = this.value;
                window.location.href = id ? (base + '?grade_id=' + encodeURIComponent(id)) : base;
            });
        })();
    </script>
@endsection
