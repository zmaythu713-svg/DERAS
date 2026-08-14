@extends('authentication.layouts.master')

@section('content')
    <style>
        /* DERAS system palette via CSS variables (see deras-tokens.css) */
        body#page-top {
            margin: 0;
            min-height: 100vh;
            background: #fff;
        }

        .login-wrap {
            min-height: 100vh;
            display: flex;
        }

        .login-visual {
            width: 48%;
            background: var(--deras-forest);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }

        .login-visual-inner {
            text-align: center;
            max-width: 400px;
        }

        .login-visual img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 2.5px solid var(--deras-gold);
            background: #fff;
            margin-bottom: 20px;
        }

        .login-visual h1 {
            margin: 0 0 12px;
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 4px;
            color: var(--deras-amber);
            line-height: 1.1;
        }

        .login-visual .system-name {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            font-weight: 600;
            color: #e2e8f0;
        }

        .login-visual .mm-note {
            margin: 18px 0 0;
            padding-top: 16px;
            border-top: 1px solid rgba(212, 175, 55, 0.4);
            font-size: 14px;
            line-height: 1.7;
            color: #cbd5e1;
        }

        .login-form-side {
            width: 52%;
            display: flex;
            flex-direction: column;
            background: var(--deras-paper);
            padding: 28px 40px 40px;
        }

        .login-topbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: auto;
            padding-bottom: 20px;
        }

        .login-topbar img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--deras-gold);
            background: #fff;
        }

        .login-topbar span {
            font-size: 13px;
            font-weight: 700;
            color: var(--deras-leaf);
            line-height: 1.35;
        }

        .login-form-center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid #dce8e1;
            border-radius: 12px;
            padding: 36px 34px;
            box-shadow: 0 2px 12px rgba(7, 42, 30, 0.06);
        }

        .login-box h2 {
            margin: 0 0 8px;
            font-size: 26px;
            font-weight: 800;
            color: var(--deras-leaf);
        }

        .login-box .sub {
            margin: 0 0 28px;
            font-size: 15px;
            color: #64748b;
            line-height: 1.5;
        }

        .login-field {
            margin-bottom: 18px;
        }

        .login-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        .login-field .form-control {
            border: 1px solid #c5d5cc;
            border-radius: 8px;
            background: #fff;
            min-height: 48px;
            padding: 11px 14px;
            font-size: 15px;
            line-height: 1.6;
            box-shadow: none;
        }

        .login-field .form-control:focus {
            border-color: var(--deras-leaf);
            box-shadow: 0 0 0 0.15rem rgba(16, 92, 58, 0.15);
        }

        .pass-wrap {
            position: relative;
        }

        .pass-wrap .form-control {
            padding-right: 42px;
        }

        .pass-wrap i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            z-index: 5;
        }

        .pass-wrap i:hover {
            color: var(--deras-leaf);
        }

        .btn-login {
            width: 100%;
            margin-top: 8px;
            border: none;
            border-radius: 8px;
            background: var(--deras-leaf);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            padding: 13px 16px;
        }

        .btn-login:hover {
            background: #0d4a2f;
            color: #fff;
        }

        .login-link-row {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .login-link-row a {
            color: var(--deras-leaf);
            font-weight: 700;
            text-decoration: none !important;
        }

        .login-link-row a:hover,
        .login-link-row a:focus,
        .login-link-row a:active,
        .login-link-row a:visited {
            color: #0d4a2f;
            text-decoration: none !important;
        }

        @media (max-width: 900px) {
            .login-wrap {
                flex-direction: column;
            }

            .login-visual,
            .login-form-side {
                width: 100%;
            }

            .login-visual {
                padding: 28px 20px;
            }

            .login-visual img {
                width: 84px;
                height: 84px;
            }

            .login-visual h1 {
                font-size: 30px;
            }

            .login-form-side {
                padding: 16px 16px 32px;
            }
        }
    </style>

    <div class="login-wrap">
        <div class="login-visual">
            <div class="login-visual-inner">
                <img src="{{ asset('image/logo.jpg') }}" alt="DERAS">
                <h1>DERAS</h1>
                <p class="system-name">Resource Allocation and Distribution System</p>
                <p class="mm-note">ပညာရေးအရင်းအမြစ်များ ခွဲတမ်းချထားခြင်းနှင့် ဖြန့်ဝေမှု စီမံခန့်ခွဲရေးစနစ်</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-topbar">
                <img src="{{ asset('image/logo.jpg') }}" alt="DERAS">
                <span>DERAS — Resource Allocation and Distribution System</span>
            </div>

            <div class="login-form-center">
                <div class="login-box">
                    <h2>အကောင့်ဝင်ရန်</h2>
                    <p class="sub">အီးမေးလ်နှင့် စကားဝှက် ထည့်သွင်းပြီး စနစ်သို့ ဝင်ရောက်ပါ</p>

                    <form method="POST" action="{{ url('/login') }}">
                        @csrf

                        <div class="login-field">
                            <label for="email">အီးမေးလ်</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="example@email.com"
                                value="{{ old('email') }}"
                                required
                                autocomplete="username"
                            >
                            @error('email')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="login-field">
                            <label for="login_password">စကားဝှက်</label>
                            <div class="pass-wrap">
                                <input
                                    type="password"
                                    id="login_password"
                                    name="password"
                                    class="form-control"
                                    placeholder="••••••••"
                                    readonly
                                    required
                                    minlength="6"
                                    autocomplete="current-password"
                                    onfocus="setTimeout(()=>{this.removeAttribute('readonly');},100);"
                                >
                                <i class="fas fa-eye-slash" id="login_icon"
                                    onclick="togglePass('login_password', 'login_icon')"></i>
                            </div>
                            @error('password')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-login">ဝင်ရောက်မည်</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>
@endsection
