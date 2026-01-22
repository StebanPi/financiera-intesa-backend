@extends('dash.app')

@section('page')
    Ver estudiante
@endsection
@php 
$ruta = "https://institutointesa.edu.co/".substr($student[0]->foto,6); 
date_default_timezone_set("America/Bogota");
@endphp
@section('content')
    <div class="profile card card-body px-3 pt-3 pb-0">
        <div class="profile-head">
         
            <div class="profile-info">
                <div class="profile-photo">
                    <img src=" @php echo $ruta; @endphp" class="img-fluid rounded-circle" alt="">
                </div>
                <div class="profile-details">
                    <div class="profile-name px-3 pt-2">
                        <h4 class="text-primary mb-0">{{ $student[0]->nombre }}</h4>
                        <p>CC {{ $student[0]->cedula }}</h4></p>
                    </div>
                    <div class="profile-email px-2 pt-2">
                        <h4 class="text-muted mb-0">{{ $student[0]->nombre_programa }}</h4>
                        <p><span class="badge light badge-primary">{{ $student[0]->estado }}</span></p>
                    </div>
                    <div class="profile-email px-2 pt-2">
                        @if($cost->id != "")
                            <a href="{{ route('entry.ViewPdfUnitedOther',$cost->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download mr-2"></i> Pagos</a>
                        @endif
                        <a href="{{ route('matricula.ficha', $student[0]->cod_alumno) }}" class="btn btn-outline-success btn-sm ml-2"><i class="fa-solid fa-file-lines mr-2"></i> Ficha de Matrícula</a>
                    </div>
                    <div class="ml-auto d-flex">
                        <div class="mr-3">
                            <small class="text-primary"><b>Saldo a Favor</b></small>
                            <h3 class="text-success">$<span id="SaldoFavorText">0</span></h3>
                        </div>
                        <div>
                            <small class="text-primary"><b>Saldo Pendiente</b></small>
                            <h3 class="text-danger">$<span id="SaldoPendienteText">0</span></h3>
                        </div>
  
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <x-error-modal :errors="$errors" />


    <input type="hidden" id="pages" value="show.cartera">
    <input type="hidden" id="id_cost" value="{{ $cost->id ?? '' }}">
    <input type="hidden" id="cod_alumno" value="{{ $student[0]->cod_alumno }}">
    <div class="row">
        <div class="col-md-4">
            <div class="card stickyMenu mr-2 bg-gray1" id="stickyMenuWidth">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-2">
                        <h5 class="text-primary my-0 text-sm font-semibold"><i class="fa-solid fa-sack-dollar mr-1"></i> Información de Costos</h5>
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors" data-toggle="modal" data-target="#modalConfigurarCostos">
                            <i class="fa-solid fa-cog mr-1"></i>Configurar
                        </button>
                    </div>
                    
                    @if($cost->id != "")
                        @php
                            // Función helper para formatear valores monetarios
                            // Limpia puntos (separadores de miles) antes de convertir a float
                            $formatMoney = function($value) {
                                if (empty($value) || $value === null) return '0';
                                // Si es string, limpiar puntos y comas, luego convertir a float
                                $cleanValue = str_replace(['.', ','], '', strval($value));
                                return number_format(floatval($cleanValue), 0, ',', '.');
                            };
                        @endphp
                        <div class="p-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="space-y-1.5">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Valor Semestre
                                        </label>
                                        <input type="text" value="{{ $formatMoney($cost->valor_semestre) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <i class="fa-solid fa-hashtag mr-1 text-xs"></i>Numero de Semestres
                                        </label>
                                        <input type="number" value="{{ $cost->numero_semestre ?? 0 }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <b><i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Valor total Programa</b>
                                        </label>
                                        <input type="text" value="{{ $formatMoney($cost->valor_total_semestre) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Descuento
                                        </label>
                                        <input type="text" value="{{ $formatMoney($cost->descuento) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <b><i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Valor total neto del Programa</b>
                                        </label>
                                        <input type="text" value="{{ $formatMoney($cost->valor_neto) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <b><i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Saldo a Financiar</b>
                                        </label>
                                        <input type="text" value="{{ $formatMoney($cost->saldo_financiar) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <i class="fa-solid fa-arrow-pointer mr-1 text-xs"></i>Periodo de Pago
                                        </label>
                                        <input type="text" value="{{ $cost->periodo ?? 'N/A' }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <i class="fa-solid fa-hashtag mr-1 text-xs"></i>Numero de cuotas
                                        </label>
                                        <input type="number" value="{{ $cost->numero_cuotas ?? 0 }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <b><i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Valor de Cuotas</b>
                                        </label>
                                        <input type="text" value="{{ $formatMoney($cost->valor_cuotas) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-0.5">
                                            <b><i class="fa-solid fa-dollar-sign mr-1 text-xs"></i>Fecha de Pago</b> <small class="text-red-500 text-xs">(Obligatorio)</small>
                                        </label>
                                        <input type="text" value="{{ $cost->fecha_pago ? \Carbon\Carbon::parse($cost->fecha_pago)->format('d/m/Y') : 'N/A' }}" disabled class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-100 text-gray-700 cursor-not-allowed">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-2">
                            <small class="text-yellow-800 text-xs">
                                <i class="fa-solid fa-exclamation-triangle mr-1"></i>No hay información de costos configurada. Haga clic en "Configurar" para agregar.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if ($cost->valor_cuotas != "")
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="profile-tab">
                            <div class="custom-tab-1">
                                <ul class="nav nav-tabs" id="content-personality">
                                    <li class="nav-item"><a href="#abono" Pestaña="abono" data-toggle="tab" class="nav-link active Pestaña">Abono</a>
                                    </li>
                                    <li class="nav-item"><a href="#otros-ingresos" Pestaña="otrosIngresos" id="" data-toggle="tab" class="nav-link Pestaña">Otros Ingresos</a>
                                    </li>
                                    <li class="nav-item"><a href="#cartera"  id="" data-toggle="tab" class="nav-link Pestaña">Cartera</a>
                                    </li>
                                    
                                </ul>
                                <div class="tab-content">
                                    <div id="abono" class="tab-pane fade active show">
                                        <div class="mt-4">
                                            <div class="d-flex">
                                                <div class="d-flex flex-row">
                                                    <button type="button" class="btn btn-primary btn-xxs my-2 mr-2 rounded-d ml-2 mb-4" data-toggle="modal" data-target="#staticBackdrop">+</button>
                                                </div>
                                                <div class="ml-auto">
                                                    <a target="__blank" href="{{ route('entry.Viewpdf',$cost->id) }}" class="btn btn-xs bg-violet text-white"><i class="fa-solid fa-file-pdf mr-2"></i>PDF</a>
                                                </div>
                                                
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-responsive-sm">
                                                    <thead class="thead-secondary text-primary text-center">
                                                        <tr class="">
                                                            <th scope="col">Con.</th>
                                                            <th scope="col">Fecha</th>
                                                            <th scope="col">Concepto</th>
                                                            <th scope="col">Descripción</th>
                                                            <th scope="col">Elaborado Por</th>
                                                            <th scope="col">Valor</th>
                                                            <th scope="col"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="table_items_abono">

                                                    </tbody>
                                                    <tfoot id="table_items_abono_tfoot">

                                                    </tfoot>

                                                </table>
                                            </div>
                                        </div>   
                                    </div>
                                    <div id="otros-ingresos" class="tab-pane fade">
                                        <div class="mt-4">
                                            <div class="d-flex">
                                                <div class="d-flex flex-row">
                                                    <button type="button" class="btn btn-primary btn-xxs my-2 mr-2 rounded-d ml-2 mb-4" data-toggle="modal" data-target="#ModalOtrosAbonos">+</button>
                                                </div>
                                                <div class="ml-auto">
                                                    <a target="__blank" href="{{ route('other.entry.Viewpdf',$cost->id) }}" class="btn btn-xs bg-violet text-white"><i class="fa-solid fa-file-pdf mr-2"></i>PDF</a>

                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-responsive-sm">
                                                    <thead class="thead-secondary text-primary text-center">
                                                        <tr>
                                                            <th scope="col">Con.</th>
                                                            <th scope="col">Fecha</th>
                                                            <th scope="col">Concepto</th>
                                                            <th scope="col">Valor</th>
                                                            <th scope="col">Elaborado Por</th>
                                                            <th scope="col">Ver</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="Table_Otros_Items">

                                            
                                                    </tbody>
                                                    <tfoot id="Table_Otros_Items_foot">

                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>  
                                    </div>
                                    <div id="cartera" class="tab-pane fade">
                                        @if ($Purses != "")
                                            <div class="mt-4">
                                                <div class="d-flex">
                       
                                                    <div class="ml-auto">
                                                        <a target="__blank" href="{{ route('purse.Viewpdfc',$cost->id) }}" class="btn btn-xs bg-violet text-white"><i class="fa-solid fa-file-pdf mr-2"></i>PDF</a>
    
                                                    </div>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped table-responsive-sm">
                                                        <thead class="thead-secondary text-primary text-center">
                                                            <tr>
                                                                <th scope="col">Id</th>
                                                                <th scope="col">Fecha de Pago</th>
                                                                <th scope="col">Cuota</th>
                                                                <th scope="col">Abonado</th>
                                                                <th scope="col">Estado Pago</th>
                                                                <th scope="col">Estado</th>
                                                                <th scope="col"></th>
                                                                <th scope="col"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="TABLE_ITEMS_CARTERA">
                                                           
                                                        </tbody>
                                                        <tfoot id="TABLE_ITEMS_CARTERA_TFOOT">

                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>          
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        @endif
    </div>


  <!-- Modal Configurar Información de Costos -->
  <div class="modal fade" id="modalConfigurarCostos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalConfigurarCostosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfigurarCostosLabel"><i class="fa-solid fa-cog mr-2"></i>Configurar Información de Costos por Semestre</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('cost.store') }}" id="FormValueProgram">
                @csrf
                <input type="hidden" value="{{ $student[0]->cod_alumno }}" name="cod_alumno">
                <input type="hidden" name="redirect_to" value="financiera">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fa-solid fa-hashtag mr-2"></i>Número Total de Semestres</label>
                            <input type="number" value="{{ count($costs) }}" name="total_semestres" id="total_semestres" class="form-control" min="1" max="10">
                        </div>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-group mb-0 w-100">
                            <label class="font-weight-bold">Seleccionar Semestre para Configurar:</label>
                            <select id="selector_semestre" class="form-control">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $i > count($costs) ? 'disabled' : '' }}>Semestre {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <div id="contenedor_semestres">
                    @for($i = 1; $i <= 10; $i++)
                        @php
                            $c = $costs->where('numero_semestre', $i)->first() ?? (object)[
                                'id' => '',
                                'valor_semestre' => '',
                                'numero_semestre' => $i,
                                'valor_total_semestre' => '',
                                'descuento' => '',
                                'valor_neto' => '',
                                'saldo_financiar' => '',
                                'periodo' => 'Mensual',
                                'numero_cuotas' => '',
                                'valor_cuotas' => '',
                                'fecha_pago' => ''
                            ];
                        @endphp
                        <div class="seccion-semestre {{ $i > count($costs) ? 'd-none' : ($i == 1 ? '' : 'd-none') }}" id="seccion_semestre_{{ $i }}" data-semestre="{{ $i }}">
                            <h5 class="text-primary border-bottom pb-2 mb-3">Configuración Semestre {{ $i }}</h5>
                            <input type="hidden" name="semestres[{{ $i }}][numero_semestre]" value="{{ $i }}">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa-solid fa-dollar-sign mr-2"></i>Valor Semestre {{ $i }}</label>
                                        <input type="text" value="{{ $c->valor_semestre }}" name="semestres[{{ $i }}][valor_semestre]" id="valor_semestre_{{ $i }}" class="form-control form-control-sm miles input-valor-semestre" data-semestre="{{ $i }}">
                                    </div>
                                    <div class="form-group">
                                        <label><b><i class="fa-solid fa-dollar-sign mr-2"></i>Valor total Neto del Semestre</b></label>
                                        <input type="text" value="{{ $c->valor_neto }}" id="valor_neto_{{ $i }}" class="form-control form-control-sm disabled-input" disabled>
                                        <input type="hidden" name="semestres[{{ $i }}][valor_neto]" value="{{ $c->valor_neto }}">
                                        <input type="hidden" name="semestres[{{ $i }}][valor_total_semestre]" value="{{ $c->valor_total_semestre }}">
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fa-solid fa-dollar-sign mr-2"></i>Descuento Semestre {{ $i }}</label>
                                        <input type="text" value="{{ $c->descuento }}" name="semestres[{{ $i }}][descuento]" id="descuento_{{ $i }}" class="form-control form-control-sm miles input-descuento" data-semestre="{{ $i }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa-solid fa-arrow-pointer mr-2"></i>Periodo de Pago</label>
                                        <select class="form-control form-control-sm" name="semestres[{{ $i }}][periodo]">
                                            <option value="Mensual" {{ $c->periodo == 'Mensual' ? 'selected' : '' }}>Mensual</option>
                                            <option value="Quincenal" {{ $c->periodo == 'Quincenal' ? 'selected' : '' }}>Quincenal</option>
                                            <option value="Semanal" {{ $c->periodo == 'Semanal' ? 'selected' : '' }}>Semanal</option>
                                            <option value="Contado" {{ $c->periodo == 'Contado' ? 'selected' : '' }}>Contado</option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fa-solid fa-hashtag mr-2"></i>Nro Cuotas</label>
                                                <input type="number" name="semestres[{{ $i }}][numero_cuotas]" value="{{ $c->numero_cuotas }}" id="numero_cuotas_{{ $i }}" class="form-control form-control-sm input-numero-cuotas" data-semestre="{{ $i }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fa-solid fa-calendar mr-2"></i>Fecha Inicio</label>
                                                <input type="date" name="semestres[{{ $i }}][fecha_pago]" value="{{ $c->fecha_pago }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><b><i class="fa-solid fa-dollar-sign mr-2"></i>Valor Cuotas</b></label>
                                        <input type="text" value="{{ $c->valor_cuotas }}" id="valor_cuotas_{{ $i }}" class="form-control form-control-sm disabled-input" disabled>
                                        <input type="hidden" name="semestres[{{ $i }}][valor_cuotas]" value="{{ $c->valor_cuotas }}">
                                        <input type="hidden" name="semestres[{{ $i }}][saldo_financiar]" value="{{ $c->saldo_financiar }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="error_noti text-warning mt-2 font-weight-bold ActiveError d-none"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary ejecutarmodal" id="btn_guardar_costos">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Todo
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog mw-100 w-50" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-sack-dollar mr-2"></i>Agregar Abono</h5>
          <button type="button" id="CloseFormEntry1" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('entry.store') }}" id="formEntry" onsubmit="return EnviarDatos(event)">
                @csrf
                <input type="hidden" id="NombreFormulario" class="nombreForm__Class" value="Entry">
                <div id="errores" class="d-none my-2">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <strong>Error!</strong> Asegurate de completar los siguientes campos :
                        <ul>
                            <li>- Concepto del Abono</li>
                            <li>- Fecha del Recibo</li>
                            <li>- Valor</li>
                            <li><small>Si digitas un consecutivo, asegurate que sea en un rango menor que el utilizado.</small></li>
                        </ul>
                        <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" value="{{ $student[0]->cod_alumno }}" name="cod_alumno">
                        <div class="form-group">
                            <label>Cedula</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->cedula }}"  readonly>
                        </div>
                        <div class="form-group">
                            <label>Estudiante</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Programa</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre_programa}}" readonly>
                        </div>
                        <input type="hidden" id="id_cost" name="id_cost" value="{{ $cost->id }}">
                        <div class="form-group">
                            <label>Concepto</label>
                            
                            <select name="concepto" id="conceptoAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($conceptos as $item)
                                    @if ($item->estado == "1")
                                        <option consecutive="{{ $item->consecutivo }}" value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descripción <small class="text-danger">(Obligatorio)</small></label>
                            <textarea name="descripcion" id="descripcionAttr" class="form-control form-control-sm number-lg descripcion__Class" name="" id="" cols="30" rows="10"></textarea>
                            <div class="error_des text-danger ActiveError d-none">

                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No.Recibo</label>
                            <input type="hidden" id="ConsecutivosOcupados" value="@foreach ($ConsecutivosOcupados as $item)
                            -{{ $item->no_recibo}}-
                        @endforeach" >
                            <input type="hidden" id="StartConsecutivo" value="{{ $con->num_start }}" >
                            <input name="no_recibo" id="noReciboAttr" type="number" class="form-control form-control-sm number-lg noRecibo__Class noRecibo__Class1" readonly value="{{ $con->num_current }}">
                            <div class="error_recibo text-danger ActiveError d-none">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Recibo</label>
                            <input name="fecha_recibo" value="@php echo date('Y-m-d'); @endphp" id="fechaReciboAttr" type="date" class="form-control form-control-sm number-lg fechaRecibo__Class" >
                            <div class="error_fecha text-danger d-none ActiveError">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Valor <small class="text-danger">(Obligatorio)</small></label>
                            <input name="valor" id="valorAttr" type="text" class="form-control form-control-sm number-lg miles valor__Class valor__Class_1" onkeypress="return valideKey(event);">
                            <div class="error_valor text-danger d-none ActiveError">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Elaborado Por <small class="text-danger">(Obligatorio)</small></label>
                            <select name="elaborado_por" id="elaboradoPorAttr" class="form-control form-control-sm number-lg elaborado__Class" tabindex="-98">
                                <option value="0">Busca tu nombre</option>
                                @foreach ($elaborados as $item)
                                    @if ($item->estado == "1")
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="error_elaborado text-danger d-none ActiveError">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>DEBE</b></label>
                            <select name="debe"  id="debeAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($debe as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>HABER</b></label>
                            <select name="haber" id="haberAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($haber as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-primary ejecutarmodal" id="savem"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar</button>
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
    </form>
      </div>
    </div>
  </div>

   <!-- Modal -->
   <div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog mw-100 w-50" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-sack-dollar mr-2"></i>Ver Abono</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <input type="hidden" value="{{ $student[0]->cod_alumno }}" name="cod_alumno">
                <div class="form-group">
                    <label>Cedula</label>
                    <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->cedula }}"  readonly>
                </div>
                <div class="form-group">
                    <label>Estudiante</label>
                    <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre }}" readonly>
                </div>
                <div class="form-group">
                    <label>Programa</label>
                    <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre_programa}}" readonly>
                </div>
                <div class="form-group">
                    <label>Concepto</label>
                    <input type="text" id="concepto" class="form-control form-control-sm number-lg" value="" readonly>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control form-control-sm number-lg" readonly name="" id="" cols="30" rows="5"></textarea>
                </div>
                <hr>
                <div class="form-group">
                    <label>No.Recibo</label>
                    <input name="no_recibo" id="no_recibo" type="number" class="form-control form-control-sm number-lg" readonly >
                </div>
                <div class="form-group">
                    <label>Fecha de Recibo</label>
                    <input name="fecha_recibo" id="fecha_recibo" type="date" class="form-control form-control-sm number-lg" readonly >
                </div>
                <div class="form-group">
                    <label>Valor</label>
                    <input name="valor" id="valor" type="text" class="form-control form-control-sm number-lg" readonly>
                </div>
                <div class="form-group">
                    <label>Elaborado Por</label>
                    <input name="elaborado" id="elaborado" type="text" class="form-control form-control-sm number-lg" readonly>
                </div>
                <div class="form-group">
                    <label>Cuenta Contable <b>DEBE</b></label>
                    <input name="valor" id="debe" type="text" class="form-control form-control-sm number-lg" readonly>
                </div>
                <div class="form-group">
                    <label>Cuenta Contable <b>HABER</b></label>
                    <input name="valor" id="haber" type="text" class="form-control form-control-sm number-lg" readonly>
                </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>




  <!-- Modal -->
  <div class="modal fade " id="ModalOtrosAbonos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog mw-100 w-50" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-sack-dollar mr-2"></i>Agregar Otros Abono</h5>
          <button type="button" class="close" id="CloseOtherModal2" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('other.entry.store') }}" id="formEntry1" onsubmit="return EnviarDatos(event)">
                @csrf
                <input type="hidden" id="NombreFormulario" class="nombreForm__Class" value="Other">
                <div id="errores" class="d-none my-2">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <strong>Error!</strong> Asegurate de completar los siguientes campos :
                        <ul>
                            <li>- Concepto del Abono</li>
                            <li>- Fecha del Recibo</li>
                            <li>- Valor</li>
                            <li><small>Si digitas un consecutivo, asegurate que sea en un rango menor que el utilizado.</small></li>
                        </ul>
                        <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" value="{{ $student[0]->cod_alumno }}" name="cod_alumno">
                        <div class="form-group">
                            <label>Cedula</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->cedula }}"  readonly>
                        </div>
                        <div class="form-group">
                            <label>Estudiante</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Programa</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre_programa}}" readonly>
                        </div>
                        <input type="hidden" name="id_cost" value="{{ $cost->id }}">
                        <div class="form-group">
                            <label>Concepto</label>
                            
                            <select name="concepto" id="conceptoAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($otrosConceptos as $item)
                                    @if ($item->estado == "1")
                                        <option consecutive="{{ $item->consecutivo }}" value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descripción <small class="text-danger">(Obligatorio)</small></label>
                            <textarea name="descripcion" id="descripcionAttr" class="form-control form-control-sm number-lg descripcion__Class" name="" id="" cols="30" rows="10"></textarea>
                            <div class="error_des text-danger ActiveError d-none">

                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No.Recibo</label>
                            <input name="no_recibo" id="noReciboAttr" type="number" class="form-control form-control-sm number-lg noRecibo__Class noRecibo__Class2" readonly value="{{ $con->num_current }}">
                            <div class="error_recibo text-danger ActiveError d-none">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Recibo</label>
                            <input name="fecha_recibo" id="fechaReciboAttr" value="@php echo date('Y-m-d'); @endphp" type="date" class="form-control form-control-sm number-lg fechaRecibo__Class">
                            <div class="error_fecha text-danger d-none ActiveError">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Valor <small class="text-danger">(Obligatorio)</small></label>
                            <input name="valor" id="valorAttr" type="text" class="form-control form-control-sm number-lg miles valor__Class" onkeypress="return valideKey(event);">
                            <div class="error_valor text-danger d-none ActiveError">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Elaborado Por <small class="text-danger">(Obligatorio)</small></label>
                            <select name="elaborado_por" id="elaboradoPorAttr" class="form-control form-control-sm number-lg elaborado__Class" tabindex="-98">
                                <option value="0">Busca tu nombre</option>
                                @foreach ($elaborados as $item)
                                    @if ($item->estado == "1")
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="error_elaborado text-danger d-none ActiveError">

                            </div>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>DEBE</b></label>
                            <select name="debe"  id="debeAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($debe as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>HABER</b></label>
                            <select name="haber" id="haberAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($haber as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-primary ejecutarmodal" id="savem"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar</button>
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
    </form>
      </div>
    </div>
  </div>


  <form  method="POST" action="{{ route('purse.all') }}" id="purseAll">
    @csrf
    <input type="hidden" name="id" value="{{ $cost->id }}">
  </form>

  <form  method="POST"  id="FormPurseHistory1">
    @csrf
    <input type="hidden" name="id_cost" value="{{ $cost->id }}">
  </form>

  <form id="FormRequestOtros">
    @csrf
    <input type="hidden" name="id" id="IdContentOperation" value="{{ $cost->id ?? '' }}">

  </form>

  <button type="button" id="buttonShowEditFecha" class="btn btn-primary mb-2 d-none" data-toggle="modal" data-target="#showEditFecha">Small modal</button>
  <div class="modal fade" id="showEditFecha" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog mw-100 w-50">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modificar Fecha</h5>
                <button type="button" id="CloseModalPurse" class="close" data-dismiss="modal"><span>×</span>
                </button>
            </div>
            <form method="POST" action="{{ route('purse.edit') }}" id="FormPurseEdit">
                @csrf

            <div class="modal-body" >
                <input type="hidden" id="ContentPurseID" name="id" value="">
                <div class="form-group">
                    <label>Fecha de Pago</label>
                    <input type="date" class="form-control form-control-sm number-lg" id="ContentPurseDATE" name="fecha_pago" value="">
                    <div class="error_fecha text-danger ActiveError d-none">

                    </div>
                </div>
                <div class="form-group">
                    <label>Valor de Cuota</label>
                    <input type="text" class="form-control form-control-sm miles number-lg" onkeypress="return valideKey(event);" id="ContentPurseCUOTA" name="cuota" value="">
                    <div class="error_valor text-danger ActiveError d-none">

                    </div>
                </div>
                <div>
                    <label>Comentario</label>
                    <textarea name="comentario" id="comentarioP" class="form-control" id="" cols="30" rows="10"></textarea>
                    <div class="error_comentario text-danger ActiveError d-none">

                    </div>
                </div>
                <div class="custom-control custom-checkbox mb-3 checkbox-warning my-2">
                    <input type="checkbox" class="custom-control-input" value="todos" name="ModifyInputLabel" id="customCheckBox4">
                    <label class="custom-control-label" for="customCheckBox4">Modificar todas hacia abajo.</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light btn-sm" data-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                <button type="submit" class="btn btn-primary btn-sm ejecutarmodal"><i class="fa-solid fa-floppy-disk"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>


