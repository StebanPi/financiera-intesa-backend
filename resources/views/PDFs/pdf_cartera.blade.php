@extends('layouts.pdf')

@section('content')
    @php
        date_default_timezone_set("America/Bogota");
    @endphp
    <div class="container text-center" style="margin-bottom: 5px;">
        <h2 style="text-align: center; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 700; margin-bottom: 3px; margin-top: 3px; text-transform: uppercase;">
            PLAN DE FINANCIAMIENTO
        </h2>
        <p style="text-align: center; font-size: 10px; font-family: 'Poppins', sans-serif; font-weight: 400; margin-bottom: 3px; margin-top: 3px;">Para 2 Periodos Académicos</p>
    </div>

    @if(isset($student) && count($student) > 0 && isset($student[0]))
    <div class="d-flex p-2" style="background-color: #e7e9eb; padding: 5px 8px; font-family: 'Poppins', sans-serif; font-size: 12px; margin-bottom: 6px;">
        <div class="mr-3 text-black" style="margin-right: 12px; font-size: 12px; font-family: 'Poppins', sans-serif;">
            Cedula: <b>{{ $student[0]->cedula ?? 'N/A' }}</b>
        </div>
        <div class="mr-3 text-black" style="margin-right: 12px; font-size: 12px; font-family: 'Poppins', sans-serif;">
            Estudiante: <b>{{ $student[0]->nombre ?? 'N/A' }}</b>
        </div>
        <div class="mr-3 text-black" style="margin-right: 12px; font-size: 12px; font-family: 'Poppins', sans-serif;">
            Programa: <b>{{ $student[0]->nombre_programa ?? 'N/A' }}</b>
        </div>
        @php
            // Calcular estado de cartera para mostrar en el header
            $estadoCarteraHeader = 'Al dia';
            $estadoCarteraColorHeader = '#2bc155';
            if(isset($purses) && count($purses) > 0) {
                foreach($purses as $purseItem) {
                    if(isset($purseItem->is_vencida) && isset($purseItem->abonado) && isset($purseItem->cuota)) {
                        $isVencida = $purseItem->is_vencida;
                        if($isVencida) {
                            $abonado = floatval($purseItem->abonado);
                            $cuota = floatval($purseItem->cuota);
                            if($abonado < $cuota) {
                                $estadoCarteraHeader = 'En Mora';
                                $estadoCarteraColorHeader = '#f72b50';
                                break;
                            }
                        }
                    }
                }
            }
        @endphp
        <div class="mr-3 text-black" style="font-size: 12px; font-family: 'Poppins', sans-serif;">
            Estado de Cartera: <b style="color:{{ $estadoCarteraColorHeader }};">{{ $estadoCarteraHeader }}</b>
        </div>
    </div>
    @else
    <div class="d-flex p-2" style="background-color: #fee2e2; padding: 5px 8px; font-family: 'Poppins', sans-serif; font-size: 12px; border: 1px solid #ef4444; border-radius: 4px; margin-bottom: 6px;">
        <div class="text-danger" style="font-size: 12px; font-family: 'Poppins', sans-serif;">
            <i class="fa-solid fa-exclamation-triangle mr-2"></i>No se encontró información del estudiante.
        </div>
    </div>
    @endif
    
    @php
        $suma = 0;
        // Calcular número de cuotas para ajustar tamaño dinámicamente (máximo 12 cuotas)
        $numeroCuotas = 0;
        if(isset($purses) && count($purses) > 0) {
            $numeroCuotas = count($purses);
        } elseif(isset($cost) && count($cost) > 0 && isset($cost[0]->numero_cuotas)) {
            // Usar numero_cuotas del cost como respaldo
            $numeroCuotas = intval($cost[0]->numero_cuotas);
        }
        
        // Ajustar tamaño de fuente y padding según número de cuotas (máximo 12)
        // Si hay más de 8 cuotas, reducir el tamaño progresivamente para que quepa todo
        $fontSize = 12;
        $thPadding = '4px 3px';
        $tdPadding = '3px';
        
        if($numeroCuotas > 8 && $numeroCuotas <= 10) {
            $fontSize = 11;
            $thPadding = '3px 2px';
            $tdPadding = '2.5px';
        } elseif($numeroCuotas > 10 && $numeroCuotas <= 12) {
            // Para 11-12 cuotas (el máximo), tamaño más pequeño para que quepa todo
            $fontSize = 10;
            $thPadding = '3px 2px';
            $tdPadding = '2px';
        }
        
        $fontSizeStr = $fontSize . 'px';
    @endphp
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Poppins', sans-serif !important;
        }
        
        .container {
            margin-top: 50px !important;
            margin-bottom: 8px !important;
            padding: 0 !important;
        }
        
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: {{ $fontSizeStr }} !important;
            margin-top: 4px !important;
        }
        
        table, th, td {
        border: 1px solid black !important;
        font-family: 'Poppins', sans-serif !important;
        }
        
        th {
            background-color: #e9ecef !important;
            font-weight: 600 !important;
            text-align: center !important;
            font-size: {{ $fontSizeStr }} !important;
            padding: {{ $thPadding }} !important;
            line-height: 1.1 !important;
            vertical-align: middle !important;
        }
        
        td {
            padding: {{ $tdPadding }} !important;
            font-size: {{ $fontSizeStr }} !important;
            line-height: 1.1 !important;
            vertical-align: middle !important;
        }
        
        table tr:nth-child(odd) {
            background-color: #fff;
        }
        
        table tr:nth-child(even) {
            background-color: #e7e9eb;
        }
    </style>
    @if(isset($id_cost) && $id_cost != 0)
    <table id="table" style="width: 100%; font-size: {{ $fontSizeStr }};" class=""> 
        <thead style="" class="thead-secondary text-black text-center">
            <th scope="col" style="width: 5%; padding: {{ $thPadding }};">ID</th>
            <th scope="col" style="width: 15%; padding: {{ $thPadding }};">Fecha de Pago</th>
            <th scope="col" style="width: 15%; padding: {{ $thPadding }};">Cuota</th>
            <th scope="col" style="width: 15%; padding: {{ $thPadding }};">Abonado</th>
            <th scope="col" style="width: 18%; padding: {{ $thPadding }};">Estado Pago</th>
            <th scope="col" style="width: 15%; padding: {{ $thPadding }};">Estado</th>
        </thead>
        <tbody>

            @php
                // Calcular estado de cartera
                $estadoCartera = 'Al dia';
                $hoy = date('Y-m-d');
                $i = 0; // Inicializar contador
                
                // Si tenemos totales calculados del servicio, usarlos directamente
                if(isset($totales)) {
                    $totalAbono = $totales['total_abono'] ?? 0;
                    $CuotasTotal = $totales['cuotas_total'] ?? 0;
                    $SaldoPendiente = $totales['saldo_pendiente'] ?? 0;
                    $SaldoAFavor = $totales['saldo_a_favor'] ?? 0;
                    $SaldoEnMora = $totales['saldo_en_mora'] ?? 0;
                    $totalAbonado = $totales['total_abonado'] ?? 0;
                } else {
                    // Fallback: calcular manualmente (lógica antigua)
                    // Nota: other_entries no se incluyen en el cálculo de cartera (son otros ingresos separados)
                    $totalAbono = 0;
                    if(!empty($entries) && isset($entries[0]) && isset($entries[0]->TotalAbono)){
                        $totalAbono = floatval($entries[0]->TotalAbono);
                    }
                    
                    $total = $totalAbono;
                    $saldoFecha = 0;
                    $SaldoPendiente = 0;
                    $SaldoAFavor = 0;
                    $SaldoEnMora = 0;
                    $hoy = date('Y-m-d');
                    $i = 0;
                    $CuotasTotal = 0;
                    
                    // Calcular estado de cartera en el fallback también
                    // Se calculará dentro del loop de las cuotas
                }
            @endphp

            @if(isset($purses) && count($purses) > 0)
                @foreach ($purses as $item)
                    @php 

                    $i++;
                    $isVencida = false;
                    $valueShow = 0;
                    
                    // Si ya tenemos totales calculados, no recalcular CuotasTotal
                    if(!isset($totales)) {
                        $CuotasTotal += $item->cuota;
                    } 

                    $cuota = floatval($item->cuota);
                    
                    // Si los datos vienen del servicio, usar los valores ya calculados
                    if(isset($item->abonado) && isset($item->estado_pago) && isset($item->estado) && isset($item->is_vencida)) {
                        // Los datos ya vienen calculados del servicio
                        $abonado = floatval($item->abonado);
                        $estadoPago = $item->estado_pago;
                        $estado = $item->estado;
                        $isVencida = $item->is_vencida;
                        // Calcular valueShow igual que en el PDF original
                        if($abonado >= $cuota){
                            $valueShow = $cuota;
                        }elseif($abonado > 0){
                            $valueShow = $abonado;
                        }else{
                            $valueShow = 0;
                        }
                        
                        // Calcular estado de cartera: si hay cuota vencida incompleta = "En Mora"
                        if($isVencida && $abonado < $cuota) {
                            $estadoCartera = 'En Mora';
                        }
                    } else {
                        // Fallback: calcular manualmente (lógica antigua)
                        $totalAntes = $total;
                        
                        // Calcular el abono dinámicamente distribuyendo el total acumulado
                        $abonado = 0;
                        if($total >= $cuota){
                            $abonado = $cuota;
                            $total = $total - $cuota;
                        }elseif($total > 0){
                            $abonado = $total;
                            $total = 0;
                        }else{
                            $abonado = 0;
                        }

                        // Verificar si la cuota está vencida
                        $fechaPagoOriginal = $item->fecha_pago;
                        $fechaPago = date('Y-m-d', strtotime($item->fecha_pago));
                        $timestampHoy = strtotime($hoy);
                        $timestampPago = strtotime($fechaPago);
                        
                        if($timestampHoy > $timestampPago){
                            $isVencida = true;
                        }

                        // Determinar el estado del pago
                        if($abonado >= $cuota){
                            $estadoPago = 'Completa';
                        }elseif($abonado > 0 && $abonado < $cuota){
                            $estadoPago = 'Incompleta';
                        }else{
                            $estadoPago = 'Pendiente';
                        }
                        
                        // Determinar el estado
                        if($abonado >= $cuota){
                            $estado = 'Al dia';
                            $valueShow = $cuota;
                        }elseif($isVencida){
                            // Si ya pasó el día de pago y no está completo, está en mora
                            if($abonado > 0){
                                $valueShow = $abonado;
                                if(!isset($totales)) {
                                    $diferencia = $cuota - $abonado;
                                    $SaldoPendiente += $diferencia;
                                    $SaldoEnMora += $diferencia; // Saldo en mora = diferencia de cuotas vencidas
                                }
                            }else{
                                $valueShow = 0;
                                if(!isset($totales)) {
                                    $SaldoPendiente += $cuota;
                                    $SaldoEnMora += $cuota; // Saldo en mora = cuota completa si no hay abono
                                }
                            }
                            $estado = 'En Mora'; // Siempre en mora si está vencida y no completa
                        }else{
                            // No está vencida
                            if($abonado > 0){
                                $valueShow = $abonado;
                                if($abonado < $cuota){
                                    $estado = 'Incompleta';
                                    if(!isset($totales)) {
                                        $SaldoPendiente += ($cuota - $abonado);
                                    }
                                }else{
                                    $estado = 'Proxima';
                                }
                            }else{
                                $valueShow = 0;
                                $estado = 'Proxima';
                            }
                        }
                        
                        // Calcular Saldo a Favor (solo si no tenemos totales del servicio)
                        if(!isset($totales) && $valueShow > 0 && $isVencida == false){
                            $SaldoAFavor += $valueShow;
                        }
                        
                        // Calcular estado de cartera en el fallback
                        if(!isset($totales) && $isVencida && $abonado < $cuota) {
                            $estadoCartera = 'En Mora';
                        }
                    }

                    @endphp
                    
                    @if($estado == 'Al dia')
                        <tr style="background-color:#2bc155;color:#fff;">
                    @elseif($estado == 'Incompleta')
                        <tr style="background-color:#ffc107;color:#000;">
                    @elseif($estado == 'Proxima' && $isVencida == false && $valueShow > 0)
                        <tr style="background-color:#98ce04;color:#fff;">
                    @elseif($estado == 'En Mora')
                        <tr style="background-color:#f72b50;color:#fff;">    
                    @else       
                        <tr>
                    @endif
       
                        <td style="text-align:center; padding: {{ $tdPadding }};">{{ $i}}</td>
                        <td style="text-align:center; padding: {{ $tdPadding }};">{{ App\Http\Controllers\DateController::getMesSubtr($item->fecha_pago) }}</td>
                        <td style="text-align:right; padding: {{ $tdPadding }};">${{ App\Http\Controllers\MoneyController::main($item->cuota) }}</td>
                        @if($valueShow > 0)
                            <td style="text-align:right; padding: {{ $tdPadding }};">${{ App\Http\Controllers\MoneyController::main($valueShow) }}</td>
                        @else
                            <td style="text-align:right; padding: {{ $tdPadding }};"></td>
                        @endif
                        <td style="text-align:center; padding: {{ $tdPadding }};">
                            {{ $estadoPago }}
                        </td>
                        <td style="text-align:center; padding: {{ $tdPadding }};">{{$estado}}</td>
                    </tr>
                @endforeach
            @endif

            <tr style="background-color:#0e00ce;color:#fff;">
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }}; font-weight: 600;">Total Financiado</td>
                <td style="text-align:right; padding: {{ $tdPadding }}; font-weight: 600;">${{ App\Http\Controllers\MoneyController::main($CuotasTotal) }}</td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
            </tr>

            <tr style="background-color:#585858;color:#fff;">
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }}; font-weight: 600;">Total Abonado</td>
                <td style="text-align:right; padding: {{ $tdPadding }}; font-weight: 600;">${{ App\Http\Controllers\MoneyController::main(isset($totales) ? $totales['total_abonado'] : $totalAbono) }}</td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
            </tr>
            <tr style="background-color:#ffebee;">
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }}; font-weight: 600;">Saldo en Mora</td>
                <td style="text-align:right; padding: {{ $tdPadding }}; color:#f72b50; font-weight: 600;">${{ App\Http\Controllers\MoneyController::main(isset($totales) ? $totales['saldo_en_mora'] : $SaldoEnMora) }}</td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
            </tr>
            <tr style="background-color:#dcecb0;">
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }}; font-weight: 600;">Saldo a Favor</td>
                <td style="text-align:right; padding: {{ $tdPadding }}; font-weight: 600;">${{ App\Http\Controllers\MoneyController::main(isset($totales) ? $totales['saldo_a_favor'] : $SaldoAFavor) }}</td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
                <td style="text-align:center; padding: {{ $tdPadding }};"></td>
            </tr>
        </tbody>
       
    </table>
    @endif 
    
@endsection
