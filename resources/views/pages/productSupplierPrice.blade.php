@extends('templates.page')

@include('pages.scripts.productSupplierPrice')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Товары Постащиков
        &nbsp;&nbsp;&nbsp;

        <a href="#item" class="btn btn-default btn-flat btn-item-create" >
            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;Создать
        </a>
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
        <h4 class="modal-title" id="item-modal-title">Создать Цену</h4>
    </div>

    <div class="modal-body">
        <form id="item-form">

            <div class="form-group">
                <label for="item-name">Наименование</label>
                <select id="item-product"></select>
            </div>

            <div class="form-group">
                <label for="item-vendor-code">Поставщик</label>
                <select id="item-supplier"></select>
            </div>

            <div class="form-group">
                <label for="item-purchase-price">Закупочная Цена</label>
                <input type="text" class="form-control" id="item-purchase-price">
            </div>

            <div class="form-group">
                <label for="item-price">Цена</label>
                <input type="text" class="form-control" id="item-price" disabled="disabled">
            </div>

            <!-- hidden fields -->
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
            <input type="hidden" id="item-id" value="0" />
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="btn-item-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Item Modal : end -->

@endsection                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     