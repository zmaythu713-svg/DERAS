@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0 mx-auto" style="max-width: 1200px; border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-link me-2"></i>အတန်း–ဘာသာရပ် တွဲချိတ်ရန်
                    <span class="fw-normal opacity-90">· {{ $grade->name }}</span>
                </h5>
            </div>

            <div class="card-body" style="padding: 24px 28px 0;">
                <style>
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
                        margin: 4px 0 12px;
                    }
                    .grade-readonly {
                        background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px;
                        padding: 12px 14px; font-size: 15px; font-weight: 600; color: #0f172a;
                    }
                </style>

                <form method="POST" action="{{ route('grade-subjects.update', $grade->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-layer-group me-1"></i> အတန်း</label>
                        <div class="grade-readonly">{{ $grade->name }}</div>
                        {{-- <small class="text-slate-500">အတန်းအမည် ပြင်ရန် <a href="{{ route('grades.edit', $grade->id) }}">အတန်းများ</a> သို့ သွားပါ။</small> --}}
                    </div>

                    <h6 class="subjects-title">
                        <i class="fas fa-books me-1"></i> ဘာသာရပ်များ (အမျိုးအစားအလိုက် ခွဲခြား)
                    </h6>

                    @include('grade-subjects._subject_fields')

                    <div class="tb-form-footer">
                        <a href="{{ route('grade-subjects.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> နောက်သို့
                        </a>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> သိမ်းဆည်းရန်
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
