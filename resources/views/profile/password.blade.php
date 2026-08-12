@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0 mx-auto" style="max-width: 700px; border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-lock me-2"></i>စကားဝှက် ပြောင်းလဲရန်
                </h5>
            </div>

            <div class="card-body" style="padding: 24px 28px;">
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
                        font-size: 15px;
                        font-weight: 600;
                        color: #105c3a;
                        margin-bottom: 10px;
                        display: block;
                    }

                    .form-label i {
                        color: #105c3a;
                    }

                    /* ===== Password Toggle Icon ===== */
                    .password-toggle-icon {
                        position: absolute;
                        right: 14px;
                        top: 50%;
                        transform: translateY(-50%);
                        cursor: pointer;
                        color: #6c757d;
                        font-size: 16px;
                        z-index: 10;
                        transition: color 0.2s;
                    }
                    .password-toggle-icon:hover {
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

                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4 py-2 px-3" style="font-size:14px;">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert rounded-3 mb-4 py-2 px-3" style="background-color:#d1fae5; color:#065f46; font-size:14px;">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-key me-1"></i> လက်ရှိစကားဝှက် <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input type="password" id="current_password" name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                style="padding-right: 42px;"
                                placeholder="လက်ရှိစကားဝှက် ထည့်သွင်းပါ"
                                required>
                            <i class="fas fa-eye-slash password-toggle-icon" id="icon_current"
                                onclick="togglePassword('current_password','icon_current')"></i>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-lock me-1"></i> စကားဝှက်အသစ် <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input type="password" id="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                style="padding-right: 42px;"
                                placeholder="စကားဝှက်အသစ် ထည့်သွင်းပါ"
                                required minlength="8" autocomplete="new-password">
                            <i class="fas fa-eye-slash password-toggle-icon" id="icon_password"
                                onclick="togglePassword('password','icon_password')"></i>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-shield-alt me-1"></i> စကားဝှက်အသစ် အတည်ပြုပါ <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control"
                                style="padding-right: 42px;"
                                placeholder="စကားဝှက်အသစ် ပြန်လည်ရိုက်ထည့်ပါ"
                                required minlength="8" data-match="password" autocomplete="new-password">
                            <i class="fas fa-eye-slash password-toggle-icon" id="icon_confirmation"
                                onclick="togglePassword('password_confirmation','icon_confirmation')"></i>
                        </div>
                    </div>

                    <div class="tb-form-footer">
                        <a href="{{ route('dashboard') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> နောက်သို့
                        </a>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-pen"></i> ပြောင်းလဲရန်
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }
    </script>
@endsection
