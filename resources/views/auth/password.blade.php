@extends('templates.auth')

@section('page-title')
    Восстановление пароля
@endsection

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ URL::to('/') }}"><b>Exterium</b> ERP</a>
        </div><!-- /.login-logo -->

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

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
            <p class="login-box-msg">Восстановление пароля</p>

            <form method="post" action="{{ url('/password/email') }}" class="form-password-reset">
                {!! csrf_field() !!}

                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="row">
                    {{--<div class="col-xs-2"></div>--}}

                    <div class="col-xs-12 submit-wrap">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Отослать ссылку для восстановления пароля</button>
                    </div><!-- /.col -->

                    {{--<div class="col-xs-2"></div>--}}
                </div>
            </form>

            <a href="{{ url('/auth/login') }}">Войти</a><br>

        </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
@endsection
