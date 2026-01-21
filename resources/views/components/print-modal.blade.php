<!-- Modal para selección de tamaño de papel -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="printModalLabel">
                    <i class="fa-solid fa-print mr-2"></i>Seleccionar Tamaño de Papel
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">Seleccione el tamaño de papel para la impresión:</p>
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-primary btn-lg w-100 mb-3" onclick="printReceipt(76)">
                            <i class="fa-solid fa-file-lines mr-2"></i>
                            <strong>76 mm</strong>
                            <br>
                            <small>Papel Estrecho</small>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-primary btn-lg w-100 mb-3" onclick="printReceipt(80)">
                            <i class="fa-solid fa-file-lines mr-2"></i>
                            <strong>80 mm</strong>
                            <br>
                            <small>Papel Estándar</small>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variable global para almacenar la URL base del recibo
var printReceiptBaseUrl = '';

/**
 * Abrir modal de impresión y establecer la URL base del recibo
 * @param {string} receiptUrl - URL base del recibo (sin parámetros paper/offset)
 */
function openPrintModal(receiptUrl) {
    printReceiptBaseUrl = receiptUrl;
    $('#printModal').modal('show');
}

/**
 * Imprimir recibo con el tamaño de papel seleccionado
 * @param {number} paperSize - Tamaño de papel (76 o 80)
 */
function printReceipt(paperSize) {
    if (!printReceiptBaseUrl) {
        console.error('URL base del recibo no definida');
        return;
    }
    
    // Construir URL con parámetro paper
    const url = printReceiptBaseUrl + (printReceiptBaseUrl.includes('?') ? '&' : '?') + 'paper=' + paperSize;
    
    // Abrir en nueva pestaña
    window.open(url, '_blank');
    
    // Cerrar modal
    $('#printModal').modal('hide');
}
</script>