<button type="button" id="ButtonModalHistory" class="btn btn-primary mb-2 d-none" data-toggle="modal" data-target="#ModalHistory" >Small modal</button>
  <div class="modal fade" id="ModalHistory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog mw-100 w-75">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" id="CloseModalHistory" class="close" data-dismiss="modal"><span>×</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped table-responsive-sm">
                    <thead class="thead-secondary text-primary text-center">
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Fecha de Pago</th>
                            <th scope="col">Cuota</th>
                            <th scope="col">Comentario</th>
                            <th scope="col">Registro</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody id="tableHistory">

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light btn-sm" data-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
    </div>
</div>

<button type="button" id="ButtonadminModal1" class="btn btn-primary mb-2 d-none" data-toggle="modal" data-target="#adminModal1" >Small modal</button>
<div class="modal fade" id="adminModal1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">¿Desea eliminar este Items?</h5>
                <button type="button" id="CloseadminModal1" class="close" data-dismiss="modal"><span>×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ModalPasswordAdmin" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="ElimationPorID">
                    <div class="form-group">
                        <label>Contraseña (Administrador)</label>
                        <input type="password" name="password" class="form-control form-control-sm number-lg" id="passwordADMIN"  >
                        <div class="error_password text-danger ActiveError d-none">
    
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary light" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash mr-2"></i>Eliminar</button>
            </div>
            </form>
        </div>
    </div>
