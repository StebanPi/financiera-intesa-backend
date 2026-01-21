@extends('dash.app')

@section('page')
    Ver Abono del Estudiante
@endsection

@section('content')
    <x-abono-receipts :content="json_decode($content)" ></x-abono-receipts>
@endsection