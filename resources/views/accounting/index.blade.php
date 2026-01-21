@extends('dash.app')

@section('page', ' Contabilidad')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-calculator mr-2"></i> Módulo de Contabilidad</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.abonos') }}" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            Informe de Abonos
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.otros-ingresos') }}" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-coins mr-2"></i>
                            Informe de Otros Ingresos
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.total-ingresos') }}" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-arrow-up mr-2"></i>
                            Informe Total Ingresos
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.total-egresos') }}" class="btn btn-danger btn-block btn-lg">
                            <i class="fas fa-arrow-down mr-2"></i>
                            Informe Total Egresos
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.arqueo-diario') }}" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-calendar-day mr-2"></i>
                            Arqueo Diario
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.informe-semanal') }}" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-calendar-week mr-2"></i>
                            Informe Semanal
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ route('accounting.informe-mensual') }}" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Informe Mensual
                        </a>
                    </div>
                </div>
                
                <hr class="my-4">
                
                @if(auth()->check() && auth()->user()->hasRole('super-admin'))
                <div class="row">
                    <div class="col-12">
                        @php
                            $initialBalance = \App\Models\InitialBalance::getActive();
                        @endphp
                        @if($initialBalance)
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle mr-2"></i> Base Inicial Configurada</h5>
                                <p class="mb-2">
                                    <strong>Fecha de inicio:</strong> {{ date('d/m/Y', strtotime($initialBalance->start_date)) }} | 
                                    <strong>Efectivo:</strong> ${{ number_format($initialBalance->base_efectivo, 0, ',', '.') }} | 
                                    <strong>Banco:</strong> ${{ number_format($initialBalance->base_banco, 0, ',', '.') }}
                                    <br><small class="text-muted">Esta configuración se usa únicamente para Informes Semanal y Mensual. El Arqueo Diario utiliza bases diarias.</small>
                                </p>
                                <a href="{{ route('accounting.base-inicial') }}" class="btn btn-success">
                                    <i class="fas fa-edit mr-2"></i>
                                    Actualizar Base Inicial
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle mr-2"></i> Base Inicial No Configurada</h5>
                                <p class="mb-2">
                                    <strong>Importante:</strong> Es necesario configurar la base inicial para generar los <strong>informes semanales y mensuales</strong>. 
                                    <br><small class="text-muted">Nota: El Arqueo Diario utiliza bases diarias y no requiere esta configuración.</small>
                                </p>
                                <a href="{{ route('accounting.base-inicial') }}" class="btn btn-warning">
                                    <i class="fas fa-coins mr-2"></i>
                                    Configurar Base Inicial
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                
                <hr class="my-4">
                @endif
                
                <div class="row">
                    <div class="col-12">
                        @php
                            $todayBase = $todayBase ?? null;
                            $today = date('Y-m-d');
                            $todayFormatted = date('d/m/Y');
                        @endphp
                        @if($todayBase)
                            <div class="alert alert-info">
                                <h5><i class="fas fa-calendar-day mr-2"></i> Base Diaria del Día ({{ $todayFormatted }})</h5>
                                <p class="mb-2">
                                    <strong>Efectivo:</strong> ${{ number_format($todayBase->base_efectivo, 2, ',', '.') }} | 
                                    <strong>Banco:</strong> ${{ number_format($todayBase->base_banco, 2, ',', '.') }}
                                    <br><small class="text-muted">Última actualización: {{ $todayBase->updated_at ? $todayBase->updated_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                </p>
                                <a href="{{ route('accounting.cash-bases', ['missing_dates' => [$today]]) }}" class="btn btn-info">
                                    <i class="fas fa-edit mr-2"></i>
                                    Actualizar Base del Día
                                </a>
                                <a href="{{ route('accounting.arqueo-diario', ['fecha' => $today]) }}" class="btn btn-success">
                                    <i class="fas fa-calendar-day mr-2"></i>
                                    Ver Arqueo del Día
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle mr-2"></i> Base Diaria del Día No Registrada ({{ $todayFormatted }})</h5>
                                <p class="mb-2">
                                    <strong>Importante:</strong> No hay base diaria registrada para el día de hoy. 
                                    Es necesario registrar la base diaria para generar el Arqueo Diario.
                                </p>
                                <a href="{{ route('accounting.cash-bases', ['missing_dates' => [$today]]) }}" class="btn btn-warning">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Registrar Base Diaria del Día
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