</div>

<button type="button" id="ButtonadminModal2" class="btn btn-primary mb-2 d-none" data-toggle="modal" data-target="#adminModal2" >Small modal</button>
<div class="modal fade" id="adminModal2" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" id="CloseadminModal2" class="close" data-dismiss="modal"><span>×</span>
                </button>
            </div>
            <div class="modal-body" id="contentResponse">
                
                    
            </div>
        </div>
    </div>
</div>

<button type="button" id="ButtonTickets" class="btn btn-primary mb-2 d-none" data-toggle="modal" data-target="#Tickets" >Small modal</button>
<div class="modal fade" id="Tickets" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" id="CloseTickets" class="close" data-dismiss="modal"><span>×</span>
                </button>
            </div>
            <div class="modal-body" id="contentTickets">
                <img
                src="{{ asset('dimages/LogoIntesa.png') }}"
                alt="Logotipo">
            <p class="text-center">TICKET DE VENTA<br>New New York<br>17/10/2017
                02:22 a.m.</p>
                <table class="">
                    <thead>
                        <tr>
                            <th>CANT</th>
                            <th>PRODUCTO</th>
                            <th>$$</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>papa</td>
                            <td>$1500</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>papa</td>
                            <td>$1000</td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn btn-primary btn-sm">Imprimir</button>

            </div>
        </div>
    </div>
