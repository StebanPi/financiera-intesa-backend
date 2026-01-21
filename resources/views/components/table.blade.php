@php
    $thead = json_decode($thead);
@endphp

<style>
    #example3_wrapper {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    #example3_wrapper .dataTables_length,
    #example3_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    
    #example3_wrapper .dataTables_length label {
        color: #374151;
        font-weight: 500;
        margin-right: 10px;
    }
    
    #example3_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #d1d5db;
        padding: 5px 10px;
        margin: 0 5px;
    }
    
    #example3_wrapper .dataTables_filter label {
        color: #374151;
        font-weight: 500;
    }
    
    #example3_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #d1d5db;
        padding: 8px 12px;
        margin-left: 10px;
    }
    
    #example3 {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    #example3 thead th {
        background-color: #f9fafb;
        color: #374151;
        font-weight: 600;
        padding: 12px 15px;
        border-bottom: 2px solid #e5e7eb;
        text-align: left;
    }
    
    #example3 tbody td {
        padding: 12px 15px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }
    
    #example3 tbody tr:hover {
        background-color: #f9fafb;
    }
    
    #example3 tbody td a {
        color: #10b981;
        text-decoration: none;
        font-weight: 500;
    }
    
    #example3 tbody td a:hover {
        text-decoration: underline;
    }
    
    #example3_wrapper .dt-buttons {
        margin-bottom: 15px;
    }
    
    #example3_wrapper .dt-buttons .btn {
        border-radius: 6px;
        padding: 8px 16px;
        font-weight: 500;
        margin-right: 8px;
    }
    
    #example3_wrapper .dataTables_info {
        color: #6b7280;
        padding-top: 12px;
    }
    
    #example3_wrapper .dataTables_paginate {
        padding-top: 12px;
    }
    
    #example3_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        padding: 6px 12px;
        margin: 0 2px;
        border: 1px solid #d1d5db;
    }
    
    #example3_wrapper .dataTables_paginate .paginate_button.current {
        background: #10b981 !important;
        color: white !important;
        border-color: #10b981 !important;
    }
    
    #example3_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
</style>

<div>
    <div class="table-responsive">
        <div id="example3_wrapper" class="dataTables_wrapper no-footer">
            <table id="example3" class="display dataTable no-footer" style="min-width: 845px" role="grid" aria-describedby="example3_info">
                <thead>
                    <tr>
                        @foreach($thead as $item)
                            @if ($item->EsVacio)
                                <th class="sorting" tabindex="0" aria-controls="example3" rowspan="1" colspan="1" aria-label="Name: activate to sort column ascending"></th>
                            @else
                                <th class="sorting" tabindex="0" aria-controls="example3" rowspan="1" colspan="1" aria-label="Name: activate to sort column ascending">
                                    {{ $item->Columna }} ↑↓
                                </th>
                            @endif 
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tbody as $item)
                        <tr>
                            @foreach($thead as $head)
                                @if ($head->EsVacio)
                                    <td></td>
                                @else   
                                    @php
                                        $columns = $head->Origen;
                                    @endphp
                                    @if ($head->EsEnlace)
                                        @php
                                            $params = $head->Ruta->Parametro;
                                        @endphp
                                        <td><a href="{{ route($head->Ruta->Nombre, $item->$params) }}">{{ $item->$columns }}</a></td>    
                                    @else
                                        <td>{{ $item->$columns ?? '-' }}</td>
                                    @endif
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
