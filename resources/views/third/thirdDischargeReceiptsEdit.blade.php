@extends('dash.app')

@section('page')
    Editar Recibo de Egreso de Terceros
@endsection

@section('content')
    <x-third-receipts types="discharge" :content="$content" >
        <x-slot name="no_recibo">{{ $id }}</x-slot>
    </x-third-receipts>
@endsection