</div>

<div class="ventanaFlotanteOver">
    Hola mundo.
</div>

@push('scripts')
<script>
jQuery(document).ready(function($) {
    // Mostrar errores de validación de Laravel en los campos correspondientes
    @if ($errors->any())
        @foreach($errors->keys() as $key)
            @php
                $errorSelector = '';
                switch($key) {
                    case 'valor_semestre':
                        $errorSelector = '.error_vs';
                        break;
                    case 'numero_semestre':
                        $errorSelector = '.error_ns';
                        break;
                    case 'valor_total_semestre':
                        $errorSelector = '.error_vtp';
                        break;
                    case 'descuento':
                        $errorSelector = '.error_d';
                        break;
                    case 'valor_neto':
                        $errorSelector = '.error_tn';
                        break;
                    case 'saldo_financiar':
                        $errorSelector = '.error_sf';
                        break;
                    case 'periodo':
                        $errorSelector = '.error_p';
                        break;
                    case 'numero_cuotas':
                        $errorSelector = '.error_nc';
                        break;
                    case 'valor_cuotas':
                        $errorSelector = '.error_vc';
                        break;
                    case 'fecha_pago':
                        $errorSelector = '.error_fp';
                        break;
                }
            @endphp
            @if($errorSelector)
                $('{{ $errorSelector }}').removeClass('d-none');
                $('{{ $errorSelector }}').html('<small><i class="fa-solid fa-exclamation-circle mr-1"></i>{!! addslashes($errors->first($key)) !!}</small>');
            @endif
        @endforeach
        
        // Scroll al primer error
        var firstError = $(".ActiveError:not(.d-none)").first();
        if(firstError.length){
            $('html, body').animate({
                scrollTop: firstError.offset().top - 100
            }, 500);
        }
    @endif

    // Lógica para el manejo dinámico de semestres
    $('#total_semestres').on('input change', function() {
        var total = parseInt($(this).val());
        if (isNaN(total) || total < 1) total = 1;
        if (total > 10) total = 10;

        // Actualizar selector y visibilidad
        $('#selector_semestre option').each(function() {
            var val = parseInt($(this).val());
            if (val <= total) {
                $(this).prop('disabled', false);
            } else {
                $(this).prop('disabled', true);
            }
        });

        // Si el seleccionado actualmente es mayor que el nuevo total, volver al 1
        if (parseInt($('#selector_semestre').val()) > total) {
            $('#selector_semestre').val(1).trigger('change');
        }

        // Copiar valores del semestre 1 a los nuevos si están vacíos (herencia)
        if (total > 1) {
            var v1 = $('#valor_semestre_1').val();
            var d1 = $('#descuento_1').val();
            var n1 = $('#numero_cuotas_1').val();

            for (var i = 2; i <= total; i++) {
                if ($('#valor_semestre_' + i).val() === '') {
                    $('#valor_semestre_' + i).val(v1).trigger('keyup');
                }
                if ($('#descuento_' + i).val() === '') {
                    $('#descuento_' + i).val(d1).trigger('keyup');
                }
                if ($('#numero_cuotas_' + i).val() === '') {
                    $('#numero_cuotas_' + i).val(n1).trigger('keyup');
                }
            }
        }
    });

    $('#selector_semestre').on('change', function() {
        var semestre = $(this).val();
        $('.seccion-semestre').addClass('d-none');
        $('#seccion_semestre_' + semestre).removeClass('d-none');
    });

    // Cálculos por semestre
    $(document).on('keyup', '.input-valor-semestre, .input-descuento, .input-numero-cuotas', function() {
        var sem = $(this).data('semestre');
        calcularValoresSemestre(sem);
    });

    function calcularValoresSemestre(sem) {
        var valorStr = $('#valor_semestre_' + sem).val().replace(/\./g, '');
        var descuentoStr = $('#descuento_' + sem).val().replace(/\./g, '');
        var nCuotas = parseInt($('#numero_cuotas_' + sem).val());

        var valor = parseInt(valorStr) || 0;
        var descuento = parseInt(descuentoStr) || 0;

        var neto = Math.max(0, valor - descuento);

        $('#valor_neto_' + sem).val(dar_formato(neto));
        $('input[name="semestres[' + sem + '][valor_neto]"]').val(neto);
        $('input[name="semestres[' + sem + '][valor_total_semestre]"]').val(valor);
        $('input[name="semestres[' + sem + '][saldo_financiar]"]').val(neto);

        if (nCuotas > 0) {
            var vCuota = Math.round(neto / nCuotas);
            $('#valor_cuotas_' + sem).val(dar_formato(vCuota));
            $('input[name="semestres[' + sem + '][valor_cuotas]"]').val(vCuota);
        } else {
            $('#valor_cuotas_' + sem).val('0');
            $('input[name="semestres[' + sem + '][valor_cuotas]"]').val('0');
        }
    }

    // Botón guardar costos unificado
    $('#btn_guardar_costos').on('click', function(e) {
        e.preventDefault();
        var total = parseInt($('#total_semestres').val());

        // Deshabilitar semestres que no se usan antes de enviar
        for (var i = 1; i <= 10; i++) {
            if (i > total) {
                $('#seccion_semestre_' + i + ' input, #seccion_semestre_' + i + ' select').prop('disabled', true);
            } else {
                $('#seccion_semestre_' + i + ' input, #seccion_semestre_' + i + ' select').prop('disabled', false);
            }
        }

        $('#FormValueProgram').submit();
    });
});

