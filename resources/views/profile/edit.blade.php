@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0 mx-auto" style="max-width: 700px; border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user-circle me-2"></i>ကိုယ်ရေးအချက်အလက်
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
                    .btn-save:disabled {
                        background: #94a3b8 !important;
                        box-shadow: none !important;
                        cursor: not-allowed !important;
                        transform: none !important;
                        opacity: 0.75;
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

                <form method="POST" action="{{ route('profile.update') }}" id="profileEditForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-user me-1"></i> အမည် <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="profile_name" class="form-control"
                            value="{{ old('name', $user->name) }}"
                            data-original="{{ $user->name }}"
                            required placeholder="အမည် ထည့်သွင်းပါ" data-validate="name" data-label="အမည်">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-envelope me-1"></i> အီးမေးလ် <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="profile_email" class="form-control"
                            value="{{ old('email', $user->email) }}"
                            data-original="{{ $user->email }}"
                            required placeholder="အီးမေးလ် ထည့်သွင်းပါ">
                    </div>

                    <div class="tb-form-footer">
                        <a href="{{ route('dashboard') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> နောက်သို့
                        </a>
                        <button type="submit" class="btn-save" id="profileSubmitBtn" disabled title="ပြောင်းလဲမှု မရှိသေးပါ">
                            <i class="fas fa-pen"></i> ပြင်ဆင်ရန်
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.getElementById('profile_name');
            const emailInput = document.getElementById('profile_email');
            const submitBtn = document.getElementById('profileSubmitBtn');
            const form = document.getElementById('profileEditForm');
            if (!nameInput || !emailInput || !submitBtn || !form) return;

            const originalName = (nameInput.dataset.original || '').trim();
            const originalEmail = (emailInput.dataset.original || '').trim();

            function syncSubmitState() {
                const changed =
                    nameInput.value.trim() !== originalName ||
                    emailInput.value.trim() !== originalEmail;
                submitBtn.disabled = !changed;
                submitBtn.title = changed ? 'ပြင်ဆင်ရန်' : 'ပြောင်းလဲမှု မရှိသေးပါ';
            }

            nameInput.addEventListener('input', syncSubmitState);
            emailInput.addEventListener('input', syncSubmitState);
            syncSubmitState();

            form.addEventListener('submit', function (e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
