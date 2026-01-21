/**
 * Función para mostrar un modal de confirmación personalizado
 * @param {string} message - Mensaje de confirmación
 * @param {string} title - Título del modal (opcional)
 * @param {string} confirmText - Texto del botón de confirmación (opcional)
 * @param {string} cancelText - Texto del botón de cancelación (opcional)
 * @param {string} confirmClass - Clase CSS del botón de confirmación (opcional, por defecto 'btn-danger')
 * @returns {Promise<boolean>} - Promise que se resuelve con true si se confirma, false si se cancela
 */
function showConfirmModal(message, title = 'Confirmar', confirmText = 'Aceptar', cancelText = 'Cancelar', confirmClass = 'btn-danger') {
    return new Promise((resolve) => {
        const uniqueId = 'temp_' + Date.now();
        
        // Crear el HTML del modal con z-index alto para aparecer sobre otros modales
        const modalHtml = `
            <div class="modal fade" id="confirmModal${uniqueId}" tabindex="-1" role="dialog" aria-labelledby="confirmModal${uniqueId}Label" aria-hidden="true" data-backdrop="static" data-keyboard="false" style="z-index: 9999 !important;">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document" style="z-index: 10000 !important;">
                    <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); z-index: 10001 !important;">
                        <div class="modal-header bg-warning text-dark" style="border-bottom: none; padding: 25px;">
                            <h5 class="modal-title d-flex align-items-center" id="confirmModal${uniqueId}Label" style="font-size: 18px; font-weight: 700;">
                                <i class="fa-solid fa-triangle-exclamation mr-3" style="font-size: 24px;"></i>
                                ${title}
                            </h5>
                            <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 28px; line-height: 1;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 30px; background: #f8f9fa;">
                            <p class="mb-0" style="font-size: 16px; line-height: 1.6; color: #374151;">
                                ${message}
                            </p>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 20px 30px; background: #ffffff;">
                            <button type="button" class="btn btn-secondary btn-sm px-4 btnCancel" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                                <i class="fa-solid fa-times mr-2"></i>${cancelText}
                            </button>
                            <button type="button" class="btn ${confirmClass} btn-sm px-4 btnConfirm" style="border-radius: 8px; font-weight: 600;">
                                <i class="fa-solid fa-check mr-2"></i>${confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Agregar el modal al body
        $('body').append(modalHtml);
        
        const $modal = $(`#confirmModal${uniqueId}`);
        
        // Verificar si hay otros modales abiertos
        const existingModals = $('.modal:visible').not($modal);
        const hasOtherModals = existingModals.length > 0;
        let highestZIndex = 1050;
        
        if (hasOtherModals) {
            // Si hay otros modales, obtener el z-index más alto
            highestZIndex = Math.max(...existingModals.map(function() {
                const zIndex = parseInt($(this).css('z-index')) || 1050;
                return zIndex;
            }).get());
            
            // Configurar z-index del modal de confirmación antes de mostrarlo
            $modal.css({
                'z-index': highestZIndex + 10,
                'position': 'fixed'
            });
        }
        
        // Mostrar el modal
        $modal.modal({
            backdrop: hasOtherModals ? false : 'static',
            keyboard: false,
            show: true
        });
        
        // Ajustar después de mostrar
        $modal.on('shown.bs.modal', function() {
            // Ajustar z-index del backdrop si hay otros modales
            if (hasOtherModals) {
                // Agregar backdrop personalizado con z-index más alto que el modal padre
                const backdropZIndex = highestZIndex + 9;
                const customBackdrop = $('<div class="modal-backdrop fade show" style="z-index: ' + backdropZIndex + '; background-color: rgba(0,0,0,0.5);"></div>');
                $('body').append(customBackdrop);
                $modal.data('custom-backdrop', customBackdrop);
                
                // Asegurar que el modal esté por encima de todo
                $modal.css({
                    'z-index': highestZIndex + 10,
                    'position': 'fixed'
                });
            } else {
                // Ajustar backdrop normal si no hay otros modales
                $('.modal-backdrop').last().css('z-index', parseInt($modal.css('z-index')) - 1 || 1040);
            }
        });
        
        // Manejar confirmación
        $modal.find('.btnConfirm').on('click', function() {
            $modal.modal('hide');
            resolve(true);
        });
        
        // Manejar cancelación
        $modal.find('.btnCancel, .close').on('click', function() {
            $modal.modal('hide');
            resolve(false);
        });
        
        // Limpiar el modal del DOM cuando se cierre
        $modal.on('hidden.bs.modal', function() {
            // Eliminar backdrop personalizado si existe
            const customBackdrop = $modal.data('custom-backdrop');
            if (customBackdrop && customBackdrop.length) {
                customBackdrop.remove();
            }
            
            // Eliminar el modal
            $modal.remove();
        });
    });
}

// Función helper para reemplazar confirm() en formularios
function confirmSubmit(form, message, title = 'Confirmar') {
    event.preventDefault();
    showConfirmModal(message, title).then(confirmed => {
        if (confirmed) {
            form.submit();
        }
    });
}
