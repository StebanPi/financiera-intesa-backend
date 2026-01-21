
var i = 0;
var valNo = 0;
$("#conceptoAttr").change(function(){
    var value = $(this);
    const num_current = $("#num_current_conse").val();
    var con = $("#conceptoAttr option[value='"+value.val()+"']").attr('consecutive');
    console.log(con);
    if(con == "0" || con == 0){
        if(i == 0){
            valNo = $("#noReciboAttr").val();
            i++;
        }
        $("#noReciboAttr").val(valNo); 
    }else{
        if(i == 0){
            valNo = $("#noReciboAttr").val();
            i++;
        }
        $("#noReciboAttr").val(num_current); 
    }
});


/*var formEntry = document.getElementById('formEntry1');
$("#formEntry1").submit(function(e){
    const no_recibo = formEntry.noReciboAttr;
    const fecha = formEntry.fechaReciboAttr;
    const valor = formEntry.valorAttr;
    if(no_recibo.value == "" || fecha.value == "" || valor.value == "" || no_recibo.value > no_recibo.attr('max')){
        e.preventDefault();
        $('.content-preloader2').addClass(' show2loader');
    }
});*/
var formEntry = document.getElementById('formEntry1');
var contentErrorRecibo = ".error_recibo";
var contentErrorFecha = ".error_fecha";
var contentErrorValor = ".error_valor";
var contentErrorDescripcion = ".error_des";
var EstaEnError = false;

$("#formEntry1").submit(function(e){
    const no_recibo = formEntry.noReciboAttr;
    const fecha = formEntry.fechaReciboAttr;
    const valor = formEntry.valorAttr;
    const descrip = formEntry.descripcionAttr;

    if(no_recibo.value == "" || no_recibo.value == 0){
        BloqueoVentana();
        $(contentErrorRecibo).removeClass('d-none');
        $(contentErrorRecibo).html('<small>Complete este campo.</small>');
        e.preventDefault();
        EstaEnError = true;
    }else{
        $(contentErrorRecibo).addClass('d-none');
    }
    if(descrip.value == ""){
        BloqueoVentana();
        $(contentErrorDescripcion).removeClass('d-none');
        $(contentErrorDescripcion).html('<small>Complete este campo.</small>');
        e.preventDefault();
        EstaEnError = true;
    }else{
        $(contentErrorDescripcion).addClass('d-none');
    }
    if(fecha.value == ""){
        BloqueoVentana();
        $(contentErrorFecha).removeClass('d-none');
        $(contentErrorFecha).html('<small>Complete este campo.</small>');
        e.preventDefault();
        EstaEnError = true;
    }else{
        $(contentErrorFecha).addClass('d-none');
    }
    if(valor.value == ""){
        BloqueoVentana();
        $(contentErrorValor).removeClass('d-none');
        $(contentErrorValor).html('<small>Complete este campo.</small>');
        e.preventDefault();
        EstaEnError = true;
    }else{
        $(contentErrorValor).addClass('d-none');
    }
    if(isAbonoInicial){

        if(no_recibo.value == "" || no_recibo.value == 0){
            BloqueoVentana();
            $(contentErrorRecibo).removeClass('d-none');
            $(contentErrorRecibo).html('<small>Complete este campo.</small>');
            e.preventDefault();
            EstaEnError = true;
        }else{
            const num = parseInt($("#StartConsecutivo").val());
            if(parseInt(no_recibo.value) >= num){
                BloqueoVentana();
                e.preventDefault();
                EstaEnError = true;
                $(contentErrorRecibo).removeClass('d-none');
                $(contentErrorRecibo).html('<small>El numero no puede ser mayor que '+num+'</small>');
            }else{
                $(contentErrorRecibo).addClass('d-none');    
            }
        }
        
    }

    if(EstaEnError){
        function QuitarAlertas(){
            $(".ActiveError").html("<small></small>");
        }
        setTimeout(QuitarAlertas,2000);
    }

});


