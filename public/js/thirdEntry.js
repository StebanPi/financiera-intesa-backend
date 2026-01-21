$("#addThirdEntry").submit((e)=>{
    
    const form = e.target;
    var msg = "";
    if(form.cedula.value == ""){
        msg += '<li>- La <b>cedula</b> es Obligatoria</li>';
    }
    if(form.nombre.value == ""){
        msg += '<li>- El <b>nombre</b> es Obligatorio</li>';
    }
    if(form.actividad.value == ""){
        msg += '<li>- La <b>actividad</b> es Obligatoria</li>';
    }

    if(msg != ""){
        e.preventDefault();
        $("#msgErrorList").html(msg);
        $("#msgError").removeClass('d-none');
        setTimeout(() =>{
            $("#msgError").addClass('d-none');
        }, 1000);
    }else{
        DesbloquearVentana();
    }
    
    
});

var isFirstIntenty = false;

$("#formAddThirdActivity").submit((e) =>{
 
    const form = e.target;
    var msg = "";
    if(form.nombre.value == ""){
        msg += '<li>- La <b>actividad</b> es Obligatoria</li>';
    }

    if(msg != ""){
        e.preventDefault();
        $("#msgErrorList1").html(msg);
        $("#msgError1").removeClass('d-none');
        setTimeout(() =>{
            $("#msgError1").addClass('d-none');
        }, 1000);
    }else{
        DesbloquearVentana();
    }
    /*const consulta = thirdEntry.addActivity();
    consulta.done((response)=>{
        if(response == 'OK'){
            isFirstIntenty = true;
            listActivity();
            form.nombre.value == "";
            $(".btnClose").click();
            BloqueoVentana();
        }
    });*/   
});



/*function listActivity(){
    const consulta = thirdEntry.listActivity();
    consulta.done((response)=>{
        if(isFirstIntenty == true){
            deleteChild("actividad");
            isFirstIntenty = false;
        }
        response = JSON.parse(response);
        console.log(response);
        var html = '';
        for (let index = 0; index < response.length; index++) {
            const element = response[index];
            html += '<option value="'+element.id+'">'+element.nombre+'</option>';
        }
        //console.log($(".listActivty").html());
        $(".listActivty").append(html);
    });
}

listActivity();*/

$("#estudianteInput").keyup(function(e){
    const name = e.target.value;
    const consulta = thirdEntry.search(name);
});


$("#thirdInput").keyup(function(e){
    const name = e.target.value;
    const consulta = thirdEntry.search(name);
    consulta.done((response) =>{
        $(".listItems").removeClass('d-none');
        var items = '';
        response = JSON.parse(response);
        for (let index = 0; index < response.length; index++) {
            const element = response[index];
            items += '<li idElement="'+element.id+'" valueElement="'+element.nombre+'" class="elementSelectThried">'+element.nombre+'</li>'; 
        }
        $(".listItems").html(items);
    });
    console.log('Hola'); 
});

$(document).on('click', '.elementSelectThried', function(e){
    const element = e.target;
    const id = element.getAttribute('idElement');
    const name = element.getAttribute('valueElement');
    $(".listItems").addClass('d-none');
    $("#thirdInput").val(name);
    $("#thirdID").val(id);
});

$("#changeConceptoThird").change(function(e){
    changeInputsDebeyHaber();
});

$(document).ready(function(){
    changeInputsDebeyHaber();
    recorrerSelect("formarReceipt-body-item-cell-select");
    recorrerSelect("formarReceipt-body-item-cell-select-2");
    /*var padre = $("#changeConceptoThird").parent();
    console.log(padre);
    $(padre).removeClass("dropdown");
    $(padre).removeClass("bootstrap-select");
    $(padre).removeClass("dropup");
    $(padre).removeClass("show");*/
});


function recorrerSelect(classe){
    var elementDelete = null;
    var elementShow = $("."+ classe);
    setTimeout(()=>{
        var i = 0;
        $("." + classe).each(function(index,element){
            if(i == 0){
                console.log(element);
                $()
                $(element).removeClass("dropdown");
                $(element).removeClass("bootstrap-select");
                $(element).removeClass("dropup");
                $(element).removeClass("show");
            }
            i++;
        });

        $("."+classe+" > button").remove();
    }, 2000);

}
//formarReceipt-body-item-cell-select


function changeInputsDebeyHaber(){
    const element = document.getElementById('changeConceptoThird');
    if(!element || !element.value) return;
    
    const debe = $("#changeConceptoThird > option[value='"+element.value+"']").attr('debe');
    const haber = $("#changeConceptoThird > option[value='"+element.value+"']").attr('haber');
    
    if(debe) {
        $('#debeAttr2 > option[value="'+debe+'"]').attr('selected', 'selected');
        const debeText = $('#debeAttr2 > option[value="'+debe+'"]').html();
        if(debeText) $("#SelectDebe").val(debeText);
    }
    
    if(haber) {
        $('#haberAttr2 > option[value="'+haber+'"]').attr('selected', 'selected');
        const haberText = $('#haberAttr2 > option[value="'+haber+'"]').html();
        if(haberText) $("#SelectHaber").val(haberText);
    }
}

