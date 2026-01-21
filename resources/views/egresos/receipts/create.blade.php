@extends('dash.app')

@section('page', ' Nuevo Recibo de Egreso')

@section('content')

<div class="modern-receipt-container">
    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('egreso.receipts.store') }}" id="formatReceiptForm">
                <div class="card modern-receipt-card">
                    <!-- Header Moderno -->
                    <div class="modern-receipt-header receipt-header-discharge">
                        <div class="row align-items-center">
                            <div class="col-lg-3 col-md-12 mb-3 mb-lg-0">
                                <div class="receipt-date-section">
                                    <div class="receipt-date-label">
                                        <i class="fa-solid fa-calendar-days mr-2"></i>Fecha
                                    </div>
                                    @php
                                        $fechaActual = date('Y-m-d');
                                        $fechaParts = explode('-', $fechaActual);
                                    @endphp
                                    <div class="receipt-date-display">
                                        <div class="date-item">
                                            <span class="date-value">{{ $fechaParts[2] }}</span>
                                            <span class="date-label">Día</span>
                                        </div>
                                        <div class="date-separator">/</div>
                                        <div class="date-item">
                                            <span class="date-value">{{ $fechaParts[1] }}</span>
                                            <span class="date-label">Mes</span>
                                        </div>
                                        <div class="date-separator">/</div>
                                        <div class="date-item">
                                            <span class="date-value">{{ $fechaParts[0] }}</span>
                                            <span class="date-label">Año</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0 text-center">
                                <h3 class="receipt-title mb-0">
                                    <i class="fa-solid fa-file-invoice mr-2"></i>
                                    Recibo de Egreso
                                </h3>
                            </div>
                            <div class="col-lg-3 col-md-12 text-center text-lg-right">
                                <div class="receipt-consecutive-section">
                                    <div class="consecutive-label">N° Recibo</div>
                                    <div class="consecutive-display justify-content-center justify-content-lg-end">
                                        <span id="showConsecutiveReceipts" class="consecutive-number">
                                            {{ $consecutive->num_current ?? '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Body del Formulario -->
                    <div class="card-body modern-receipt-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <input type="hidden" name="no_recibo" value="{{ $consecutive->num_current ?? '' }}">
                        @csrf

                        <!-- Primera Fila: Proveedor y Concepto -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-building mr-2"></i>Proveedor
                                        <small class="text-danger">*</small>
                                    </label>
                                    <select name="proveedor_id" class="form-control no-selectpicker" required data-no-selectpicker="true">
                                        <option value="">Seleccione un proveedor...</option>
                                        @foreach ($providers as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->nombre }} - {{ $provider->cedula ?? 'N/A' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-tag mr-2"></i>Concepto
                                        <small class="text-danger">*</small>
                                    </label>
                                    <select name="concepto" id="changeConceptoEgreso" class="form-control no-selectpicker" required data-no-selectpicker="true">
                                        <option value="">Seleccione un concepto...</option>
                                        @foreach ($concepts as $concept)
                                            <option value="{{ $concept->id }}" 
                                                    @if($concept->debe) debe="{{ $concept->debe }}" @endif
                                                    @if($concept->haber) haber="{{ $concept->haber }}" @endif>
                                                {{ $concept->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Segunda Fila: Descripción -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                    </label>
                                    <textarea name="descripcion" class="modern-textarea" rows="3" placeholder="Ingrese la descripción del recibo..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tercera Fila: Debe, Haber y Elaborado Por -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-arrow-down mr-2"></i>Debe
                                    </label>
                                    <div class="modern-select-wrapper">
                                        <input readonly type="text" id="SelectDebeEgreso" class="modern-input modern-input-readonly" placeholder="Se selecciona automáticamente">
                                        <select name="debe" id="debeAttr2Egreso" class="modern-select-hidden">
                                            @foreach (($debe ?? []) as $item)
                                                <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-arrow-up mr-2"></i>Haber
                                    </label>
                                    <div class="modern-select-wrapper">
                                        <input readonly type="text" id="SelectHaberEgreso" class="modern-input modern-input-readonly" placeholder="Se selecciona automáticamente">
                                        <select name="haber" id="haberAttr2Egreso" class="modern-select-hidden">
                                            @foreach (($haber ?? []) as $item)
                                                <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-user-check mr-2"></i>Elaborado Por
                                        <small class="text-danger">*</small>
                                    </label>
                                    <select name="elaborado_por" class="form-control no-selectpicker" required data-no-selectpicker="true">
                                        <option value="">Seleccione...</option>
                                        @foreach ($elaborados as $item)
                                            <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Cuarta Fila: Forma de Pago y Valor -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-money-bill-wave mr-2"></i>Forma de Pago
                                        <small class="text-danger">*</small>
                                    </label>
                                    <div class="modern-radio-group">
                                        <div class="custom-radio-wrapper">
                                            <input name="forma" class="custom-radio-input" type="radio" value="Efectivo" checked id="formaEfectivo">
                                            <label class="custom-radio-label" for="formaEfectivo">
                                                <span class="radio-custom"></span>
                                                <span class="radio-text">Efectivo</span>
                                            </label>
                                        </div>
                                        <div class="custom-radio-wrapper">
                                            <input name="forma" class="custom-radio-input" type="radio" value="Bancos" id="formaBancos">
                                            <label class="custom-radio-label" for="formaBancos">
                                                <span class="radio-custom"></span>
                                                <span class="radio-text">Bancos</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-dollar-sign mr-2"></i>Valor
                                        <small class="text-danger">*</small>
                                    </label>
                                    <div class="modern-value-input-wrapper">
                                        <div class="value-sign">$</div>
                                        <input name="valor" type="text" class="modern-value-input miles" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo oculto para fecha -->
                        <input type="hidden" name="fecha_recibo" value="{{ date('Y-m-d') }}">

                        <!-- Script para prevenir selectpicker ANTES de que se cargue -->
                        <script>
                        // Este script debe ejecutarse ANTES de que se cargue custom.min.js
                        (function() {
                            // Interceptar selectpicker antes de que se defina
                            if (typeof jQuery !== 'undefined') {
                                // Guardar referencia original
                                var originalSelectpicker = null;
                                
                                // Interceptar cuando se defina selectpicker
                                Object.defineProperty(jQuery.fn, 'selectpicker', {
                                    get: function() {
                                        if (originalSelectpicker) {
                                            return function() {
                                                // Si el select tiene la clase no-selectpicker, no inicializar
                                                if (this.hasClass && this.hasClass('no-selectpicker')) {
                                                    return this;
                                                }
                                                return originalSelectpicker.apply(this, arguments);
                                            };
                                        }
                                        return function() {
                                            if (this.hasClass && this.hasClass('no-selectpicker')) {
                                                return this;
                                            }
                                            return this;
                                        };
                                    },
                                    set: function(value) {
                                        originalSelectpicker = value;
                                    },
                                    configurable: true
                                });
                            }
                        })();
                        </script>

                        <!-- Quinta Fila: Botones -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="modern-submit-section d-flex gap-2">
                                    <button type="submit" class="btn btn-modern-submit">
                                        <i class="fa-solid fa-plus-circle mr-2"></i>Crear Recibo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos Modernos para el Recibo de Egreso - Mismo diseño que recibos de terceros */
.modern-receipt-container {
    padding: 15px 0;
}

.modern-receipt-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
    overflow: hidden;
    background: #ffffff;
}

.modern-receipt-header {
    padding: 20px 25px;
    border-bottom: 2px solid rgba(34, 197, 94, 0.12);
}

.modern-receipt-header.receipt-header-discharge {
    background: linear-gradient(135deg, #86efac 0%, #bbf7d0 50%, #d1fae5 100%);
}

.receipt-date-section {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 10px rgba(34, 197, 94, 0.08);
    border: 2px solid rgba(255, 255, 255, 0.9);
}

.receipt-date-label {
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #15803d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.receipt-date-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}

.date-item {
    flex: 1;
    text-align: center;
    background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
    border-radius: 10px;
    padding: 8px 6px;
    border: 2px solid rgba(34, 197, 94, 0.2);
    box-shadow: 0 2px 6px rgba(34, 197, 94, 0.1);
}

.date-value {
    display: block;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.2;
    color: #15803d;
}

.date-label {
    display: block;
    font-size: 11px;
    color: #22c55e;
    margin-top: 4px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.date-separator {
    font-size: 18px;
    font-weight: 700;
    color: #86efac;
    margin: 0 2px;
}

.receipt-title {
    font-size: 26px;
    font-weight: 700;
    color: #15803d;
    text-shadow: 0 1px 3px rgba(255, 255, 255, 0.5);
    letter-spacing: 0.3px;
}

.receipt-consecutive-section {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 10px rgba(34, 197, 94, 0.08);
    border: 2px solid rgba(255, 255, 255, 0.9);
}

.consecutive-label {
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #15803d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.consecutive-display {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.consecutive-number {
    font-size: 28px;
    font-weight: 800;
    background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
    color: #15803d;
    padding: 8px 16px;
    border-radius: 10px;
    min-width: 80px;
    display: inline-block;
    text-align: center;
    border: 2px solid rgba(34, 197, 94, 0.3);
    box-shadow: 0 2px 6px rgba(34, 197, 94, 0.15);
}

.modern-receipt-body {
    padding: 25px;
    background: linear-gradient(to bottom, #f0fdf4 0%, #f7fef7 100%);
}

.modern-form-group {
    margin-bottom: 0;
}

.modern-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #15803d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.modern-label i {
    color: #22c55e;
    font-size: 15px;
}

.modern-input,
.modern-textarea {
    width: 100%;
    padding: 12px 16px;
    font-size: 16px;
    border: 2px solid #bbf7d0;
    border-radius: 10px;
    background: #ffffff;
    transition: all 0.3s ease;
    color: #1e293b;
}

.modern-input:focus,
.modern-textarea:focus {
    outline: none;
    border-color: #86efac;
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
    background: #ffffff;
}

.modern-input::placeholder,
.modern-textarea::placeholder {
    color: #94a3b8;
}

.modern-input-readonly {
    background: #eff6ff;
    cursor: not-allowed;
    color: #64748b;
    border-color: #dbeafe;
}

.modern-select-wrapper {
    position: relative;
    width: 100%;
}

.modern-select-hidden {
    position: absolute !important;
    left: -9999px !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
    z-index: -1 !important;
    display: none !important;
}

.modern-textarea {
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
    line-height: 1.5;
}

.modern-radio-group {
    display: flex;
    gap: 10px;
    margin-top: 6px;
    flex-wrap: wrap;
}

.custom-radio-wrapper {
    display: flex;
    align-items: center;
}

.custom-radio-input {
    display: none;
}

.custom-radio-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 10px 18px;
    background: #ffffff;
    border: 2px solid #bbf7d0;
    border-radius: 10px;
    transition: all 0.3s ease;
    user-select: none;
    box-shadow: 0 2px 5px rgba(34, 197, 94, 0.1);
}

.custom-radio-label:hover {
    border-color: #86efac;
    background: #f0fdf4;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
}

.custom-radio-input:checked + .custom-radio-label {
    border-color: #22c55e;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15), 0 4px 12px rgba(34, 197, 94, 0.2);
}

.radio-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #bbf7d0;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
    transition: all 0.3s ease;
    flex-shrink: 0;
    background: #ffffff;
}

.custom-radio-input:checked + .custom-radio-label .radio-custom {
    border-color: #22c55e;
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
}

.custom-radio-input:checked + .custom-radio-label .radio-custom::after {
    content: '';
    position: absolute;
    width: 8px;
    height: 8px;
    background: #ffffff;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.radio-text {
    font-size: 16px;
    font-weight: 600;
    color: #15803d;
}

.modern-value-input-wrapper {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 2px solid #bbf7d0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(34, 197, 94, 0.1);
}

.modern-value-input-wrapper:focus-within {
    border-color: #86efac;
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15), 0 4px 12px rgba(34, 197, 94, 0.2);
}

.value-sign {
    background: linear-gradient(135deg, #22c55e 0%, #4ade80 100%);
    color: #ffffff;
    padding: 12px 18px;
    font-size: 22px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 2px 0 6px rgba(34, 197, 94, 0.2);
}

.modern-value-input {
    flex: 1;
    border: none;
    padding: 12px 16px;
    font-size: 22px;
    font-weight: 700;
    color: #15803d;
    background: transparent;
}

.modern-value-input:focus {
    outline: none;
}

.modern-value-input::placeholder {
    color: #86efac;
    font-weight: 500;
}

.modern-submit-section {
    margin-top: 0;
}

.btn-modern-submit {
    padding: 14px 24px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #22c55e 0%, #4ade80 100%);
    color: #ffffff;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
}

.btn-modern-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.45);
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    color: #ffffff;
}

.btn-modern-submit:active {
    transform: translateY(-1px);
}

/* Los selects se mantienen con form-control (no se tocan) */
.form-control {
    padding: 12px 16px;
    font-size: 16px;
    border: 2px solid #bbf7d0;
    border-radius: 10px;
    background: #ffffff;
    transition: all 0.3s ease;
    color: #1e293b;
}

.form-control:focus {
    outline: none;
    border-color: #86efac;
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
    background: #ffffff;
}

/* Responsive */
@media (max-width: 991px) {
    .modern-receipt-header {
        padding: 25px 20px;
    }

    .receipt-title {
        font-size: 22px;
        margin: 15px 0;
    }

    .receipt-date-section,
    .receipt-consecutive-section {
        margin-bottom: 15px;
    }

    .consecutive-display {
        justify-content: center !important;
    }
}

@media (max-width: 768px) {
    .modern-receipt-header {
        padding: 20px 15px;
    }

    .receipt-title {
        font-size: 20px;
    }

    .receipt-date-display {
        flex-wrap: wrap;
        gap: 8px;
    }

    .date-separator {
        display: none;
    }

    .date-item {
        flex: 1;
        min-width: 70px;
    }

    .modern-receipt-body {
        padding: 25px 20px;
    }

    .modern-radio-group {
        flex-direction: column;
        gap: 10px;
    }

    .modern-value-input-wrapper {
        flex-direction: column;
    }

    .value-sign {
        width: 100%;
        text-align: center;
        border-radius: 12px 12px 0 0;
    }

    .modern-value-input {
        border-radius: 0 0 12px 12px;
    }
}
</style>

@endsection

@push('scripts')
<script>
// Función para limpiar opciones duplicadas
function limpiarDuplicadosSelect(selectElement) {
    var select = $(selectElement);
    var valores = {};
    var opciones = select.find('option');
    var opcionesAEliminar = [];
    
    opciones.each(function(index) {
        var valor = $(this).val();
        var texto = $(this).text().trim();
        var key = valor + '|' + texto;
        
        if (valores[key] !== undefined) {
            // Esta opción ya existe, marcar para eliminar
            opcionesAEliminar.push($(this));
        } else {
            valores[key] = index;
        }
    });
    
    // Eliminar opciones duplicadas
    opcionesAEliminar.forEach(function(opcion) {
        opcion.remove();
    });
    
    // También limpiar en el select original si está dentro de bootstrap-select
    var bootstrapSelect = select.parent().find('.bootstrap-select select');
    if (bootstrapSelect.length > 0) {
        var valoresBootstrap = {};
        bootstrapSelect.find('option').each(function() {
            var valor = $(this).val();
            var texto = $(this).text().trim();
            var key = valor + '|' + texto;
            
            if (valoresBootstrap[key] !== undefined) {
                $(this).remove();
            } else {
                valoresBootstrap[key] = true;
            }
        });
    }
}

// Prevenir que selectpicker se inicialice en nuestros selects
(function() {
    if (typeof jQuery !== 'undefined') {
        // Interceptar selectpicker antes de que se inicialice
        var originalSelectpicker = null;
        
        // Esperar a que selectpicker esté disponible
        var checkSelectpicker = setInterval(function() {
            if (typeof jQuery.fn.selectpicker !== 'undefined' && !originalSelectpicker) {
                originalSelectpicker = jQuery.fn.selectpicker;
                
                // Sobrescribir selectpicker
                jQuery.fn.selectpicker = function() {
                    // Si el select tiene la clase no-selectpicker, no inicializar
                    if (this.hasClass && this.hasClass('no-selectpicker')) {
                        return this;
                    }
                    return originalSelectpicker.apply(this, arguments);
                };
                
                clearInterval(checkSelectpicker);
            }
        }, 50);
        
        // Limpiar después de 5 segundos
        setTimeout(function() {
            clearInterval(checkSelectpicker);
        }, 5000);
    }
})();

$(document).ready(function() {
    // Limpiar duplicados inmediatamente
    $('select.no-selectpicker').each(function() {
        limpiarDuplicadosSelect(this);
    });
    
    // Destruir selectpicker si ya se inicializó
    function destruirSelectpicker() {
        if (typeof $.fn.selectpicker !== 'undefined') {
            $('select.no-selectpicker').each(function() {
                var $select = $(this);
                if ($select.hasClass('selectpicker')) {
                    try {
                        $select.selectpicker('destroy');
                    } catch(e) {}
                    $select.removeClass('selectpicker');
                }
                // Eliminar wrapper de bootstrap-select
                $select.parent().find('.bootstrap-select').remove();
                // Asegurar que el select esté visible
                $select.css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                });
            });
        }
    }
    
    destruirSelectpicker();
    
    // Aplicar formato de miles
    $('.miles').on('input', function() {
        var value = $(this).val().replace(/\./g, '');
        $(this).val(value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    });
    
    // Limpiar duplicados periódicamente por si selectpicker los agrega
    setInterval(function() {
        $('select.no-selectpicker').each(function() {
            limpiarDuplicadosSelect(this);
            destruirSelectpicker();
        });
    }, 500);
    
    // Llenar automáticamente debe y haber cuando se selecciona un concepto
    // Hacer la función global para que esté disponible en window.load
    window.changeInputsDebeyHaberEgreso = function(){
        const element = document.getElementById('changeConceptoEgreso');
        if(!element) {
            return;
        }
        if(!element.value || element.value === '') {
            $("#SelectDebeEgreso").val('');
            $("#SelectHaberEgreso").val('');
            $('#debeAttr2Egreso').val('');
            $('#haberAttr2Egreso').val('');
            return;
        }
        
        const selectedOption = $("#changeConceptoEgreso option:selected");
        const debe = selectedOption.attr('debe');
        const haber = selectedOption.attr('haber');
        
        if(debe && debe !== 'undefined' && debe !== '' && debe !== '0') {
            // Seleccionar la opción en el select hidden
            $('#debeAttr2Egreso').val(debe);
            
            // Obtener el texto de la opción seleccionada
            const debeOption = $('#debeAttr2Egreso > option[value="'+debe+'"]');
            const debeText = debeOption.text() || debeOption.html();
            
            if(debeText && debeText.trim() !== '') {
                $("#SelectDebeEgreso").val(debeText.trim());
            }
        } else {
            $("#SelectDebeEgreso").val('');
            $('#debeAttr2Egreso').val('');
        }
        
        if(haber && haber !== 'undefined' && haber !== '' && haber !== '0') {
            // Seleccionar la opción en el select hidden
            $('#haberAttr2Egreso').val(haber);
            
            // Obtener el texto de la opción seleccionada
            const haberOption = $('#haberAttr2Egreso > option[value="'+haber+'"]');
            const haberText = haberOption.text() || haberOption.html();
            
            if(haberText && haberText.trim() !== '') {
                $("#SelectHaberEgreso").val(haberText.trim());
            }
        } else {
            $("#SelectHaberEgreso").val('');
            $('#haberAttr2Egreso').val('');
        }
    }
    
    // Asignar evento change al select de concepto
    $('#changeConceptoEgreso').on('change', function(e){
        changeInputsDebeyHaberEgreso();
    });
    
    // Delegación de eventos como respaldo
    $(document).on('change', '#changeConceptoEgreso', function(e){
        changeInputsDebeyHaberEgreso();
    });
    
    // Ejecutar al cargar si ya hay un concepto seleccionado
    setTimeout(function() {
        changeInputsDebeyHaberEgreso();
    }, 300);
});

// Prevenir inicialización después de window.load
$(window).on('load', function() {
    // Limpiar duplicados
    $('select.no-selectpicker').each(function() {
        limpiarDuplicadosSelect(this);
    });
    
    // Destruir selectpicker
    if (typeof $.fn.selectpicker !== 'undefined') {
        $('select.no-selectpicker').each(function() {
            var $select = $(this);
            if ($select.hasClass('selectpicker')) {
                try {
                    $select.selectpicker('destroy');
                } catch(e) {}
                $select.removeClass('selectpicker');
            }
            $select.parent().find('.bootstrap-select').remove();
            $select.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1'
            });
        });
    }
    
    // Asegurar que el evento change esté asignado después de window.load
    var conceptoSelect = document.getElementById('changeConceptoEgreso');
    if (conceptoSelect) {
        conceptoSelect.addEventListener('change', function() {
            changeInputsDebeyHaberEgreso();
        });
        
        // Ejecutar si ya hay un concepto seleccionado
        if (conceptoSelect.value) {
            changeInputsDebeyHaberEgreso();
        }
    }
});
</script>
@endpush
