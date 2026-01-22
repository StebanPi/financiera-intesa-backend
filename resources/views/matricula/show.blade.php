@extends('dash.app')

@section('page')
    Ver Estudiante - Matrícula
@endsection
@php 
// Usar placeholder para foto ya que matrícula no tiene foto
$ruta = asset('dimages/LogoIntesa.png'); 
date_default_timezone_set("America/Bogota");
@endphp
@section('content')
    <div class="profile card card-body px-3 pt-3 pb-0">
        <div class="profile-head">
         
            <div class="profile-info">
                        <div class="profile-photo">
                    <img src="{{ $ruta }}" class="img-fluid rounded-circle" alt="" style="width: 80px; height: 80px; object-fit: cover;">
                </div>
                <div class="profile-details">
                    <div class="profile-name px-3 pt-2">
                        <h4 class="text-primary mb-0">{{ $student[0]->nombre }}</h4>
                        <p>{{ $matricula->tipo_documento }} {{ $student[0]->cedula }}</p>
                    </div>
                    <div class="profile-email px-2 pt-2">
                        <h4 class="text-muted mb-0">{{ $student[0]->nombre_programa }}</h4>
                        <p><span class="badge light badge-primary">{{ $student[0]->estado }}</span></p>
                        @if($matricula->sede)
                            <p><small class="text-muted">Sede: {{ $matricula->sede }}</small></p>
                        @endif
                    </div>
                    <div class="profile-email px-2 pt-2">
                        @if($cost->id != "")
                            <a href="{{ route('entry.ViewPdfUnitedOther',$cost->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download mr-2"></i> Pagos</a>
                        @endif
                        <a href="{{ route('matricula.ficha', $student[0]->cod_alumno) }}" class="btn btn-outline-success btn-sm ml-2"><i class="fa-solid fa-file-lines mr-2"></i> Ficha de Matrícula</a>
                        <a href="{{ route('matricula.ficha.view', $student[0]->cod_alumno) }}" class="btn btn-outline-info btn-sm ml-2" target="_blank"><i class="fa-solid fa-eye mr-2"></i> Ver PDF</a>
                        <a href="{{ route('matricula.ficha.download', $student[0]->cod_alumno) }}" class="btn btn-outline-danger btn-sm ml-2" target="_blank"><i class="fa-solid fa-file-pdf mr-2"></i> Generar PDF</a>
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
    <input type="hidden" id="cod_alumno_hidden" value="{{ $student[0]->cod_alumno }}">
    <input type="hidden" id="id_cost" value="{{ $cost->id ?? '' }}">
    <input type="hidden" id="cod_alumno" value="{{ $student[0]->cod_alumno }}">
    <div class="row">
        <div class="col-md-4">
            <div class="card stickyMenu mr-2 bg-gray1" id="stickyMenuWidth">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-2">
                        <h5 class="text-primary my-0 text-sm font-semibold"><i class="fa-solid fa-sack-dollar mr-1"></i> Información de Costos</h5>
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-xs font-medium transition-colors" data-toggle="modal" data-target="#modalConfigurarCostos">
                            <i class="fa-solid fa-cog mr-2"></i>Configurar
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
                            @if(auth()->check() && auth()->user()->hasRole('super-admin'))
                            <div class="mt-2 pt-2 border-t border-gray-200">
                                <button type="button" class="w-full bg-red-500 hover:bg-red-600 text-white px-2 py-2 rounded text-xs font-medium transition-colors" id="btnEliminarCostosEstudiante" data-cod-alumno="{{ $student[0]->cod_alumno }}">
                                    <i class="fa-solid fa-trash-alt mr-1"></i>Eliminar Costos
                                </button>
                            </div>
                            @endif
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
                                                    @if($cost->id != "")
                                                        <button type="button" class="btn btn-primary btn-xxs my-2 mr-2 rounded-d ml-2 mb-4" data-toggle="modal" data-target="#staticBackdrop">+</button>
                                                    @else
                                                        <div class="alert alert-warning my-2 ml-2 mb-4">
                                                            <small><i class="fa-solid fa-exclamation-triangle mr-1"></i>Configure primero la información de costos para agregar abonos.</small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-auto">
                                                    @if($cost->id != "")
                                                        <a target="__blank" href="{{ route('entry.Viewpdf',$cost->id) }}" class="btn btn-xs bg-violet text-white"><i class="fa-solid fa-file-pdf mr-2"></i>PDF</a>
                                                    @endif
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
                                                    @if($cost->id != "")
                                                        <button type="button" class="btn btn-primary btn-xxs my-2 mr-2 rounded-d ml-2 mb-4" data-toggle="modal" data-target="#ModalOtrosAbonos">+</button>
                                                    @else
                                                        <div class="alert alert-warning my-2 ml-2 mb-4">
                                                            <small><i class="fa-solid fa-exclamation-triangle mr-1"></i>Configure primero la información de costos para agregar otros ingresos.</small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-auto">
                                                    @if($cost->id != "")
                                                        <a target="__blank" href="{{ route('other.entry.Viewpdf',$cost->id) }}" class="btn btn-xs bg-violet text-white"><i class="fa-solid fa-file-pdf mr-2"></i>PDF</a>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-responsive-sm">
                                                    <thead class="thead-secondary text-primary text-center">
                                                        <tr>
                                                            <th scope="col">Con.</th>
                                                            <th scope="col">Fecha</th>
                                                            <th scope="col">Concepto</th>
                                                            <th scope="col">Elaborado Por</th>
                                                            <th scope="col">Valor</th>
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
                                        @if ($Purses != "" && $cost->id != "")
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
                                                                <th scope="col">Semestre</th>
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
                                        @else
                                            <div class="mt-4 text-center p-4">
                                                <div class="alert alert-info">
                                                    <i class="fa-solid fa-info-circle mr-2"></i>
                                                    <strong>No hay información de cartera disponible.</strong>
                                                    <p class="mb-0 mt-2">Por favor, configure primero la información de costos y guarde para poder ver la cartera.</p>
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
                            <label>Cédula</label>
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
                        @if($cost->id != "")
                            <input type="hidden" name="id_cost" value="{{ $cost->id }}">
                        @else
                            <div class="alert alert-warning">
                                <small><i class="fa-solid fa-exclamation-triangle mr-1"></i>Debe configurar primero la información de costos antes de agregar abonos.</small>
                            </div>
                            <input type="hidden" name="id_cost" value="">
                        @endif
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
                            <input name="fecha_recibo" value="@php echo date('Y-m-d'); @endphp" id="fechaReciboAttr" type="date" class="form-control form-control-sm number-lg fechaRecibo__Class">
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
                        <div class="form-group">
                            <label>Forma de Pago <small class="text-danger">(Obligatorio)</small></label>
                            <select name="forma" id="formaAttr" class="form-control form-control-sm number-lg" tabindex="-98" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Consignación">Banco</option>
                            </select>
                            <div class="error_forma text-danger d-none ActiveError">
                            </div>
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
                            <label>Cédula</label>
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
                        @if($cost->id != "")
                            <input type="hidden" name="id_cost" value="{{ $cost->id }}">
                        @else
                            <div class="alert alert-warning">
                                <small><i class="fa-solid fa-exclamation-triangle mr-1"></i>Debe configurar primero la información de costos antes de agregar otros ingresos.</small>
                            </div>
                            <input type="hidden" name="id_cost" value="">
                        @endif
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
                            <input name="fecha_recibo" id="fechaReciboAttr" value="@php echo date('Y-m-d'); @endphp" type="date" class="form-control form-control-sm number-lg fechaRecibo__Class" >
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
                        <div class="form-group">
                            <label>Forma de Pago <small class="text-danger">(Obligatorio)</small></label>
                            <select name="forma" id="formaAttrOther" class="form-control form-control-sm number-lg" tabindex="-98" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Consignación">Banco</option>
                            </select>
                            <div class="error_forma text-danger d-none ActiveError">
                            </div>
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
                <input type="hidden" name="redirect_to" value="matricula">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fa-solid fa-hashtag mr-2"></i>Número Total de Semestres</label>
                            <div class="d-flex">
                                @php
                                    // Obtener el número máximo de semestre configurado
                                    $maxSemestre = 0;
                                    if ($costs && $costs->isNotEmpty()) {
                                        foreach ($costs as $cost) {
                                            $numSem = isset($cost->numero_semestre) ? (int)$cost->numero_semestre : 0;
                                            if ($numSem > $maxSemestre) {
                                                $maxSemestre = $numSem;
                                            }
                                        }
                                    }
                                    $totalSemestresInput = max(1, max(count($costs), $maxSemestre));
                                @endphp
                                <input type="number" value="{{ $totalSemestresInput }}" name="total_semestres" id="total_semestres" class="form-control" min="1" max="10" style="flex: 1;">
                                <button type="button" id="btn_actualizar_select" class="btn btn-primary ml-2" title="Actualizar selector de semestres">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-group mb-0 w-100">
                            <label class="font-weight-bold">Seleccionar Semestre para Configurar:</label>
                            <select id="selector_semestre" class="form-control no-selectpicker" data-no-selectpicker="true" data-initial-total="{{ $totalSemestresInput }}">
                                @for($i = 1; $i <= $totalSemestresInput; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>Semestre {{ $i }}</option>
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
                                        <select class="form-control form-control-sm input-periodo" name="semestres[{{ $i }}][periodo]" id="periodo_{{ $i }}" data-semestre="{{ $i }}">
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
                                                <input type="date" name="semestres[{{ $i }}][fecha_pago]" value="{{ $c->fecha_pago }}" id="fecha_pago_{{ $i }}" class="form-control form-control-sm input-fecha-pago" data-semestre="{{ $i }}" {{ $i == 1 ? '' : 'readonly' }}>
                                                @if($i > 1)
                                                    <small class="text-muted"><i class="fa-solid fa-info-circle"></i> Calculada automáticamente</small>
                                                @endif
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

  <!-- Modal Editar Abono -->
  <div class="modal fade" id="ModalEditEntry" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalEditEntryLabel" aria-hidden="true">
    <div class="modal-dialog mw-100 w-50" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalEditEntryLabel"><i class="fa-solid fa-edit mr-2"></i>Editar Abono</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form method="POST" id="formEditEntry" onsubmit="return EnviarDatosEditEntry(event)">
                @csrf
                <input type="hidden" name="redirect_to" value="matricula">
                <input type="hidden" id="editEntryId" name="id" value="">
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" value="{{ $student[0]->cod_alumno }}" name="cod_alumno">
                        <div class="form-group">
                            <label>Cédula</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->cedula }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Estudiante</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Programa</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre_programa}}" readonly>
                        </div>
                        @if($cost->id != "")
                            <input type="hidden" id="editEntryIdCost" name="id_cost" value="{{ $cost->id }}">
                        @endif
                        <div class="form-group">
                            <label>Concepto</label>
                            <select name="concepto" id="editEntryConcepto" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($conceptos as $item)
                                    @if ($item->estado == "1")
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descripción <small class="text-danger">(Obligatorio)</small></label>
                            <textarea name="descripcion" id="editEntryDescripcion" class="form-control form-control-sm number-lg" cols="30" rows="10"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No.Recibo</label>
                            <input name="no_recibo" id="editEntryNoRecibo" type="number" class="form-control form-control-sm number-lg" readonly>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Recibo</label>
                            <input name="fecha_recibo" id="editEntryFechaRecibo" type="date" class="form-control form-control-sm number-lg">
                        </div>
                        <div class="form-group">
                            <label>Valor <small class="text-danger">(Obligatorio)</small></label>
                            <input name="valor" id="editEntryValor" type="text" class="form-control form-control-sm number-lg miles" onkeypress="return valideKey(event);">
                        </div>
                        <div class="form-group">
                            <label>Elaborado Por <small class="text-danger">(Obligatorio)</small></label>
                            <select name="elaborado_por" id="editEntryElaboradoPor" class="form-control form-control-sm number-lg" tabindex="-98">
                                <option value="0">Busca tu nombre</option>
                                @foreach ($elaborados as $item)
                                    @if ($item->estado == "1")
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>DEBE</b></label>
                            <select name="debe" id="editEntryDebe" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($debe as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>HABER</b></label>
                            <select name="haber" id="editEntryHaber" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($haber as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Forma de Pago <small class="text-danger">(Obligatorio)</small></label>
                            <select name="forma" id="editEntryForma" class="form-control form-control-sm number-lg" tabindex="-98" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Bancos">Bancos</option>
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-primary ejecutarmodal"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar</button>
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
    </form>
      </div>
    </div>
  </div>

  <!-- Modal Editar Otros Ingresos -->
  <div class="modal fade" id="ModalEditOtherEntry" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalEditOtherEntryLabel" aria-hidden="true">
    <div class="modal-dialog mw-100 w-50" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalEditOtherEntryLabel"><i class="fa-solid fa-edit mr-2"></i>Editar Otros Ingresos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form method="POST" id="formEditOtherEntry" onsubmit="return EnviarDatosEditOtherEntry(event)">
                @csrf
                <input type="hidden" name="redirect_to" value="matricula">
                <input type="hidden" id="editOtherEntryId" name="id" value="">
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" value="{{ $student[0]->cod_alumno }}" name="cod_alumno">
                        <div class="form-group">
                            <label>Cédula</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->cedula }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Estudiante</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Programa</label>
                            <input type="text" class="form-control form-control-sm number-lg" value="{{ $student[0]->nombre_programa}}" readonly>
                        </div>
                        @if($cost->id != "")
                            <input type="hidden" id="editOtherEntryIdCost" name="id_cost" value="{{ $cost->id }}">
                        @endif
                        <div class="form-group">
                            <label>Concepto</label>
                            <select name="concepto" id="editOtherEntryConcepto" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($otrosConceptos as $item)
                                    @if ($item->estado == "1")
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descripción <small class="text-danger">(Obligatorio)</small></label>
                            <textarea name="descripcion" id="editOtherEntryDescripcion" class="form-control form-control-sm number-lg" cols="30" rows="10"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No.Recibo</label>
                            <input name="no_recibo" id="editOtherEntryNoRecibo" type="number" class="form-control form-control-sm number-lg" readonly>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Recibo</label>
                            <input name="fecha_recibo" id="editOtherEntryFechaRecibo" type="date" class="form-control form-control-sm number-lg">
                        </div>
                        <div class="form-group">
                            <label>Valor <small class="text-danger">(Obligatorio)</small></label>
                            <input name="valor" id="editOtherEntryValor" type="text" class="form-control form-control-sm number-lg miles" onkeypress="return valideKey(event);">
                        </div>
                        <div class="form-group">
                            <label>Elaborado Por <small class="text-danger">(Obligatorio)</small></label>
                            <select name="elaborado_por" id="editOtherEntryElaboradoPor" class="form-control form-control-sm number-lg" tabindex="-98">
                                <option value="0">Busca tu nombre</option>
                                @foreach ($elaborados as $item)
                                    @if ($item->estado == "1")
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>DEBE</b></label>
                            <select name="debe" id="editOtherEntryDebe" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($debe as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Contable <b>HABER</b></label>
                            <select name="haber" id="editOtherEntryHaber" class="form-control form-control-sm number-lg" tabindex="-98">
                                @foreach ($haber as $item)
                                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Forma de Pago <small class="text-danger">(Obligatorio)</small></label>
                            <select name="forma" id="editOtherEntryForma" class="form-control form-control-sm number-lg" tabindex="-98" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Bancos">Bancos</option>
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-primary ejecutarmodal"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar</button>
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
    </form>
      </div>
    </div>
  </div>

  <form  method="POST" action="{{ route('purse.all') }}" id="purseAll">
    @csrf
    <input type="hidden" name="id" value="{{ $cost->id ?? '' }}">
  </form>

  <form  method="POST"  id="FormPurseHistory1">
    @csrf
    <input type="hidden" name="id_cost" value="{{ $cost->id ?? '' }}">
  </form>

  <form id="FormRequestOtros">
    @csrf
    <input type="hidden" name="id" id="IdContentOperation" value="">

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

@push('styles')
<style>
    /* Estilos para botones outline - texto blanco en hover con máxima especificidad */
    a.btn.btn-outline-primary:hover,
    a.btn.btn-outline-primary:focus,
    a.btn.btn-outline-primary:active,
    a.btn.btn-outline-primary:not(:disabled):not(.disabled):active,
    a.btn.btn-outline-success:hover,
    a.btn.btn-outline-success:focus,
    a.btn.btn-outline-success:active,
    a.btn.btn-outline-success:not(:disabled):not(.disabled):active,
    a.btn.btn-outline-info:hover,
    a.btn.btn-outline-info:focus,
    a.btn.btn-outline-info:active,
    a.btn.btn-outline-info:not(:disabled):not(.disabled):active,
    a.btn.btn-outline-danger:hover,
    a.btn.btn-outline-danger:focus,
    a.btn.btn-outline-danger:active,
    a.btn.btn-outline-danger:not(:disabled):not(.disabled):active {
        color: #ffffff !important;
    }
    
    /* Incluir iconos y texto dentro de los botones */
    a.btn.btn-outline-primary:hover *,
    a.btn.btn-outline-primary:focus *,
    a.btn.btn-outline-primary:active *,
    a.btn.btn-outline-success:hover *,
    a.btn.btn-outline-success:focus *,
    a.btn.btn-outline-success:active *,
    a.btn.btn-outline-info:hover *,
    a.btn.btn-outline-info:focus *,
    a.btn.btn-outline-info:active *,
    a.btn.btn-outline-danger:hover *,
    a.btn.btn-outline-danger:focus *,
    a.btn.btn-outline-danger:active * {
        color: #ffffff !important;
    }
    
    /* Estilos para botón PDF con bg-violet - cambio de color de fondo en hover */
    a.btn.bg-violet:hover,
    a.btn.bg-violet:focus,
    a.btn.bg-violet:active,
    .btn.bg-violet:hover,
    .btn.bg-violet:focus,
    .btn.bg-violet:active {
        color: #ffffff !important;
        background-color: #4610a3 !important;
        border-color: #4610a3 !important;
    }
    
    /* Incluir iconos y texto dentro del botón PDF */
    a.btn.bg-violet:hover *,
    a.btn.bg-violet:focus *,
    a.btn.bg-violet:active *,
    .btn.bg-violet:hover *,
    .btn.bg-violet:focus *,
    .btn.bg-violet:active * {
        color: #ffffff !important;
    }
    
    /* Estilos para campos disabled - fondo oscuro y texto oscuro */
    input:disabled,
    input.disabled-input,
    .form-control:disabled,
    .form-control.disabled-input {
        background-color: #e9ecef !important;
        color: #495057 !important;
        opacity: 1 !important;
        cursor: not-allowed !important;
    }
    
    select:disabled,
    select.form-control:disabled {
        background-color: #e9ecef !important;
        color: #495057 !important;
        opacity: 1 !important;
        cursor: not-allowed !important;
    }
    
    /* Estilos para el select "Periodo de Pago" - fondo blanco para indicar que es editable */
    .periodo-pago-container .dropdown-toggle,
    .bootstrap-select.periodo-pago-container .dropdown-toggle {
        background-color: #ffffff !important;
        color: #212529 !important;
        border-color: #ced4da !important;
    }
    
    .periodo-pago-container .dropdown-toggle:hover,
    .bootstrap-select.periodo-pago-container .dropdown-toggle:hover {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border-color: #adb5bd !important;
    }
    
    .periodo-pago-container .dropdown-toggle:focus,
    .periodo-pago-container .dropdown-toggle:active,
    .bootstrap-select.periodo-pago-container .dropdown-toggle:focus,
    .bootstrap-select.periodo-pago-container .dropdown-toggle:active {
        background-color: #ffffff !important;
        color: #212529 !important;
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
</style>
@endpush

@push('scripts')
<script>
jQuery(document).ready(function($) {
    // Mostrar errores de validación de Laravel en los campos correspondientes
    @if ($errors->any())
        var errorMap = {
            'valor_semestre': '.error_vs',
            'numero_semestre': '.error_ns',
            'valor_total_semestre': '.error_vtp',
            'descuento': '.error_d',
            'valor_neto': '.error_tn',
            'saldo_financiar': '.error_sf',
            'periodo': '.error_p',
            'numero_cuotas': '.error_nc',
            'valor_cuotas': '.error_vc',
            'fecha_pago': '.error_fp'
        };
        
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

    // Inicializar campos hidden con valores sin formato al cargar la página
    function initializeHiddenFields() {
        // Obtener valores de los campos visibles y actualizar los hidden
        var vts = $('#valor_total_semestre').val();
        if(vts) {
            var vtsClean = vts.replace(/\./g, '');
            $('input[name="valor_total_semestre"]').val(vtsClean);
        }
        
        var vn = $('#valor_neto').val();
        if(vn) {
            var vnClean = vn.replace(/\./g, '');
            $('input[name="valor_neto"]').val(vnClean);
        }
        
        var sf = $('#saldo_financiar').val();
        if(sf) {
            var sfClean = sf.replace(/\./g, '');
            $('input[name="saldo_financiar"]').val(sfClean);
        }
        
        var vc = $('#valor_cuota').val();
        if(vc) {
            var vcClean = vc.replace(/\./g, '');
            $('input[name="valor_cuotas"]').val(vcClean);
        }
    }
    
    // Inicializar al cargar la página
    initializeHiddenFields();
    
    // También inicializar cuando se actualizan los valores calculados
    // Esto asegura que los campos hidden se actualicen incluso si el usuario no interactúa con los campos base
    $('#valor_total_semestre, #valor_neto, #saldo_financiar, #valor_cuota').on('change', function() {
        var value = $(this).val();
        if(value) {
            var cleanValue = value.replace(/\./g, '');
            var fieldName = $(this).attr('id');
            if(fieldName === 'valor_total_semestre') {
                $('input[name="valor_total_semestre"]').val(cleanValue);
            } else if(fieldName === 'valor_neto') {
                $('input[name="valor_neto"]').val(cleanValue);
            } else if(fieldName === 'saldo_financiar') {
                $('input[name="saldo_financiar"]').val(cleanValue);
            } else if(fieldName === 'valor_cuota') {
                $('input[name="valor_cuotas"]').val(cleanValue);
            }
        }
    });
    
    // Lógica para el manejo dinámico de semestres
    function actualizarSelectorSemestres(forceUpdate) {
        var total = parseInt($('#total_semestres').val());
        if (isNaN(total) || total < 1) total = 1;
        if (total > 10) total = 10;

        var $select = $('#selector_semestre');
        if ($select.length === 0) {
            console.log('Select no encontrado');
            return;
        }
        
        var currentSelected = parseInt($select.val()) || 1;
        var currentOptions = $select.find('option').length;
        
        console.log('Actualizando select - Total:', total, 'Opciones actuales:', currentOptions, 'Forzar:', forceUpdate); // Debug
        
        // Asegurar que selectpicker NO se inicialice en este select
        $select.addClass('no-selectpicker').attr('data-no-selectpicker', 'true');
        
        // Si selectpicker ya está activo, destruirlo completamente
        if (typeof $.fn.selectpicker !== 'undefined') {
            try {
                if ($select.hasClass('selectpicker') || $select.data('selectpicker')) {
                    $select.selectpicker('destroy');
                    $select.removeClass('selectpicker');
                }
                // Remover cualquier wrapper de Bootstrap Select
                var $parent = $select.parent();
                if ($parent.hasClass('bootstrap-select')) {
                    $select.detach();
                    $parent.replaceWith($select);
                }
            } catch(e) {
                console.log('Error al destruir selectpicker:', e);
            }
        }
        
        // Actualizar siempre si se fuerza, o si el número de opciones es diferente
        if (forceUpdate || currentOptions !== total) {
            // Guardar el valor seleccionado actual si es válido
            if (currentSelected > total) {
                currentSelected = 1;
            }
            
            // Detener cualquier animación o proceso en curso
            $select.stop(true, true);
            
            // Limpiar y recrear todas las opciones
            $select.empty();
            for (var i = 1; i <= total; i++) {
                var selected = (i === currentSelected) ? 'selected' : '';
                $select.append('<option value="' + i + '" ' + selected + '>Semestre ' + i + '</option>');
            }
            
            // Asegurar que el valor seleccionado sea válido
            $select.val(currentSelected);
            
            // Forzar re-render del navegador
            $select[0].offsetHeight; // Trigger reflow
            
            // Disparar eventos para actualizar cualquier listener
            $select.trigger('change');
            
            console.log('Select actualizado con', total, 'opciones. Opciones en DOM:', $select.find('option').length); // Debug
            console.log('Opciones visibles:', $select.find('option').map(function() { return $(this).text(); }).get()); // Debug
            
            // Disparar evento change si cambió la selección
            if (currentSelected > total) {
                $select.trigger('change');
            }
        }
    }
    
    // Botón para forzar actualización manual
    $('#btn_actualizar_select').on('click', function() {
        console.log('Botón de actualización presionado');
        actualizarSelectorSemestres(true);
        // Mostrar feedback visual
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.html('<i class="fa-solid fa-check"></i>').addClass('btn-success').removeClass('btn-primary');
        setTimeout(function() {
            $btn.html(originalHtml).removeClass('btn-success').addClass('btn-primary');
        }, 1000);
    });

    // Manejar cambios en el selector de semestre
    $('#selector_semestre').on('change', function() {
        var semestre = parseInt($(this).val()) || 1;
        $('.seccion-semestre').addClass('d-none');
        $('#seccion_semestre_' + semestre).removeClass('d-none');
    });
    
    // Manejar cambios en el campo de total de semestres - actualización inmediata
    // Usar .off() primero para evitar múltiples bindings
    $('#total_semestres').off('input keyup change').on('input keyup change', function() {
        var total = parseInt($(this).val());
        if (isNaN(total) || total < 1) total = 1;
        if (total > 10) {
            total = 10;
            $(this).val(10);
        }
        
        console.log('Total semestres cambiado a:', total); // Debug
        
        // Actualizar el select inmediatamente (forzar actualización)
        actualizarSelectorSemestres(true);
        
        // Si el semestre seleccionado es mayor que el nuevo total, cambiar al primero
        var selected = parseInt($('#selector_semestre').val()) || 1;
        if (selected > total) {
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
        
        // Recalcular todas las fechas después de cambiar el total
        for (var i = 2; i <= total; i++) {
            calcularFechaInicioSemestre(i);
        }
    });
    
    // Prevenir que selectpicker se inicialice en nuestro select
    var $selectorSemestre = $('#selector_semestre');
    if ($selectorSemestre.length) {
        $selectorSemestre.addClass('no-selectpicker').attr('data-no-selectpicker', 'true');
        
        // Si selectpicker ya se inicializó, destruirlo
        if (typeof $.fn.selectpicker !== 'undefined') {
            try {
                if ($selectorSemestre.hasClass('selectpicker') || $selectorSemestre.data('selectpicker')) {
                    $selectorSemestre.selectpicker('destroy');
                    $selectorSemestre.removeClass('selectpicker');
                }
                // Remover wrapper de Bootstrap Select si existe
                var $parent = $selectorSemestre.parent();
                if ($parent.hasClass('bootstrap-select')) {
                    $selectorSemestre.detach();
                    $parent.replaceWith($selectorSemestre);
                }
            } catch(e) {
                console.log('Error al prevenir selectpicker:', e);
            }
        }
    }
    
    // Inicializar al cargar la página - mover fuera del document.ready anidado
    // Asegurar que el select tenga todas las opciones correctas
    actualizarSelectorSemestres();
    
    // Calcular fechas de inicio de todos los semestres al cargar
    var totalSemestres = parseInt($('#total_semestres').val()) || 1;
    for (var i = 2; i <= totalSemestres; i++) {
        calcularFechaInicioSemestre(i);
    }
    
    // Mostrar el primer semestre por defecto
    var initialSemestre = parseInt($('#selector_semestre').val()) || 1;
    $('.seccion-semestre').addClass('d-none');
    $('#seccion_semestre_' + initialSemestre).removeClass('d-none');

    // Función para calcular la fecha de inicio de un semestre basándose en el anterior
    function calcularFechaInicioSemestre(semestre) {
        if (semestre <= 1) {
            return; // El semestre 1 no se calcula, es manual
        }
        
        // Obtener fecha de inicio del semestre anterior
        var fechaAnterior = $('#fecha_pago_' + (semestre - 1)).val();
        if (!fechaAnterior) {
            return;
        }
        
        // Obtener número de cuotas del semestre anterior
        var numCuotasAnterior = parseInt($('#numero_cuotas_' + (semestre - 1)).val()) || 0;
        if (numCuotasAnterior <= 0) {
            return;
        }
        
        // Obtener período del semestre anterior
        var periodoAnterior = $('#periodo_' + (semestre - 1)).val();
        
        // Calcular fecha de inicio del semestre actual
        var fecha = new Date(fechaAnterior);
        var año = fecha.getFullYear();
        var mes = fecha.getMonth() + 1; // JavaScript usa 0-11, necesitamos 1-12
        var dia = fecha.getDate();
        
        // Calcular según el período
        if (periodoAnterior === 'Mensual') {
            // Sumar meses según número de cuotas
            mes += numCuotasAnterior;
            // Ajustar año si es necesario
            while (mes > 12) {
                mes -= 12;
                año += 1;
            }
        } else if (periodoAnterior === 'Quincenal') {
            // Sumar quincenas (15 días por cuota)
            var diasTotales = numCuotasAnterior * 15;
            fecha.setDate(fecha.getDate() + diasTotales);
            año = fecha.getFullYear();
            mes = fecha.getMonth() + 1;
            dia = fecha.getDate();
        } else if (periodoAnterior === 'Semanal') {
            // Sumar semanas (7 días por cuota)
            var diasTotales = numCuotasAnterior * 7;
            fecha.setDate(fecha.getDate() + diasTotales);
            año = fecha.getFullYear();
            mes = fecha.getMonth() + 1;
            dia = fecha.getDate();
        } else if (periodoAnterior === 'Contado') {
            // Contado generalmente es 1 cuota, usar la misma fecha o sumar 1 mes
            mes += 1;
            if (mes > 12) {
                mes = 1;
                año += 1;
            }
        }
        
        // Validar y ajustar día si es necesario (ej: 31 de febrero)
        var ultimoDiaMes = new Date(año, mes, 0).getDate();
        if (dia > ultimoDiaMes) {
            dia = ultimoDiaMes;
        }
        
        // Formatear fecha como YYYY-MM-DD
        var fechaFormateada = año + '-' + 
            String(mes).padStart(2, '0') + '-' + 
            String(dia).padStart(2, '0');
        
        // Actualizar el campo de fecha del semestre actual
        $('#fecha_pago_' + semestre).val(fechaFormateada);
        
        // Si hay más semestres, calcularlos también
        var totalSemestres = parseInt($('#total_semestres').val()) || 1;
        if (semestre < totalSemestres) {
            calcularFechaInicioSemestre(semestre + 1);
        }
    }
    
    // Calcular todas las fechas cuando cambie la fecha del semestre 1
    $('#fecha_pago_1').on('change', function() {
        var totalSemestres = parseInt($('#total_semestres').val()) || 1;
        for (var i = 2; i <= totalSemestres; i++) {
            calcularFechaInicioSemestre(i);
        }
    });
    
    // Calcular fechas cuando cambie el número de cuotas o período de cualquier semestre
    $(document).on('change keyup', '.input-numero-cuotas, .input-periodo', function() {
        var semestre = parseInt($(this).data('semestre')) || 1;
        var totalSemestres = parseInt($('#total_semestres').val()) || 1;
        
        // Recalcular desde el semestre siguiente al que cambió
        for (var i = semestre + 1; i <= totalSemestres; i++) {
            calcularFechaInicioSemestre(i);
        }
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

    // Manejar click en botón editar abono
    $(document).on('click', '.editEntryBtn', function(e) {
        e.preventDefault();
        var entryId = $(this).data('id');
        
        // Cargar datos del abono
        var obj = ENTRY.get(entryId);
        obj.done(function(response) {
            if(response && response.id) {
                $('#editEntryId').val(response.id);
                $('#editEntryIdCost').val(response.id_cost);
                $('#editEntryConcepto').val(response.concepto);
                $('#editEntryDescripcion').val(response.descripcion);
                $('#editEntryNoRecibo').val(response.no_recibo);
                $('#editEntryFechaRecibo').val(response.fecha_recibo);
                $('#editEntryValor').val(dar_formato(response.valor));
                $('#editEntryElaboradoPor').val(response.elaborado_por);
                $('#editEntryDebe').val(response.debe);
                $('#editEntryHaber').val(response.haber);
                $('#editEntryForma').val(response.forma || 'Efectivo');
            }
        }).fail(function(xhr) {
            console.error('Error al cargar datos del abono:', xhr);
            alert('Error al cargar los datos del abono');
        });
    });

    // Manejar click en botón editar otros ingresos
    $(document).on('click', '.editOtherEntryBtn', function(e) {
        e.preventDefault();
        var entryId = $(this).data('id');
        
        // Cargar datos del otro ingreso
        var obj = OtherENTRIES.get(entryId);
        obj.done(function(response) {
            if(response && response.id) {
                $('#editOtherEntryId').val(response.id);
                $('#editOtherEntryIdCost').val(response.id_cost);
                $('#editOtherEntryConcepto').val(response.concepto);
                $('#editOtherEntryDescripcion').val(response.descripcion);
                $('#editOtherEntryNoRecibo').val(response.no_recibo);
                $('#editOtherEntryFechaRecibo').val(response.fecha_recibo);
                $('#editOtherEntryValor').val(dar_formato(response.valor));
                $('#editOtherEntryElaboradoPor').val(response.elaborado_por);
                $('#editOtherEntryDebe').val(response.debe);
                $('#editOtherEntryHaber').val(response.haber);
                $('#editOtherEntryForma').val(response.forma || 'Efectivo');
            }
        }).fail(function(xhr) {
            console.error('Error al cargar datos del otro ingreso:', xhr);
            alert('Error al cargar los datos del otro ingreso');
        });
    });

    // Variable para evitar cargas duplicadas
    var carteraCargada = false;
    
    // Cargar cartera cuando se hace clic en la pestaña
    $('a[href="#cartera"]').on('shown.bs.tab', function (e) {
        if(!carteraCargada){
            console.log('Pestaña Cartera activada');
            carteraCargada = true;
            Mostrar_SheetCartera();
        }
    });

    // Cargar cartera si la pestaña está activa al cargar la página
    setTimeout(function() {
        if(($('#cartera').hasClass('active') || $('a[href="#cartera"]').hasClass('active')) && !carteraCargada){
            console.log('Cartera activa al cargar página');
            carteraCargada = true;
            Mostrar_SheetCartera();
        }
    }, 500);
    
    // Resetear la bandera cuando se cambia de pestaña
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if($(e.target).attr('href') !== '#cartera'){
            carteraCargada = false;
        }
    });

    // Variable para almacenar los datos de cartera
    var carteraDataCache = [];
    
    // Manejar click en icono para editar purse
    $(document).on('click', '.ShowRegisterPurse', function(e) {
        e.preventDefault();
        var purseId = $(this).data('id-purse');
        
        if(!purseId) {
            console.error('No se encontró el ID del purse');
            return;
        }
        
        // Buscar el purse en los datos cacheados
        var purse = null;
        for(var i = 0; i < carteraDataCache.length; i++) {
            if(carteraDataCache[i].id == purseId) {
                purse = carteraDataCache[i];
                break;
            }
        }
        
        if(!purse) {
            console.error('No se encontraron datos del purse con ID:', purseId);
            // Intentar obtener desde la tabla como fallback
            var row = $(this).closest('tr');
            var fechaPago = row.find('td').eq(1).text().trim();
            var cuota = row.find('td').eq(2).text().trim();
            var comentario = row.find('td').eq(6).find('i').attr('message') || '';
            
            // Convertir fecha
            var fechaFormateada = '';
            if(fechaPago) {
                var partesFecha = fechaPago.split('-');
                if(partesFecha.length === 3) {
                    var dia = partesFecha[0];
                    var mes = partesFecha[1];
                    var año = partesFecha[2];
                    var meses = {'Ene': '01', 'Feb': '02', 'Mar': '03', 'Abr': '04', 'May': '05', 'Jun': '06',
                                'Jul': '07', 'Ago': '08', 'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dic': '12'};
                    var mesNum = meses[mes] || '01';
                    fechaFormateada = año + '-' + mesNum + '-' + (dia.length === 1 ? '0' + dia : dia);
                }
            }
            var cuotaNum = cuota.replace('$', '').replace(/\./g, '').trim();
            
            $('#ContentPurseID').val(purseId);
            $('#ContentPurseDATE').val(fechaFormateada);
            $('#ContentPurseCUOTA').val(cuotaNum);
            $('#comentarioP').val(comentario);
        } else {
            // Usar datos del cache
            // La fecha viene en formato "06-Ene-2026", necesitamos convertirla
            var fechaPago = purse.fecha_pago;
            var fechaFormateada = '';
            if(fechaPago) {
                var partesFecha = fechaPago.split('-');
                if(partesFecha.length === 3) {
                    var dia = partesFecha[0];
                    var mes = partesFecha[1];
                    var año = partesFecha[2];
                    var meses = {'Ene': '01', 'Feb': '02', 'Mar': '03', 'Abr': '04', 'May': '05', 'Jun': '06',
                                'Jul': '07', 'Ago': '08', 'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dic': '12'};
                    var mesNum = meses[mes] || '01';
                    fechaFormateada = año + '-' + mesNum + '-' + (dia.length === 1 ? '0' + dia : dia);
                }
            }
            
            // La cuota viene con formato, necesitamos limpiarla
            var cuotaNum = typeof purse.cuota === 'number' ? purse.cuota : parseFloat(String(purse.cuota).replace(/\./g, '').replace(',', '.')) || 0;
            
            $('#ContentPurseID').val(purse.id);
            $('#ContentPurseDATE').val(fechaFormateada);
            $('#ContentPurseCUOTA').val(cuotaNum);
            $('#comentarioP').val(purse.comentario || '');
        }
        
        // Abrir el modal
        $('#showEditFecha').modal('show');
    });

    // Manejar submit del formulario de edición de purse
    $('#FormPurseEdit').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var obj = CARTERA.edit();
        
        obj.done(function(response) {
            // Manejar respuesta como texto o JSON
            var success = false;
            if(typeof response === 'object' && response.success) {
                success = response.success;
            } else if(typeof response === 'string' && (response == 'OK' || response.trim() == 'OK')) {
                success = true;
            } else if(response == 'OK') {
                success = true;
            }
            
            if(success) {
                // Cerrar el modal primero
                $('#showEditFecha').modal('hide');
                
                // Mostrar mensaje de éxito
                alert('Pago actualizado correctamente');
                
                // Recargar la página automáticamente después de un pequeño delay
                setTimeout(function() {
                    window.location.reload();
                }, 500);
            } else {
                alert('Error al actualizar el pago');
            }
        }).fail(function(xhr) {
            console.error('Error al actualizar purse:', xhr);
            alert('Error al actualizar el pago');
        });
        
        return false;
    });
});

// Función para mostrar la tabla de cartera
function Mostrar_SheetCartera(){
    let id_cost = $("#id_cost").val();
    if(!id_cost || id_cost == ""){
        console.error("Mostrar_SheetCartera: id_cost no encontrado");
                $("#TABLE_ITEMS_CARTERA").html("<tr><td colspan='9' class='text-center'>No hay información de costos disponible</td></tr>");
        return;
    }
    
    console.log("Mostrar_SheetCartera: Cargando cartera para id_cost:", id_cost);
    
    // Limpiar tabla completamente antes de cargar (solo si no tiene el mensaje de carga)
    var currentContent = $("#TABLE_ITEMS_CARTERA").html();
    if(!currentContent || (!currentContent.includes("Actualizando") && !currentContent.includes("spinner"))) {
        $("#TABLE_ITEMS_CARTERA").empty();
        $("#TABLE_ITEMS_CARTERA_TFOOT").empty();
    }
    
    // Obtener datos de cartera
    const obj = CARTERA.all();
    obj.done(function(response){
        console.log("Mostrar_SheetCartera: Respuesta recibida:", response);
        try {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            // Guardar datos en cache para uso posterior
            carteraDataCache = data || [];
            
            // Limpiar tabla completamente (incluyendo totales y spinner)
            $("#TABLE_ITEMS_CARTERA").empty();
            $("#TABLE_ITEMS_CARTERA_TFOOT").empty();
            
            if(!data || data.length === 0){
                console.log("Mostrar_SheetCartera: No hay datos de cartera");
                $("#TABLE_ITEMS_CARTERA").html("<tr><td colspan='9' class='text-center'>No hay cuotas registradas</td></tr>");
                return;
            }
            
            // Obtener totales
            const totalesObj = CARTERA.totales();
            totalesObj.done(function(totalesResponse){
                try {
                    const totales = typeof totalesResponse === 'string' ? JSON.parse(totalesResponse) : totalesResponse;
                    
                    // DEBUG: Mostrar datos de la base de datos y cálculos
                    console.log("=== DEBUG CARTERA - DATOS DE LA BASE DE DATOS ===");
                    console.log("Datos de cartera recibidos:", data);
                    console.log("Totales calculados:", totales);
                    console.log("Número de cuotas:", data.length);
                    
                    // Calcular sumas manualmente para verificar
                    let sumaCuotas = 0;
                    let sumaAbonado = 0;
                    data.forEach(function(item) {
                        const cuota = typeof item.cuota === 'number' ? item.cuota : parseFloat(String(item.cuota).replace(/\./g, '').replace(',', '.')) || 0;
                        const abonado = typeof item.abonado === 'number' ? item.abonado : parseFloat(String(item.abonado).replace(/\./g, '').replace(',', '.')) || 0;
                        sumaCuotas += cuota;
                        sumaAbonado += abonado;
                        console.log(`Cuota ${item.id}: Fecha=${item.fecha_pago}, Cuota=${cuota}, Abonado=${abonado}, Estado=${item.estado}, Vencida=${item.is_vencida}`);
                    });
                    
                    console.log("=== SUMAS MANUALES ===");
                    console.log("Suma de cuotas:", sumaCuotas);
                    console.log("Suma de abonado:", sumaAbonado);
                    console.log("Total Abono (del servicio):", totales.total_abono);
                    console.log("Total Abonado (del servicio):", totales.total_abonado);
                    console.log("Saldo Pendiente (del servicio):", totales.saldo_pendiente);
                    console.log("Saldo a Favor (del servicio):", totales.saldo_a_favor);
                    
                    // Mostrar datos raw de la base de datos si están disponibles
                    if(totales.debug) {
                        console.log("=== DATOS RAW DE LA BASE DE DATOS ===");
                        console.log("ENTRIES (Abonos):", totales.debug.entries);
                        console.log("OTHER_ENTRIES (Otros Ingresos):", totales.debug.other_entries);
                        console.log("PURSES (Cuotas):", totales.debug.purses);
                        console.log("Suma Entries:", totales.debug.suma_entries);
                        console.log("Suma Other Entries:", totales.debug.suma_other_entries);
                        console.log("Suma Total Abono (Entries + Other):", totales.debug.suma_total_abono);
                        console.log("Suma Cuotas (de purses):", totales.debug.suma_cuotas);
                        console.log("================================================");
                    }
                    console.log("================================================");
                    
                    // Generar filas de la tabla
                    for (let index = 0; index < data.length; index++) {
                        const item = data[index];
                        const i = index + 1;
                        
                        // Determinar clase de fila según estado
                        let rowClass = '';
                        let estadoPago = '';
                        let estadoBadge = '';
                        
                        // Calcular estado del pago
                        // Los valores pueden venir como número o string con formato
                        const cuota = typeof item.cuota === 'number' ? item.cuota : parseFloat(String(item.cuota).replace(/\./g, '').replace(',', '.')) || 0;
                        const abonado = typeof item.abonado === 'number' ? item.abonado : parseFloat(String(item.abonado).replace(/\./g, '').replace(',', '.')) || 0;
                        
                        if(abonado >= cuota){
                            estadoPago = 'Completa';
                            estadoBadge = '<span class="badge badge-success">Completa</span>';
                            rowClass = 'style="background-color:#2bc155;color:#fff;"';
                        } else if(abonado > 0 && abonado < cuota){
                            estadoPago = 'Incompleta';
                            estadoBadge = '<span class="badge badge-warning">Incompleta</span>';
                            rowClass = 'style="background-color:#ffc107;color:#000;"';
                        } else {
                            estadoPago = 'Pendiente';
                            estadoBadge = '<span class="badge badge-secondary">Pendiente</span>';
                            rowClass = '';
                        }
                        
                        // Determinar estado general
                        let estado = item.estado || 'Proxima';
                        if(estado == 'Al dia'){
                            rowClass = 'style="background-color:#2bc155;color:#fff;"';
                        } else if(estado == 'En Mora'){
                            rowClass = 'style="background-color:#f72b50;color:#fff;"';
                        } else if(estado == 'Incompleta'){
                            rowClass = 'style="background-color:#ffc107;color:#000;"';
                        }
                        
                        const abonadoDisplay = abonado > 0 ? '$' + dar_formato(abonado) : '';
                        const comentario = item.comentario || '';
                        
                        // Determinar color del texto según el estado
                        let estadoColor = '';
                        if(estado == 'Al dia'){
                            estadoColor = 'color:#2bc155 !important;'; // Verde
                        } else if(estado == 'En Mora'){
                            estadoColor = 'color:#f72b50 !important;'; // Rojo
                        } else if(estado == 'Incompleta'){
                            estadoColor = 'color:#ffc107 !important;'; // Naranja/Amarillo
                        } else {
                            estadoColor = 'color:#6c757d !important;'; // Gris para Proxima
                        }
                        
                        // Función para convertir número a romano
                        function numeroARomano(num) {
                            num = parseInt(num) || 1;
                            if (num <= 0 || num > 3999) return num.toString();
                            var valores = [
                                {valor: 1000, letra: 'M'}, {valor: 900, letra: 'CM'}, {valor: 500, letra: 'D'}, {valor: 400, letra: 'CD'},
                                {valor: 100, letra: 'C'}, {valor: 90, letra: 'XC'}, {valor: 50, letra: 'L'}, {valor: 40, letra: 'XL'},
                                {valor: 10, letra: 'X'}, {valor: 9, letra: 'IX'}, {valor: 5, letra: 'V'}, {valor: 4, letra: 'IV'}, {valor: 1, letra: 'I'}
                            ];
                            var romano = '';
                            for (var j = 0; j < valores.length; j++) {
                                var cantidad = Math.floor(num / valores[j].valor);
                                romano += valores[j].letra.repeat(cantidad);
                                num = num % valores[j].valor;
                            }
                            return romano;
                        }
                        
                        const numeroSemestre = item.numero_semestre || 1;
                        const semestreRomano = numeroARomano(numeroSemestre);
                        
                        const TR = $("<tr " + rowClass + "></tr>");
                        const TD1 = $("<td style='text-align:center;' class='text-center text-black'>" + i + "</td>");
                        const TD2 = $("<td style='text-align:center;' class='text-center text-black'>" + semestreRomano + "</td>");
                        const TD3 = $("<td style='text-align:center;' class='text-center text-black'>" + (item.fecha_pago || '') + "</td>");
                        const TD4 = $("<td style='text-align:right;font-weight:bold !important;' class='text-center text-black font-weight-bold'>$" + dar_formato(cuota) + "</td>");
                        const TD5 = $("<td style='text-align:right;font-weight:bold !important;' class='text-center text-black font-weight-bold'>" + abonadoDisplay + "</td>");
                        const TD6 = $("<td style='text-align:center;' class='text-center text-black'>" + estadoBadge + "</td>");
                        const TD7 = $("<td style='text-align:center;font-weight:bold !important;" + estadoColor + "' class='text-center font-weight-bold'>" + estado + "</td>");
                        const TD8 = $("<td style='text-align:center;' class='text-center text-black'><i message='" + comentario + "' class='fa-solid fa-comment-dots text-primary showMessage pointer cpointer'></i></td>");
                        const TD9 = $("<td style='text-align:center;' class='text-center text-black'><i class='fa-solid fa-file-waveform ml-2 text-primary ShowRegisterPurse cpointer' data-toggle='modal' data-id-purse='" + item.id + "' data-target='#showEditFecha'></i></td>");
                        
                        TR.append(TD1, TD2, TD3, TD4, TD5, TD6, TD7, TD8, TD9);
                        $("#TABLE_ITEMS_CARTERA").append(TR);
                    }
                    
                    // Calcular estado total de la cartera
                    // Lógica: Si todas las cuotas vencidas están completas = "Al día", si hay alguna vencida pendiente o incompleta = "En Mora"
                    let estadoCartera = 'Al dia';
                    let estadoCarteraColor = 'color:#2bc155 !important;'; // Verde
                    
                    for (let idx = 0; idx < data.length; idx++) {
                        const item = data[idx];
                        const cuota = typeof item.cuota === 'number' ? item.cuota : parseFloat(String(item.cuota).replace(/\./g, '').replace(',', '.')) || 0;
                        const abonado = typeof item.abonado === 'number' ? item.abonado : parseFloat(String(item.abonado).replace(/\./g, '').replace(',', '.')) || 0;
                        const isVencida = item.is_vencida || false;
                        
                        // Si la cuota está vencida y no está completa
                        if (isVencida && abonado < cuota) {
                            estadoCartera = 'En Mora';
                            estadoCarteraColor = 'color:#f72b50 !important;'; // Rojo
                            break; // No necesitamos seguir buscando
                        }
                    }
                    
                    // Agregar filas de totales
                    const cuotasTotal = totales.cuotas_total || 0;
                    const totalAbonado = totales.total_abonado || 0;
                    const saldoPendiente = totales.saldo_pendiente || 0;
                    const saldoAFavor = totales.saldo_a_favor || 0;
                    const saldoEnMora = totales.saldo_en_mora || 0;
                    
                    // Fila Total Programa
                    const TR1 = $("<tr style='background-color:#0e00ce;color:#fff;'></tr>");
                    TR1.append(
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;font-weight:bold !important;' class='text-center font-weight-bold'>Total Programa</td>"),
                        $("<td style='text-align:right;color:#fff;font-weight:bold !important;' class='text-center font-weight-bold'>$" + dar_formato(cuotasTotal) + "</td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>")
                    );
                    $("#TABLE_ITEMS_CARTERA").append(TR1);
                    
                    // Fila Total Abonado
                    const TR2 = $("<tr style='background-color:#585858;color:#fff;'></tr>");
                    TR2.append(
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;font-weight:bold !important;' class='text-center font-weight-bold'>Total Abonado</td>"),
                        $("<td style='text-align:right;color:#fff;font-weight:bold !important;' class='text-center font-weight-bold'>$" + dar_formato(totalAbonado) + "</td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>"),
                        $("<td style='text-align:center;color:#fff;' class='text-center'> </td>")
                    );
                    $("#TABLE_ITEMS_CARTERA").append(TR2);
                    
                    // Fila Saldo Pendiente con Estado Total de Cartera
                    const TR3 = $("<tr style='background-color:#F3CAD5;'></tr>");
                    TR3.append(
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;font-weight:bold !important;' class='text-center text-black font-weight-bold'>Saldo Pendiente</td>"),
                        $("<td style='text-align:right;color:#000;font-weight:bold !important;' class='text-center text-black font-weight-bold'>$" + dar_formato(saldoPendiente) + "</td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;font-weight:bold !important;" + estadoCarteraColor + "' class='text-center font-weight-bold'>Estado de Cartera: " + estadoCartera + "</td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>")
                    );
                    $("#TABLE_ITEMS_CARTERA").append(TR3);
                    
                    // Fila Saldo en Mora
                    const TR4 = $("<tr style='background-color:#ffebee;'></tr>");
                    TR4.append(
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;font-weight:bold !important;' class='text-center text-black font-weight-bold'>Saldo en Mora</td>"),
                        $("<td style='text-align:right;color:#f72b50 !important;font-weight:bold !important;' class='text-center font-weight-bold'>$" + dar_formato(saldoEnMora) + "</td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>")
                    );
                    $("#TABLE_ITEMS_CARTERA").append(TR4);
                    
                    // Fila Saldo a Favor
                    const TR5 = $("<tr style='background-color:#dcecb0;'></tr>");
                    TR5.append(
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;font-weight:bold !important;' class='text-center text-black font-weight-bold'>Saldo a Favor</td>"),
                        $("<td style='text-align:right;color:#000;font-weight:bold !important;' class='text-center text-black font-weight-bold'>$" + dar_formato(saldoAFavor) + "</td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>"),
                        $("<td style='text-align:center;color:#000;' class='text-center text-black'> </td>")
                    );
                    $("#TABLE_ITEMS_CARTERA").append(TR5);
                    
                    // Actualizar saldos en el header
                    $("#SaldoFavorText").text(dar_formato(saldoAFavor));
                    $("#SaldoPendienteText").text(dar_formato(saldoPendiente));
                    
                    // Asegurar que cualquier spinner se haya ocultado completamente
                    $("#TABLE_ITEMS_CARTERA tr").filter(function() {
                        return $(this).html().includes("spinner") || $(this).html().includes("Actualizando");
                    }).remove();
                    
                } catch(e) {
                    console.error("Mostrar_SheetCartera: Error al procesar totales:", e);
                    // Limpiar spinner en caso de error
                    $("#TABLE_ITEMS_CARTERA tr").filter(function() {
                        return $(this).html().includes("spinner") || $(this).html().includes("Actualizando");
                    }).remove();
                    if($("#TABLE_ITEMS_CARTERA").children().length === 0) {
                        $("#TABLE_ITEMS_CARTERA").html("<tr><td colspan='8' class='text-center text-danger'>Error al procesar los datos de cartera</td></tr>");
                    }
                }
            }).fail(function(xhr, status, error){
                console.error("Mostrar_SheetCartera: Error al obtener totales:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                // Mostrar error en la tabla si falla la obtención de totales
                if($("#TABLE_ITEMS_CARTERA").children().length === 0) {
                    $("#TABLE_ITEMS_CARTERA").html("<tr><td colspan='8' class='text-center text-danger'>Error al cargar los totales de cartera</td></tr>");
                }
            });
            
        } catch(e) {
            console.error("Mostrar_SheetCartera: Error al procesar datos:", e);
            $("#TABLE_ITEMS_CARTERA").html("<tr><td colspan='8' class='text-center text-danger'>Error al procesar los datos de cartera: " + e.message + "</td></tr>");
        }
    }).fail(function(xhr, status, error){
        console.error("Mostrar_SheetCartera: Error en la petición:", {
            status: status,
            error: error,
            responseText: xhr.responseText
        });
        $("#TABLE_ITEMS_CARTERA").html("<tr><td colspan='8' class='text-center text-danger'>Error al cargar los datos de cartera</td></tr>");
    });
}

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

// Función para enviar formulario de edición de abono
function EnviarDatosEditEntry(event) {
    event.preventDefault();
    var form = $('#formEditEntry');
    var entryId = $('#editEntryId').val();
    
    if(!entryId) {
        alert('Error: No se encontró el ID del abono');
        return false;
    }
    
    var obj = ENTRY.updateForm(entryId, form);
    obj.done(function(response) {
        if(response == 'OK' || response.trim() == 'OK') {
            // Recargar la página o actualizar la tabla
            location.reload();
        } else {
            alert('Error al actualizar el abono');
        }
    }).fail(function(xhr) {
        console.error('Error al actualizar abono:', xhr);
        alert('Error al actualizar el abono');
    });
    
    return false;
}

// Función para enviar formulario de edición de otros ingresos
function EnviarDatosEditOtherEntry(event) {
    event.preventDefault();
    var form = $('#formEditOtherEntry');
    var entryId = $('#editOtherEntryId').val();
    
    if(!entryId) {
        alert('Error: No se encontró el ID del otro ingreso');
        return false;
    }
    
    var obj = OtherENTRIES.updateForm(entryId, form);
    obj.done(function(response) {
        if(response == 'OK' || response.trim() == 'OK') {
            // Recargar la página o actualizar la tabla
            location.reload();
        } else {
            alert('Error al actualizar el otro ingreso');
        }
    }).fail(function(xhr) {
        console.error('Error al actualizar otro ingreso:', xhr);
        alert('Error al actualizar el otro ingreso');
    });
    
    return false;
}

// Eliminar costos del estudiante - Solo Super Admin
@if(auth()->check() && auth()->user()->hasRole('super-admin'))
document.getElementById('btnEliminarCostosEstudiante')?.addEventListener('click', function() {
    const codAlumno = this.getAttribute('data-cod-alumno');
    const nombreEstudiante = '{{ $student[0]->nombre ?? "el estudiante" }}';
    
    if (!confirm('¿Está seguro de que desea eliminar TODA la información de costos de ' + nombreEstudiante + '?\n\nEsto eliminará:\n- Costos\n- Cartera\n- Historial de Cartera\n- Abonos\n- Otros Abonos\n\n⚠️ Esta acción es IRREVERSIBLE.')) {
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Eliminando...';
    
    fetch('{{ route("cost.eliminar-estudiante", ":cod_alumno") }}'.replace(':cod_alumno', codAlumno), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash-alt mr-2"></i>Eliminar Costos';
        
        if (data.success) {
            alert('Éxito: ' + data.message + '\n\nRegistros eliminados:\n- Costos: ' + data.eliminados.costs + '\n- Cartera: ' + data.eliminados.purses + '\n- Historial: ' + data.eliminados.history_purses + '\n- Abonos: ' + data.eliminados.entries + '\n- Otros Abonos: ' + data.eliminados.other_entries + '\n\nTotal: ' + data.total + ' registros');
            // Recargar la página para actualizar la vista
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash-alt mr-2"></i>Eliminar Costos';
        console.error('Error:', error);
        alert('Error al eliminar costos: ' + error.message);
    });
});
@endif
</script>
@endpush

@endsection