<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('image/logo.jpg') }}">

    <title>DERAS - Resource Allocation and Distribution System</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/deras-tokens.css') }}?v=1" rel="stylesheet">

    <style>
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        div,
        label,
        input,
        button,
        select,
        table,
        a {
            font-family: 'Noto Sans Myanmar', 'Pyidaungsu', 'Myanmar Text', sans-serif !important;
        }

        /* Myanmar text in selects / filter inputs — avoid clipping */
        select,
        select.modern-select,
        select.form-control,
        input.modern-input,
        input.form-control,
        .form-control,
        .modern-select,
        .modern-input {
            line-height: 1.8 !important;
        }

        select.modern-select,
        select.form-control,
        select {
            min-height: 44px;
            padding-top: 0.55rem !important;
            padding-bottom: 0.55rem !important;
            height: auto !important;
        }

        input.modern-input,
        input.form-control:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]) {
            min-height: 44px;
            padding-top: 0.55rem !important;
            padding-bottom: 0.55rem !important;
            height: auto !important;
        }

        select option {
            line-height: 1.8;
            padding: 0.4rem 0.5rem;
        }

        .fa,
        .fas,
        .far,
        .fab,
        .fa-solid,
        .fa-regular,
        .fa-brands {
            font-family: "Font Awesome 5 Free", "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }

        /* Sidebar Modern Customizations */
        #accordionSidebar {
            position: sticky;
            top: 0;
            left: 0;
            width: 260px !important;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            background: linear-gradient(180deg, var(--deras-forest) 0%, var(--deras-pine) 45%, var(--deras-deep) 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
            flex-shrink: 0;
        }

        #accordionSidebar::-webkit-scrollbar {
            width: 5px;
        }

        #accordionSidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .sidebar-brand-container {
            padding: 20px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            background: linear-gradient(135deg, rgba(0,0,0,0.25) 0%, rgba(7,42,30,0.4) 100%);
        }

        .logo {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 50%;
            border: 2.5px solid var(--deras-gold);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35), 0 0 0 4px rgba(212,175,55,0.15);
            background: #fff;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .sidebar-brand-container:hover .logo {
            transform: scale(1.06);
        }

        .brand-title {
            font-size: 28px;
            font-weight: 900;
            color: var(--deras-amber);
            letter-spacing: 3px;
            line-height: 1;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6), 0 0 20px rgba(245,158,11,0.3);
        }

        /* Sidebar Navigation Items */
        .sidebar .nav-item {
            position: relative;
            margin: 3px 12px;
        }

        .sidebar .nav-item .nav-link {
            display: flex !important;
            align-items: center !important;
            padding: 11px 14px !important;
            border-radius: 12px !important;
            color: #e2e8f0 !important;
            transition: all 0.2s ease-in-out !important;
            text-decoration: none !important;
            width: 100% !important;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .sidebar .nav-item .nav-link i.main-icon {
            width: 24px;
            min-width: 24px;
            font-size: 16px;
            margin-right: 12px;
            color: var(--deras-mint);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .sidebar .nav-item .nav-link span {
            flex: 1;
            white-space: normal;
            word-break: break-word;
        }

        .sidebar .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.12) !important;
            color: var(--deras-amber-light) !important;
            transform: translateX(3px);
        }

        .sidebar .nav-item .nav-link:hover i.main-icon {
            color: var(--deras-amber-light);
            transform: scale(1.1);
        }

        /* Dropdown Chevron Indicator */
        .sidebar .nav-item .nav-link.collapsed .chevron-icon {
            transform: rotate(0deg);
        }

        .sidebar .nav-item .nav-link:not(.collapsed) .chevron-icon {
            transform: rotate(180deg);
            color: var(--deras-amber-light);
        }

        .chevron-icon {
            font-size: 12px;
            margin-left: 8px;
            transition: transform 0.3s ease;
            color: #94a3b8;
        }

        /* Sidebar Dropdown Submenus Absolute Fix */
        #accordionSidebar .nav-item .collapse {
            position: static !important;
            left: auto !important;
            right: auto !important;
            top: auto !important;
            width: 100% !important;
            margin: 0 !important;
            float: none !important;
            box-shadow: none !important;
            z-index: 10 !important;
        }

        #accordionSidebar .nav-item .collapse.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
        }

        #accordionSidebar .nav-item .collapse .collapse-inner {
            background: rgba(3, 20, 14, 0.85) !important;
            border: 1px solid rgba(52, 211, 153, 0.2) !important;
            border-radius: 12px !important;
            margin: 4px 0 8px 0 !important;
            padding: 6px !important;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.4) !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
            min-width: 100% !important;
        }

        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item {
            display: flex !important;
            align-items: center !important;
            padding: 9px 12px !important;
            color: #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            transition: all 0.2s ease-in-out !important;
            line-height: 1.4 !important;
            white-space: normal !important;
            word-break: break-word !important;
            background: transparent !important;
        }

        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item i {
            width: 22px !important;
            min-width: 22px !important;
            margin-right: 10px !important;
            font-size: 14px !important;
            color: var(--deras-mint) !important;
            text-align: center !important;
            flex-shrink: 0 !important;
        }

        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item:hover,
        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item.active,
        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item.active-item {
            background: rgba(16, 185, 129, 0.25) !important;
            color: var(--deras-amber-light) !important;
            padding-left: 16px !important;
        }

        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item:hover i,
        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item.active i,
        #accordionSidebar .nav-item .collapse .collapse-inner .collapse-item.active-item i {
            color: var(--deras-amber-light) !important;
        }

        /* Global Button Overrides: Edit (Blue) and Delete (Red) with Hover & Click Effect */
        .btn-warning, .btn-action-edit, .btn-modern-warning {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 34px !important;
            height: 34px !important;
            padding: 0 !important;
            font-size: 13px !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 2px 5px rgba(37, 99, 235, 0.2) !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }

        .btn-warning:hover, .btn-action-edit:hover, .btn-modern-warning:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.08) !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4) !important;
            text-decoration: none !important;
        }

        .btn-warning:active, .btn-action-edit:active, .btn-modern-warning:active {
            transform: translateY(0) scale(0.95) !important;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3) !important;
        }

        .btn-danger, .btn-action-delete, .btn-modern-danger {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 34px !important;
            height: 34px !important;
            padding: 0 !important;
            font-size: 13px !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 2px 5px rgba(220, 38, 38, 0.2) !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }

        .btn-danger:hover, .btn-action-delete:hover, .btn-modern-danger:hover {
            background-color: #b91c1c !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.08) !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
            text-decoration: none !important;
        }

        .btn-danger:active, .btn-action-delete:active, .btn-modern-danger:active {
            transform: translateY(0) scale(0.95) !important;
            box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3) !important;
        }

        /* Status Badge Overrides */
        .badge-active {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 5px 14px !important;
            border-radius: 9999px !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border: 1px solid #a7f3d0 !important;
            white-space: nowrap !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        .badge-inactive {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 5px 14px !important;
            border-radius: 9999px !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            background-color: #fff1f2 !important;
            color: #be123c !important;
            border: 1px solid #fecdd3 !important;
            white-space: nowrap !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        /* Form create/edit headers → match index (forest, no gold bar) */
        .card-header.bg-success {
            background-color: var(--deras-forest) !important;
            background-image: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
        }

        /* Default Tables & Modern Table Overrides */
        .default-table {
            border-collapse: collapse;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #cbd5e1 !important;
        }

        .default-table th {
            background: linear-gradient(180deg, var(--deras-pine) 0%, var(--deras-forest) 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            padding: 12px 14px !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        .default-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 10px 12px !important;
            vertical-align: middle !important;
            color: #334155;
            font-size: 13.5px;
        }

        .default-table tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* Project-wide Table TD & TH Border Enforcement */
        table th, .table th, .modern-table th, .default-table th, .quota-table th, .textbook-table th, .distribution-table th, .school-supplies-table th, .supply-detail-table th, .stock-table th, .teacher-guide-table th, .issue-table th, .summary-table th {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
        }

        table td, .table td, .modern-table td, .default-table td, .quota-table td, .textbook-table td, .distribution-table td, .school-supplies-table td, .supply-detail-table td, .stock-table td, .teacher-guide-table td, .issue-table td, .summary-table td {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle !important;
        }

        thead th, .table thead th, .modern-table thead th, .default-table thead th, .quota-table thead th, .textbook-table thead th, .distribution-table thead th {
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        /* Dropdown Item & User Dropdown Light Green Focus/Active Override */
        .dropdown-item:active,
        .dropdown-item:focus,
        .dropdown-item.active,
        .dropdown-item:hover {
            background-color: #ecfdf5 !important;
            color: #065f46 !important;
            outline: none !important;
        }

        .topbar .nav-item.dropdown .nav-link:focus,
        .topbar .nav-item.dropdown .nav-link:active,
        .topbar .nav-item.dropdown.show .nav-link {
            background-color: #f0fdf4 !important;
            border-radius: 8px !important;
        }

        .topbar-page-title {
            max-width: calc(100% - 200px);
        }

        .topbar-breadcrumb-list {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
            list-style: none;
            min-width: 0;
        }

        .topbar-breadcrumb-list > li {
            display: inline-flex;
            align-items: center;
            min-width: 0;
        }

        .topbar-crumb-sep {
            color: #94a3b8;
            font-size: 0.65rem;
            flex-shrink: 0;
            line-height: 1;
        }

        .topbar-crumb-muted {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .topbar-crumb-current {
            color: #334155;
            font-size: 0.95rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* SweetAlert delete confirm */
        .deras-swal-popup {
            border-radius: 18px !important;
            padding: 1.5rem 1.25rem 1.25rem !important;
            font-family: 'Noto Sans Myanmar', 'Pyidaungsu', 'Myanmar Text', sans-serif !important;
        }

        .deras-swal-title {
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        .deras-swal-confirm {
            border-radius: 12px !important;
            font-weight: 700 !important;
            padding: 0.65rem 1.15rem !important;
        }

        .deras-swal-cancel {
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 0.65rem 1.15rem !important;
        }
    </style>

    <link href="{{ asset('admin_folder/css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('css/position_fixed.css') }}?v=18" rel="stylesheet">
    <link href="{{ asset('css/deras-validation.css') }}?v=1" rel="stylesheet">
    <link href="{{ asset('css/deras-pagination.css') }}?v=1" rel="stylesheet">

    {{-- FIXED LAYOUT: sidebar & topbar always stay in place regardless of scroll --}}
    <style>
        /* Base */
        html, body {
            overflow-x: hidden;
            overflow-y: auto;
            margin: 0;
            padding: 0;
            height: 100%;
        }

        /* Wrapper: just a full-page container */
        #wrapper {
            display: block !important;
            position: relative !important;
        }

        /* Sidebar: fixed to left, always visible */
        #accordionSidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 260px !important;
            min-width: 260px !important;
            height: 100vh !important;
            overflow-y: auto !important;
            z-index: 1050 !important;
            flex-shrink: 0 !important;
        }

        /* Content wrapper: push right of sidebar */
        #wrapper #content-wrapper {
            margin-left: 260px !important;
            overflow-x: auto !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* Topbar: fixed to top of content area (right of sidebar) */
        nav.topbar {
            position: sticky !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1040 !important;
        }

        /* Tables scroll inside container, don't break layout */
        .table-responsive,
        .modern-table-container {
            overflow-x: auto !important;
        }
    </style>
</head>

<body id="page-top" class="bg-slate-50 text-slate-800 antialiased">
    <div id="wrapper">
        <!-- Sidebar Navigation -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand-container" href="/">
                <img src="{{ asset('image/logo.jpg') }}" class="logo" alt="DERAS Logo">
                <span class="brand-title">DERAS</span>
            </a>

            <div class="my-2 px-3">
                <hr style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin: 0;">
            </div>

            <!-- 1. Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('/') ? 'bg-white/10 text-amber-300 font-semibold' : '' }}" href="/">
                    <i class="fas fa-chart-line main-icon"></i>
                    <span>ဒက်ရှ်ဘုတ်</span>
                </a>
            </li>

            <!-- 2. Textbook Dropdown -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                    data-target="#collapseTextbook" aria-expanded="false" aria-controls="collapseTextbook">
                    <i class="fas fa-book-reader main-icon"></i>
                    <span>ပြဌာန်းစာအုပ်</span>
                </a>

                <div id="collapseTextbook" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ request()->is('allocation-plans*') ? 'active-item' : '' }}" href="/allocation-plans">
                            <span>ခွဲတမ်းတွက်ချက်မှု</span>
                        </a>

                        <a class="collapse-item {{ request()->is('textbook*') ? 'active-item' : '' }}" href="/textbook">
                            <span>ပုံမှန်ဖြန့်ဝေစာရင်း</span>
                        </a>

                        <a class="collapse-item {{ request()->is('stocks*') ? 'active-item' : '' }}" href="/stocks">
                            <span>ထပ်ဆောင်းဖြန့်ဝေစာရင်း</span>
                        </a>
                    </div>
                </div>
            </li>

            <!-- 3. Student Quota Calculation -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('quota*') ? 'bg-white/10 text-amber-300 font-semibold' : '' }}" href="/quota">
                    <i class="fas fa-calculator main-icon"></i>
                    <span>ကျောင်းသားဦးရေတွက်ချက်မှု</span>
                </a>
            </li>

            <!-- 4. School Supplies Dropdown -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                    data-target="#collapseSchoolSupplies" aria-expanded="false" aria-controls="collapseSchoolSupplies">
                    <i class="fas fa-boxes main-icon"></i>
                    <span>သင်ထောက်ကူပစ္စည်းများ</span>
                </a>

                <div id="collapseSchoolSupplies" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ request()->is('school-supplies*') ? 'active-item' : '' }}" href="/school-supplies">
                            <span>ခွဲတမ်းတွက်ချက်မှု</span>
                        </a>

                        <a class="collapse-item {{ request()->is('supply-details*') ? 'active-item' : '' }}" href="/supply-details">
                            <span>ထုတ်ပေးမှု</span>
                        </a>
                    </div>
                </div>
            </li>

            <!-- 5. Teacher Guide Dropdown -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                    data-target="#collapseTeacherGuide" aria-expanded="false" aria-controls="collapseTeacherGuide">
                    <i class="fas fa-chalkboard-teacher main-icon"></i>
                    <span>ဆရာကိုင်နှင့်လမ်းညွှန်</span>
                </a>

                <div id="collapseTeacherGuide" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ request()->is('teacher-guides*') ? 'active-item' : '' }}" href="/teacher-guides">
                            <span>လက်ခံရရှိမှု</span>
                        </a>

                        <a class="collapse-item {{ request()->is('teacher-guide-distributions*') ? 'active-item' : '' }}" href="/teacher-guide-distributions">
                            <span>ဖြန့်ဝေရန်ခွဲတမ်း</span>
                        </a>

                        <a class="collapse-item {{ request()->is('teacher-guide-issues*') ? 'active-item' : '' }}" href="/teacher-guide-issues">
                            <span>ဖြန့်ဝေစာရင်း</span>
                        </a>

                        <a class="collapse-item {{ request()->is('teacher-guide-summaries*') ? 'active-item' : '' }}" href="/teacher-guide-summaries">
                            <span>စာရင်းချုပ်</span>
                        </a>
                    </div>
                </div>
            </li>

            <!-- 6. Master Data Dropdown -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                    data-target="#collapseMaster" aria-expanded="false" aria-controls="collapseMaster">
                    <i class="fas fa-database main-icon"></i>
                    <span>အခြေခံအချက်အလက်များ</span>
                </a>

                <div id="collapseMaster" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ request()->is('townships*') ? 'active-item' : '' }}" href="/townships">
                            <span>မြို့နယ်များ</span>
                        </a>

                        <a class="collapse-item {{ request()->is('academic-years*') ? 'active-item' : '' }}" href="/academic-years">
                            <span>ပညာသင်နှစ်များ</span>
                        </a>

                        <a class="collapse-item {{ request()->is('grades*') && !request()->is('grade-subjects*') ? 'active-item' : '' }}" href="/grades">
                            <span>အတန်းများ</span>
                        </a>

                        <a class="collapse-item {{ request()->is('book-names*') ? 'active-item' : '' }}" href="/book-names">
                            <span>ဘာသာရပ်များ</span>
                        </a>

                        <a class="collapse-item {{ request()->is('grade-subjects*') ? 'active-item' : '' }}" href="/grade-subjects">
                            <span>အတန်း–ဘာသာရပ်</span>
                        </a>

                        <a class="collapse-item {{ request()->is('company-contacts*') ? 'active-item' : '' }}" href="/company-contacts">
                            <span>ကုမ္ပဏီများ</span>
                        </a>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Main Content Area -->
        <div id="content-wrapper" class="d-flex flex-column content-fixed bg-slate-50 min-h-screen">
            <div id="content">
                <!-- Modern Topbar Navbar -->
                <nav class="mb-4 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-200/80 navbar navbar-expand navbar-light topbar topbar-fixed px-4">
                    @php
                        $path = request()->path();
                        $pageParent = null;
                        $pageTitle = 'DERAS';
                        $pageIcon = 'fa-file-alt';

                        if (request()->is('/')) {
                            $pageTitle = 'ဒက်ရှ်ဘုတ်';
                            $pageIcon = 'fa-chart-line';
                        } elseif (request()->is('allocation-plans*')) {
                            $pageParent = 'ပြဌာန်းစာအုပ်';
                            $pageTitle = 'ခွဲတမ်းတွက်ချက်မှု';
                            $pageIcon = 'fa-calculator';
                        } elseif (request()->is('textbook*')) {
                            $pageParent = 'ပြဌာန်းစာအုပ်';
                            $pageTitle = 'ပုံမှန်ဖြန့်ဝေစာရင်း';
                            $pageIcon = 'fa-book';
                        } elseif (request()->is('stocks*')) {
                            $pageParent = 'ပြဌာန်းစာအုပ်';
                            $pageTitle = 'ထပ်ဆောင်းဖြန့်ဝေစာရင်း';
                            $pageIcon = 'fa-truck';
                        } elseif (request()->is('quota*')) {
                            $pageTitle = 'ကျောင်းသားဦးရေတွက်ချက်မှု';
                            $pageIcon = 'fa-calculator';
                        } elseif (request()->is('school-supplies*')) {
                            $pageParent = 'သင်ထောက်ကူပစ္စည်းများ';
                            $pageTitle = 'ခွဲတမ်းတွက်ချက်မှု';
                            $pageIcon = 'fa-boxes';
                        } elseif (request()->is('supply-details*')) {
                            $pageParent = 'သင်ထောက်ကူပစ္စည်းများ';
                            $pageTitle = 'ထုတ်ပေးမှု';
                            $pageIcon = 'fa-hand-holding';
                        } elseif (request()->is('teacher-guides*')) {
                            $pageParent = 'ဆရာကိုင်နှင့်လမ်းညွှန်';
                            $pageTitle = 'လက်ခံရရှိမှု';
                            $pageIcon = 'fa-inbox';
                        } elseif (request()->is('teacher-guide-distributions*')) {
                            $pageParent = 'ဆရာကိုင်နှင့်လမ်းညွှန်';
                            $pageTitle = 'ဖြန့်ဝေရန်ခွဲတမ်း';
                            $pageIcon = 'fa-share-alt';
                        } elseif (request()->is('teacher-guide-issues*')) {
                            $pageParent = 'ဆရာကိုင်နှင့်လမ်းညွှန်';
                            $pageTitle = 'ဖြန့်ဝေစာရင်း';
                            $pageIcon = 'fa-list';
                        } elseif (request()->is('teacher-guide-summaries*')) {
                            $pageParent = 'ဆရာကိုင်နှင့်လမ်းညွှန်';
                            $pageTitle = 'စာရင်းချုပ်';
                            $pageIcon = 'fa-clipboard-list';
                        } elseif (request()->is('townships*')) {
                            $pageParent = 'အခြေခံအချက်အလက်များ';
                            $pageTitle = 'မြို့နယ်များ';
                            $pageIcon = 'fa-map-marker-alt';
                        } elseif (request()->is('academic-years*')) {
                            $pageParent = 'အခြေခံအချက်အလက်များ';
                            $pageTitle = 'ပညာသင်နှစ်များ';
                            $pageIcon = 'fa-calendar-alt';
                        } elseif (request()->is('grade-subjects*')) {
                            $pageParent = 'အခြေခံအချက်အလက်များ';
                            $pageTitle = 'အတန်း–ဘာသာရပ်';
                            $pageIcon = 'fa-link';
                        } elseif (request()->is('grades*')) {
                            $pageParent = 'အခြေခံအချက်အလက်များ';
                            $pageTitle = 'အတန်းများ';
                            $pageIcon = 'fa-layer-group';
                        } elseif (request()->is('book-names*')) {
                            $pageParent = 'အခြေခံအချက်အလက်များ';
                            $pageTitle = 'ဘာသာရပ်များ';
                            $pageIcon = 'fa-book-open';
                        } elseif (request()->is('company-contacts*')) {
                            $pageParent = 'အခြေခံအချက်အလက်များ';
                            $pageTitle = 'ကုမ္ပဏီများ';
                            $pageIcon = 'fa-building';
                        } elseif (request()->is('admin-users*')) {
                            $pageTitle = 'စီမံခန့်ခွဲသူများစာရင်း';
                            $pageIcon = 'fa-users';
                        } elseif (request()->is('profile*')) {
                            $pageTitle = 'ကိုယ်ရေးအချက်အလက်';
                            $pageIcon = 'fa-user';
                        } elseif (request()->is('password*')) {
                            $pageTitle = 'စကားဝှက်ပြောင်းရန်';
                            $pageIcon = 'fa-lock';
                        }

                        $pageAction = null;
                        if (str_ends_with($path, '/create') || $path === 'create') {
                            $pageAction = 'ဖန်တီးရန်';
                        } elseif (preg_match('#/(?:\d+|[^/]+)/edit$#', $path)) {
                            $pageAction = 'ပြင်ဆင်ရန်';
                        }
                    @endphp

                    <div class="d-flex align-items-center flex-grow-1 min-w-0 me-3 topbar-page-title">
                        <nav class="topbar-breadcrumb text-truncate" aria-label="လက်ရှိစာမျက်နှာ">
                            <ol class="topbar-breadcrumb-list">
                                @if ($pageParent)
                                    <li>
                                        <span class="topbar-crumb-muted">{{ $pageParent }}</span>
                                    </li>
                                    <li class="topbar-crumb-sep" aria-hidden="true">
                                        <i class="fas fa-chevron-right"></i>
                                    </li>
                                @endif
                                <li>
                                    <span class="topbar-crumb-current">
                                        {{ $pageTitle }}@if ($pageAction) · {{ $pageAction }}@endif
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <ul class="ml-auto navbar-nav align-items-center flex-shrink-0">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#"
                                id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <span class="mr-2 text-emerald-800 font-semibold d-none d-lg-inline text-sm">
                                    {{ auth()->user()->name }}
                                </span>
                                <div class="w-9 h-9 rounded-full bg-emerald-100 border border-emerald-300 flex items-center justify-center text-emerald-700 font-bold text-sm shadow-sm">
                                    {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                            </a>

                            <div class="shadow-lg rounded-xl border border-slate-200 dropdown-menu dropdown-menu-right animated--grow-in p-2"
                                aria-labelledby="userDropdown" style="min-width: 220px;">

                                <a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors" href="{{ route('profile.edit') }}">
                                    <i class="mr-2.5 fas fa-user text-emerald-600"></i>
                                    ကိုယ်ရေးအချက်အလက်
                                </a>

                                @if (auth()->user()?->canManageUsers())
                                    <a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors" href="{{ route('admin-users.index') }}">
                                        <i class="mr-2.5 fas fa-users text-emerald-600"></i>
                                        စီမံခန့်ခွဲသူများစာရင်း
                                    </a>
                                @endif

                                <a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors" href="{{ route('password.edit') }}">
                                    <i class="mr-2.5 fas fa-lock text-emerald-600"></i>
                                    စကားဝှက်ပြောင်းရန်
                                </a>

                                <div class="dropdown-divider my-2 border-slate-100"></div>

                                <form action="{{ route('logout') }}" method="post" class="px-1">
                                    @csrf
                                    <button type="submit" class="btn btn-modern-danger w-100 justify-content-center py-2">
                                        <i class="fas fa-sign-out-alt"></i>
                                        အကောင့်မှ ထွက်မည်
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Page Content -->
                <main class="px-3 px-md-4 py-2">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('admin_folder/vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('admin_folder/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin_folder/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('admin_folder/js/sb-admin-2.min.js') }}"></script>

    <script src="{{ asset('js/deras-form.js') }}?v=4"></script>
    <script src="{{ asset('js/deras-validation.js') }}?v=18"></script>
    <script src="{{ asset('js/deras-pagination.js') }}?v=1"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: @json(session('success'))
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: @json(session('error'))
            });
        @endif

        @if (session('warning'))
            Toast.fire({
                icon: 'warning',
                title: @json(session('warning'))
            });
        @endif

        @if (session('info'))
            Toast.fire({
                icon: 'info',
                title: @json(session('info'))
            });
        @endif

        /**
         * Pretty SweetAlert confirm for all DELETE forms.
         */
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            var methodInput = form.querySelector('input[name="_method"]');
            var isDelete = methodInput && String(methodInput.value).toUpperCase() === 'DELETE';
            if (!isDelete || form.dataset.derasConfirmed === '1') return;

            event.preventDefault();
            event.stopPropagation();

            Swal.fire({
                title: 'ဖျက်ရန် သေချာပါသလား?',
                html: '<p style="margin:0;color:#64748b;font-size:0.95rem;line-height:1.6;">ဤအချက်အလက်ကို ဖျက်လိုက်ပါက<br><strong style="color:#dc2626;">ပြန်လည်ရယူ၍ မရနိုင်ပါ။</strong></p>',
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                reverseButtons: true,
                confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> ဖျက်မည်',
                cancelButtonText: 'မလုပ်တော့ပါ',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                buttonsStyling: true,
                customClass: {
                    popup: 'deras-swal-popup',
                    title: 'deras-swal-title',
                    confirmButton: 'deras-swal-confirm',
                    cancelButton: 'deras-swal-cancel'
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.derasConfirmed = '1';
                    form.submit();
                }
            });
        }, true);

        // Restore accordion state
        $(function() {
            const lastMenu = localStorage.getItem('sidebarMenu');
            if (lastMenu) {
                $(lastMenu).addClass('show');
                const triggerLink = $('[data-target="' + lastMenu + '"]');
                triggerLink.removeClass('collapsed').attr('aria-expanded', 'true');
            }

            $('#collapseMaster, #collapseSchoolSupplies, #collapseTextbook, #collapseTeacherGuide')
                .on('shown.bs.collapse', function() {
                    localStorage.setItem('sidebarMenu', '#' + this.id);
                })
                .on('hidden.bs.collapse', function() {
                    if (localStorage.getItem('sidebarMenu') === '#' + this.id) {
                        localStorage.removeItem('sidebarMenu');
                    }
                });
        });
    </script>
    <script>
        /**
         * Ctrl+- zoom out → allow full-width layout (no max-w-7xl cap).
         * 100% zoom → keep original centered max-w-7xl layout.
         */
        (function () {
            function isBrowserZoomedOut() {
                var screenW = window.screen && window.screen.availWidth
                    ? window.screen.availWidth
                    : window.screen.width;
                if (!screenW) return false;
                return (window.innerWidth / screenW) > 1.08;
            }

            function syncZoomLayout() {
                document.documentElement.classList.toggle(
                    'deras-zoomed-out',
                    isBrowserZoomedOut()
                );
            }

            var timer = null;
            function scheduleSync() {
                clearTimeout(timer);
                timer = setTimeout(syncZoomLayout, 50);
            }

            window.addEventListener('resize', scheduleSync);
            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', scheduleSync);
            }
            document.addEventListener('DOMContentLoaded', syncZoomLayout);
            window.addEventListener('load', syncZoomLayout);
            syncZoomLayout();
        })();
    </script>
    @yield('script-code')
</body>

</html>
