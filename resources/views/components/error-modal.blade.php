@props(['errors' => null])

@if($errors && $errors->any())
<div class="modal fade" id="errorModalValidation" tabindex="-1" role="dialog" aria-labelledby="errorModalValidationLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
            <div class="modal-header bg-danger text-white" style="border-bottom: none; padding: 25px;">
                <h5 class="modal-title d-flex align-items-center" id="errorModalValidationLabel" style="font-size: 18px; font-weight: 700;">
                    <i class="fa-solid fa-triangle-exclamation mr-3" style="font-size: 24px;"></i>
                    Errores de Validación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 28px; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 30px; background: #f8f9fa;">
                <p class="mb-3" style="font-size: 16px; color: #374151; font-weight: 600;">
                    Por favor corrige los siguientes errores:
                </p>
                <ul class="mb-0" style="padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li style="font-size: 15px; line-height: 1.8; color: #374151; margin-bottom: 8px;">
                            <i class="fa-solid fa-circle-exclamation text-danger mr-2"></i>{{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 20px 30px; background: #ffffff;">
                <button type="button" class="btn btn-danger btn-sm px-4" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-check mr-2"></i>Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#errorModalValidation').modal('show');
    
    // No auto-cerrar para errores de validación, el usuario debe cerrar manualmente
    $('#errorModalValidation').on('hidden.bs.modal', function () {
        $(this).remove();
    });
});
</script>
@endif
