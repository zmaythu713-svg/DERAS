<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>DERAS — Resource Allocation and Distribution System</title>

    <!-- Custom fonts for this template-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('admin_folder/css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('css/deras-tokens.css') }}?v=1" rel="stylesheet">
    <link href="{{ asset('css/deras-validation.css') }}?v=1" rel="stylesheet">

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
        button {
            font-family: 'Noto Sans Myanmar', 'Pyidaungsu', 'Myanmar Text', sans-serif !important;
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
    </style>

</head>

<body id="page-top">

    @yield('content')

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('admin_folder/vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('admin_folder/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('admin_folder/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('admin_folder/js/sb-admin-2.min.js') }}"></script>


    <script src="{{ asset('admin_folder/vendor/chart.js/Chart.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script src="{{ asset('admin_folder/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('admin_folder/js/demo/chart-pie-demo.js') }}"></script>
    <script src="{{ asset('js/deras-validation.js') }}?v=18"></script>

    @yield('scripts')


</body>

</html>
