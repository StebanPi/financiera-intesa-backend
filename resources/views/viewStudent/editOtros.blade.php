@extends('login.app')

@section('content')

        <div class="authincation h-100">
            <div class="container h-100">
                <div class="row justify-content-center h-100 align-items-center">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa-solid fa-marker mr-2"></i> Editar Abono
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('other.entry.update',$entry->id)}}" id="formEntry1">
                                    @csrf
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
                                            <input type="hidden" id="num_current_conse" value="{{ $num_current }}">
                                            <div class="form-group">
                                                <label>Concepto</label>
                                                <select name="concepto" id="conceptoAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                                    @foreach ($conceptos as $item)
                                                        @if ($item->estado == "1")
                                                            <option consecutive="{{ $item->consecutivo }}" value="{{ $item->id }}" 
                                                                @if ($item->id == $entry->concepto)
                                                                    Selected
                                                                @endif
                                                            >{{ $item->nombre }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Descripción</label>
                                                <textarea name="descripcion" id="descripcionAttr" class="form-control form-control-sm number-lg" name="" id="" cols="30" rows="5">{{ $entry->descripcion }}</textarea>
                                                <div class="error_des text-danger ActiveError d-none"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>No.Recibo</label>
                                                <input name="no_recibo" readonly id="noReciboAttr" type="number" class="form-control form-control-sm number-lg" value="{{  $entry->no_recibo }}">
                                                <div class="error_recibo text-danger ActiveError d-none"></div>
                                            </div>
                                            <div class="form-group">
                                                <label>Fecha de Recibo</label>
                                                <input name="fecha_recibo" id="fechaReciboAttr" type="date" class="form-control form-control-sm number-lg" value="{{ $entry->fecha_recibo }}">
                                                <div class="error_fecha text-danger ActiveError d-none"></div>
                                            </div>
                                            <div class="form-group">
                                                <label>Valor</label>
                                                @php
                                                   $valor = str_replace(',','.',strval(number_format($entry->valor)));
                                                @endphp
                                                <input name="valor" id="valorAttr" type="text" class="form-control form-control-sm number-lg miles" onkeypress="return valideKey(event);" value="{{ $valor }}">
                                                <div class="error_valor text-danger ActiveError d-none"></div>
                                            </div>
                                            <div class="form-group">
                                                <label>Elaborado Por</label>
                                                <select name="elaborado_por" id="elaboradoPorAttr" class="form-control form-control-sm number-lg" tabindex="-98" >
                                                    @foreach ($elaborados as $item)
                                                        @if ($item->estado == "1")
                                                            <option value="{{ $item->id }}"
                                                                @if ($item->id == $entry->elaborado_por)
                                                                    Selected
                                                                @endif
                                                            >{{ $item->nombre }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Cuenta Contable <b>DEBE</b></label>
                                                <select name="debe"  id="debeAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                                    @foreach ($debe as $item)
                                                        <option value="{{ $item->id }}"
                                                            @if ($item->id == $entry->debe)
                                                                Selected
                                                            @endif    
                                                        >{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Cuenta Contable <b>HABER</b></label>
                                                <select name="haber" id="haberAttr" class="form-control form-control-sm number-lg" tabindex="-98">
                                                    @foreach ($haber as $item)
                                                        <option value="{{ $item->id }}"
                                                            @if ($item->id == $entry->haber)
                                                                Selected
                                                            @endif     
                                                        >{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
        
                                        </div>
                                    </div>
                                    
                                 
                                    <button type="submit" class="btn btn-sm btn-primary ejecutarmodal"><i class="fa-solid fa-floppy-disk mr-2"></i>Guardar</button>
                                    <button type="button" class="btn btn-sm btn-danger " data-toggle="modal" data-target="#exampleModal2"><i class="fa-solid fa-trash mr-2"></i>Eliminar</button>
                                </form>
                                    
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  

        
        
   <!-- Modal -->
   <div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-sack-dollar mr-2"></i>Ver Abono</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('other.entry.destroy',$entry->id) }}" method="POST">
            @csrf
        <div class="modal-body">
             <p>¿Desea eliminar este Abono #{{ $entry->no_recibo }}?</p>   
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">No</button>
          <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash mr-2"></i>Si, eliminar</button>
        </div>
        </form>
      </div>
    </div>
  </div>
    

@endsection