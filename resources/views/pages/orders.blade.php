@extends('templates.page')

@include('pages.scripts.orders')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Заказы
        &nbsp;&nbsp;&nbsp;

        <div class="btn-group table-data-filter">
            <button class="btn btn-default active" data-status="open">Открытые</button>
            <button class="btn btn-default" data-status="fulfilled">Выполненные</button>
            <button class="btn btn-default" data-status="cancelled">Отмененные</button>
            <button class="btn btn-default" data-status="all">Все</button>
        </div>
        &nbsp;&nbsp;&nbsp;

        <button class="btn btn-default btn-flat btn-order-create">
            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;Создать
        </button>
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
                <div class="box-header">
                    <!--<h3 class="box-title">Data Table With Full Features</h3>-->
                </div>

                <div class="box-body">
                    <table id="orders-table" class="table table-bordered table-striped"
                           data-page-length='{{ config('items_per_page') }}'
                           style="width: 100%;"
                    >
                        <thead>{!! $ordersTableHead !!}</thead>
                        <tbody>{!! $ordersTableBody !!}</tbody>
                        <tfoot>{!! $ordersTableHead !!}</tfoot>
                    </table>
                </div>
            </div><!-- /.box -->

        </div><!-- /.col -->
    </div><!-- /.row -->
</section>
<!-- Main content : end -->

<!-- Order Modal -->
<div id="order" class="modal fade b-modal" tabindex="-1" data-width="900" data-keyboard="false" style="display: none;" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="order-modal-title">Создать Заказ</h4>
    </div>

    <div class="modal-body">
        <ul class="nav nav-tabs" id="tabContent">
            <li class="active"><a href="#order-tab" data-toggle="tab">Заказ</a></li>
            <li><a href="#order-products-balances-tab" class="order-products-balances-tab" data-toggle="tab">Остатки</a></li>
            @can('access', 'watch-history')
                <li><a href="#order-history-tab" data-toggle="tab" class="order-history-tab">История</a></li>
            @endcan
        </ul>

        <!-- .tab-content -->
        <div class="tab-content">
            <!-- order tab -->
            <div class="tab-pane active" id="order-tab">

                <form id="order-form">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="order-buyer">Покупатель</label>&nbsp;

                            <button class="btn btn-default btn-flat btn-buyer-create btn-xs">
                                <i class="fa fa-plus-circle"></i>
                            </button>

                            <button class="btn btn-default btn-flat btn-buyer-edit btn-xs">
                                <i class="fa fa-pencil"></i>
                            </button>

                            <select id="order-buyer"></select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="order-payment-method">Оплата</label>
                            {!! Form::select(
                                    '',
                                    App\Models\PaymentMethod::lists('name', 'id'),
                                    null, ['class' => 'form-control', 'id' => 'order-payment-method'] )
                            !!}
                        </div>

                        <div class="form-group col-md-4">
                            <label for="order-shipping-method">Доставка</label>
                            {!! Form::select(
                                    '', App\Models\ShippingMethod::lists('name', 'id'),
                                    null, ['class' => 'form-control', 'id' => 'order-shipping-method'] )
                            !!}
                        </div>

                        <div class="form-group col-md-4">
                            <label for="order-shipping-cost">Стоимость Доставки</label>
                            <input type="text" class="form-control" id="order-shipping-cost">
                        </div>

                        <div class="form-group col-md-4">
                            <label for="order-NP-city-store">Город Склад НП</label>
                            <input type="text" class="form-control" id="order-NP-city-store">
                        </div>

                        <div class="form-group col-md-12">
                            <h4>Товары</h4>

                            <table id="products-table" class="table table-bordered table-striped"
                                   cellpadding="0" cellspacing="0" border="0"
                                   style="width: 100%;"
                            >
                                <thead>{!! $orderProductsTableHead !!}</thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th colspan="6"><select id="order-product"></select></th>
                                    </tr>
                                </tfoot>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="form-group">
                            <h4  class="col-md-12">Итоговая Информация</h4>
                            <div class="col-md-3">
                                <label for="order-discount">Скидка</label>

                                <div class="input-group discount-group">
                                    <input type="text" class="form-control" id="order-discount-value">

                                    <select name="order-discount-type" id="order-discount-type" class="form-control">
                                        <option value="currency">грн.</option>
                                        <option value="percent">%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <label>Итого:</label><br>
                                Товары: <span class="order-products-total">0.00</span> грн. &nbsp;&nbsp;&nbsp;
                                Скидка: <span class="order-discount-cost">0.00</span> грн. &nbsp;&nbsp;&nbsp;
                                Доставка: <span class="order-shipping-cost">0.00</span> грн. <br/>
                                Итого: <span class="order-grand-total">0.00</span> грн.
                            </div>
                        </div>
                    </div>

                    <!-- hidden fields -->
                    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                    <input type="hidden" id="order-id" value="0" />
                </form>

            </div>
            <!-- order tab : end -->

            <!-- balances tab -->
            <div class="tab-pane history-type-tab" id="order-products-balances-tab"></div>
            <!-- balances tab : end -->

            <!-- history tab -->
            <div class="tab-pane history-type-tab" id="order-history-tab"></div>
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
        <button type="button" id="btn-order-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Order Modal : end-->

