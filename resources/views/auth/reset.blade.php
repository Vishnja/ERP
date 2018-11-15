@extends('templates.auth')

@section('page-title')
    Установить пароль
@endsection

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ URL::to('/') }}"><b>Exterium</b> ERP</a>
        </div><!-- /.login-logo -->

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="login-box-body">
            <p class="login-box-msg">Установить пароль</p>

            <form method="post" action="{{ url('/password/reset') }}">
                {!! csrf_field() !!}
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Пароль" name="password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Подтверждение пароля" name="password_confirmation">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="row">
                    <div class="col-xs-2"></div>

                    <div class="col-xs-8">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Изменить пароль</button>
                    </div><!-- /.col -->

                    <div class="col-xs-2"></div>
                </div>
            </form>

            <a href="{{ url('/auth/login') }}">Войти</a><br>

        </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
@endsection