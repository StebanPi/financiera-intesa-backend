// Función transformDate si no está definida (fallback)
// MESES ya está definido en cartera.js, no lo redeclaramos
if(typeof transformDate === 'undefined'){
    function transformDate(date){
        if(!date) return '';
        try {
            var fecha = date.split('-');
            if(fecha.length < 3) return date;
            // Usar MESES de cartera.js si está disponible, sino usar array local
            var meses = (typeof MESES !== 'undefined' && MESES) ? MESES : ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            var mesIndex = parseInt(fecha[1]) - 1;
            if(mesIndex < 0 || mesIndex >= meses.length) return date;
            var mes = meses[mesIndex];
            return fecha[2]+"-"+mes+"-"+fecha[0];
        } catch(e) {
            console.error("Error en transformDate:", e);
            return date;
        }
    }
}

function Mostrar_SheetAbonos(){
    let id_cost = $("#id_cost").val();
    if(!id_cost || id_cost == ""){
        console.error("Mostrar_SheetAbonos: id_cost no encontrado");
        return;
    }
    $("#IdContentOperation").val(id_cost);
    console.log("Mostrar_SheetAbonos: Cargando abonos para id_cost:", id_cost);
    const obj = ENTRY.all();
    obj.done(function(response){
        console.log("Mostrar_SheetAbonos: Respuesta recibida:", response);
        try {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            if(!data || data.length === 0){
                console.log("Mostrar_SheetAbonos: No hay datos de abonos");
                return;
            }
            var SUMA = 0;
            for (let index = 0; index < data.length; index++) {
                var TR = $("<tr></tr>");
                var TD1,TD2,TD3,TD4,TD5,TD6,TD7,TD8;
                const a = [data[index].id, data[index].no_recibo,data[index].fecha_recibo, data[index].concepto,data[index].descripcion,data[index].valor,data[index].elaborado_por,data[index].debe, data[index].haber, data[index].forma || 'Efectivo'];
                SUMA = SUMA + parseInt(a[5]);
                TD1 = $("<td class='text-center'>"+ a[1] +"</td>");
                TD2 = $("<td class='text-center'>"+(typeof transformDate !== 'undefined' ? transformDate(a[2]) : a[2]) +"</td>");
                TD3 = $("<td class='text-center'>"+ a[3] +"</td>");
                TD4 = $("<td class='text-left' style='max-width: 200px; word-wrap: break-word;' title='"+a[4]+"'>"+ (a[4] || '') +"</td>");
                TD5 = $("<td class='font-weight-bold text-right number-lg d-flex'><div class=''>$</div><div class='ml-auto'>"+ dar_formato(a[5]) +"</div></td>");
                TD6 = $("<td class='text-center'>"+ a[6]+"</td>");
                // Botón de eliminar solo para admin y super-admin
                var deleteButton = '';
                if (typeof window.canDeleteReceipts !== 'undefined' && window.canDeleteReceipts) {
                    deleteButton = '<form action="/entry/destroy/'+a[0]+'" method="POST" style="display: inline;" onsubmit="event.preventDefault(); const form = this; showConfirmModal(\'¿Está seguro de eliminar el abono #'+a[1]+'? Esta acción NO se puede deshacer.\', \'Confirmar Eliminación\', \'Eliminar\', \'Cancelar\', \'btn-danger\').then(confirmed => { if(confirmed) { form.submit(); } }); return false;"><input type="hidden" name="_token" value="'+($('meta[name="csrf-token"]').attr('content') || '')+'"><input type="hidden" name="_method" value="POST"><button type="submit" class="btn btn-danger shadow btn-xs sharp mx-1 text-white" title="Eliminar" onclick="event.stopPropagation();"><i class="fa-solid fa-trash"></i></button></form>';
                }
                TD7 = $("<td class='text-center'><button type='button' onclick=\"openPrintModal('/finanzas/recibos/entry/"+a[0]+"/print')\" class='btn btn-primary shadow btn-xs cpointer sharp mx-1 text-white' title='Imprimir'><i class='fa-solid fa-print'></i></button><a class='btn btn-warning shadow btn-xs sharp mx-1 cpointer text-white editEntryBtn' data-id='"+a[0]+"' data-toggle='modal' data-target='#ModalEditEntry' title='Editar'><i class='fa-solid fa-edit'></i></a><a class='btn btn-success shadow btn-xs sharp mx-1 cpointer showMessage text-white' message='"+a[4]+"' title='Ver descripción'><i class='fa-solid fa-comment-dots'></i></a><a class='btn btn-danger shadow btn-xs sharp mx-1 text-white cpointer buttonAttr' data-toggle='modal' data-target='#exampleModal2' noRecibo='"+a[1]+"' fechaRecibo='"+a[2]+"' concepto='"+a[3]+"' descripcion='"+a[4]+"' valor='"+a[5]+"' elaboradoPor='"+a[6]+"' debe='"+a[7]+"' haber='"+a[8]+"' title='Ver detalles'><i class='fa-solid fa-eye'></i></a>"+deleteButton+"</td>");
            ApppendTo([TD1,TD2,TD3,TD4,TD6,TD5,TD7],TR);
            TR.appendTo("#table_items_abono");
        }
        var TR_tfoot = $("<tr class='bg-gray-1'></tr>");
        var TD1,TD2,TD3,TD4,TD5,TD6,TD7,TD8;
        TD1 = $("<td></td>");
        TD2 = $("<td></td>");
        TD3 = $("<td></td>");
        TD4 = $("<td></td>");
        TD5 = $("<td class='text-center'>Total Abono</td>");
        TD6 = $("<td class='d-flex font-weight-bold'><div>$</div><div class='ml-auto'>"+dar_formato(SUMA)+"</div></td>");
        TD7 = $("<td></td>");
        ApppendTo([TD1,TD2,TD3,TD4,TD5,TD6,TD7],TR_tfoot);
        TR_tfoot.appendTo("#table_items_abono_tfoot");

        const Neto = ($('#NetoP').val()).replace(/\./g,'');
        const Pendiente = parseInt(Neto) - parseInt(SUMA);
        var TR_tfoot = $("<tr class='bg-gray-2'></tr>");
        var TD1,TD2,TD3,TD4,TD5,TD6,TD7,TD8;
        TD1 = $("<td></td>");
        TD2 = $("<td></td>");
        TD3 = $("<td></td>");
        TD4 = $("<td></td>");
        TD5 = $("<td class='text-center' >Saldo</td>");
        TD6 = $("<td  class='d-flex font-weight-bold'><div>$</div><div class='ml-auto'>"+dar_formato(Pendiente)+"</div></td>");
        TD7 = $("<td></td>");
        ApppendTo([TD1,TD2,TD3,TD4,TD5,TD6,TD7],TR_tfoot);
        TR_tfoot.appendTo("#table_items_abono_tfoot");
        } catch(e) {
            console.error("Mostrar_SheetAbonos: Error al procesar datos:", e);
        }
    }).fail(function(xhr, status, error){
        console.error("Mostrar_SheetAbonos: Error en la petición:", {
            status: status,
            error: error,
            responseText: xhr.responseText
        });
    });
}

