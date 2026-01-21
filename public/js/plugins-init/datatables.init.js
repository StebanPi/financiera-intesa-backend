(function($) {
    // Idioma español definido directamente para evitar CORS y asegurar consistencia
    var spanishLanguage = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": ">",
            "sPrevious": "<"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };

    var table = $('#example3').DataTable({
        "language": spanishLanguage,
        "responsive": "true",
        "dom": 'lBfrtip',
        "buttons": [{
                extend: 'excel',
                text: '<i class="mdi mdi-file-excel">Excel</i>',
                titleAttr: 'Exportar a Excel',
                className: 'ml-3 btn btn-success btn-xs',
                excelStyles: {
                    template: 'blue_medium'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="mdi mdi-file-pdf">PDF</i>',
                titleAttr: 'Exportar a Pdf',
                className: 'ml-3 btn btn-danger btn-xs'

            },
            {
                extend: 'print',
                text: '<i class="mdi mdi-cloud-print">Imprimir</i>',
                titleAttr: 'Exportar a Pdf',
                className: 'ml-3 btn btn-info btn-xs'
            }
        ]
    });
    $('tbody').on('click', 'tr', function() {
        var data = table.row(this).data();
    });

    let fLote = document.getElementById("fLote");
    if (comprobar(fLote)) {
        fLote.onchange = (event) => {
            let data = event.target.value;
            filter(event.target.getAttribute("data-index"), data);
        }
    }
    let fCargo = document.getElementById("fCargo");
    if (comprobar(fCargo)) {
        fCargo.onchange = (event) => {
            let data = event.target.value;
            filter(event.target.getAttribute("data-index"), data);
        }
    }

    function filter(column, data) {
        if (data == -1) {
            table.column(column).search("").draw();
        } else {
            table.column(column).search(data).draw();
        }
    }

    function comprobar(tag) {
        if (tag != undefined) {
            return true;
        }
        return false;
    }

})(jQuery);