<div class="text-black">
    @php
        $suma = 0;
        $abono = 0;
        $otrosAbonos = 0;
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
    <table id="table" style="width: 100%;" class=""> 
        <thead  class="thead-secondary text-black text-center">
            <tr style="background-color: #ffffff;color:#000;">
                <th colspan="5" style="text-align:center;">Abonos a matricula</th>
            </tr>
            <tr style="background-color: #a6c307;color:white;">
                <th scope="col">No. Recibo</th>
                <th scope="col">Fecha</th>
                <th scope="col">Concepto</th>
                <th scope="col">Comentario</th>
                <th scope="col">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $item)
            @php
                $suma = $suma + $item->valor;
            @endphp
                <tr style="">
                    <td style="text-align:center;" class="text-center text-black">{{ $item->no_recibo}}</td>
                    <td style="text-align:center;" class="text-center text-black">@livewire('setting.date', ['date' => $item->fecha_recibo])</td>
                    <td style="text-align:center;" class="text-center text-black">{{ $item->concepto}}</td>
                    <td style="text-align:center;" class="text-center text-black">{{ $item->descripcion}}</td>
                    <td style="text-align:right;" class="d-flex font-weight-bold number-lg text-black" >@livewire('setting.money', ['money' => $item->valor])</td>
                </tr>
            @endforeach
            @php
                $abono = $suma; 
                $saldo = intval($cost[0]->valor_neto) - intval($suma);
            @endphp

            <tr style="background-color: #CBF993;">
                    <td style="text-align:center;" class="text-center text-black"></td>
                    <td style="text-align:center;" class="text-center text-black"></td>
                    <td style="text-align:center;" class="text-center text-black"></td>
                    <td style="text-align:center;" class="text-center text-black">Total Abonado</td>
                    <td style="text-align:right;" class="d-flex font-weight-bold number-lg text-black" >@livewire('setting.money', ['money' => $suma])</td>
                </tr>
            <tr style="background-color: #F9BBB0;">
                    <td style="text-align:center;" class="text-center text-black"></td>
                    <td style="text-align:center;" class="text-center text-black"></td>
                    <td style="text-align:center;" class="text-center text-black"></td>
                    <td style="text-align:center;" class="text-center text-black">Saldo Pendiente</td>
                    <td style="text-align:right;" class="d-flex font-weight-bold number-lg text-black" >@livewire('setting.money', ['money' => $saldo])</td>
                </tr>
        </tbody>
       
    </table>

    @php
        $suma = 0;
    @endphp

    <br>
    <table id="table" style="width: 100%;" class=""> 
        <thead class="thead-secondary text-black text-center">
            <tr style="background-color: #ffffff;color:#000;">
                <th colspan="5" style="text-align:center;">Otros Abonos</th>
            </tr>
            <tr style="background-color: #a6c307;color:white;">
                <th scope="col">No. Recibo</th>
                <th scope="col">Fecha</th>
                <th scope="col">Concepto</th>
                <th scope="col">Comentario</th>
                <th scope="col">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($others as $item)
            @php
                $suma = $suma + $item->valor;
            @endphp
                <tr style="">
                    <td style="text-align:center;" class="text-center text-black">{{ $item->no_recibo}}</td>
                    <td style="text-align:center;" class="text-center text-black">@livewire('setting.date', ['date' => $item->fecha_recibo])</td>
                    <td style="text-align:center;" class="text-center text-black">{{ $item->concepto}}</td>
                    <td style="text-align:center;" class="text-center text-black">{{ $item->descripcion}}</td>
                    <td style="text-align:right;" class="d-flex font-weight-bold number-lg text-black" >@livewire('setting.money', ['money' => $item->valor])</td>
                </tr>
            @endforeach
            @php
                $otrosAbonos = $suma;
            @endphp
        </tbody>
        <tfoot>
            <tr style="background-color: #E3FBC3;">
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;">Total</td>
                <td style="text-align: right;">@livewire('setting.money', ['money' => $suma])</td>
            </tr>
        </tfoot>
       
    </table>
            @php
                $sumaTotal = intval($abono) + intval($otrosAbonos);
            @endphp

            <br>
    <table style="width: 100%;">
        <thead class="thead-secondary text-black text-center">
            <tr style="background-color: #ffffff;color:#000;">
                <th colspan="5" style="text-align:center;">Total Cancelado (Abonos a matricula + Otros Abonos)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color:#E3FBC3;color:#000;">
                <th colspan="4">Total </th>
                <th scope="col" style="text-align: right;">@livewire('setting.money', ['money' => $sumaTotal])</th>
            </tr>
        </tbody>    
    </table>

    
</div>
