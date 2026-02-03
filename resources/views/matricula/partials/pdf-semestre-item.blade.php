@php
    $valorSemestre = cleanMoneyValue($cost->valor_semestre ?? 0);
    $descuento = cleanMoneyValue($cost->descuento ?? 0);
    $saldoFinanciar = cleanMoneyValue($cost->saldo_financiar ?? 0);
    $valorCuotas = cleanMoneyValue($cost->valor_cuotas ?? 0);
@endphp

@if(isset($showTitle) && $showTitle)
    <div class="semestre-number">Semestre {{ $cost->numero_semestre ?? 'N/A' }}</div>
@endif

<div class="financing-row">
    <div class="financing-label">Costos por semestre:</div>
    <div class="financing-value">{{ formatMoney($valorSemestre) }}</div>
</div>

<div class="financing-row">
    <div class="financing-label">Descuento:</div>
    <div class="financing-value">{{ formatMoney($descuento) }}</div>
</div>



<div class="financing-row">
    <div class="financing-label">Valor a financiar:</div>
    <div class="financing-value">{{ formatMoney($saldoFinanciar) }}</div>
</div>


