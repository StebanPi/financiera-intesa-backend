@extends('dash.app')

@section('page', 'Bases Diarias')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-dollar-sign mr-2"></i> Registrar Bases Diarias</h4>
            </div>
            <div class="card-body">
                @php
                    $missing_dates = $missing_dates ?? session('missing_dates', []);
                    if (!is_array($missing_dates)) {
                        $missing_dates = [];
                    }
                @endphp

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (count($missing_dates) > 0)
                    @php
                        $missingDatesMessage = 'Faltan bases diarias para las siguientes fechas:<br><ul style="margin-top: 10px; padding-left: 20px;">';
                        foreach ($missing_dates as $date) {
                            $missingDatesMessage .= '<li>' . date('d/m/Y', strtotime($date)) . '</li>';
                        }
                        $missingDatesMessage .= '</ul>';
                    @endphp
                    <x-alert-modal 
                        type="warning" 
                        title="Atención"
                        :message="$missingDatesMessage" 
                        id="missing-dates" />
                @endif

                <form method="POST" action="{{ route('accounting.cash-bases') }}">
                    @csrf
                    <div id="bases-container">
                        @if (count($missing_dates) > 0)
                            @foreach ($missing_dates as $index => $date)
                                <div class="row mb-3 base-row">
                                    <div class="col-md-3">
                                        <label>Fecha</label>
                                        <input type="date" name="bases[{{ $index }}][fecha]" class="form-control" value="{{ $date }}" readonly required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Base de Efectivo</label>
                                        <input type="number" name="bases[{{ $index }}][base_efectivo]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Base de Banco</label>
                                        <input type="number" name="bases[{{ $index }}][base_banco]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>No hay fechas faltantes. Puedes agregar una base diaria manualmente.
                            </div>
                            <div class="row mb-3 base-row">
                                <div class="col-md-3">
                                    <label>Fecha</label>
                                    <input type="date" name="bases[0][fecha]" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Base de Efectivo</label>
                                    <input type="number" name="bases[0][base_efectivo]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Base de Banco</label>
                                    <input type="number" name="bases[0][base_banco]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-info" id="add-more-base" style="display: {{ count($missing_dates) > 0 ? 'none' : 'inline-block' }};">
                            <i class="fas fa-plus mr-2"></i> Agregar Otra Fecha
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Guardar Bases
                        </button>
                        <a href="{{ route('accounting.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let baseIndex = {{ count($missing_dates ?? []) > 0 ? count($missing_dates) : 1 }};

    document.getElementById('add-more-base')?.addEventListener('click', function() {
        const container = document.getElementById('bases-container');
        const newRow = document.createElement('div');
        newRow.className = 'row mb-3 base-row';
        newRow.innerHTML = `
            <div class="col-md-3">
                <label>Fecha</label>
                <input type="date" name="bases[${baseIndex}][fecha]" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label>Base de Efectivo</label>
                <input type="number" name="bases[${baseIndex}][base_efectivo]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
            </div>
            <div class="col-md-4">
                <label>Base de Banco</label>
                <input type="number" name="bases[${baseIndex}][base_banco]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
            </div>
        `;
        container.appendChild(newRow);
        baseIndex++;
    });
</script>
@endpush
@endsection
