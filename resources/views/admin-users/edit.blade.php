@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0 mx-auto" style="max-width: 850px; border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-pen me-2"></i>စီမံခန့်ခွဲသူ အကောင့် ပြင်ဆင်ရန်
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

                <form method="POST" action="{{ route('admin-users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label"><i class="fas fa-user me-1"></i> အမည် <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required placeholder="အမည် ထည့်သွင်းပါ" data-validate="name" data-label="အမည်">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-envelope me-1"></i> အီးမေးလ် <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required placeholder="အီးမေးလ် ထည့်သွင်းပါ">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label"><i class="fas fa-user-shield me-1"></i> အမျိုးအစား (Role) <span class="text-danger">*</span></label>
                            @if ($user->role === 'super')
                                <input type="hidden" name="role" value="super">
                                <div class="form-control bg-light" style="pointer-events: none;">Super Admin</div>
                                <small class="text-muted">Super Admin role ကို ပြောင်း၍မရပါ။</small>
                            @else
                                <input type="hidden" name="role" value="admin">
                                <div class="form-control bg-light" style="pointer-events: none;">Admin</div>
                                <small class="text-muted">Admin ကို Super Admin သို့ ပြောင်း၍မရပါ။</small>
                            @endif
                            @error('role')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-lock me-1"></i> စကားဝှက်အသစ်</label>
                            <div class="position-relative">
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" style="padding-right: 42px;" placeholder="မပြောင်းလဲပါက ချန်ထားပါ" minlength="8" autocomplete="new-password">
                                <i class="fas fa-eye-slash password-toggle-icon" id="password_icon" onclick="togglePassword('password','password_icon')"></i>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 12px;">မပြောင်းလဲချင်ပါက ကွက်လပ်ထားခဲ့ပါ။</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-key me-1"></i> စကားဝှက် အတည်ပြုပါ</label>
                            <div class="position-relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" style="padding-right: 42px;" placeholder="စကားဝှက်အသစ် ပြန်လည်ရိုက်ထည့်ပါ" data-match="password" minlength="8" autocomplete="new-password">
                                <i class="fas fa-eye-slash password-toggle-icon" id="password_confirmation_icon" onclick="togglePassword('password_confirmation','password_confirmation_icon')"></i>
                            </div>
                        </div>
                    </div>

                    <div class="tb-form-footer">
                        <a href="{{ route('admin-users.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> နောက်သို့
                        </a>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-pen"></i> ပြင်ဆင်ရန်
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
