@extends('dash.app')

@section('page')
    Ver Cartera del Estudiante
@endsection

@section('content')
    <x-cartera :id_cost="$id_cost" :entries="json_decode($entries)" :student="json_decode($student)" :purses="json_decode($purses)" ></x-cartera>
@endsection