// Función para enviar formulario de creación de abono/otros ingresos
function EnviarDatos(event) {
    event.preventDefault();
    var form = $(event.target);
    var nombreFormulario = form.find('.nombreForm__Class').val();
    
    // Determinar qué tipo de formulario es
    var obj;
    if(nombreFormulario === 'Entry') {
        obj = ENTRY.createForm('#formEntry');
    } else if(nombreFormulario === 'Other') {
        obj = OtherENTRIES.createForm('#formEntry1');
    } else {
        alert('Error: Tipo de formulario no reconocido');
        return false;
    }
    
    obj.done(function(response) {
        // Manejar respuesta como texto "OK" o JSON
        var success = false;
        if(typeof response === 'object' && response.success) {
            success = true;
        } else if(typeof response === 'string' && (response == 'OK' || response.trim() == 'OK')) {
            success = true;
        }
        
        if(success) {
            // Cerrar el modal primero
            if(form.closest('.modal').length) {
                form.closest('.modal').modal('hide');
            }
            
            // Recargar la página para mostrar los nuevos datos
            location.reload();
        } else {
            alert('Error al guardar el registro');
        }
    }).fail(function(xhr) {
        console.error('Error al guardar:', xhr);
        
        // Si hay errores de validación, mostrarlos
        if(xhr.responseJSON && xhr.responseJSON.errors) {
            var errores = xhr.responseJSON.errors;
            var mensajeError = 'Errores de validación:\n';
            for(var campo in errores) {
                mensajeError += '- ' + errores[campo][0] + '\n';
            }
            alert(mensajeError);
        } else {
            alert('Error al guardar el registro');
        }
    });
    
    return false;
}
</script>
@endpush

@endsection