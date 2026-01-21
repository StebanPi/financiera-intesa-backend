<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('dvendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dvendor/fontawesome/css/all.css')  }}">
    <style>
        * {
            font-family: 'Poppins', sans-serif !important;
        }
        body {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>


</head>
<body id="body">
    <nav class="navbar navbar-light bg-white" style="height:70px;width: 100%;margin-bottom:15px;">
        <a class="navbar-brand" href="#" style="display: inline-block;width: 55%;font-size:13px;text-decoration: none;">

                <div style="display: inline-block;width: 35%;margin-top:20px;" >

                    @php
                        $img = public_path('dimages/LogoIntesa.png');
                        $pic = '';
                        if (file_exists($img)) {
                            $type = pathinfo($img, PATHINFO_EXTENSION);
                            $data1 = file_get_contents($img);
                            $pic = 'data:image/'.$type. ';base64,'.base64_encode($data1);
                        } else {
                            // Fallback: usar asset si no se encuentra el archivo
                            $pic = asset('dimages/LogoIntesa.png');
                        }
                    @endphp 
                    @if($pic)
                        <img src="{{ $pic }}" width="120" height="80" class="d-inline-block align-top" alt="">
                    @endif

                </div>
                <div class="ml-4" style="color:#000000;font-size:12px;">
                    <b>{{ $institucion->name ?? 'INSTITUTO TECNICO DEL SABER' }}</b> <br>
                    Educación para el trabajo y el desarrollo humano
                </div>
        </a>
        <div class="ml-auto text-black" style="display: inline-block;width: 40%;text-align:right;font-size:12px;margin-bottom:22px;">
                @if(isset($institucion) && ($institucion->address || $institucion->phone || $institucion->website))
                    @if($institucion->address)
                        <b>{{ $institucion->address }}</b><br>
                    @endif
                    @if($institucion->phone)
                        Telefono: {{ $institucion->phone }}<br>
                    @endif
                    @if($institucion->website)
                        <b>{{ $institucion->website }}</b><br>
                    @endif
                @else
                    {{-- Valores por defecto si no hay configuración --}}
                <b>Sede Barrancabermeja, Barrio Galan. <br>
                    Calle 51 No.16-66</b>
                    <br>Telefono: 622 321 - 
                    Celular: 322 3647768
                    <br><b>www.institutointesa.edu.co </b><br>
                @endif
        </div>
      </nav>
      <div class="my-3">

      </div>
      <div class="container-fluid">
        @yield('content')
      </div>

      @if(!isset($hideDefaultFooter) || !$hideDefaultFooter)
      <div style="position: absolute; bottom: 80px; left: 0; right: 0; padding: 12px; background-color: #f8f9fa; font-family: 'Poppins', sans-serif; width: 100%; font-size: 10px;">
        <p style="margin: 0; font-weight: 600; color: #000; margin-bottom: 6px; font-size: 10px;">Nota:</p>
        <p style="margin: 0; font-size: 10px; line-height: 1.5; color: #333; text-align: justify;">
          El valor de la cuota no cambiará mientras cancele en las fechas estipuladas en el plan de Financiamiento. Las fechas del plan de financiamiento son independientes al calendario académico o de vacaciones. Los compromisos de pagos no alteran las fechas de pago ya establecidas. En el mes de Diciembre de cada año se realizará un cierre contable en el cual debe estar al día a la fecha del 30 de diciembre con las fechas ya vencidas, de lo contrario asumirá el costo del recargo por mora que tiene valor de $50.000 el cual se aplica el 01 de Enero del siguiente año en curso a la cuota del mes de diciembre que se encuentre en mora. El incumplimiento de los pagos en las fechas establecidas incurren en la suspensión académica del estudiante.
        </p>
      </div>
      @endif

      <footer style="text-align:center;position: absolute;bottom: 0;width:100%;font-size: 12px;">
        @if(isset($institucion))
          <div style="padding: 8px; margin-bottom: 4px;">
            {{ $institucion->footer_licencia_texto ?? 'Licencia de Funcionamiento según Resolución No. 3021 del 15 de diciembre de 2015' }}
          </div>
          @if(isset($institucion->footer_mostrar_ubicacion_fecha) && $institucion->footer_mostrar_ubicacion_fecha)
            <div style="font-weight: bold;">
              {{ $institucion->footer_ciudad ?? 'Barrancabermeja - Santander' }} {{ App\Http\Controllers\DateController::getMesSubtr(date('Y-m-d')) }}
            </div>
          @endif
        @else
          <div style="padding: 8px; margin-bottom: 4px;">
            Licencia de Funcionamiento según Resolución No. 3021 del 15 de diciembre de 2015
          </div>
          <div style="font-weight: bold;">
            Barrancabermeja - Santander {{ App\Http\Controllers\DateController::getMesSubtr(date('Y-m-d')) }}
          </div>
        @endif
      </footer>
      
      @yield('customFooter')

      <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.0.272/jspdf.debug.js"></script>
      <script src="{{ asset_versioned('js/pdfGenerator.js') }}"></script>
</body>
</html>

