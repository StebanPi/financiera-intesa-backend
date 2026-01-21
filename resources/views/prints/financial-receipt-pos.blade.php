@php
    $institution = institution_settings();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo POS {{ $paperWidth }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --paper-width: {{ $paperWidth }};
            --offset-left-mm: {{ $offsetLeft }};
        }
        
        @page {
            size: {{ $paperWidth }} auto;
            margin: 0 !important;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: var(--paper-width);
            max-width: var(--paper-width);
            font-family: 'Poppins', sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
            overflow-x: hidden;
            transform: none !important;
            zoom: 1 !important;
        }
        
        .receipt-container {
            width: 100%;
            padding-left: var(--offset-left-mm);
            padding-right: 2mm;
            padding-top: 3mm;
            padding-bottom: 3mm;
            word-wrap: break-word;
            overflow-wrap: break-word;
            transform-origin: top left;
        }
        
        .separator {
            border-top: 1px solid #000;
            margin: 6px 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 4px;
        }
        
        .logo img {
            max-width: 100%;
            height: auto;
            max-height: 40px;
        }
        
        .institution-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2px;
        }
        
        .institution-subtitle {
            font-size: 9pt;
            margin-bottom: 2px;
        }
        
        .institution-info {
            font-size: 8pt;
            line-height: 1.3;
            margin-bottom: 4px;
        }
        
        .institution-info p {
            margin: 1px 0;
        }
        
        .receipt-title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin: 6px 0;
        }
        
        .receipt-body {
            margin-top: 6px;
        }
        
        .field {
            margin-bottom: 4px;
        }
        
        .field-label {
            font-weight: bold;
            font-size: 8pt;
        }
        
        .field-value {
            font-size: 8pt;
            margin-top: 1px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .value-highlight {
            background-color: #e0e0e0;
            padding: 2px 4px;
        }
        
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: var(--paper-width) !important;
                max-width: var(--paper-width) !important;
            }
            
            .receipt-container {
                padding-left: var(--offset-left-mm);
                padding-right: 2mm;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        @media screen {
            body {
                background: #fff;
                margin: 20px auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Logo INTESA arriba del header -->
        <div class="logo-intesa" style="text-align: center; margin-bottom: 5px;">
            <img src="{{ asset('dimages/LogoIntesa.png') }}" alt="INTESA" style="max-width: 60px; height: auto;">
        </div>
        
        <!-- Separador superior -->
        <div class="separator"></div>
        
        <div class="header">
            @if($institution->logo_path && file_exists(public_path($institution->logo_path)))
                <div class="logo">
                    <img src="{{ asset($institution->logo_path) }}" alt="Logo">
                </div>
            @endif
            
            @if($institution->institucion_subtitulo)
                <div class="institution-subtitle">{{ $institution->institucion_subtitulo }}</div>
            @endif
            
            @if($institution->name)
                <div class="institution-name">{{ $institution->name }}</div>
            @endif
            
            <div class="institution-info">
                @if($institution->sede)
                    <p>{{ $institution->sede }}</p>
                @endif
                @if($institution->address)
                    <p>{{ $institution->address }}</p>
                @endif
                @if($institution->phone)
                    <p>Tels. {{ $institution->phone }}</p>
                @endif
                @if($institution->telefono2 || $institution->telefono3)
                    <p>
                        @if($institution->telefono2){{ $institution->telefono2 }}@endif
                        @if($institution->telefono2 && $institution->telefono3) - @endif
                        @if($institution->telefono3){{ $institution->telefono3 }}@endif
                    </p>
                @endif
            </div>
            
            @if($institution->footer_firma)
                <div style="border-top: 1px solid #ddd; margin-top: 5px; padding-top: 5px;"></div>
                <div style="font-size: 7pt; color: #999; text-align: center; margin-top: 3px;">{{ $institution->footer_firma }}</div>
            @endif
        </div>
        
        <div class="separator"></div>
        <div class="receipt-title">REGISTRO DE {{ strtoupper($tipo_recibo == 'egreso' ? 'EGRESO' : 'INGRESO') }}</div>
        
        <div class="receipt-body">
            @if(isset($consecutivo) && $consecutivo)
                <div class="field">
                    <div class="field-label">Identidad de Pago:</div>
                    <div class="field-value">{{ $consecutivo }}</div>
                </div>
            @endif
            
            @if(isset($estudiante_cedula) && $estudiante_cedula)
                <div class="field">
                    <div class="field-label">
                        @if(isset($tipo_documento) && $tipo_documento)
                            {{ $tipo_documento }} del Alumno(a):
                        @else
                            C.C del Alumno(a):
                        @endif
                    </div>
                    <div class="field-value">{{ $estudiante_cedula }}</div>
                </div>
            @endif
            
            @if(isset($estudiante_nombre) && $estudiante_nombre)
                <div class="field">
                    <div class="field-label">Estudiante:</div>
                    <div class="field-value">{{ strtoupper($estudiante_nombre) }}</div>
                </div>
            @endif
            
            @if(isset($programa) && $programa)
                <div class="field">
                    <div class="field-label">Programa:</div>
                    <div class="field-value">{{ strtoupper($programa) }}</div>
                </div>
            @endif
            
            @if(isset($proveedor_nombre) && $proveedor_nombre)
                <div class="field">
                    <div class="field-label">Proveedor:</div>
                    <div class="field-value">{{ strtoupper($proveedor_nombre) }}</div>
                </div>
            @endif
            
            @if(isset($tercero_nombre) && $tercero_nombre)
                <div class="field">
                    <div class="field-label">Tercero:</div>
                    <div class="field-value">{{ strtoupper($tercero_nombre) }}</div>
                </div>
            @endif
            
            @if(isset($tercero_documento) && $tercero_documento)
                <div class="field">
                    <div class="field-label">Documento:</div>
                    <div class="field-value">{{ $tercero_documento }}</div>
                </div>
            @endif
            
            @if(isset($concepto) && $concepto)
                <div class="field">
                    <div class="field-label">Concepto:</div>
                    <div class="field-value">{{ $concepto }}</div>
                </div>
            @endif
            
            @if(isset($descripcion) && $descripcion)
                <div class="field">
                    <div class="field-label">Descripción:</div>
                    <div class="field-value">{{ $descripcion }}</div>
                </div>
            @endif
            
            @if(isset($forma) && $forma)
                <div class="field">
                    <div class="field-label">Forma de Pago:</div>
                    <div class="field-value">{{ $forma }}</div>
                </div>
            @endif
            
            <div class="separator"></div>
            
            @if(isset($valor) && $valor)
                <div class="field">
                    <div class="field-value value-highlight">
                        <strong>Valor: ${{ number_format($valor, 0, ',', '.') }}</strong>
                    </div>
                </div>
            @endif
            
            @if(isset($fecha) && $fecha)
                <div class="field">
                    <div class="field-value">
                        <strong>Fecha: {{ $fecha }}</strong>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="separator"></div>
    </div>
    
    <script>
        // Auto-impresión al cargar
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
