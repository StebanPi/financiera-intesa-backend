@props(['type' => 'success', 'message' => '', 'title' => null, 'id' => null])

@php
    $uniqueId = $id ?? uniqid('alert_');
    $icons = [
        'success' => 'fa-circle-check',
        'error' => 'fa-circle-xmark',
        'danger' => 'fa-circle-xmark',
        'warning' => 'fa-triangle-exclamation',
        'info' => 'fa-circle-info'
    ];
    
    $colors = [
        'success' => ['bg' => 'bg-success', 'text' => 'text-white', 'icon' => '#10b981'],
        'error' => ['bg' => 'bg-danger', 'text' => 'text-white', 'icon' => '#ef4444'],
        'danger' => ['bg' => 'bg-danger', 'text' => 'text-white', 'icon' => '#ef4444'],
        'warning' => ['bg' => 'bg-warning', 'text' => 'text-dark', 'icon' => '#f59e0b'],
        'info' => ['bg' => 'bg-info', 'text' => 'text-white', 'icon' => '#3b82f6']
    ];
    
    $titles = [
        'success' => 'Éxito',
        'error' => 'Error',
        'danger' => 'Error',
        'warning' => 'Advertencia',
        'info' => 'Información'
    ];
    
    $icon = $icons[$type] ?? 'fa-circle-info';
    $color = $colors[$type] ?? $colors['info'];
    $titleText = $title ?? $titles[$type] ?? 'Información';
@endphp

@if($message)
<div class="modal fade" id="alertModal{{ $uniqueId }}" tabindex="-1" role="dialog" aria-labelledby="alertModal{{ $uniqueId }}Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
            <div class="modal-header {{ $color['bg'] }} {{ $color['text'] }}" style="border-bottom: none; padding: 25px;">
                <h5 class="modal-title d-flex align-items-center" id="alertModal{{ $uniqueId }}Label" style="font-size: 18px; font-weight: 700;">
                    <i class="fa-solid {{ $icon }} mr-3" style="font-size: 24px;"></i>
                    {{ $titleText }}
                </h5>
                <button type="button" class="close {{ $color['text'] }}" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 28px; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 30px; background: #f8f9fa;">
                <div style="font-size: 16px; line-height: 1.6; color: #374151;">
                    {!! $message !!}
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 20px 30px; background: #ffffff;">
                <button type="button" class="btn btn-primary btn-sm px-4" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-check mr-2"></i>Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#alertModal{{ $uniqueId }}').modal('show');
    
    // Auto-cerrar después de 5 segundos
    setTimeout(function() {
        $('#alertModal{{ $uniqueId }}').modal('hide');
    }, 5000);
    
    // Eliminar el modal del DOM después de cerrarlo
    $('#alertModal{{ $uniqueId }}').on('hidden.bs.modal', function () {
        $(this).remove();
    });
});
</script>
@endif
