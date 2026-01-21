@extends('dash.app')

@section('page', ' Recibos de Egreso')

@section('content')

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="color: #374151; font-weight: 600;">
                    <i class="fa-solid fa-file-invoice-dollar text-danger mr-2"></i>
                    Recibos de Egreso
                </h4>
                <div>
                    <a href="{{ route('egreso.receipts.create') }}" class="btn btn-danger" style="border-radius: 8px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                        <i class="fa-solid fa-plus-circle mr-2"></i>Nuevo Recibo de Egreso
                    </a>
                    <a href="{{ route('egreso.providers.index') }}" class="btn btn-secondary" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa-solid fa-building mr-2"></i>Gestionar Proveedores
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; border-radius: 12px 12px 0 0; padding: 20px;">
                    <h5 class="mb-0 d-flex align-items-center" style="color: #374151; font-weight: 600;">
                        <i class="fa-solid fa-list mr-2"></i>
                        Lista de Recibos de Egreso
                    </h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="receiptsTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>N° Recibo</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Concepto</th>
                                    <th>Forma de Pago</th>
                                    <th>Valor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($receipts as $receipt)
                                    <tr>
                                        <td>{{ $receipt->no_recibo }}</td>
                                        <td>
                                            @if($receipt->fecha_recibo)
                                                {{ \Carbon\Carbon::parse($receipt->fecha_recibo)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $receipt->provider->nombre ?? 'N/A' }}</td>
                                        <td>{{ $receipt->concepto }}</td>
                                        <td>
                                            <span class="badge badge-{{ $receipt->forma == 'Efectivo' ? 'success' : 'info' }}">
                                                {{ $receipt->forma }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($receipt->valor, 2, ',', '.') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" title="Imprimir" onclick="openPrintModal('{{ route('financial.receipt.print', ['type' => 'egreso', 'id' => $receipt->id]) }}')">
                                                <i class="fa-solid fa-print"></i> Imprimir
                                            </button>
                                            <a href="{{ route('egreso.receipts.edit', $receipt->no_recibo) }}" class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fa-solid fa-edit"></i> Editar
                                            </a>
                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin')))
                                            <form method="POST" action="{{ route('egreso.receipts.destroy', $receipt->no_recibo) }}" style="display: inline;" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar el recibo de egreso #{{ $receipt->no_recibo }}? Esta acción NO se puede deshacer.', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="alert alert-info mb-0">
                                                <i class="fa-solid fa-info-circle mr-2"></i>
                                                No hay recibos de egreso registrados.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.print-modal')

@endsection

@section('scripts')
@if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin')))
<script src="{{ asset('js/confirm-modal.js') }}"></script>
@endif
<script>
// Inicializar DataTables
$(document).ready(function() {
    $('#receiptsTable').DataTable({
        language: window.DataTablesSpanish || {},
        order: [[1, 'desc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: 6 } // Deshabilitar ordenamiento en columna de acciones
        ]
    });
});
</script>
@endsection