Mostrar_SheetAbonos();

function ChangeForm_SheetAbonos(){
    $(".noRecibo__Class_1").val(parseInt($(".noRecibo__Class_1").val())+1);
    $(".valor__Class_1").val("");
}


$("#estudianteInput").keyup(function(e){
    const name = e.target.value;
    console.log("Estas buscando " + name);
    const consulta = ESTUDIANTE.search(name);
    consulta.done((response) =>{
        $(".listItems").removeClass('d-none');
        var items = '';
        response = JSON.parse(response);
        for (let index = 0; index < response.length; index++) {
            const element = response[index];
            items += '<li idElement="'+element.id_cost+'" valueElement="'+element.nombre+'" class="elementSelectStudent">'+element.nombre+'</li>'; 
        }
        $(".listItems").html(items);
    });
});

$(document).on('click', '.elementSelectStudent', function(e){
    const element = e.target;
    const id = element.getAttribute('idElement');
    const name = element.getAttribute('valueElement');
    $(".listItems").addClass('d-none');
    $("#estudianteInput").val(name);
    $("#estudianteID").val(id);
});

$(".formatReceiptFormAbono").submit((e)=>{
    e.preventDefault();
    var i = 0;
    const estudiante = $("#estudianteID").val();
    if(estudiante == ""){
        $(".errorThirdReceipt").removeClass('d-none');
        setTimeout(()=>{
            $(".errorThirdReceipt").addClass('d-none');
        }, 1000);
        i++;

    }
    if(!$('input[name="forma"]').is(':checked')){
        $(".errorFormaReceipt").removeClass('d-none');
        setTimeout(()=>{
            $(".errorFormaReceipt").addClass('d-none');
        }, 1000);
        i++;
    }
    const values = $("#valor").val();
    if(values == ""){
        $(".errorValueReceipt").removeClass('d-none');
        setTimeout(()=>{
            $(".errorValueReceipt").addClass('d-none');
        }, 1000);
        i++;
    }
    if(i == 0){
        const send = ENTRY.createForm(".formatReceiptFormAbono");
        DesbloquearVentana();
        send.done((response) => {
            if(response == 'OK'){
                location.href ='/abonos/';
            }
        });
    }
});

$(document).on('keyup', '.searchAbonoReceipts',(e) => {
    if(e.keyCode == 16){
        const values = $('.searchAbonoReceipts').val();
        if(values != ""){
            location.href ='/abonos/'+values;
        }
    }
});