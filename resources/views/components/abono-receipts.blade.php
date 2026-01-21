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
                  @if(isset($content) && $content != null)
                    <input type="number" value="{{ $content->no_recibo }}" class="form-control number-lg searchAbonoReceipts" style="font-size: 25px !important" aria-describedby="basic-addon1">
                  @else
                    <input type="number" class="form-control number-lg searchAbonoReceipts" style="font-size: 25px !important" aria-describedby="basic-addon1">
                  @endif
                </div>
              </div>
            </div>
          </div>
        
        </div>
      </div>
      <div class="col-md-12">
        <form method="POST" id="formatReceiptForm" class="formatReceiptFormAbono" >
          @csrf
          <div class="formatReceipt">
            <div class="formatReceipt-header ">
              <div class="formatReceipt-header-d">
                <div class="formatReceipt-header-date">
                  <div class="formatReceipt-header-date-title text-center bg-white font-wight-bold">
                    Fecha
                  </div>
                  @php
                    $fecha = null;
                    if(isset($content) && $content != null){
                        $fecha = explode("-",$content->fecha_recibo);
                    }
                  @endphp
                  <div class="formatReceipt-header-date-body">
                    <div  class="formatReceipt-header-date-body-item">
                      @if(isset($content) && $content != null && isset($fecha) && isset($fecha[2]))
                        {{ $fecha[2] }}
                      @else
                        {{ date('d')}}
                      @endif

                    </div>
                    <div class=" formatReceipt-header-date-body-item">
                      @if(isset($content) && $content != null && isset($fecha) && isset($fecha[1]))
                        {{ $fecha[1] }}
                      @else
                        {{ date('m')}}
                      @endif
                    </div>
                    <div class="formatReceipt-header-date-body-item">
                      @if(isset($content) && $content != null && isset($fecha) && isset($fecha[0]))
                        {{ $fecha[0] }}
                      @else
                        {{ date('Y')}}
                      @endif
                    </div>
                  </div>
                </div>
              </div>
              <div class=" text-center formatReceipt-header-title" >
                <div>
                  {{ $title ?? '' }}
                </div>

              </div>
              <div  class=" formatReceipt-header-consecutive">
                <div>
                  <span id="showConsecutiveReceipts"> 
                    @if (isset($content) && $content != null)
                      {{ $content->no_recibo }}
                      <input type="hidden" name="no_recibo" value="{{ $content->no_recibo }}">
                    @else
                      {{ isset($consecutive) && $consecutive ? $consecutive->num_current : '' }}
                      <input type="hidden" name="no_recibo" value="{{ isset($consecutive) && $consecutive ? $consecutive->num_current : '' }}">
                    @endif 
                  </span>
                  <input type="text" class="d-none" id="showInputSearchReceipts">
                  <input type="hidden" name="fecha_recibo" value="@php echo date('Y-m-d'); @endphp">
                </div>
              </div>
            </div>
            <div class="formarReceipt-body">
              <div class="formarReceipt-body-item ">
                <div class="formarReceipt-body-item-cell">
                  <span>Estudiante <small class="text-danger errorThirdReceipt font-weight-bold d-none">(Completa este campo)</small></span>
                  <input type="hidden" name="id_cost" id="estudianteID" @if(isset($content) && $content != null){ value="{{ $content->id_cost }}" } @endif >
                  <input type="text" id="estudianteInput" @if(isset($content) && $content != null){ value="{{ $content->nombre }}" readonly } @endif >
                  <ul class="listItems d-none">
                    <li>Juan</li>
                    <li>Pedro</li>
                  </ul>
                </div>
                <div class="formarReceipt-body-item-cell">
                  <span>Debe</span>
                    <select name="debe"  id="debeAttr2" class="d-none" >
                      @foreach (($debe ?? []) as $item)
                          <option @if(isset($content) && $content != null && $content->debe == $item->id){ Selected } @endif value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                      @endforeach
                    </select>
                    <input readonly type="text" id="SelectDebe">
                </div>
              </div>
              <div class="formarReceipt-body-item ">
                <div class="formarReceipt-body-item-cell">
                  <span>Concepto</span>
                  <select style="background-color: #fff" class="formarReceipt-body-item-cell-select" name="concepto" id="changeConceptoThird">
                    @foreach(($concepts ?? []) as $item)
                      @if ($item->estado == 1)
                        <option @if(isset($content) && $content != null && $item->id == $content->concepto){ Selected } @endif  debe="{{ $item->debe }}" haber="{{ $item->haber }}" value="{{ $item->id }}">{{ $item->nombre }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>
                <div class="formarReceipt-body-item-cell">
                  <span>Haber</span>
                    <input readonly type="text" id="SelectHaber">
                    <select name="haber" id="haberAttr2" class="d-none" >
                      @foreach (($haber ?? []) as $item)
                          <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                      @endforeach
                    </select>
                </div>
              </div>
              <div class="formarReceipt-body-item">
                <div class="formarReceipt-body-item-celld">
                  <span>Detalles</span>
                    <textarea name="descripcion" id="descripcion" cols="30" rows="5" @if(isset($content) && $content != null){ readonly } @endif >@if(isset($content) && $content != null){{ $content->descripcion }} @endif</textarea>
                </div>
              </div >
              <div class="formarReceipt-body-item ">
                <div class="formarReceipt-body-item-cell-radio">
                  <span>Elije una Opción <small class="text-danger errorFormaReceipt d-none font-weight-bold">(Completa este campo)</small></span>
                  <div>
                    <div class="form-check-inline">
                      <input @if(isset($content) && $content != null && $content->forma == "Efectivo") { checked } @endif name="forma" class="form-check-input" type="radio" value="Efectivo" id="flexRadioDefault1">
                      <label class="form-check-label" for="flexRadioDefault1">
                        Efectivo
                      </label>
                    </div>
                    <div class="form-check-inline">
                      <input @if(isset($content) && $content != null && $content->forma == "Consignación"){ checked } @endif name="forma" class="form-check-input" type="radio" value="Consignación"  id="flexRadioDefault1" >
                      <label class="form-check-label" for="flexRadioDefault2">
                        Consignación
                      </label>
                    </div>
                  </div>
                </div>
                <div class="formarReceipt-body-item-cell-elaborado">
                  <span>Elaborado Por</span>
                    <select name="elaborado_por" @if(isset($content) && $content != null) readonly @endif class="formarReceipt-body-item-cell-select-2" id="">
                      @foreach(($elaborados ?? []) as $item)
                        <option @if(isset($content) && $content != null && $item->id == $content->elaborado_por){ Selected } @endif value="{{ $item->id }}" >{{ $item->nombre }}</option>
                      @endforeach
                    </select>
                </div>
              </div>
              <div class="formarReceipt-body-item">
                <div class="formarReceipt-body-item-cash">
                  <span>Valor <small class="text-danger errorValueReceipt font-weight-bold d-none">(Completa este campo)</small></span>
                  <div class="formarReceipt-body-item-cell-inputValor">
                    <div class="formarReceipt-body-item-cell-inputValor-signal">
                      $
                    </div>
                    @php
                      $valor = null;
                      if(isset($content) && $content != null){
                        $valor  = str_replace(',','.',strval(number_format($content->valor)));
                      }
                    @endphp
                    <div class="formarReceipt-body-item-cell-inputValor-dinput">
                      <input name="valor" type="text" class="miles" id="valor" @if(isset($content) && $content != null && isset($valor)){
                        value="{{$valor}}" readonly
                      }
                          
                      @endif />
                    </div>
                  </div>

                </div>
                <div class="formarReceipt-body-item-button">
                  <div>
                    @if (isset($content) && $content != null)
                    
                    @else
                      <span>Desea crear el recibo?</span>
                      <button class="formarReceipt-body-item-button-first"><i class="fa-solid fa-hand-pointer mr-2"></i>Crear</button>
                    @endif
                  </div>
                </div>
              </div >
          </div>
        </div>
      </form>
      </div>
    <hr>
</div>