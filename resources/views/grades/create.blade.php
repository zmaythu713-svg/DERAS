@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0 mx-auto" style="max-width: 700px; border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-layer-group me-2"></i>အတန်း ဖန်တီးရန်
                </h5>
            </div>

            <div class="card-body" style="padding: 24px 28px;">
                <style>
                    .form-select:focus, .form-control:focus {
                        border-color: #105c3a !important;
                        box-shadow: 0 0 0 0.2rem rgba(16, 92, 58, 0.18) !important;
                        outline: none !important;
                    }
                    .form-select, .form-control {
                        height: 48px !important; font-size: 15px !important;
                        border-radius: 8px !important; border: 1.5px solid #dee2e6 !important;
                        padding: 0.5rem 0.75rem; width: 100% !important;
                    }
                    .form-label { font-size: 15px; font-weight: 600; color: #105c3a; margin-bottom: 10px; display: block; }
                    .status-switch {
                        position: relative; width: 74px; height: 36px; border-radius: 999px;
                        background: gray; cursor: pointer; transition: .25s ease; user-select: none;
                    }
                    .status-switch.active { background: var(--deras-leaf); }
                    .status-knob {
                        position: absolute; top: 3px; left: 3px; width: 30px; height: 30px;
                        background: #fff; border-radius: 50%; transition: .25s ease;
                        box-shadow: 0 2px 8px rgba(0,0,0,.25);
                    }
                    .status-switch.active .status-knob { transform: translateX(38px); }
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
                        margin: 24px -28px -24px; padding: 16px 28px;
                        display: flex; justify-content: flex-end; gap: 10px;
                    }
                </style>

                <form method="POST" action="{{ route('grades.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-tag me-1"></i> အတန်းအမည် <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="ဥပမာ - KG, Grade-1 ..." required data-allow-digits="1">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-toggle-on me-1"></i> အခြေအနေ</label>
                        <input type="hidden" name="is_active" id="is_active" value="{{ old('is_active', 1) }}">
                        <div id="statusToggle" class="status-switch {{ old('is_active', 1) ? 'active' : '' }}">
                            <div class="status-knob"></div>
                        </div>
                    </div>

                    <div class="tb-form-footer">
                        <a href="{{ route('grades.index') }}" class="btn-back">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const t = document.getElementById('statusToggle'), i = document.getElementById('is_active');
            if (t && i) {
                t.addEventListener('click', function () {
                    this.classList.toggle('active');
                    i.value = this.classList.contains('active') ? 1 : 0;
                });
            }
        });
    </script>
@endsection
