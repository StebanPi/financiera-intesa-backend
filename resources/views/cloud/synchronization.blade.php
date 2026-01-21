<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sincronización</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cloud.css') }}">
    <link rel="stylesheet" href="{{ asset('dvendor/fontawesome/css/all.css')  }}">
</head>
<body>
    <div class="content-preloader2 show2loader">
        <div class="preloader2"></div>
    </div>

    <div class="content">
        <div class="content-child">
            <h2 class="text-white">Sincronización</h2>
            <p class="text-white">Sistema de Subida y Bajada de Datos</p>
            <img src="{{ asset('dimages/internet.png') }}" width="30%" height="30%" alt="">
            <br>
            <a href="{{ route('synchronization.local') }}" class="btn btn-secondary btn-xs ejecutarmodal"><i class="fa-solid fa-arrow-up-from-bracket mr-2"></i>Transfer Local - Cloud</a>
            <a href="{{ route('synchronization.cloud') }}" class="btn btn-primary btn-xs text-white"><i class="fa-solid fa-cloud-arrow-down mr-2"></i>Transfer Cloud - Local</a>
        </div>
    </div>
    <script src="{{ asset_versioned('dvendor/datatables2/DataTables-1.12.1/js/jquery.dataTables.min.js') }}"></script>
    <script> 
        $('.ejecutarmodal').click(function(){
            $('.content-preloader2').removeClass(' show2loader');
        });
    </script>
</body>
</html>