$("#formatReceiptForm").submit((e)=>{
    const tercero = e.target.thirdID;
    const valor = e.target.valor;
    if(tercero.value == ""){
        e.preventDefault();
        $(".errorThirdReceipt").removeClass('d-none');
        setTimeout(()=>{ $(".errorThirdReceipt").addClass('d-none'); }, 1000);
    }
    if(valor.value == ""){
        e.preventDefault();
        $(".errorValueReceipt").removeClass('d-none');
        setTimeout(()=>{ $(".errorValueReceipt").addClass('d-none'); }, 1000);
    }

});

var stateShowReceipt = "showConsecutiveReceipts";
var stateNoneReceipt = "showInputSearchReceipts";
$("#changeConsecutiveInputReceipts").click(()=>{
    $("#"+stateShowReceipt).addClass('d-none');
    $("#"+stateNoneReceipt).removeClass('d-none');
    const saveValue = stateShowReceipt;
    stateShowReceipt = stateNoneReceipt;
    stateNoneReceipt = saveValue;
});

$(document).keyup((e)=>{
    if(e.keyCode == 16){
        const inputElement = document.getElementById('showInputSearchReceipts');
        if(inputElement && inputElement.value != ""){
            const val = inputElement.value;
            const type = $("#TypeReceipts").val();
            location.href ='/receipts/third/'+type+'/'+ val;
        }
    }
});

//FINANCIERA

$(".searchStudent").keyup(function(e){
    const name = e.target.value;
    const consulta = ESTUDIANTE.searchAll(name);
    consulta.done((response) =>{
        $(".listItemName").removeClass('d-none');
        var items = '';
        response = JSON.parse(response);
        for (let index = 0; index < response.length; index++) {
            const element = response[index];
            items += '<li idElement="'+element.cod_alumno+'" valueElement="'+element.nombre+'" class="elementSelectStudent1">'+element.nombre+'</li>'; 
        }
        $(".listItemName").html(items);
    });
    console.log('Hola'); 
});

$(document).on('click', '.elementSelectStudent1', function(e){
    const element = e.target;
    const id = element.getAttribute('idElement');
    location.href ='/financiera/'+id+'/';
    DesbloquearVentana();
  
    const name = element.getAttribute('valueElement');
    $(".listItemName").addClass('d-none');
    $(".searchStudent").val(name);
    $(".studentSelect").val(id);
});

$("#formatFormFinanciera").submit(function(e){

    var fechaActual = ($("#fecha_pago").val()).split('-');
    EstaEnError = false;

    if($("#valor_semestre").val() == ""){
        EstaEnError = true;
        classe = '.error_vs';
        e.preventDefault();
        $(".error_vs").removeClass('d-none');
        
    }
    if($("#numero_semestre").val() == ""){
        EstaEnError = true;
        classe = '.error_ns';
        e.preventDefault();
        $(".error_ns").removeClass('d-none');
        
    }   
    if($("#descuento").val() == ""){
        EstaEnError = true;
        classe = '.error_d';
        e.preventDefault();
        $(".error_d").removeClass('d-none');
        
    }
    if($("#numero_cuota").val() == ""){
        EstaEnError = true;
        classe = '.error_nc';
        e.preventDefault();
        $(".error_nc").removeClass('d-none');
        
    }

    if($("#fecha_pago").val() == ""){
        EstaEnError = true;
        e.preventDefault();
        $(".error_fp").removeClass('d-none');
        $(".error_fp").text('Complete este campo.');
    }

    if(fechaActual[2] > 28){
        EstaEnError = true;
        e.preventDefault();
        $(".error_fp").removeClass('d-none');
        $(".error_fp").text('No se pueden utilizar los dias 29, 30 y 31.');
    }

    if(EstaEnError){
        setTimeout(() => {
            $('.ActiveError').addClass('d-none');
        }, 1000);
    }else{
        DesbloquearVentana();
    }
});

/*$("#numero_cuotas").keyup((e)=>{
    const sf = ($("#saldo_financiar").val()).replace(/\./g,'');
    const nc = e.target.value;
    if(nc == ""){
        $("#valor_cuota").val("");
    }else{
        const vc =  Math.round(sf / nc);
        if(!isNaN(vc)){
            $("#valor_cuota").val(dar_formato(vc));
        }else{
            $("#valor_cuota").val("");
        }
    }
});*/


///CARTERA

$(".searchStudentCartera").keyup(function(e){
    const name = e.target.value;
    const consulta = ESTUDIANTE.searchAll(name);
    consulta.done((response) =>{
        $(".listItemName").removeClass('d-none');
        var items = '';
        response = JSON.parse(response);
        for (let index = 0; index < response.length; index++) {
            const element = response[index];
            items += '<li idElement="'+element.cod_alumno+'" valueElement="'+element.nombre+'" class="elementSelectStudentCartera1">'+element.nombre+'</li>'; 
        }
        $(".listItemName").html(items);
    });
    console.log('Hola'); 
});

$(document).on('click', '.elementSelectStudentCartera1', function(e){
    const element = e.target;
    const id = element.getAttribute('idElement');
    location.href ='/cartera/'+id+'/';
    DesbloquearVentana();
  
    const name = element.getAttribute('valueElement');
    $(".listItemName").addClass('d-none');
    $(".searchStudent").val(name);
    $(".studentSelect").val(id);
});