// Función simplificada para obtener totales del servidor
function TotalPursesSimplificado(){
    let id_cost = $("#id_cost").val();
    if(!id_cost){
        console.error('id_cost no encontrado');
        return;
    }
    
    var tfootElement = document.getElementById('TABLE_ITEMS_CARTERA_TFOOT');
    if(!tfootElement){
        console.error('TABLE_ITEMS_CARTERA_TFOOT no existe');
        return;
    }
    
    tfootElement.innerHTML = "";
    
    // Obtener totales calculados del servidor
    $.ajax({
        url: '/purse/totales/',
        method: 'POST',
        data: { id: id_cost },
        success: function(response){
            try {
                var totales = typeof response === 'string' ? JSON.parse(response) : response;
                
                var CuotasTotal = parseFloat(totales.cuotas_total) || 0;
                var Abonado = parseFloat(totales.total_abonado) || 0;
                var SaldoPendiente = parseFloat(totales.saldo_pendiente) || 0;
                var SaldoAFavor = parseFloat(totales.saldo_a_favor) || 0;
                
                console.log("TotalPurses - Totales del servidor:", {
                    CuotasTotal: CuotasTotal,
                    Abonado: Abonado,
                    SaldoPendiente: SaldoPendiente,
                    SaldoAFavor: SaldoAFavor
                });
                
                // Crear filas de totales
                var tfootEl = $("#TABLE_ITEMS_CARTERA_TFOOT");
                
                // Total Programa
                var TR1 = $("<tr class='bg-azul'></tr>");
                TR1.append($("<td></td>"));
                TR1.append($("<td></td>"));
                TR1.append($("<td class='text-center'>Total Programa</td>"));
                TR1.append($("<td class='text-center font-weight-bold'>$"+dar_formato(CuotasTotal)+"</td>"));
                for(var i = 0; i < 5; i++){ TR1.append($("<td></td>")); }
                tfootEl.append(TR1);
                
                // Total Abonado
                var TR2 = $("<tr class='bg-gray-2'></tr>");
                TR2.append($("<td></td>"));
                TR2.append($("<td></td>"));
                TR2.append($("<td></td>"));
                TR2.append($("<td class='text-center font-weight-bold'>Total Abonado</td>"));
                TR2.append($("<td class='d-flex font-weight-bold'><div>$</div><div class='ml-auto'>"+dar_formato(Abonado)+"</div></td>"));
                for(var i = 0; i < 4; i++){ TR2.append($("<td></td>")); }
                tfootEl.append(TR2);
                
                // Saldo Pendiente
                var TR3 = $("<tr class='bg-rojosuave'></tr>");
                TR3.append($("<td></td>"));
                TR3.append($("<td></td>"));
                TR3.append($("<td></td>"));
                TR3.append($("<td class='text-center'>Saldo Pendiente</td>"));
                TR3.append($("<td class='d-flex font-weight-bold'><div>$</div><div class='ml-auto'>"+dar_formato(SaldoPendiente)+"</div></td>"));
                for(var i = 0; i < 4; i++){ TR3.append($("<td></td>")); }
                tfootEl.append(TR3);
                
                // Saldo a Favor
                var TR4 = $("<tr class='bg-verdesuave'></tr>");
                TR4.append($("<td></td>"));
                TR4.append($("<td></td>"));
                TR4.append($("<td></td>"));
                TR4.append($("<td class='text-center'>Saldo a Favor</td>"));
                TR4.append($("<td class='d-flex font-weight-bold'><div>$</div><div class='ml-auto'>"+dar_formato(SaldoAFavor)+"</div></td>"));
                for(var i = 0; i < 4; i++){ TR4.append($("<td></td>")); }
                tfootEl.append(TR4);
                
                // Actualizar valores en el header si existen
                var saldoFavorElement = $("#SaldoFavorText");
                var saldoPendienteElement = $("#SaldoPendienteText");
                
                if(saldoFavorElement.length > 0){
                    saldoFavorElement.html(dar_formato(SaldoAFavor));
                }
                
                if(saldoPendienteElement.length > 0){
                    saldoPendienteElement.html(dar_formato(SaldoPendiente));
                }
                
                console.log("TotalPurses - Filas de totales agregadas correctamente");
            } catch(e) {
                console.error('Error al procesar totales:', e);
            }
        },
        error: function(xhr, status, error){
            console.error('Error al obtener totales:', error);
        }
    });
}

