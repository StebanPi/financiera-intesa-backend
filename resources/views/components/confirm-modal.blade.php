@props(['id' => null, 'message' => '¿Está seguro?', 'title' => 'Confirmar', 'confirmText' => 'Aceptar', 'cancelText' => 'Cancelar', 'confirmClass' => 'btn-danger'])

@php
    $uniqueId = $id ?? uniqid('confirm_');
@endphp

<div class="modal fade" id="confirmModal{{ $uniqueId }}" tabindex="-1" role="dialog" aria-labelledby="confirmModal{{ $uniqueId }}Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
            <div class="modal-header bg-warning text-dark" style="border-bottom: none; padding: 25px;">
                <h5 class="modal-title d-flex align-items-center" id="confirmModal{{ $uniqueId }}Label" style="font-size: 18px; font-weight: 700;">
                    <i class="fa-solid fa-triangle-exclamation mr-3" style="font-size: 24px;"></i>
                    {{ $title }}
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 28px; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 30px; background: #f8f9fa;">
                <p class="mb-0" style="font-size: 16px; line-height: 1.6; color: #374151;">
                    {{ $message }}
                </p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 20px 30px; background: #ffffff;">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-times mr-2"></i>{{ $cancelText }}
                </button>
                <button type="button" id="confirmBtn{{ $uniqueId }}" class="btn {{ $confirmClass }} btn-sm px-4" style="border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-check mr-2"></i>{{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
