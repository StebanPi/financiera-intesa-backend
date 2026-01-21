@extends('dash.app')
@section('content')
    <div class="container-fluid">
        <div class="text-center">
            <h2>Informes</h2>
        </div>
        <div class="row">
            <div class="col-md-4">
                <a class="itemReceipt">
                    <div>
                        <div class="mb-3">
                            <i class="fa-brands fa-codepen"></i>
                        </div>
                        Arqueo de Caja
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="itemReceipt">
                    <div>
                        <div class="mb-3">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        Cartera por Programa
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="itemReceipt">
                    <div>
                        <div class="mb-3">
                            <i class="fa-solid fa-book-atlas"></i>
                        </div>
                        Cartera General
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <a class="itemReceipt">
                    <div>
                        <div class="mb-3">
                            <i class="fa-solid fa-arrow-up"></i>
                            <i class="fa-solid fa-building"></i>
                        </div>
                        Ingreso de Terceros
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="itemReceipt">
                    <div>
                        <div class="mb-3">
                            <i class="fa-solid fa-arrow-down"></i>
                            <i class="fa-solid fa-building"></i>
                        </div>
                        Egreso de Terceros
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
@section('page')
    Recibos
@endsection