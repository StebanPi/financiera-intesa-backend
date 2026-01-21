@extends('dash.app')

@section('page')
    Ver Otro Abono del Estudiante
@endsection

@section('content')
    <x-otros-abonos :content="json_decode($content)" ></x-otros-abonos>
@endsection