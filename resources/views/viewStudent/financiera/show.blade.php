@extends('dash.app')

@section('page')
    Ver Financiera del Estudiante
@endsection

@section('content')
    <x-financiera :content="json_decode($content)" :alumno="json_decode($alumno)" ></x-financiera>
@endsection