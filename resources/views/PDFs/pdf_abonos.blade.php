@extends('layouts.pdf')

@section('content')
    @php
        date_default_timezone_set("America/Bogota");
    @endphp
    <div class="container text-center" style="margin-bottom: 6px;">
        <h2 style="text-align: center; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 700; margin-bottom: 4px; margin-top: 3px; text-transform: uppercase;">
            ABONOS
        </h2>
    </div>

    @if(isset($student) && count($student) > 0 && isset($student[0]))
    <div class="d-flex p-2" style="background-color: #e7e9eb; padding: 5px 8px; font-family: 'Poppins', sans-serif; font-size: 12px; margin-bottom: 6px;">
        <div class="mr-3 text-black" style="margin-right: 12px; font-size: 12px;">
            Cedula: <b>{{ $student[0]->cedula ?? 'N/A' }}</b>
        </div>
        <div class="mr-3 text-black" style="margin-right: 12px; font-size: 12px;">
            Estudiante: <b>{{ $student[0]->nombre ?? 'N/A' }}</b>
        </div>
        <div class="mr-3 text-black" style="margin-right: 12px; font-size: 12px;">
            Programa: <b>{{ $student[0]->nombre_programa ?? 'N/A' }}</b>
        </div>
    </div>
    @else
    <div class="d-flex p-2" style="background-color: #fee2e2; padding: 5px 8px; font-family: 'Poppins', sans-serif; font-size: 12px; border: 1px solid #ef4444; border-radius: 4px; margin-bottom: 6px;">
        <div class="text-danger" style="font-size: 12px;">
            <i class="fa-solid fa-exclamation-triangle mr-2"></i>No se encontró información del estudiante.
        </div>
    </div>
    @endif
    
    @php
        $suma = 0;
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
            font-size: 12px !important;
            margin-top: 4px !important;
        }
        
        table {
            border: 1px solid black !important;
            border-collapse: collapse !important;
        }
        
        table, th, td {
        border: 1px solid black !important;
            font-family: 'Poppins', sans-serif !important;
            border-width: 1px !important;
        }
        
        th {
            background-color: #a6c307 !important;
            color: white !important;
            font-weight: 600 !important;
            text-align: center !important;
            font-size: 12px !important;
            padding: 4px 3px !important;
            line-height: 1.2 !important;
            vertical-align: middle !important;
            border: 1px solid black !important;
        }
        
        td {
            padding: 3px !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            vertical-align: middle !important;
            border: 1px solid black !important;
        }
        
        table tr:nth-child(odd) {
            background-color: #fff;
        }
        
        table tr:nth-child(even) {
            background-color: #e7e9eb;
        }
    </style>
    <table id="table" style="width: 100%; font-size: 12px; border: 1px solid black;" class=""> 
        <thead class="thead-secondary text-black text-center">
            <th scope="col" style="width: 15%; padding: 4px 3px; border: 1px solid black;">No. Recibo</th>
            <th scope="col" style="width: 18%; padding: 4px 3px; border: 1px solid black;">Fecha</th>
            <th scope="col" style="width: 25%; padding: 4px 3px; border: 1px solid black;">Concepto</th>
            <th scope="col" style="width: 22%; padding: 4px 3px; border: 1px solid black;">Elaborado Por</th>
            <th scope="col" style="width: 20%; padding: 4px 3px; border: 1px solid black;">Valor</th>
        </thead>
        <tbody>
            @if(isset($entries) && count($entries) > 0)
                @foreach ($entries as $item)
                @php
                    $suma = $suma + $item->valor;
                @endphp
                    <tr>
                        <td style="text-align:center; padding: 3px; border: 1px solid black;">{{ $item->no_recibo}}</td>
                        <td style="text-align:center; padding: 3px; border: 1px solid black;">{{ App\Http\Controllers\DateController::getMesSubtr($item->fecha_recibo) }}</td>
                        <td style="text-align:center; padding: 3px; border: 1px solid black;">{{ $item->concepto}}</td>
                        <td style="text-align:center; padding: 3px; border: 1px solid black;">{{ $item->descripcion}}</td>
                        <td style="text-align:right; padding: 3px; border: 1px solid black; font-weight: 600;">${{ App\Http\Controllers\MoneyController::main($item->valor) }}</td>
                    </tr>
                @endforeach
            @else
                {{-- Agregar fila vacía cuando no hay abonos para que se vean los bordes correctamente --}}
                <tr>
                    <td style="text-align:center; padding: 8px; border: 1px solid black; color: #999;">Sin abonos registrados</td>
                    <td style="text-align:center; padding: 8px; border: 1px solid black;"></td>
                    <td style="text-align:center; padding: 8px; border: 1px solid black;"></td>
                    <td style="text-align:center; padding: 8px; border: 1px solid black;"></td>
                    <td style="text-align:center; padding: 8px; border: 1px solid black;"></td>
                </tr>
            @endif
            @php
                $saldo = 0;
                if(isset($cost) && count($cost) > 0 && isset($cost[0])) {
                    $saldo = intval($cost[0]->valor_neto) - intval($suma);
                }
            @endphp
        </tbody>
    </table>

    <div style="width: 50%; margin-top: 15px; display: inline-block; float: right;">
        <table style="width: 100%; font-size: 12px; border-collapse: collapse; border: 1px solid black;">
            <tr>
                <td style="text-align: center; padding: 4px; border: 1px solid black; font-size: 12px; background-color: #f8f9fa;">Total Abonado</td>
                <td style="text-align: right; padding: 4px; border: 1px solid black; font-size: 12px; font-weight: 600; background-color: #f8f9fa;">${{ App\Http\Controllers\MoneyController::main($suma) }}</td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 4px; border: 1px solid black; font-size: 12px; background-color: #f8f9fa;">Saldo Pendiente</td>
                <td style="text-align: right; padding: 4px; border: 1px solid black; font-size: 12px; font-weight: 600; background-color: #f8f9fa;">${{ App\Http\Controllers\MoneyController::main($saldo) }}</td>
            </tr>
        </table>   
    </div>
    
@endsection
