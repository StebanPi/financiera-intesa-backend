@extends('dash.app')

@section('page')
    Recibos de Egreso de Terceros
@endsection

@section('content')
    <x-third-receipts types="discharge" ></x-third-receipts>
@endsection
