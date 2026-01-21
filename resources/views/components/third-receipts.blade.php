<div class="modern-receipt-container">
      <div class="row">
        <div class="col-12">
            <form action="{{ route('receipts.store') }}" method="POST" id="formatReceiptForm">
                <div class="card modern-receipt-card">
                    <!-- Header Moderno -->
                    <div class="modern-receipt-header @if(isset($types) && $types == 'entry') receipt-header-entry @else receipt-header-discharge @endif">
                        <div class="row align-items-center">
                            <div class="col-lg-3 col-md-12 mb-3 mb-lg-0">
                                <div class="receipt-date-section">
                                    <div class="receipt-date-label">
                                        <i class="fa-solid fa-calendar-days mr-2"></i>Fecha
                    </div>
                    @php
                      $fecha = null;
                      if(isset($content) && $content != null){
                          $fecha = explode("-",$content->created_at);
                      }
                    @endphp
                                    <div class="receipt-date-display">
                                        <div class="date-item">
                                            <span class="date-value">
                        @if(isset($content) && $content != null && isset($fecha) && isset($fecha[2]))
                          {{ (explode(" ", $fecha[2]))[0] }}
                        @else
                          {{ date('d')}}
                        @endif
                                            </span>
                                            <span class="date-label">Día</span>
                      </div>
                                        <div class="date-separator">/</div>
                                        <div class="date-item">
                                            <span class="date-value">
                        @if(isset($content) && $content != null && isset($fecha) && isset($fecha[1]))
                          {{ $fecha[1] }}
                        @else
                          {{ date('m')}}
                        @endif
                                            </span>
                                            <span class="date-label">Mes</span>
                      </div>
                                        <div class="date-separator">/</div>
                                        <div class="date-item">
                                            <span class="date-value">
                        @if(isset($content) && $content != null && isset($fecha) && isset($fecha[0]))
                          {{ $fecha[0] }}
                        @else
                          {{ date('Y')}}
                        @endif
                                            </span>
                                            <span class="date-label">Año</span>
                      </div>
                    </div>
                  </div>
                </div>
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0 text-center">
                                <h3 class="receipt-title mb-0">
                                    <i class="fa-solid fa-file-invoice mr-2"></i>
                                    {{ $title ?? 'Recibo de Terceros' }}
                                </h3>
                  </div>
                            <div class="col-lg-3 col-md-12 text-center text-lg-right">
                                <div class="receipt-consecutive-section">
                                    <div class="consecutive-label">N° Recibo</div>
                                    <div class="consecutive-display justify-content-center justify-content-lg-end">
                                        <span id="showConsecutiveReceipts" class="consecutive-number">
                      @if (isset($content) && $content != null)
                        {{ $content->no_recibo }}
                      @else
                        {{ isset($consecutive) && $consecutive ? $consecutive->num_current : '' }}
                      @endif 
                    </span>
                    <input type="text" class="d-none" id="showInputSearchReceipts">
                    @if(isset($content) && $content != null)
                                            <a href="{{ route('third.receipts.'.($types ?? 'entry')) }}" class="btn-new-receipt-modern" title="Nuevo Recibo">
                                                <i class="fa-solid fa-plus-circle"></i>
                                            </a>
                    @else
                                            <button type="button" id="changeConsecutiveInputReceipts" class="btn-rotate-consecutive-modern" title="Actualizar">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>
                    @endif
                  </div>
                                </div>
                            </div>
                </div>
              </div>

                    <!-- Body del Formulario -->
                    <div class="card-body modern-receipt-body">
                  @if(isset($content) && $content != null)
                            <input type="hidden" name="id" value="{{ $content->id }}">
                  <input type="hidden" name="no_recibo" value="{{ $content->no_recibo }}">
                  @else
                            <input type="hidden" name="no_recibo" value="{{ isset($consecutive) && $consecutive ? $consecutive->num_current : '' }}">
                  @endif
                 
                <input type="hidden" id="TypeReceipts" name="type" value="{{ $types ?? 'entry' }}">
                @csrf

                        <!-- Primera Fila: Tercero y Debe -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-user mr-2"></i>Tercero
                                        <small class="text-danger errorThirdReceipt d-none">(Completa este campo)</small>
                                    </label>
                                    <input type="hidden" name="third" id="thirdID" @if(isset($content) && $content != null && isset($content->thirdObject)) value="{{ $content->thirdObject->id }}" @endif>
                                    <div class="modern-input-wrapper">
                                        <input type="text" id="thirdInput" class="modern-input" 
                                               @if(isset($content) && $content != null && isset($content->thirdObject)) value="{{ $content->thirdObject->nombre }}" @endif
                                               placeholder="Buscar o seleccionar tercero"
                                               autocomplete="off">
                                        <i class="fa-solid fa-search input-icon"></i>
                                        <ul class="listItems d-none modern-dropdown"></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-arrow-down mr-2"></i>Debe
                                    </label>
                                    <div class="modern-select-wrapper">
                                        <input readonly type="text" id="SelectDebe" class="modern-input modern-input-readonly" placeholder="Se selecciona automáticamente">
                                        <select name="debe" id="debeAttr2" class="modern-select-hidden">
                                            @foreach (($debe ?? []) as $item)
                                                <option @if(isset($content) && $content != null && $content->debe == $item->id) selected @endif value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Segunda Fila: Concepto y Haber -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-tag mr-2"></i>Concepto
                                    </label>
                                    <div class="modern-select-wrapper">
                                        <select class="modern-select" name="concepto" id="changeConceptoThird">
                                            <option value="">Seleccione un concepto...</option>
                                            @forelse(($concepts ?? []) as $item)
                                                <option @if(isset($content) && $content != null && $item->id == $content->concepto) selected @endif 
                                                        debe="{{ $item->debe }}" haber="{{ $item->haber }}" value="{{ $item->id }}">
                                                    {{ $item->name }}
                                                </option>
                                            @empty
                                                <option value="" disabled>No hay conceptos disponibles</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-arrow-up mr-2"></i>Haber
                                    </label>
                                    <div class="modern-select-wrapper">
                                        <input readonly type="text" id="SelectHaber" class="modern-input modern-input-readonly" placeholder="Se selecciona automáticamente">
                                        <select name="haber" id="haberAttr2" class="modern-select-hidden">
                                            @foreach (($haber ?? []) as $item)
                                                <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tercera Fila: Detalles -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-align-left mr-2"></i>Detalles
                                    </label>
                                    <textarea name="detalles" class="modern-textarea" rows="3" 
                                              placeholder="Ingrese los detalles del recibo...">@if(isset($content) && $content != null){{ $content->detalles }}@endif</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Cuarta Fila: Forma de Pago y Elaborado Por -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-money-bill-wave mr-2"></i>Forma de Pago
                                    </label>
                                    <div class="modern-radio-group">
                                        <div class="custom-radio-wrapper">
                                            <input @if(isset($content) && $content != null && $content->forma == "Efectivo") checked @endif 
                                                   name="forma" class="custom-radio-input" type="radio" value="Efectivo" checked id="flexRadioDefault1">
                                            <label class="custom-radio-label" for="flexRadioDefault1">
                                                <span class="radio-custom"></span>
                                                <span class="radio-text">Efectivo</span>
                                            </label>
                                        </div>
                                        <div class="custom-radio-wrapper">
                                            <input @if(isset($content) && $content != null && $content->forma == "Consignación") checked @endif 
                                                   name="forma" class="custom-radio-input" type="radio" value="Consignación" id="flexRadioDefault2">
                                            <label class="custom-radio-label" for="flexRadioDefault2">
                                                <span class="radio-custom"></span>
                                                <span class="radio-text">Consignación</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-user-check mr-2"></i>Elaborado Por
                                    </label>
                                    <div class="modern-select-wrapper">
                                        <select name="elaborado_por" class="modern-select" id="elaboradoSelect">
                                            @foreach(($elaborados ?? []) as $item)
                                                <option @if(isset($content) && $content != null && $item->id == $content->elaborado_por) selected @endif value="{{ $item->id }}">
                                                    {{ $item->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quinta Fila: Valor y Botón -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group modern-form-group">
                                    <label class="modern-label">
                                        <i class="fa-solid fa-dollar-sign mr-2"></i>Valor
                                        <small class="text-danger errorValueReceipt d-none">(Completa este campo)</small>
                                    </label>
                                    <div class="modern-value-input-wrapper">
                                        <div class="value-sign">$</div>
                                        @php
                                            $valor = null;
                                            if(isset($content) && $content != null){
                                                $valor = str_replace(',','.',strval(number_format($content->valor)));
                                            }
                                        @endphp
                                        <input name="valor" type="text" class="modern-value-input miles" id="valor" 
                                               @if(isset($content) && $content != null && isset($valor)) value="{{$valor}}" @endif
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="modern-submit-section w-100">
                                    @if (isset($content) && $content != null)
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('third.receipts.index') }}" class="btn btn-secondary" style="padding: 14px 24px; font-size: 16px; font-weight: 700; border-radius: 10px; border: none; background: #6b7280; color: #ffffff; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="fa-solid fa-arrow-left mr-2"></i>Volver
                                            </a>
                                            <button type="submit" class="btn btn-primary btn-modern-submit flex-fill">
                                                <i class="fa-solid fa-save mr-2"></i>Guardar Recibo
                                            </button>
                                        </div>
                                    @else
                                        <button type="submit" class="btn btn-primary btn-modern-submit w-100">
                                            <i class="fa-solid fa-plus-circle mr-2"></i>Crear Recibo
                                        </button>
                                    @endif
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
/* Estilos Modernos para el Recibo con Tonos Pastel */
.modern-receipt-container {
    padding: 15px 0;
}

