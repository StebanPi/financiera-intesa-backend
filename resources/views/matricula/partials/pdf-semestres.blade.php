@php
    // Asegurar que los indices son 0 y 1 si es una colección, o reindexar si es array
    if(is_object($semestres) && method_exists($semestres, 'values')) {
        $semestres = $semestres->values();
    } else {
        $semestres = array_values((array)$semestres);
    }
    $count = count($semestres);
@endphp

@if($count === 1)
    {{-- Un solo semestre: estructura completa --}}
    <div class="financing-full">
        @include('matricula.partials.pdf-semestre-item', ['cost' => $semestres[0], 'showTitle' => true])
    </div>
@else
    {{-- Dos semestres: dividido a la mitad --}}
    <div class="financing-split">
        <div class="financing-column financing-left">
            @include('matricula.partials.pdf-semestre-item', ['cost' => $semestres[0], 'showTitle' => true])
        </div>
        <div class="financing-separator"></div>
        <div class="financing-column financing-right">
            @include('matricula.partials.pdf-semestre-item', ['cost' => $semestres[1], 'showTitle' => true])
        </div>
    </div>
@endif
