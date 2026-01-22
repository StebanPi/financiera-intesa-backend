<div>
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
    <div class="row">
        <div class="col-md-12 text-center ">
            <div class="bg-gradients-azul">
              <img width="100px" height="100px" src="{{ asset('dimages/anuncio.png') }}" alt="">
              <div class="row">
                <div class="col-md-12">
                  <p class="text-white"><b>Los estudiantes que NO tengan registrado la matricula financiera, no apareceran en la busqueda , aunque esten registrados.</b></p>
                </div>
                <div class="col-md-12 text-center">
                  <div class="d-flex justify-content-center align-items-center">
                    <div class="input-group mb-3 mx-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-magnifying-glass"></i></span>
                      </div>
                      <input type="text"  class="form-control number-lg searchStudentCartera" style="font-size: 25px !important" aria-describedby="basic-addon1">
                      
                    </div>
                    
                  </div>
                  <ul class="listItemName d-none">
                    <li>Sebastyan Pineda</li>
                    <li>Juan Meneses</li>
                    <li>Lucas Modric</li>
                  </ul>
                </div>
              </div>
            
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-sm">
                <thead class="thead-secondary text-primary text-center">
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Semestre</th>
                        <th scope="col">Fecha de Pago</th>
                        <th scope="col">Cuota</th>
                        <th scope="col">Abonado</th>
                        <th scope="col">Estado Pago</th>
                        <th scope="col">Estado</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Usar el servicio centralizado para calcular cartera
                        use App\Services\CarteraService;
                        
                        if(!empty($id_cost) && $id_cost > 0){
                            $carteraData = CarteraService::calcularCartera($id_cost);
                            $cuotasCalculadas = $carteraData['cuotas'];
                            $totales = $carteraData['totales'];
                        } else {
                            // Fallback si no hay id_cost
                            $cuotasCalculadas = [];
                            $totales = [
                                'total_abono' => 0,
                                'cuotas_total' => 0,
                                'total_abonado' => 0,
                                'saldo_pendiente' => 0,
                                'saldo_a_favor' => 0,
                            ];
                        }
                    @endphp
        
                    @foreach ($cuotasCalculadas as $index => $cuota)
                        @php
                            $i = $index + 1;
                            $estado = $cuota['estado'];
                            $estadoPago = $cuota['estado_pago'];
                            $valueShow = $cuota['abonado'];
                            $isVencida = $cuota['is_vencida'];
                        @endphp
                        
                        @if($estado == 'Al dia')
                            <tr style="background-color:#2bc155;color:#fff;">
                        @elseif($estado == 'Incompleta')
                            <tr style="background-color:#ffc107;color:#000;">
                        @elseif($estado == 'Proxima' && !$isVencida && $valueShow > 0)
                            <tr style="background-color:#98ce04;color:#fff;">
                        @elseif($estado == 'En Mora')
                            <tr style="background-color:#f72b50;color:#fff;">    
                        @else       
                            <tr>
                        @endif
           
                            <td style="text-align:center;" class="text-center text-black">{{ $i }}</td>
                            <td style="text-align:center;" class="text-center text-black">{{ numero_a_romano($cuota['numero_semestre'] ?? 1) }}</td>
                            <td style="text-align:center;" class="text-center text-black">{{ App\Http\Controllers\DateController::getMesSubtr($cuota['fecha_pago']) }}</td>
                            <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($cuota['cuota']) }}</td>
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
                            <td style="text-align:center;" class="text-center text-black">{{ $estado }}</td>
                            <td style="text-align:center;" class="text-center text-black">
                                <i message="{{ $cuota['comentario'] }}" class="fa-solid fa-comment-dots text-primary showMessage pointer cpointer"></i>
                            </td>
                            <td style="text-align:center;" class="text-center text-black">
                                <i class="fa-solid fa-file-waveform ml-2 text-primary ShowRegisterPurse cpointer" data-toggle="modal" id_purse="{{ $cuota['id'] }}" data-target="#ModalHistory"></i>
                            </td>
                        </tr>
                    @endforeach
        
                    {{-- Filas de totales --}}
                    <tr style="background-color:#0e00ce;color:#fff;">
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black">Total Programa</td>
                        <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($totales['cuotas_total']) }}</td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                    </tr>
        
                    <tr style="background-color:#585858;color:#fff;">
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black">Total Abonado</td>
                        <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($totales['total_abonado']) }}</td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                    </tr>
                    <tr style="background-color:#F3CAD5 ;">
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black">Saldo Pendiente</td>
                        <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($totales['saldo_pendiente']) }}</td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                    </tr>
                    <tr style="background-color:#dcecb0;">
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black">Saldo a Favor</td>
                        <td style="text-align:right;" class="text-center text-black">${{ App\Http\Controllers\MoneyController::main($totales['saldo_a_favor']) }}</td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                        <td style="text-align:center;" class="text-center text-black"></td>
                    </tr>
                </tbody>
                <tfoot id="TABLE_ITEMS_CARTERA_TFOOT">
    
                </tfoot>
            </table>
        </div>
    </div>
    
</div>