.modern-receipt-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.1);
    overflow: hidden;
    background: #ffffff;
}

.modern-receipt-header {
    padding: 20px 25px;
    border-bottom: 2px solid rgba(59, 130, 246, 0.12);
}

.modern-receipt-header.receipt-header-entry {
    background: linear-gradient(135deg, #93c5fd 0%, #bfdbfe 50%, #dbeafe 100%);
}

.modern-receipt-header.receipt-header-discharge {
    background: linear-gradient(135deg, #86efac 0%, #bbf7d0 50%, #d1fae5 100%);
}

.receipt-date-section {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 10px rgba(59, 130, 246, 0.08);
    border: 2px solid rgba(255, 255, 255, 0.9);
}

.receipt-date-label {
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #1e40af;
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
    background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
    border-radius: 10px;
    padding: 8px 6px;
    border: 2px solid rgba(59, 130, 246, 0.2);
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);
}

.date-value {
    display: block;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.2;
    color: #1e40af;
}

.date-label {
    display: block;
    font-size: 11px;
    color: #3b82f6;
    margin-top: 4px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.date-separator {
    font-size: 18px;
    font-weight: 700;
    color: #93c5fd;
    margin: 0 2px;
}

.receipt-title {
    font-size: 26px;
    font-weight: 700;
    color: #1e40af;
    text-shadow: 0 1px 3px rgba(255, 255, 255, 0.5);
    letter-spacing: 0.3px;
}

.receipt-consecutive-section {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 10px rgba(59, 130, 246, 0.08);
    border: 2px solid rgba(255, 255, 255, 0.9);
}

.consecutive-label {
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #1e40af;
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
    background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
    color: #1e40af;
    padding: 8px 16px;
    border-radius: 10px;
    min-width: 80px;
    display: inline-block;
    text-align: center;
    border: 2px solid rgba(59, 130, 246, 0.3);
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.15);
}

.btn-new-receipt-modern,
.btn-rotate-consecutive-modern {
    background: linear-gradient(135deg, #bfdbfe 0%, #dbeafe 100%);
    border: 2px solid rgba(59, 130, 246, 0.4);
    color: #1e40af;
    border-radius: 10px;
    padding: 6px 10px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-new-receipt-modern:hover,
.btn-rotate-consecutive-modern:hover {
    background: linear-gradient(135deg, #93c5fd 0%, #bfdbfe 100%);
    border-color: rgba(59, 130, 246, 0.6);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    color: #1e3a8a;
    text-decoration: none;
}

.btn-new-receipt-modern:active,
.btn-rotate-consecutive-modern:active {
    transform: translateY(0);
}

.btn-back-receipt-modern {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #1e40af;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(59, 130, 246, 0.3);
}

.btn-back-receipt-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    background: #ffffff;
    color: #1e40af;
    text-decoration: none;
    border-color: #3b82f6;
}

.btn-back-receipt-modern:active {
    transform: translateY(0);
}

.modern-receipt-body {
    padding: 25px;
    background: linear-gradient(to bottom, #f8f9ff 0%, #fafbff 100%);
}

.modern-form-group {
    margin-bottom: 0;
}

.modern-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.modern-label i {
    color: #3b82f6;
    font-size: 15px;
}

.modern-input-wrapper {
    position: relative;
    width: 100%;
}

.modern-input-wrapper .modern-dropdown {
    position: absolute;
    top: calc(100% + 2px);
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1050;
}

.modern-input,
.modern-select,
.modern-textarea {
    width: 100%;
    padding: 12px 16px;
    font-size: 16px;
    border: 2px solid #bfdbfe;
    border-radius: 10px;
    background: #ffffff;
    transition: all 0.3s ease;
    color: #1e293b;
}

.modern-input:focus,
.modern-select:focus,
.modern-textarea:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
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

.modern-input-wrapper .input-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #93c5fd;
    pointer-events: none;
    font-size: 14px;
}

.modern-select-wrapper {
    position: relative;
    width: 100%;
}

.modern-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%233b82f6' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 38px;
    cursor: pointer;
    background-color: #ffffff;
    width: 100%;
    display: block;
    position: relative;
    z-index: 1;
}

.modern-select:hover {
    border-color: #93c5fd;
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

/* Prevenir que Bootstrap Select interfiera - pero permitir selects normales */
.modern-select-wrapper .bootstrap-select,
.modern-select-wrapper .bootstrap-select .dropdown-toggle,
.modern-select-wrapper .bootstrap-select .dropdown-menu {
    display: none !important;
}

#changeConceptoThird.bootstrap-select,
#elaboradoSelect.bootstrap-select {
    display: block !important;
}

#changeConceptoThird.bootstrap-select .dropdown-toggle,
#changeConceptoThird.bootstrap-select .dropdown-menu,
#elaboradoSelect.bootstrap-select .dropdown-toggle,
#elaboradoSelect.bootstrap-select .dropdown-menu {
    display: none !important;
}

/* Asegurar que los selects normales sean visibles */
.modern-select-wrapper .modern-select:not(.modern-select-hidden) {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
}

/* Asegurar específicamente que changeConceptoThird y elaboradoSelect sean visibles */
#changeConceptoThird,
#elaboradoSelect {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 100% !important;
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
    border: 2px solid #bfdbfe;
    border-radius: 10px;
    transition: all 0.3s ease;
    user-select: none;
    box-shadow: 0 2px 5px rgba(59, 130, 246, 0.1);
}

