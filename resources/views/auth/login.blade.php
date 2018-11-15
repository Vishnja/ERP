@extends('templates.auth')

@section('page-title')
    Войти
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
            <p class="login-box-msg">Войти</p>

            <form method="post" action="{{ url('/auth/login') }}">
                {!! csrf_field() !!}

                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Пароль" name="password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="row">
                    <div class="col-xs-8">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" name="remember"> <span class="icheckbox_caption">Запомнить меня</span>
                            </label>
                        </div>
                    </div><!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Войти</button>
                    </div><!-- /.col -->
                </div>
            </form>

            <a href="{{ url('/password/email') }}">Забыли пароль?</a><br>

        </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
@endsection