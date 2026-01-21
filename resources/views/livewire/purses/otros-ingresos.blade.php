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
    <table id="table" style="width: 100%;" class=""> 
        <thead style="background-color: #a6c307;color:white;" class="thead-secondary text-black text-center">
            <th scope="col">No. Recibo</th>
            <th scope="col">Fecha</th>
            <th scope="col">Concepto</th>
            <th scope="col">Elaborado Por</th>
            <th scope="col">Valor</th>
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
                    <td style="text-align:center;" class="text-center text-black">{{ $item->elaborado_por}}</td>
                    <td style="text-align:right;" class="d-flex font-weight-bold number-lg text-black" >@livewire('setting.money', ['money' => $item->valor])</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;">Total</td>
                <td style="text-align: right;">@livewire('setting.money', ['money' => $suma])</td>
            </tr>
        </tfoot>
       
    </table>
</div>
