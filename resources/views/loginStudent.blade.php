@extends('login.app')

@section('content')
<div class="authincation h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="col-md-6">
                <div class="authincation-content">
                    <div class="row no-gutters">
                        <div class="col-xl-12">
                            <div class="auth-form">
                                <h4 class="text-center mb-4">INTESA</h4>
                                <form action="index.html">
                                    <div class="form-group">
                                        <label class="mb-1"><strong>Cedula</strong></label>
                                        <input type="number" class="form-control" name="cedula" value="">
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-block">Buscar</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection