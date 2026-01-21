let ventana = "";
let Iventana = 0;
$('.miles').keyup((e) => {
    var entrada = e.target.value.split('.').join('');
    entrada = entrada.split('').reverse();
    
    var salida = [];
    var aux = '';
    
    var paginador = Math.ceil(entrada.length / 3);
    
    for(let i = 0; i < paginador; i++) {
        for(let j = 0; j < 3; j++) {
            "123 4"
            if(entrada[j + (i*3)] != undefined) {
                aux += entrada[j + (i*3)];
            }
        }
        salida.push(aux);
        aux = '';
       
        e.target.value = salida.join('.').split("").reverse().join('');
    }
});


function dar_formato(num){
 
    var cadena = ""; var aux;
  
    var cont = 1,m,k;
  
    if(num<0) aux=1; else aux=0;
    
    num=num.toString();
 
    for(m=num.length-1; m>=0; m--){

     cadena = num.charAt(m) + cadena;
    
     if(cont%3 == 0 && m >aux)  cadena = "." + cadena; else cadena = cadena;
   
     if(cont== 3) cont = 1; else cont++;
    
    }
    
    cadena = cadena.replace(/.,/,",");
    
    return cadena;
    
    }


    function valideKey(evt){
			
        // code is the decimal ASCII representation of the pressed key.
        var code = (evt.which) ? evt.which : evt.keyCode;
        
        if(code==8) { // backspace.
          return true;
        } else if(code>=48 && code<=57) { // is a number.
          return true;
        } else{ // other keys.
          return false;
        }
    }

var vs_content = '#valor_semestre';
var ns_content = '#numero_semestre';
var vts_content = '#valor_total_semestre';
var d_content = '#descuento';
var vn_content = '#valor_neto';
var sf_content = '#saldo_financiar';
var nc_content = '#numero_cuota';
var vc_content = '#valor_cuota';

$(vs_content).keyup((e)=>{
    console.log('Hola mundo');
    var entrada = (e.target.value).replace(/\./g,'');
    console.log(entrada);
    var ns = $(ns_content).val();
    if(ns != "" && isNaN(ns) == false){
        let calc = parseInt(entrada) * parseInt(ns);
        if(isNaN(calc) == false){
            $(vts_content).val(dar_formato(calc));
            $('input[name="valor_total_semestre"]').val(calc);
            ApplyDescuento($(d_content).val());
        }else{
            $(vts_content).val('');
            $('input[name="valor_total_semestre"]').val('');
        }
     
    }
});

$(ns_content).keyup((e)=>{
    var entrada = (e.target.value).replace(/\./g,'');
    console.log(entrada);
    var vs = ($(vs_content).val()).replace(/\./g,'');;
    if(vs != "" && isNaN(vs) == false){
        let calc = parseInt(entrada) * parseInt(vs);
        if(isNaN(calc) == false){
            $(vts_content).val(dar_formato(calc));
            $('input[name="valor_total_semestre"]').val(calc);
            ApplyDescuento($(d_content).val());
        }else{
            $(vts_content).val('');
            $('input[name="valor_total_semestre"]').val('');
        }
        
    }
});

$(d_content).keyup((e)=>{
    ApplyDescuento(e.target.value);
});

function ApplyDescuento(entrada){
    entrada = (entrada).replace(/\./g,'');
    var vts = ($(vts_content).val()).replace(/\./g,'');
    if(vts != "" && isNaN(vts) == false){
        let calc = parseInt(vts) - parseInt(entrada);
        if(isNaN(calc) == false){
            $(vn_content).val(dar_formato(calc));
            $('input[name="valor_neto"]').val(calc);
            $(sf_content).val(dar_formato(calc));
            $('input[name="saldo_financiar"]').val(calc);
            ApplyCuotas($(nc_content).val())
        }else{
            $(vn_content).val('');
            $('input[name="valor_neto"]').val('');
            $(sf_content).val('');
            $('input[name="saldo_financiar"]').val('');
        }
    }
}

$(nc_content).keyup((e)=>{
    console.log('holaaaa');
    ApplyCuotas(e.target.value);
});

function ApplyCuotas(entrada){
    entrada = (entrada).replace(/\./g,'');
    var sf = ($(sf_content).val()).replace(/\./g,'');
    if(sf != "" && isNaN(sf) == false && entrada != "" && isNaN(entrada) == false){
        let calc = Math.round(parseInt(sf) / parseInt(entrada));
        if(isNaN(calc) == false){
            $(vc_content).val(dar_formato(calc));
            $('input[name="valor_cuotas"]').val(calc);
        }else{
            $(vc_content).val('');
            $('input[name="valor_cuotas"]').val('');
        }
    }else{
        $(vc_content).val('');
        $('input[name="valor_cuotas"]').val('');
    }
}

$('.ejecutarmodal').click(function(){
    $('.content-preloader2').removeClass(' show2loader');
});


$(document).on('click','.buttonAttr',function(e){
    let noRecibo = $(this).attr('noRecibo');
    let fechaRecibo = $(this).attr('fechaRecibo');
    let concepto = $(this).attr('concepto');
    let descripcion = $(this).attr('descripcion');
    let valor = $(this).attr('valor');
    let debe  =$(this).attr('debe');
    let haber = $(this).attr('haber');
    let elaboradoPor = $(this).attr('elaboradoPor');
    const Arrays = [noRecibo,fechaRecibo,concepto,descripcion,valor,debe,haber,elaboradoPor];
    const KeyId = ['#no_recibo','#fecha_recibo','#concepto','#descripcion','#valor','#debe','#haber','#elaborado'];
    AsignarAtributos(KeyId,Arrays);
}); 

function AsignarAtributos(KeyId, Arrays){
    for (let index = 0; index < KeyId.length; index++) {
        let value = Arrays[index];
        ($(KeyId[index])).val(value);
    }
}


$(document).on('click','.clickNoRecibo',function(e){
    $("#NoReciboAdmin").val($(this).text());
    var ruta = $(this).attr('ventana');
    ventana = window.open(ruta,'width=50%','height=100%');
    Iventana++;
});
let interval = setInterval(StatusVentana,500);


function StatusVentana(){
    if(ventana != ""){
        if(ventana.closed !== false && Iventana > 0){
            $('.content-preloader2').removeClass(' show2loader');
            window.location.reload();
            clearInterval(interval);
        }else{
            console.log("Esta cerrada");
        }
    }
}

function DesbloquearVentana(){
    $('.content-preloader2').removeClass(' show2loader');
}
