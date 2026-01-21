<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>INTESA -@yield('page')</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dimages/LogoIntesa.png') }}">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('dvendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{  asset('dvendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('dvendor/chartist/css/chartist.min.css') }}">
    <link href="{{ asset('dvendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
	<link href="{{ asset('css/LineIcons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/my.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('dvendor/fontawesome/css/all.css')  }}">
    <script async src="{{ asset_versioned('js/googlemanager.js')  }}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-V469GS1LH2');
    </script>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    </style>
    @stack('styles')
</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div class="content-preloader2 show2loader">
        <div class="preloader2"></div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        @include('dash.nav-header')
        <!--**********************************
            Nav header end
        ***********************************-->
		
		<!--**********************************
            Chat box start
        ***********************************-->
		@include('dash.chat-box')
		<!--**********************************
            Chat box End
        ***********************************-->
		
		<!--**********************************
            Header start
        ***********************************-->
        @include('dash.header-start')
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        @include('dash.sidebar-start')
        <!--**********************************
            Sidebar end
        ***********************************-->
		
		<!--**********************************
            Content body start
        ***********************************-->
		<div class="content-body">
			<!-- row -->
			<div class="container-fluid">
        		@yield('content')
			</div>
		</div>
        <!--**********************************
            Content body end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->
    <script src="{{ asset_versioned('js/plugin-ticket-js/Impresora.js') }}"></script>

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="{{ asset_versioned('dvendor/global/global.min.js') }}"></script>
	<script src="{{ asset_versioned('dvendor/bootstrap-select/dist/js/bootstrap-select.min.js') }} "></script>
	<script src="{{ asset_versioned('dvendor/chart.js/Chart.bundle.min.js') }}"></script>
    <script src="{{ asset_versioned('js/custom.min.js') }}"></script>
	<script src="{{ asset_versioned('js/deznav-init.js') }}"></script>
	
	<!-- Counter Up -->
    <script src="{{ asset_versioned('dvendor/waypoints/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset_versioned('dvendor/jquery.counterup/jquery.counterup.min.js') }}"></script>	
	
	<!-- Apex Chart -->
	<script src="{{ asset_versioned('dvendor/apexchart/apexchart.js') }}"></script>	
	
	<!-- Chart piety plugin files -->
	<script src="{{ asset_versioned('dvendor/peity/jquery.peity.min.js') }}"></script>
	


	<!-- Dashboard 1 -->
	<script src="{{ asset_versioned('js/dashboard/dashboard-1.js') }}"></script>
	

      <!-- DataTables -->
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/JSZip-2.5.0/jszip.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/pdfmake-0.1.36/pdfmake.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/pdfmake-0.1.36/vfs_fonts.js') }}"></script>
    <script src="{{ asset_versioned('dvendor/datatables2/DataTables-1.12.1/js/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/Buttons-2.2.3/js/dataTables.buttons.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/Buttons-2.2.3/js/buttons.bootstrap4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/Buttons-2.2.3/js/buttons.html5.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset_versioned('dvendor/datatables2/Buttons-2.2.3/js/buttons.print.min.js') }}"></script>
    <script>
    // Configuración global de DataTables en español - Cargar antes de datatables.init.js
    window.DataTablesSpanish = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": ">",
            "sPrevious": "<"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };

    // Configuración por defecto para todas las tablas DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            "language": window.DataTablesSpanish
        });
    }

    // Función para aplicar diseño moderno a todos los DataTables después de inicializarse
    function applyModernPaginationDesign() {
        // Cambiar textos de botones Previous/Next a símbolos
        $('.dataTables_paginate .paginate_button.previous').each(function() {
            var text = $(this).text().trim();
            if (text === 'Anterior' || text === 'Previous' || text.includes('Anterior') || text.includes('Previous')) {
                $(this).text('<');
            }
        });
        $('.dataTables_paginate .paginate_button.next').each(function() {
            var text = $(this).text().trim();
            if (text === 'Siguiente' || text === 'Next' || text.includes('Siguiente') || text.includes('Next')) {
                $(this).text('>');
            }
        });
    }

    // Aplicar después de que se cargue la página
    $(document).ready(function() {
        setTimeout(applyModernPaginationDesign, 500);
        // Aplicar cada vez que DataTables redibuje
        setInterval(applyModernPaginationDesign, 1000);
    });

    // Aplicar cuando DataTables se inicialice o redibuje
    $(document).on('init.dt draw.dt', function() {
        setTimeout(applyModernPaginationDesign, 100);
    });
    </script>
    <script src="{{ asset_versioned('js/plugins-init/datatables.init.js') }}"></script>
    <script src="{{ asset_versioned('js/Impresiones.js') }}"></script>
    <script src="{{ asset_versioned('js/miles.js') }}"></script>
    <script src="{{ asset_versioned('js/cartera.js') }}"></script>
    <script src="{{ asset_versioned('js/setting.js') }}"></script>
    @php
        $canDeleteReceipts = auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'));
    @endphp
    <script>
        // Variable global para verificar permisos de eliminación de recibos
        window.canDeleteReceipts = @json($canDeleteReceipts);
    </script>
    <script src="{{ asset_versioned('js/abonos.js') }}"></script>
    <script src="{{ asset_versioned('js/otrosAbonos.js') }}"></script>
    <script src="{{ asset_versioned('js/onload.js') }}"></script>
    <script src="{{ asset_versioned('js/thirdEntry.js') }}"></script>
    <script src="{{ asset('js/confirm-modal.js') }}"></script>

    @livewireScripts
    
    @stack('scripts')

    <!-- Sistema Global de Modales para Mensajes Flash -->
    @if (session('success'))
        <x-alert-modal type="success" :message="session('success')" title="Éxito" id="success" />
    @endif
    
    @if (session('error'))
        <x-alert-modal type="error" :message="session('error')" title="Error" id="error" />
    @endif
    
    @if (session('warning'))
        <x-alert-modal type="warning" :message="session('warning')" title="Advertencia" id="warning" />
    @endif
    
    @if (session('info'))
        <x-alert-modal type="info" :message="session('info')" title="Información" id="info" />
    @endif

    <!-- Modal global para selección de tamaño de papel en impresión -->
    @include('components.print-modal')

</body>
</html>