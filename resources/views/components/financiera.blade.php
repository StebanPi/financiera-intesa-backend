
<div>
    <div class="row">
      <div class="col-md-12 text-center ">
        <div class="bg-gradients-azul">
          <img width="100px" height="100px" src="{{ asset('dimages/anuncio.png') }}" alt="">
          <div class="row">
            <div class="col-md-12">
              <p class="text-white"><b>Los estudiantes que NO tengan registrado la matricula financiera, no apareceran en la busqueda , aunque esten registrados.</b></p>
            </div>
            <div class="col-md-12 text-center">
              <div class="d-flex justify-content-center align-items-center">
                <div class="input-group mb-3 mx-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-magnifying-glass"></i></span>
                  </div>
                  <input type="text" @if($content != null && isset($alumno[0]) && $alumno[0] != null){ value="{{ $alumno[0]->nombre }}" @endif class="form-control number-lg searchStudent" style="font-size: 25px !important" aria-describedby="basic-addon1">
                  
                </div>
                
              </div>
              <ul class="listItemName d-none">
                <li>Sebastyan Pineda</li>
                <li>Juan Meneses</li>
                <li>Lucas Modric</li>
              </ul>
            </div>
          </div>
        
        </div>
      </div>
      <div class="col-md-12">
        <form method="POST" @if($content != null){ action="{{ route('cost.store')}}" @endif  id="formatFormFinanciera" >
            <input type="hidden" name="cod_alumno" id="studentSelect" @if($content != null && isset($alumno[0]) && $alumno[0] != null){ value="{{ $alumno[0]->cod_alumno }}" @endif >
          @csrf
          <div class="formatReceipt">
            <div class="formatReceipt-header ">
              <div class="formatReceipt-header-d">
                <div class="formatReceipt-header-date">
                  <div class="formatReceipt-header-date-title text-center bg-white font-wight-bold">
                    Fecha
                  </div>
                  <div class="formatReceipt-header-date-body">
                    <div  class="formatReceipt-header-date-body-item">
                      {{ date('d')}}

                    </div>
                    <div class=" formatReceipt-header-date-body-item">
                      {{ date('m')}}
                    </div>
                    <div class="formatReceipt-header-date-body-item">
                      {{ date('Y')}}
                    </div>
                  </div>
                </div>
              </div>
              <div class=" text-center formatReceipt-header-title" >
                <div>
                  {{ $title }}
                </div>
                
              </div>
              <div  class=" formatReceipt-header-consecutive">
                <div>
                  <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </div>
              </div>
            </div>
            <div class="formarReceipt-body">
              <div class="formarReceipt-body-item ">
                <div class="formarReceipt-body-item-cell">
                  <span>Valor Semestre <small class="text-danger error_vs ActiveError font-weight-bold d-none">(Completa este campo)</small></span>
                  <input type="text" name="valor_semestre" class="miles" id="valor_semestre"  @if($content != null){ value="{{ $content->valor_semestre }}"  } @endif onkeypress="return valideKey(event);" >
                </div>
                <div class="formarReceipt-body-item-cell">
                    <span>Saldo a Financiar </span>
                    <input type="text" name="saldo_financiar" class="miles" id="saldo_financiar" readonly  @if($content != null){ value="{{ $content->saldo_financiar }}"  } @endif >
                </div>
               </div>
               <div class="formarReceipt-body-item ">
                    <div class="formarReceipt-body-item-cell">
                        <span>Numero de Semestre <small class="text-danger error_ns ActiveError font-weight-bold d-none">(Completa este campo)</small></span>
                        <input type="text" name="numero_semestre" id="numero_semestre" @if($content != null){ value="{{ $content->numero_semestre }}"  } @endif onkeypress="return valideKey(event);" >
                    </div>
                    <div class="formarReceipt-body-item-cell">
                        <span>Periodo de Pago</span>
                          <select name="periodo"  class="formarReceipt-body-item-cell-select" >
                            <option value="Semanal"  @if($content != null && $content->periodo == "Semanal") {{ "selected" }} @endif>Semanal</option>
                            <option value="Quincenal" @if($content != null && $content->periodo == "Quincenal") {{ "selected" }} @endif>Quincenal</option>
                            <option value="Mensual" @if($content != null && $content->periodo == "Mensual") {{ "selected" }} @endif>Mensual</option>
                            <option value="Contado" @if($content != null && $content->periodo == "Contado") {{ "selected" }} @endif>Contado</option>
                          </select>
                    </div>
                </div>
                <div class="formarReceipt-body-item ">
                    <div class="formarReceipt-body-item-cell">
                        <span>Valor total Programa <small class="text-danger error_vtp ActiveError font-weight-bold d-none">(Completa este campo)</small> </span>
                        <input class="miles" name="valor_total_semestre" id="valor_total_semestre" readonly type="text"  @if($content != null){ value="{{ $content->valor_total_semestre }}"  } @endif >
                    </div>
                    <div class="formarReceipt-body-item-cell">
                        <span>Numero de Cuotas <small class="text-danger error_nc ActiveError font-weight-bold d-none">(Completa este campo)</small></span>
                        <input type="text"  name="numero_cuotas" id="numero_cuota"  @if($content != null){ value="{{ $content->numero_cuotas }}"  } @endif onkeypress="return valideKey(event);" >
                    </div>
                </div>
                <div class="formarReceipt-body-item ">
                    <div class="formarReceipt-body-item-cell">
                        <span>Descuento <small class="text-danger error_d ActiveError font-weight-bold d-none">(Completa este campo)</small></span>
                        <input class="miles" name="descuento" id="descuento" type="text"  @if($content != null){ value="{{ $content->descuento }}"  } @endif onkeypress="return valideKey(event);" >
                    </div>
                    <div class="formarReceipt-body-item-cell">
                        <span>Valor de Cuotas </span>
                        <input class="miles" name="valor_cuotas" type="text" id="valor_cuota" readonly  @if($content != null){ value="{{ $content->valor_cuotas }}"  } @endif >
                    </div>
                </div>
                <div class="formarReceipt-body-item ">
                    <div class="formarReceipt-body-item-cell">
                        <span>Valor Total Neto del Programa </span>
                        <input class="miles" name="valor_neto" id="valor_neto" type="text" readonly  @if($content != null){ value="{{ $content->valor_neto }}"  } @endif >
                    </div>
                    <div class="formarReceipt-body-item-cell">
                        <span>Fecha de Pago <small class="text-danger error_fp ActiveError font-weight-bold d-none">(Completa este campo)</small></span>
                        <input type="date" name="fecha_pago" id="fecha_pago"  @if($content != null){ value="{{ $content->fecha_pago }}"  } @endif >
                    </div>
                </div>
                <div class="formarReceipt-body-item">
                    <div class="formarReceipt-body-item-celld">
                      <span>Detalles</span>
                        <textarea name="detalles" id="descripcion" cols="10" rows="2"  >@if($content != null){{ $content->detalles }} @endif</textarea>
                    </div>
                  </div >         
            </div>
        </div>
      </form>
      </div>
    <hr>
</div>