<!-- Buyer Modal -->
<div id="buyer" class="modal fade b-modal" tabindex="-1" data-width="760" data-keyboard="false" style="display: none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="buyer-modal-title">Создать Покупателя</h4>
    </div>

    <div class="modal-body">
        <form id="buyer-form">
            <div class="form-group">
                <label for="buyer-name">Имя</label>
                <input type="text" class="form-control" id="buyer-name" name="buyer-name">
            </div>

            <div class="form-group">
                <label for="buyer-surname">Фамилия</label>
                <input type="text" class="form-control" id="buyer-surname" name="buyer-surname">
            </div>

            <div class="form-group">
                <label for="buyer-phone">Телефон</label>
                <input type="text" class="form-control" id="buyer-phone" name="buyer-phone">
            </div>

            <div class="form-group">
                <label for="buyer-email">Email</label>
                <input type="text" class="form-control" id="buyer-email" name="buyer-email">
            </div>

            <div class="form-group">
                <label for="buyer-city">Город</label>
                <input type="text" class="form-control" id="buyer-city" name="buyer-city">
            </div>

            <div class="form-group">
                <label for="buyer-address">Адрес</label>
                <input type="text" class="form-control" id="buyer-address" name="buyer-address">
            </div>

            <div class="form-group">
                <label for="buyer-np-number">Номер отеделения Новой Почты</label>
                <input type="text" class="form-control" id="buyer-np-number" name="buyer-np-number">
            </div>

            <input type="hidden" id="buyer-id" value="0" />
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="btn-buyer-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Buyer Modal : end -->

<!-- Product Modal -->
<div id="product" class="modal fade b-modal" tabindex="-1" data-width="760" data-keyboard="false" style="display: none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="product-modal-title">Редактировать Продукт</h4>
    </div>

    <div class="modal-body">
        <form id="product-form">
            <div class="form-group">
                <label for="product-name">Наименование</label>
                <input type="text" class="form-control" id="product-name" name="product-name">
            </div>

            <div class="form-group">
                <label for="product-vendor-code">Артикул</label>
                <input type="text" class="form-control" id="product-vendor-code" name="product-vendor-code">
            </div>

            <div class="form-group">
                <label for="product-description">Характеристика</label>
                <input type="text" class="form-control" id="product-description" name="product-description">
            </div>

            <div class="form-group">
                <label for="product-price">Цена</label>
                <input type="text" class="form-control" id="product-price" name="product-price">
            </div>

            <div class="form-group">
                <label for="product-quantity-receipt">В собственности</label>
                <input type="text" class="form-control" id="product-quantity-receipt" disabled="disabled">
            </div>

            <div class="form-group">
                <label for="product-quantity-realization">На реализации</label>
                <input type="text" class="form-control" id="product-quantity-realization" disabled="disabled">
            </div>

            <input type="hidden" id="product-id" value="0" />
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" id="btn-product-save" class="btn btn-primary">Сохранить</button>
    </div>
</div>
<!-- Product Modal : end -->

@endsection                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     