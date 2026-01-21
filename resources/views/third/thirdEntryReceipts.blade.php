@extends('dash.app')

@section('page')
    Recibos de Ingreso de Terceros
@endsection

@section('content')
    <x-third-receipts types="entry" ></x-third-receipts>
@endsection
