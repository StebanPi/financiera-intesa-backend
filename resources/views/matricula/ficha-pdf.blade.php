@extends('layouts.pdf')

@section('content')
@php
    date_default_timezone_set("America/Bogota");
    use Illuminate\Support\Facades\Storage;
    
    // Formatear números con separadores de miles
    function formatMoney($value) {
        // Si es null, vacío o 0, retornar $0
        if($value === null || $value === '' || $value === 0) {
            return '$0';
        }
        
        // Convertir a string para procesar
        $strValue = trim((string)$value);
        
        // Si está vacío después de trim, retornar $0
        if($strValue === '') {
            return '$0';
        }
        
        // Remover símbolos de moneda y espacios
        $cleaned = str_replace(['$', ' ', 'COP', 'cop'], '', $strValue);
        
        // Detectar si tiene puntos o comas
        $hasComma = strpos($cleaned, ',') !== false;
        $hasDot = strpos($cleaned, '.') !== false;
        $dotCount = $hasDot ? substr_count($cleaned, '.') : 0;
        
        // Procesar según el formato detectado
        if($hasComma && $hasDot) {
            // Tiene ambos: el punto es separador de miles, la coma es decimal
            // Ejemplo: "1.500.000,50" -> "1500000.50"
            $parts = explode(',', $cleaned);
            $integerPart = str_replace('.', '', $parts[0]);
            $decimalPart = $parts[1] ?? '0';
            $cleaned = $integerPart . '.' . $decimalPart;
        } elseif($hasDot && !$hasComma) {
            // Solo tiene punto(s)
            if($dotCount > 1) {
                // Múltiples puntos = separadores de miles: "1.500.000" -> "1500000"
                $cleaned = str_replace('.', '', $cleaned);
            } elseif($dotCount == 1) {
                // Un solo punto: determinar si es decimal o separador de miles
                $dotPosition = strpos($cleaned, '.');
                $length = strlen($cleaned);
                $digitsAfterDot = $length - $dotPosition - 1;
                
                // Si hay más de 3 dígitos después del punto, probablemente es decimal
                // Si hay 3 o menos dígitos después del punto y el número total tiene 4+ dígitos, es separador de miles
                if($digitsAfterDot > 3) {
                    // Es decimal: "1234.5678" -> mantener
                    // No hacer nada
                } else {
                    // Probablemente es separador de miles: "1.234" -> "1234"
                    $cleaned = str_replace('.', '', $cleaned);
                }
            }
        } elseif($hasComma && !$hasDot) {
            // Solo tiene coma: puede ser decimal o separador de miles
            // Si tiene más de 3 dígitos después de la coma, es decimal
            $commaPosition = strpos($cleaned, ',');
            $length = strlen($cleaned);
            $digitsAfterComma = $length - $commaPosition - 1;
            
            if($digitsAfterComma > 3) {
                // Es decimal: convertir coma a punto
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                // Probablemente es separador de miles (formato europeo): "1,234" -> "1234"
                $cleaned = str_replace(',', '', $cleaned);
            }
        }
        
        // Convertir a número (float para mantener precisión)
        $numericValue = (float)$cleaned;
        
        // Si después de la conversión es 0, inválido o NaN, retornar $0
        if($numericValue <= 0 || !is_finite($numericValue) || is_nan($numericValue)) {
            return '$0';
        }
        
        // Formatear con puntos como separadores de miles y sin decimales
        // number_format() automáticamente agrega separadores solo cuando el número tiene 4+ dígitos
        return '$' . number_format($numericValue, 0, ',', '.');
    }
    
    // Obtener fecha formateada
    $fechaActual = \Carbon\Carbon::now()->format('d/m/Y');
    
    // Obtener persona responsable (emergencia)
    $personaResponsable = $matricula->telefono_emergencia ? 'Contacto: ' . $matricula->telefono_emergencia : 'N/A';
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    * {
        font-family: 'Poppins', sans-serif !important;
        box-sizing: border-box;
    }
    
    body {
        margin: 0;
        padding: 15px;
        font-size: 10px;
        line-height: 1.3;
        color: #000;
    }
    
    .container-pdf {
        margin-top: 60px;
        width: 100%;
        max-width: 100%;
    }
    
    .title-planilla {
        text-align: center;
        margin: 40px 0 6px 0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Encabezado superior - Dos columnas */
    .header-section {
        display: table;
        width: 100%;
        margin-bottom: 15px;
        page-break-inside: avoid;
    }
    
    .header-left, .header-right {
        display: table-cell;
        vertical-align: top;
        width: 50%;
        padding: 10px;
    }
    
    .header-left {
        border-right: 1px solid #000;
        padding-right: 15px;
    }
    
    .header-right {
        padding-left: 15px;
        position: relative;
    }
    
    .section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 8px;
        color: #000;
        border-bottom: 1px solid #000;
        padding-bottom: 3px;
        background: #e0e0e0;
    }
    
    .info-row {
        margin-bottom: 5px;
        font-size: 9px;
    }
    
    .info-label {
        font-weight: 600;
        display: inline-block;
        min-width: 100px;
    }
    
    .info-value {
        color: #000;
    }
    
    /* Foto del estudiante */
    .student-photo {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 80px;
        height: 100px;
        border: 1px solid #000;
        text-align: center;
        background: #f5f5f5;
    }
    
    .student-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .student-photo-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        font-size: 8px;
        color: #999;
    }
    
    /* Bloque de financiación */
    .financing-section {
        margin-top: 10px;
        margin-bottom: 15px;
        border: 1px solid #000;
        padding: 8px;
        page-break-inside: avoid;
    }
    
    .financing-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 8px;
        text-align: center;
        background: #e0e0e0;
        padding: 3px;
    }
    
    .financing-row {
        display: table;
        width: 100%;
        margin-bottom: 4px;
        font-size: 9px;
    }
    
    .financing-label {
        display: table-cell;
        width: 60%;
        font-weight: 600;
    }
    
    .financing-value {
        display: table-cell;
        width: 40%;
        text-align: right;
        font-weight: 700;
    }
    
    .financing-value.highlight {
        background: #ffff00;
        padding: 2px 5px;
    }
    
    /* Zona inferior izquierda - QR y datos bancarios */
    .bottom-section {
        display: table;
        width: 100%;
        margin-top: 15px;
        page-break-inside: avoid;
    }
    
    .bottom-left {
        display: table-cell;
        width: 40%;
        vertical-align: top;
        padding-right: 15px;
    }
    
    .bottom-right {
        display: table-cell;
        width: 60%;
        vertical-align: top;
        padding-left: 15px;
    }
    
    .qr-container {
        text-align: center;
        margin-bottom: 10px;
    }
    
    .qr-container img {
        width: 120px;
        height: 120px;
        border: 1px solid #000;
    }
    
    /* Firma */
    .signature-section {
        margin-top: 20px;
        page-break-inside: avoid;
    }
    
    .signature-text {
        font-size: 8px;
        text-align: justify;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .signature-line {
        border-top: 1px solid #000;
        width: 100%;
        margin-top: 40px;
        margin-bottom: 5px;
    }
    
    .signature-info {
        font-size: 10px;
        margin-top: 5px;
    }
</style>

<div class="container-pdf">
    <!-- Encabezado Superior -->
    <h2 class="title-planilla">FICHA DE MATRÍCULA</h2>
    <div class="header-section">
        <!-- Columna Izquierda: Información General -->
        <div class="header-left">
            <div class="section-title">Información General</div>
            
            <div class="info-row">
                <span class="info-label">Código:</span>
                <span class="info-value">{{ $matricula->cod_alumno ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">{{ $fechaActual }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Apellidos y Nombre:</span>
                <span class="info-value">{{ $matricula->nombre_completo ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">{{ $matricula->tipo_documento ?? 'T.I' }}:</span>
                <span class="info-value">{{ $matricula->numero_documento ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Expedida en:</span>
                <span class="info-value">{{ $matricula->lugar_expedicion_documento ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Fecha de Nacimiento:</span>
                <span class="info-value">
                    @if($matricula->fecha_nacimiento)
                        {{ \Carbon\Carbon::parse($matricula->fecha_nacimiento)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Dirección:</span>
                <span class="info-value">{{ $matricula->direccion_barrio ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Ciudad / Departamento:</span>
                <span class="info-value">{{ $matricula->ciudad_residencia ?? 'N/A' }} / {{ $matricula->departamento ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Teléfono / Celular:</span>
                <span class="info-value">{{ $matricula->telefono_personal ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Emergencia:</span>
                <span class="info-value">{{ $personaResponsable }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Correo:</span>
                <span class="info-value">{{ $matricula->correo_gmail ?? 'N/A' }}</span>
            </div>
        </div>
        
        <!-- Columna Derecha: Información del Programa + Foto -->
        <div class="header-right">
            <div class="section-title">Información del Programa</div>
            
            <div class="info-row">
                <span class="info-label">Programa:</span>
                <span class="info-value">{{ $matricula->programa ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Sede:</span>
                <span class="info-value">{{ $matricula->sede ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Número de grupo:</span>
                <span class="info-value">{{ $matricula->numero_grupo ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Horario:</span>
                <span class="info-value">{{ $matricula->horario ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Periodos de pago:</span>
                <span class="info-value">{{ $cost->periodo ?? 'N/A' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label"># de cuotas:</span>
                <span class="info-value">{{ $cost->numero_cuotas ?? 'N/A' }}</span>
            </div>
            
            <!-- Foto del estudiante -->
            <div class="student-photo">
                @if(isset($photoBase64) && $photoBase64)
                    <img src="{{ $photoBase64 }}" alt="Foto del estudiante">
                @else
                    <div class="student-photo-placeholder">
                        Sin foto
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Bloque Condiciones de Financiación -->
    <div class="financing-section">
        <div class="financing-title">Condiciones de Financiación</div>
        
        @php
            // Función para limpiar valores que vienen formateados desde MoneyController::datas()
            // Estos valores pueden venir como strings con formato "1.500.000" o "600"
            function cleanMoneyValue($value) {
                if($value === null || $value === '' || $value === 0) {
                    return 0;
                }
                // Si ya es numérico y no es string, retornar directamente
                if(is_numeric($value) && !is_string($value)) {
                    return (float)$value;
                }
                // Si es string, limpiar formato
                $str = trim((string)$value);
                // Remover puntos (separadores de miles) y comas (decimales)
                $cleaned = str_replace(['.', ',', '$', ' '], '', $str);
                $numeric = (float)$cleaned;
                return is_finite($numeric) && !is_nan($numeric) ? $numeric : 0;
            }
            
            // Limpiar valores del cost antes de formatear
            // Estos valores vienen formateados desde MoneyController::datas()
            $valorSemestre = $cost ? cleanMoneyValue($cost->valor_semestre ?? 0) : 0;
            $descuento = $cost ? cleanMoneyValue($cost->descuento ?? 0) : 0;
            $saldoFinanciar = $cost ? cleanMoneyValue($cost->saldo_financiar ?? 0) : 0;
            $valorCuotas = $cost ? cleanMoneyValue($cost->valor_cuotas ?? 0) : 0;
        @endphp
        
        <div class="financing-row">
            <div class="financing-label">Costos por semestre:</div>
            <div class="financing-value">{{ formatMoney($valorSemestre) }}</div>
        </div>
        
        <div class="financing-row">
            <div class="financing-label">Descuento:</div>
            <div class="financing-value">{{ formatMoney($descuento) }}</div>
        </div>
        
        <div class="financing-row">
            <div class="financing-label">Crédito por semestre:</div>
            <div class="financing-value">{{ formatMoney($valorSemestre) }}</div>
        </div>
        
        <div class="financing-row">
            <div class="financing-label">Valor a financiar:</div>
            <div class="financing-value">{{ formatMoney($saldoFinanciar) }}</div>
        </div>
        
        <div class="financing-row">
            <div class="financing-label">Valor de cuota:</div>
            <div class="financing-value">{{ formatMoney($valorCuotas) }}</div>
        </div>
    </div>
    
    <!-- Zona Inferior -->
    <div class="bottom-section">
        <!-- Columna Izquierda: Texto de Aceptación y Firma -->
        <div class="bottom-left">
            <div class="signature-section">
                <div class="signature-text">
                    Por medio de la presente firma, acepto los términos y condiciones establecidos en este documento de matrícula, así como el reglamento interno de la institución y me comprometo a cumplir con todas las obligaciones académicas y financieras correspondientes.
                </div>
                
                <div class="signature-line"></div>
                
                <div class="signature-info">
                    <strong>{{ $matricula->nombre_completo ?? 'N/A' }}</strong><br>
                    {{ $matricula->tipo_documento ?? 'N/A' }} {{ $matricula->numero_documento ?? 'N/A' }}
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: QR Code y Reglas -->
        <div class="bottom-right">
            <div style="display: flex; flex-direction: column; gap: 15px; align-items: flex-start; margin-left: 15px;">
                <!-- Reglas -->
                <div style="text-align: left; width: 100%;">
                    <div style="margin-bottom: 8px; font-size: 8px; line-height: 1.4;">
                        • El uso del uniforme es obligatorio para la asistencia a clases.
                    </div>
                    <div style="font-size: 8px; line-height: 1.4;">
                        • La nota mínima para aprobar es de 3,5.
                    </div>
                </div>
                
                <!-- QR Code -->
                
                <!-- Información Bancaria -->
                <div style="text-align: left; width: 100%; margin-top: 15px;">
                    <div style="font-weight: bold; text-decoration: underline; margin-bottom: 5px; font-size: 8px; line-height: 1.4;">
                        Cuenta Bancolombia
                    </div>
                    <div style="font-weight: bold; margin-bottom: 5px; font-size: 8px; line-height: 1.4;">
                    •  Ahorros :{{ $institucion->cuenta_bancaria ?? '29736096526' }}
                    </div>
                    <div style="font-weight: bold; margin-bottom: 10px; font-size: 8px; line-height: 1.4;">
                    • Nombre: INTESA
                    </div>
                    <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase; margin-bottom: 10px; font-size: 8px; line-height: 1.4;">
                    NO SE HACEN DEVOLUCIONES DE DINERO
                    </div>
                    <div style="color: #28a745; font-size: 8px; line-height: 1.4; font-weight: bold;">
                        Gracias Por Depositar Su Confianza En Nuestra Institución!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
