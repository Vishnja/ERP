@extends('templates.page')

@include('pages.scripts.buyers')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Покупатели
        &nbsp;&nbsp;&nbsp;

        <a href="#item" class="btn btn-default btn-flat btn-item-create" >
            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;Создать
        </a>&nbsp;&nbsp;&nbsp;

        @can('access', 'delete-records')
            <button class="btn btn-default btn-action disabled" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>
        @endcan
    </h1>
</section>
<!-- Content Header (Page header) : end -->

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <div class="box">
                <div class="box-header"></div>

                <div class="box-body">
                    <table id="items-table" class="table table-bordered table-striped"
                           data-page-length='{{ config('items_per_page') }}'
                           style="width: 100%;"
                    >
                        <thead>{!! $tableHead !!}</thead>
                        <tbody>{!! $tableBody !!}</tbody>
                        <tfoot>{!! $tableHead !!}</tfoot>
                    </table>
                </div>
            </div><!-- /.box -->

        </div><!-- /.col -->
    </div><!-- /.row -->
</section>
<!-- Main content : end -->

<!-- Item Modal -->
<div id="item" class="modal fade b-modal" tabindex="-1" data-width="760" data-keyboard="false" style="display: none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="item-modal-title">Создать Покупателя</h4>
    </div>

    <div class="modal-body">
        <form id="item-form">

            <div class="form-group">
                <label for="item-name">Имя</label>
                <input type="text" class="form-control" id="item-name">
            </div>

            <div class="form-group">
                <label for="item-surname">Фамилия</label>
                <input type="text" class="form-control" id="item-surname">
            </div>

            <div class="form-group">
                <label for="item-phone">Телефон</label>
                <input type="text" class="form-control" id="item-phone">
            </div>

            <div class="form-group">
                <label for="item-email">Email</label>
                <input type="text" class="form-control" id="item-email">
            </div>

            <div class="form-group">
                <label for="item-city">Город</label>
                <input type="text" class="form-control" id="item-city">
            </div>

            <div class="form-group">
                <label for="item-address">Адрес</label>
                <input type="text" class="form-control" id="item-address">
            </div>

            <div class="form-group">
                <label for="item-np-number">Номер отеделения Новой Почты</label>
                <input type="text" class="form-control" id="item-np-number">
            </div>

            <!-- hidden fields -->
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
            <input type="hidden" id="item-id" value="0" />
        </form>
    </div>

    <div class="modal-footer">
        <div class="pull-left modal-footer-actions">
            @can('access', 'delete-records')
                <button class="btn btn-danger disabled" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>
            @endcan
        </div>

        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="btn-item-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Item Modal : end -->

@endsection                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     