.custom-radio-label:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.custom-radio-input:checked + .custom-radio-label {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 4px 12px rgba(59, 130, 246, 0.2);
}

.radio-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #bfdbfe;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
    transition: all 0.3s ease;
    flex-shrink: 0;
    background: #ffffff;
}

.custom-radio-input:checked + .custom-radio-label .radio-custom {
    border-color: #3b82f6;
    background: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
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
    color: #1e40af;
}

.modern-value-input-wrapper {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 2px solid #bfdbfe;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);
}

.modern-value-input-wrapper:focus-within {
    border-color: #93c5fd;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 4px 12px rgba(59, 130, 246, 0.2);
}

.value-sign {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    color: #ffffff;
    padding: 12px 18px;
    font-size: 22px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 2px 0 6px rgba(59, 130, 246, 0.2);
}

.modern-value-input {
    flex: 1;
    border: none;
    padding: 12px 16px;
    font-size: 22px;
    font-weight: 700;
    color: #1e40af;
    background: transparent;
}

.modern-value-input:focus {
    outline: none;
}

.modern-value-input::placeholder {
    color: #93c5fd;
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
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    color: #ffffff;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.btn-modern-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.45);
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: #ffffff;
}

.btn-modern-submit:active {
    transform: translateY(-1px);
}

