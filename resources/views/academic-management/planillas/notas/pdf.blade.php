@extends('layouts.pdf')

@section('content')
@php
    date_default_timezone_set("America/Bogota");
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    * {
        font-family: 'Poppins', sans-serif !important;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .container-fluid {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    
    .footer-planilla {
        display: block !important;
    }
    
    .title-planilla {
        text-align: center;
        margin: 40px 0 6px 0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .info-section {
        margin-bottom: 6px;
        padding: 2px 0;
        background-color: #ffffff;
        border: none;
        text-align: center;
        width: 100%;
    }
    
    .info-row {
        display: block;
        margin-bottom: 1px;
        font-size: 9px;
        line-height: 1.1;
        text-align: center;
        width: 100%;
    }
    
    .info-row span {
        display: inline;
    }
    
    .info-label {
        font-weight: 400;
        color: #666;
        margin-right: 5px;
    }
    
    .info-value {
        font-weight: 600;
        color: #000;
        margin-right: 20px;
    }
    
    .table-container {
        margin-bottom: 120px; /* Espacio para el footer */
        width: 100%;
        box-sizing: border-box;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
        font-size: 11px;
        box-sizing: border-box;
    }
    
    table, th, td {
        border: 1px solid #000 !important;
    }
    
    th {
        background-color: #e9ecef;
        font-weight: 600;
        text-align: center;
        padding: 4px 3px;
        font-size: 11px;
        line-height: 1.3;
    }
    
    td {
        padding: 3px 4px;
        text-align: left;
        font-size: 11px;
        line-height: 1.3;
    }
    
    /* # */
    td:nth-child(1) {
        text-align: center;
        width: 4%;
        padding: 3px;
    }
    
    /* Nombre del Estudiante */
    td:nth-child(2) {
        width: 36%;
        padding: 3px 4px;
    }
    
    /* 20% */
    td:nth-child(3) {
        width: 10%;
        text-align: center;
        padding: 3px;
    }
    
    /* 30% */
    td:nth-child(4) {
        width: 10%;
        text-align: center;
        padding: 3px;
    }
    
    /* 50% */
    td:nth-child(5) {
        width: 10%;
        text-align: center;
        padding: 3px;
    }
    
    /* Definitiva */
    td:nth-child(6) {
        width: 15%;
        text-align: center;
        padding: 3px;
    }
    
    /* Fallas */
    td:nth-child(7) {
        width: 15%;
        text-align: center;
        padding: 3px;
    }
    
    tbody tr {
        height: 24px;
    }
    
    tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .footer-planilla {
        position: absolute;
        bottom: 30px;
        left: 20px;
        right: 20px;
        padding: 8px 0;
        width: calc(100% - 40px);
        font-size: 9px;
        font-family: 'Poppins', sans-serif;
        margin-top: 15px;
        min-height: 100px;
        box-sizing: border-box;
    }
    
    .observaciones-label {
        font-weight: 600;
        color: #000;
        margin-bottom: 3px;
        font-size: 9px;
    }
    
    .observaciones-line {
        border-top: 1px dashed #666;
        margin-top: 3px;
        margin-bottom: 25px;
        padding-top: 3px;
        width: 100%;
    }
    
    .firma-line {
        border-top: 1px solid #666;
        width: 33%;
        margin-bottom: 3px;
    }
    
    .firma-label {
        font-weight: 600;
        color: #000;
        margin-top: 3px;
        margin-bottom: 5px;
        font-size: 9px;
    }
    
    .footer-bottom {
        margin-top: 5px;
        font-size: 9px;
        color: #000 !important;
        width: 100%;
        min-height: 12px;
        overflow: visible;
    }
    
    @media print {
        .no-break {
            page-break-inside: avoid;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }
        
        .footer-planilla {
            position: fixed;
            bottom: 40px;
        }
    }
</style>

<h2 class="title-planilla">PLANILLA DE NOTAS</h2>

<div class="info-section">
    <div class="info-row">
        <span class="info-label">PROGRAMA:</span>
        <span class="info-value">{{ $programa }}</span>
        <span class="info-label">MODULO:</span>
        <span class="info-value">{{ $modulo }}</span>
        <span class="info-label">FECHA DE INICIO:</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}</span><br>
        <span class="info-label">FECHA DE FINALIZACIÓN:</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">DOCENTE:</span>
        <span class="info-value">{{ $docente ?? '' }}</span>
        <span class="info-label">ÚLTIMA FECHA PARA ENTREGA DE NOTAS:</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }}</span>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre del Estudiante</th>
                <th>20%</th>
                <th>30%</th>
                <th>50%</th>
                <th>Definitiva</th>
                <th>Fallas</th>
            </tr>
        </thead>
        <tbody>
            @if($estudiantes->count() > 0)
                @foreach($estudiantes as $index => $estudiante)
                    <tr class="no-break">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $estudiante->nombre_completo }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        <em>No se encontraron estudiantes matriculados con los criterios seleccionados.</em>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="footer-planilla">
    <div class="observaciones-label">OBSERVACIONES</div>
    <div class="observaciones-line"></div>
    <div class="firma-line"></div>
    <div class="firma-label">FIRMA DEL DOCENTE</div>
    <div class="footer-bottom" style="width: 100%; margin-top: 8px; font-size: 12px; color: #000;">
        <div class="date" style="float: left; display: block; color: #000; font-size: 12px;">{{ date('d-m-Y') }}</div>
        <div class="page-number" style="float: right; display: block; text-align: right; color: #000; font-size: 12px;">&nbsp;</div>
        <div style="clear: both; height: 0;"></div>
    </div>
</div>

@endsection
