@extends('login.app')

@section('content')

<div class="authincation h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-lock mr-2"></i>Privilegios de Administrador
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('post.privileges') }}">
                            @csrf
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Contraseña</label>
                                <div class="col-sm-9">
                                    <input type="password" name="password" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">NoRecibo</label>
                                <div class="col-sm-9">
                                    <input type="no_recibo" readonly name="no_recibo" value="{{ $no_recibo }}" class="form-control" id="NoReciboAdmin">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-primary btn-sm ejecutarmodal"><i class="fa-solid fa-lock-open mr-2"></i>Acceder</button>
                                </div>
                            </div>
                            <input type="hidden" name="route" value="{{ $route }}">
                        </form>

                        
                            @if ($errors->any())
                            <div class="alert alert-warning alert-dismissible fade show">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <strong>Error</strong> <small>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li><i class="fa-solid fa-circle-exclamation text-white mr-2"></i><small>{{ $error }}</small></li>
                                        @endforeach
                                    </ul>
                                </small>
                                <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                                </button>
                            </div>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection