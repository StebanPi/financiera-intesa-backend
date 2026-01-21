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

function Mostrar_SheetOtrosAbonos(){
    let id_cost = $("#id_cost").val();
    if(!id_cost || id_cost == ""){
        console.error("Mostrar_SheetOtrosAbonos: id_cost no encontrado");
        return;
    }
    $("#IdContentOperation").val(id_cost);
    console.log("Mostrar_SheetOtrosAbonos: Cargando otros abonos para id_cost:", id_cost);
    const obj = OtherENTRIES.all();
    obj.done(function(response){
        console.log("Mostrar_SheetOtrosAbonos: Respuesta recibida:", response);
        try {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            if(!data || data.length === 0){
                console.log("Mostrar_SheetOtrosAbonos: No hay datos de otros abonos");
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
            TD5 = $("<td class='text-center'>"+ a[6]+"</td>");
            TD6 = $("<td class='font-weight-bold text-right number-lg d-flex'><div class=''>$</div><div class='ml-auto'>"+ dar_formato(a[5]) +"</div></td>");
            // Botón de eliminar solo para admin y super-admin
            var deleteButton = '';
            if (typeof window.canDeleteReceipts !== 'undefined' && window.canDeleteReceipts) {
                deleteButton = '<form action="/other/entry/destroy/'+a[0]+'" method="POST" style="display: inline;" onsubmit="event.preventDefault(); const form = this; showConfirmModal(\'¿Está seguro de eliminar el otro abono #'+a[1]+'? Esta acción NO se puede deshacer.\', \'Confirmar Eliminación\', \'Eliminar\', \'Cancelar\', \'btn-danger\').then(confirmed => { if(confirmed) { form.submit(); } }); return false;"><input type="hidden" name="_token" value="'+($('meta[name="csrf-token"]').attr('content') || '')+'"><input type="hidden" name="_method" value="POST"><button type="submit" class="btn btn-danger shadow btn-xs sharp mx-1 text-white" title="Eliminar" onclick="event.stopPropagation();"><i class="fa-solid fa-trash"></i></button></form>';
            }
            TD7 = $("<td class='text-center'><button type='button' onclick=\"openPrintModal('/finanzas/recibos/other-entry/"+a[0]+"/print')\" class='btn btn-primary shadow btn-xs cpointer sharp mx-1 text-white' title='Imprimir'><i class='fa-solid fa-print'></i></button><a class='btn btn-warning shadow btn-xs sharp mx-1 cpointer text-white editOtherEntryBtn' data-id='"+a[0]+"' data-toggle='modal' data-target='#ModalEditOtherEntry' title='Editar'><i class='fa-solid fa-edit'></i></a><a class='btn btn-success shadow btn-xs sharp mx-1 cpointer showMessage text-white' message='"+a[4]+"' title='Ver descripción'><i class='fa-solid fa-comment-dots'></i></a><a class='btn btn-danger shadow btn-xs sharp mx-1 text-white cpointer buttonAttr' data-toggle='modal' data-target='#exampleModal2' noRecibo='"+a[1]+"' fechaRecibo='"+a[2]+"' concepto='"+a[3]+"' descripcion='"+a[4]+"' valor='"+a[5]+"' elaboradoPor='"+a[6]+"' debe='"+a[7]+"' haber='"+a[8]+"' title='Ver detalles'><i class='fa-solid fa-eye'></i></a>"+deleteButton+"</td>");
            ApppendTo([TD1,TD2,TD3,TD5,TD6,TD7],TR);
            TR.appendTo("#Table_Otros_Items");
        }
        var TR_tfoot = $("<tr class='bg-gray-1'></tr>");
        var TD1,TD2,TD3,TD4,TD5,TD6,TD7,TD8;
        TD1 = $("<td></td>");
        TD2 = $("<td></td>");
        TD3 = $("<td></td>");
        TD4 = $("<td class='text-center'>Total Abono</td>");
        TD5 = $("<td class='d-flex font-weight-bold'><div>$</div><div class='ml-auto'>"+dar_formato(SUMA)+"</div></td>");
        TD6 = $("<td></td>");
        TD7 = $("<td></td>");
        ApppendTo([TD1,TD2,TD3,TD4,TD5,TD6,TD7],TR_tfoot);
        TR_tfoot.appendTo("#Table_Otros_Items_foot");
        } catch(e) {
            console.error("Mostrar_SheetOtrosAbonos: Error al procesar datos:", e);
        }
    }).fail(function(xhr, status, error){
        console.error("Mostrar_SheetOtrosAbonos: Error en la petición:", {
            status: status,
            error: error,
            responseText: xhr.responseText
        });
    });
}

Mostrar_SheetOtrosAbonos();

$(".formatReceiptFormOtrosAbono").submit((e)=>{
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
        const send = OtherENTRIES.createForm(".formatReceiptFormOtrosAbono");
        DesbloquearVentana();
        send.done((response) => {
            if(response == 'OK'){
                location.href ='/otros/abonos/';
            }
        });
    }
});

$(document).on('keyup', '.searchOtrosAbonoReceipts',(e) => {
    if(e.keyCode == 16){
        const values = $('.searchOtrosAbonoReceipts').val();
        if(values != ""){
            location.href ='/otros/abonos/'+values;
        }
    }
});