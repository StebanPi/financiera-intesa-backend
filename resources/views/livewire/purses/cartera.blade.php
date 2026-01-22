<div class="text-black">
    @php
        $suma = 0;
    @endphp
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <style>
        table, th, td {
        border: 1px solid black !important;
        border-collapse: collapse !important;
        }
        table tr:nth-child(odd)
        { background-color: #fff;
        }
        table tr:nth-child(even)
        { background-color: #e7e9eb;
        }
    </style>
    @if ($id_cost != 0)
    <table id="table" style="width: 100%;" class=""> 
        <thead style="" class="thead-secondary text-black text-center">
            <th scope="col">ID</th>
            <th scope="col">Semestre</th>
            <th scope="col">Fecha de Pago</th>
            <th scope="col">Cuota</th>
            <th scope="col">Abonado</th>
            <th scope="col">Estado Pago</th>
            <th scope="col">Estado</th>
        </thead>
        <tbody>

            @php
                // Validar que entries tenga datos
                $totalAbono = 0;
                if(!empty($entries) && isset($entries[0]) && isset($entries[0]->TotalAbono)){
                    $totalAbono = floatval($entries[0]->TotalAbono);
                }
                
                $total = $totalAbono; 
                $saldoFecha = 0;
                $SaldoPendiente = 0;
                $SaldoAFavor = 0;
                $hoy = date('Y-m-d'); // Formato correcto: año de 4 dígitos
                $i = 0;
                $CuotasTotal = 0;
                
                // LOG: Información inicial
                \Log::info('CARTERA LIVEWIRE DEBUG - Inicio', [
                    'total_abono' => $total,
                    'hoy' => $hoy,
                    'total_cuotas' => count($purses ?? []),
                    'entries_empty' => empty($entries),
                    'entries_count' => count($entries ?? [])
                ]);
            @endphp

            @foreach ($purses as $item)
                @php 

                $i++;
                $isVencida = false;
                $valueShow = 0;
                $CuotasTotal += $item->cuota; 

                $cuota = floatval($item->cuota);
                $totalAntes = $total; // Guardar total antes de calcular
                
                // Calcular el abono dinámicamente distribuyendo el total acumulado
                $abonado = 0;
                if($total >= $cuota){
                    // Hay suficiente abono para cubrir esta cuota completa
                    $abonado = $cuota;
                    $total = $total - $cuota;
                }elseif($total > 0){
                    // Hay abono parcial para esta cuota
                    $abonado = $total;
                    $total = 0;
                }else{
                    // No hay abono disponible
                    $abonado = 0;
                }

                // Verificar si la cuota está vencida (comparar fechas correctamente)
                $fechaPagoOriginal = $item->fecha_pago;
                $fechaPago = date('Y-m-d', strtotime($item->fecha_pago));
                $timestampHoy = strtotime($hoy);
                $timestampPago = strtotime($fechaPago);
                
                if($timestampHoy > $timestampPago){
                    $isVencida = true;
                }

                // LOG: Información de cada cuota
                \Log::info("CARTERA LIVEWIRE DEBUG - Cuota #{$i}", [
                    'id' => $item->id ?? 'N/A',
                    'fecha_pago_original' => $fechaPagoOriginal,
                    'fecha_pago_formateada' => $fechaPago,
                    'hoy' => $hoy,
                    'timestamp_hoy' => $timestampHoy,
                    'timestamp_pago' => $timestampPago,
                    'is_vencida' => $isVencida,
                    'cuota' => $cuota,
                    'total_antes' => $totalAntes,
                    'abonado_calculado' => $abonado,
                    'total_despues' => $total
                ]);

                // Determinar el estado del pago (Completa, Incompleta, Pendiente)
                if($abonado >= $cuota){
                    $estadoPago = 'Completa';
                }elseif($abonado > 0 && $abonado < $cuota){
                    $estadoPago = 'Incompleta';
                }else{
                    $estadoPago = 'Pendiente';
                }
                
                // Determinar el estado basado en el abono real de la cuota (lógica original)
                if($abonado >= $cuota){
                    // Abono completo o mayor a la cuota
                    $estado = 'Al dia';
                    $valueShow = $cuota;

                }elseif($isVencida){
                    // Si ya pasó el día de pago y no está completo, está en mora
                    if($abonado > 0){
                        $valueShow = $abonado;
                        $SaldoPendiente += ($cuota - $abonado);
                    }else{
                        $valueShow = 0;
                        $SaldoPendiente += $cuota;
                    }
                    $estado = 'En Mora'; // Siempre en mora si está vencida y no completa

                }else{
                    // No está vencida
                    if($abonado > 0){
                        $valueShow = $abonado;
                        
                        // Si el abono es mayor a 0 pero menor a la cuota, cambiar estado a "Incompleta"
                        if($abonado < $cuota){
                            $estado = 'Incompleta';
                            // Calcular saldo pendiente para cuotas no vencidas con abono parcial
                            $SaldoPendiente += ($cuota - $abonado);
                        }else{
                            $estado = 'Proxima';
                        }
                    }else{
                        $valueShow = 0;
                        $estado = 'Proxima';
                        // No se suma al saldo pendiente porque aún no está vencida
                    }
                }
                
                // LOG: Estado final
                \Log::info("CARTERA LIVEWIRE DEBUG - Estado final Cuota #{$i}", [
                    'estado' => $estado,
                    'valueShow' => $valueShow,
                    'abonado' => $abonado,
                    'cuota' => $cuota,
                    'is_vencida' => $isVencida,
                    'SaldoPendiente' => $SaldoPendiente,
                    'SaldoAFavor' => $SaldoAFavor
                ]);
                
                // Calcular Saldo a Favor: cuando hay abono y no está vencida
                if($valueShow > 0 && $isVencida == false){
                    $SaldoAFavor += $valueShow;
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
   
                    <td style="text-align:center;" class="text-center text-black">{{ $i}}</td>
                    <td style="text-align:center;" class="text-center text-black">{{ numero_a_romano($item->numero_semestre ?? 1) }}</td>
                    <td style="text-align:center;" class="text-center text-black">{{ App\Http\Controllers\DateController::getMesSubtr($item->fecha_pago) }}</td>
                    <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($item->cuota) }}</td>
                    @if($valueShow > 0)
                        <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($valueShow) }}</td>
                    @else
                        <td style="text-align:right;" class="text-center text-black"></td>
                    @endif
                    <td style="text-align:center;" class="text-center text-black">
                        @if($estadoPago == 'Completa')
                            <span class="badge badge-success">Completa</span>
                        @elseif($estadoPago == 'Incompleta')
                            <span class="badge badge-warning">Incompleta</span>
                        @else
                            <span class="badge badge-secondary">Pendiente</span>
                        @endif
                    </td>
                    <td style="text-align:center;" class="text-center text-black">{{$estado}}</td>
                </tr>
            @endforeach

            <tr style="background-color:#0e00ce;color:#fff;">
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black">Total Programa</td>
                <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($CuotasTotal) }}</td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
            </tr>

            <tr style="background-color:#585858;color:#fff;">
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black">Total Abono</td>
                <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($totalAbono) }}</td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
            </tr>
            <tr style="background-color:#F3CAD5 ;">
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black">Saldo Pendiente</td>
                <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($SaldoPendiente) }}</td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
            </tr>
            <tr style="background-color:#dcecb0;">
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black">Saldo a Favor</td>
                <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($SaldoAFavor) }}</td>
                <td style="text-align:center;" class="text-center text-black"></td>
                <td style="text-align:center;" class="text-center text-black"></td>
            </tr>
        </tbody>
       
    </table>
    @endif 
</div>
