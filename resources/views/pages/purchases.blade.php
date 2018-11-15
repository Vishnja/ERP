@extends('templates.page')

@include('pages.scripts.purchases')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Закупки
        <div class="btn-group table-data-filter">
            <button type="button" class="btn btn-default" data-type="purchases">Закупки</button>
            <button type="button" class="btn btn-default" data-type="returns">Возврат</button>
            <button type="button" class="btn btn-default active" data-type="all">Все</button>
        </div>
        &nbsp;&nbsp;&nbsp;

        <a href="#" class="btn btn-default btn-flat btn-purchase-create">
            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;Создать Закупку
        </a>
        <a href="#" class="btn btn-default btn-flat btn-return-create">
            <i class="fa fa-refresh"></i>&nbsp;&nbsp;Создать Возврат
        </a>
        &nbsp;&nbsp;&nbsp;

        <button class="btn btn-default disabled" data-action="paid-cash" title="Оплачен"><i class="fa fa-money"></i></button>
        <button class="btn btn-default disabled" data-action="paid-cashless" title="Оплачен б/н"><i class="fa fa-credit-card"></i></button>
        <button class="btn btn-default disabled" data-action="shipped" title="Доставлен"><i class="fa fa-truck"></i></button>
        <button class="btn btn-default disabled" data-action="cancelled" title="Отменен"><i class="fa fa-minus-circle"></i></button>
        @can('access', 'delete-records')
            <button class="btn btn-default disabled" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>
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
                    <table id="purchases-table" class="table table-bordered table-striped"
                           data-page-length='{{ config('items_per_page') }}'
                           style="width: 100%;"
                            >
                        <thead>{!! $purchasesTableHead !!}</thead>
                        <tbody>{!! $purchasesTableBody !!}</tbody>
                        <tfoot>{!! $purchasesTableHead !!}</tfoot>
                    </table>
                </div>
            </div><!-- /.box -->

        </div><!-- /.col -->
    </div><!-- /.row -->
</section>
<!-- Main content : end -->

<!-- Purchase Modal -->
<div id="purchase" class="modal fade b-modal" tabindex="-1" data-width="900" data-keyboard="false" style="display: none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="purchase-modal-title">Создать Закупку</h4>
    </div>

    <div class="modal-body">
        <ul class="nav nav-tabs" id="tabContent">
            <li class="active"><a href="#purchase-tab" data-toggle="tab">Закупка</a></li>

            @can('access', 'watch-history')
                <li><a href="#purchase-history-tab" data-toggle="tab" class="purchase-history-tab">История</a></li>
            @endcan
        </ul>

        <!-- .tab-content -->
        <div class="tab-content">

            <!-- purchase tab -->
            <div class="tab-pane active" id="purchase-tab">

                <form id="purchase-form">
                    <div class="row">

                        <div class="form-group col-md-12">
                            <label for="purchase-type">Тип</label>
                            {!! Form::select( 'purchase-type',
                                              App\Models\PurchaseRepository::$purchaseTypes,
                                              1,
                                              [ 'class' => 'form-control',
                                                'id' => 'purchase-type' ] ) !!}
                        </div>

                        <div class="form-group col-md-12">
                            <h4>Товары</h4>

                            <table id="purchase-products-table" class="table table-bordered table-striped"
                                   cellpadding="0" cellspacing="0" border="0"
                                   style="width: 100%;"
                            >
                                <thead>{!! $productsTableHead !!}</thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th colspan="6"><select id="purchase-product"></select></th>
                                </tr>
                                </tfoot>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="col-md-12">
                            <label>Итого:</label><br>
                            Итого: <span class="products-grand-total">0.00</span> грн.
                        </div>
                    </div>

                    <!-- hidden fields -->
                    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                    <input type="hidden" id="purchase-id" value="0" />
                </form>

            </div>
            <!-- purchase tab : end -->

            <!-- history tab -->
            <div class="tab-pane history-type-tab" id="purchase-history-tab"></div>
            <!-- history tab : end -->

        </div>
        <!-- .tab-content : end -->
    </div>

    <div class="modal-footer">
        <div class="pull-left modal-footer-actions">
            <button class="btn btn-success disabled" data-action="paid" title="Оплачен"><i class="fa fa-money"></i></button>
            <button class="btn btn-default disabled" data-action="shipped" title="Доставлен"><i class="fa fa-truck"></i></button>
            <button class="btn btn-warning disabled" data-action="cancelled" title="Отменен"><i class="fa fa-minus-circle"></i></button>
            @can('access', 'delete-records')
                <button class="btn btn-danger disabled" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>
            @endcan
        </div>

        <div class="pull-left">
            <button type="button" class="btn btn-default btn-create-return-from-purchase disabled">
                <i class="fa fa-refresh"></i>&nbsp;&nbsp;Создать Возврат
            </button>
        </div>

        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="btn-purchase-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Purchase Modal : end -->

<!-- Return Modal -->
<div id="return" class="modal fade b-modal" tabindex="-1" data-width="900" data-keyboard="false" style="display: none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="return-modal-title">Создать Возврат</h4>
    </div>

    <div class="modal-body">
        <ul class="nav nav-tabs" id="tabContent">
            <li class="active"><a href="#return-tab" data-toggle="tab">Возврат</a></li>

            @can('access', 'watch-history')
                <li><a href="#return-history-tab" data-toggle="tab" class="return-history-tab">История</a></li>
            @endcan
        </ul>


        <!-- .tab-content -->
        <div class="tab-content">

            <!-- return tab -->
            <div class="tab-pane active" id="return-tab">

                <form id="return-form">
                    <div class="row">

                        <div class="form-group col-md-12">
                            <label for="purchase-type">Тип</label>
                            {!! Form::select( 'purchase-type',
                                              App\Models\PurchaseRepository::$returnTypes,
                                              1,
                                              [ 'class' => 'form-control',
                                                'id' => 'return-type' ] ) !!}
                        </div>

                        <div class="form-group col-md-12">
                            <h4>Товары</h4>

                            <table id="return-products-table" class="table table-bordered table-striped"
                                   cellpadding="0" cellspacing="0" border="0"
                                   style="width: 100%;"
                            >
                                <thead>{!! $productsTableHead !!}</thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th colspan="6"><select id="return-product"></select></th>
                                </tr>
                                </tfoot>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="col-md-12">
                            <label>Итого:</label><br>
                            Итого: <span class="products-grand-total">0.00</span> грн.
                        </div>
                    </div>

                    <!-- hidden fields -->
                    <input type="hidden" id="return-id" value="0" />
                </form>

            </div>
            <!-- return tab : end -->

            <!-- history tab -->
            <div class="tab-pane history-type-tab" id="return-history-tab"></div>
            <!-- history tab : end -->

        </div>
        <!-- .tab-content : end -->
    </div>

    <div class="modal-footer">
        <div class="pull-left modal-footer-actions">
            <button class="btn btn-success disabled" data-action="paid" title="Оплачен"><i class="fa fa-money"></i></button>
            <button class="btn btn-default disabled" data-action="shipped" title="Доставлен"><i class="fa fa-truck"></i></button>
            <button class="btn btn-warning disabled" data-action="cancelled" title="Отменен"><i class="fa fa-minus-circle"></i></button>
            @can('access', 'delete-records')
                <button class="btn btn-danger disabled" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>
            @endcan
        </div>

        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="btn-return-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Return Modal : end -->

@endsection