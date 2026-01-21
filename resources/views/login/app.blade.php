<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cartera</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dimages/LogoIntesa.png') }}">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('dvendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset_versioned('css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dvendor/fontawesome/css/all.css')  }}">
    <style>
        /* Aplicar Poppins globalmente, excepto para iconos */
        * {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
        body, html {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
        /* Preservar Font Awesome */
        .fas, .far, .fab, .fa, [class*="fa-"], [class^="fa-"] {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        }
        .fas, .fa-solid {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }
        .far, .fa-regular {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 400 !important;
        }
        .fab, .fa-brands {
            font-family: "Font Awesome 6 Brands" !important;
            font-weight: 400 !important;
        }
        .number-lg{
            font-size: 14px !important;
        }
    </style>
</head>

<body class="h-100">
    <div class="content-preloader2 show2loader">
        <div class="preloader2"></div>
    </div>
    @yield('content')

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="{{ asset_versioned('dvendor/global/global.min.js') }}"></script>
	<script src="{{ asset_versioned('dvendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset_versioned('js/custom.min.js') }}"></script>
    <script src="{{ asset_versioned('js/deznav-init.js') }}"></script>

    <script src="{{ asset_versioned('js/miles.js') }}"></script>
    <script src="{{ asset_versioned('js/settingLog.js') }}"></script>
</body>

</html>