.modern-dropdown {
    background: #ffffff;
    border: 2px solid #bfdbfe;
    border-top: none;
    border-radius: 0 0 12px 12px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.2);
    margin-top: -2px;
}

.modern-dropdown.d-none {
    display: none !important;
}

.modern-dropdown li {
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #eff6ff;
    color: #1e293b;
    font-size: 16px;
}

.modern-dropdown li:hover {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1e40af;
    padding-left: 22px;
}

.modern-dropdown li:last-child {
    border-bottom: none;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Desactivar Bootstrap Select en los selects específicos ANTES de que se inicialice
    if (typeof $.fn.selectpicker !== 'undefined') {
        $('#changeConceptoThird, #elaboradoSelect').selectpicker('destroy');
        $('#changeConceptoThird, #elaboradoSelect').removeClass('selectpicker');
    }

    // Ocultar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        const thirdInput = document.getElementById('thirdInput');
        const dropdown = document.querySelector('.listItems');
        
        if (thirdInput && dropdown && !thirdInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('d-none');
        }
    });

    // Ocultar dropdown cuando se pierde el foco del input (con un pequeño delay para permitir clicks)
    const thirdInput = document.getElementById('thirdInput');
    if (thirdInput) {
        thirdInput.addEventListener('blur', function(e) {
            const dropdown = document.querySelector('.listItems');
            if (dropdown) {
                // Delay para permitir que se ejecute el click en los items del dropdown
                setTimeout(function() {
                    dropdown.classList.add('d-none');
                }, 200);
            }
        });

        // Asegurar que el dropdown solo se muestre cuando hay texto
        thirdInput.addEventListener('keyup', function(e) {
            const dropdown = document.querySelector('.listItems');
            if (dropdown && (!this.value || this.value.trim() === '')) {
                dropdown.classList.add('d-none');
            }
        });
    }

    // Prevenir que Bootstrap Select interfiera con los selects
    const selectsToFix = ['changeConceptoThird', 'elaboradoSelect'];
    
    function fixSelect(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Asegurar que el select sea visible inmediatamente
        select.style.display = 'block';
        select.style.visibility = 'visible';
        select.style.opacity = '1';
        select.style.width = '100%';
        select.style.position = 'relative';
        select.style.zIndex = '1';
        
        // Desactivar Bootstrap Select si está activo
        if (typeof $ !== 'undefined' && typeof $.fn.selectpicker !== 'undefined') {
            try {
                $(select).selectpicker('destroy');
                $(select).removeClass('selectpicker');
            } catch(e) {
                console.log('Bootstrap Select no estaba inicializado en', selectId);
            }
        }
        
        // Remover cualquier wrapper de Bootstrap Select
        let currentElement = select;
        while (currentElement.parentElement) {
            if (currentElement.parentElement.classList.contains('bootstrap-select') || 
                currentElement.parentElement.classList.contains('bootstrap-select-wrapper')) {
                const parent = currentElement.parentElement;
                const wrapper = document.createElement('div');
                wrapper.className = 'modern-select-wrapper';
                parent.parentNode.insertBefore(wrapper, parent);
                wrapper.appendChild(select);
                parent.remove();
                break;
            }
            currentElement = currentElement.parentElement;
        }
        
        // Remover cualquier botón de toggle que Bootstrap Select haya creado
        const toggleButtons = document.querySelectorAll('#' + selectId + ' + button, #' + selectId + ' ~ button, .bootstrap-select button');
        toggleButtons.forEach(function(btn) {
            const btnParent = btn.parentElement;
            if (btnParent && (btnParent.classList.contains('bootstrap-select') || 
                btn.closest('.modern-select-wrapper') || 
                btnParent === select.parentElement)) {
                btn.remove();
            }
        });
        
        // Asegurar que el select tenga las clases correctas
        select.classList.remove('bootstrap-select', 'selectpicker', 'd-none', 'show-tick');
        select.classList.add('modern-select');
        
        // Asegurar que esté dentro de un wrapper
        if (!select.parentElement || !select.parentElement.classList.contains('modern-select-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'modern-select-wrapper';
            if (select.parentElement) {
                select.parentNode.insertBefore(wrapper, select);
            }
            wrapper.appendChild(select);
        }
        
        // Forzar visibilidad de nuevo después de todo
        setTimeout(function() {
            select.style.display = 'block';
            select.style.visibility = 'visible';
            select.style.opacity = '1';
            select.style.width = '100%';
        }, 50);
    }
    
    // Aplicar fix inmediatamente
    selectsToFix.forEach(fixSelect);
    
    // Aplicar fix después de un delay para asegurar que se ejecute después de otros scripts
    setTimeout(function() {
        selectsToFix.forEach(fixSelect);
    }, 100);
    
    // Aplicar fix periódicamente para mantener los selects visibles
    const fixInterval = setInterval(function() {
        selectsToFix.forEach(function(selectId) {
            const select = document.getElementById(selectId);
            if (select) {
                // Desactivar Bootstrap Select si se reactiva
                if (typeof $ !== 'undefined' && typeof $.fn.selectpicker !== 'undefined') {
                    if ($(select).hasClass('selectpicker')) {
                        try {
                            $(select).selectpicker('destroy');
                            $(select).removeClass('selectpicker');
                        } catch(e) {}
                    }
                }
                
                // Verificar visibilidad
                if (select.style.display === 'none' || select.offsetParent === null || 
                    select.classList.contains('d-none') || 
                    select.parentElement && select.parentElement.classList.contains('bootstrap-select')) {
                    fixSelect(selectId);
                }
            }
        });
    }, 1000);
    
    // Limpiar intervalo después de 30 segundos
    setTimeout(function() {
        clearInterval(fixInterval);
    }, 30000);
});
</script>