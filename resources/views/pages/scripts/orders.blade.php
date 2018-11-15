@section('head-scripts')
@endsection

@section('body-scripts')
    <script>
        $(function () {
            /**
             * DataTables
             */

            /*** Orders ***/
            var ordersTable = $('#orders-table').DataTable({
                "language": {
                    "url": "./plugins/datatables/localization/Russian.json"
                },
                "dom": '<"top"<"col-sm-6"l><"col-sm-6"f><"col-sm-12"i>>' +
                       'rt' +
                       '<"bottom"<"col-sm-6"i><"col-sm-6"p>>',

                order: [[2, 'desc']],
                "columnDefs": [
                    // 0 and 9 columns aren't orderable
                    { "orderable": false, "targets": [0, 9] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('orders.search') }}',
                    "data": function (data) {
                        data['filter'] = $('.table-data-filter button.active').data('status');
                        return data;
                    }
                },

                "createdRow": function ( row, data, index ) {
                    switch (data[8]) {
                        case 'Открытый': $(row).addClass('order-status-open'); break;
                        //case 'Новый': $(row).addClass('order-status-new'); break;
                        //case 'Отгружен': $(row).addClass('order-status-shipped'); break;
                        //case 'Оплачен': $(row).addClass('order-status-paid'); break;
                        //case 'Удален': $(row).addClass('order-status-deleted'); break;
                        //case 'В обработке': $(row).addClass('order-status-processing'); break;
                        //case 'Возврат': $(row).addClass('order-status-return'); break;
                        case 'Отменен': $(row).addClass('order-status-cancelled'); break;
                        case 'Выполнен': $(row).addClass('order-status-fulfilled'); break;
                    }
                },

                "deferLoading": '{{ $total }}',
            });

            // redraw on collapse / expand menu
            $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
                ordersTable.columns.adjust().draw();
            });

            // filter by order status
            $('.table-data-filter button').on('click', function(){
                $('.table-data-filter button').removeClass("active");
                $(this).addClass("active");

                ordersTable.ajax.reload();
            });

            /*** Products ***/
            var productsTable = $('#products-table').DataTable({
                "language": {
                    "url": "./plugins/datatables/localization/Russian.json"
                },

                "dom": 'rt',

                "ordering": false,
                "columnDefs": [
                    { "width": '1em', "targets": 0 },
                    { "width": '35%', "targets": 1 },
                ],

                "scrollY":        "200px",
                "scrollCollapse": true,
                "paging":         false,
            });

            // column width fix
             $('#order').on('shown', function () {
                $('#products-table').DataTable().columns.adjust().draw(false);
            });

            /**
             * Order totals.
             *
             * Only parameter that is used in calculation and can changed independently
             * (without explicit order save) is shipping_cost.
             * While editing order it is taken first from DB.
             */
            function renderOrderTotals(shippingCostParam) {
                // total (sum)
                var productsTotal = 0;

                $(".product-total").each(function() {
                    productsTotal += parseFloat( $(this).text() );
                });

                $(".order-products-total").text(productsTotal.toFixed(2));

                // discount
                var discountVal = $('#order-discount-value').val() ?
                                  parseFloat( $('#order-discount-value').val() ) :
                                  0;
                var discount = $('#order-discount-type').val() == 'currency' ?
                               discountVal :
                               discountVal * productsTotal / 100;

                $(".order-discount-cost").text(discount.toFixed(2));

                // shipping
                var shippingCostFloat;

                if (typeof shippingCostParam === 'undefined') {
                    shippingCostFloat = 0;

                    switch ($("#order-shipping-method").val()) {
                        // courier
                        case '1':
                            if (productsTotal <= 350) shippingCostFloat = 30;
                            break;
                        // Nova Poshta
                        case '3':
                            // todo: get price from api;
                            shippingCostFloat = 20;
                            break;
                    }
                } else {
                    // string or empty string
                    shippingCostFloat = shippingCostParam !== '' ? parseFloat(shippingCostParam) : 0;
                }

                    // Problem with editing #order-shipping-cost if float is passed.
                    // In this case shipping cost is text parameter
                    // passed to this function from handler
                var shippingCostForTopField = typeof shippingCostParam === 'undefined' ?
                                              shippingCostFloat.toFixed(2) : shippingCostParam;

                $("#order-shipping-cost").val(shippingCostForTopField);
                $(".order-shipping-cost").text(shippingCostFloat.toFixed(2));

                // grand total
                var grandTotal = productsTotal - discount + shippingCostFloat;
                $(".order-grand-total").text(grandTotal.toFixed(2));
            }

            // Update product total on spinner or price change, render totals
            // that is .product-num-spinner or .product-price
            function updateProductTotalAndRenderOrderTotals(that) {
                var $tr = $(that).parents('tr');

                var $productTotal = $tr.find('.product-total');
                var productPrice = $tr.find('.product-price').val();
                var productQuantity = $tr.find('.product-num-spinner').val();
                var productTotal = productQuantity * productPrice;

                $productTotal.text( productTotal.toFixed(2) );
                renderOrderTotals();
            }

            // delete row
            $(document).on('click', '#products-table button.remove-row', function(e){
                //if (! confirm("Подтвердите удаление")) return false;

                productsTable.row( $(this).parents('tr') ).remove().draw();
                renderOrderTotals();
                return false;
            });

            // product total spinner and price
            $(document).on('change', 'input.product-num-spinner', function(){
                updateProductTotalAndRenderOrderTotals(this);
            });

            $(document).on('keyup', 'input.product-price', function(){
                updateProductTotalAndRenderOrderTotals(this);
            });

            // custom shipping cost
            $("#order-shipping-cost").on('keyup', function(){ renderOrderTotals( $(this).val() ); });

            // discount
            $("#order-discount-value").on('keyup', function(){ renderOrderTotals(); });
            $("#order-discount-type").on('change', function(){ renderOrderTotals(); });

            // shipping
            $("#order-shipping-method").on('change', function(){ renderOrderTotals(); });

            /**
             * Select2
             */

            // Buyer
            $("#order-buyer").select2({
                language: "ru",
                ajax: {
                    url: "{{ route('buyers.selectSearch') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    }
                },
                minimumInputLength: 1
            });

            // Buyer event handler
            $('#order-buyer').on("select2:select", function(e) {
                $(".btn-buyer-edit").removeAttr('disabled');
            });

            // Product
            $("#order-product").select2({
                language: "ru",
                ajax: {
                    url: "{{ route('productSupplierPrice.selectSearch') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                },
                minimumInputLength: 1
            });

            // Product select event handler
            $('#order-product').on("select2:select", function(e) {
                // selected option data
                addOrderProductRows($("#order-product").select2('data')[0]);
                renderOrderTotals();

                // datatables bug, table is misaligned
                setTimeout(function(){
                    $('#products-table').DataTable().columns.adjust().draw(false);

                    // and scroll to bottom
                    var $scrollBody = $(productsTable.table().node()).parent();
                    $scrollBody.scrollTop($scrollBody.get(0).scrollHeight);
                }, 10);

                select2clear("#order-product");
            });

            /**
             * Modals
             */

            /*** Order Create ***/
            $(".btn-order-create").on('click', function(){
                // prepare UI
                    // order tab
                activateTab('order-tab');
                    // clean other tabs
                $('#history-tab, #balances-tab').html('');
                    // title
                $('#order .modal-title').text('Создать Заказ');
                    // alert messages
                $('#order .alert').remove();

                $("#order-form #order-id").val(0);
                select2clear('#order-buyer');
                $(".btn-buyer-edit").attr('disabled', 'disabled');
                $("#order-form #order-payment-method").val(1);
                $("#order-form #order-shipping-method").val(1);
                $("#order-form #order-shipping-cost").val('');
                // #order-form #order-order-NP-city-store is calculated in renderOrderTotals()

                    // products table
                productsTable.clear().draw(false);
                    // totals
                $("#order-discount-value").val('');
                $("#order-discount-type").val('currency');
                renderOrderTotals();

                    // buttons
                $("#order button[data-action]").removeClass('crossed').addClass('disabled');
                $('#btn-order-save').text('Сохранить');

                // modal
                $('#order').modal({ keyboard: false });
            });

            // misaligned table bug with 'new' order
            $('#order').on('shown', function() {
                productsTable.draw();
            });

            /*** Order Edit ***/
            $(document).on('click', '.btn-order-edit', function(){
                // prepare UI
                activateTab('order-tab');
                $('#history-tab, #balances-tab').html('');
                $('#order .alert').remove();

                $(".btn-buyer-edit").removeAttr('disabled');
                productsTable.clear();

                $('#btn-order-save').text('Обновить');

                // load data
                $('body').modalmanager('loading');

                var itemId = $(this).closest('tr').find('.cb-select-item').data('item-id');
                var data = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),
                    item_id: itemId
                };

                $.ajax({
                    url: '{{ URL::to('/') }}/orders/' + itemId,
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#order #order-id").val(itemId);
                            $('#order .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );

                            select2load('#order-buyer', item.order_buyer);
                            $("#order-form #order-payment-method").val(item.payment_method_id);
                            $("#order-form #order-shipping-method").val(item.shipping_method_id);
                            // #order-shipping-cost is set in renderOrderTotals()
                            $("#order-form #order-NP-city-store").val(item.NP_city_store);

                            addOrderProductRows(item.products);

                            $("#order-discount-value").val(item.discount_value);
                            $("#order-discount-type").val(item.discount_type);
                            renderOrderTotals(item.shipping_cost);

                            $('#order .modal-footer-actions').html(item.action_buttons_html);

                            // modal
                            $('#order').modal({ keyboard: false });
                        }
                        else {
                            alert('Произошла ошибка при загрузке данных заказа!');
                        }
                    },
                    error: function(){
                        alert('Произошла ошибка при загрузке данных заказа!');
                    }
                });

                return false;
            });

            /*** Buyer Create ***/
            $("#order .btn-buyer-create").on('click', function(){
                // prepare UI
                $("#buyer .alert").remove();
                $("#buyer-name, #buyer-surname, #buyer-phone, #buyer-email, #buyer-city, #buyer-address, #buyer-np-number").val('');
                $("#buyer-id").val(0);
                $('#buyer-modal-title').text('Создать Покупателя');
                $('#btn-buyer-save').text('Сохранить');

                // modal
                $('#buyer').modal({ keyboard: false });

                return false;
            });

            /*** Buyer Edit ***/
            $(".btn-buyer-edit").on('click', function(){
                if ($(".btn-buyer-edit").is("[disabled]")) return false;

                // prepare UI
                $("#buyer .alert").remove();
                $("#buyer-name, #buyer-surname, #buyer-phone, #buyer-email, #buyer-city, #buyer-address, #buyer-np-number").val('');
                $('#buyer-modal-title').text('Редактировать Покупателя');
                $('#btn-buyer-save').text('Обновить');

                // load data
                $('body').modalmanager('loading');

                var itemId = $("#order-buyer").val();
                var data = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),
                    item_id: itemId
                };

                $.ajax({
                    url: '{{ URL::to('/') }}/buyers/' + itemId,
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#buyer-id").val(item.id);
                            $("#buyer-name").val(item.name);
                            $("#buyer-surname").val(item.surname);
                            $("#buyer-phone").val(item.phone);
                            $("#buyer-email").val(item.email);
                            $("#buyer-city").val(item.city);
                            $("#buyer-address").val(item.address);
                            $("#buyer-np-number").val(item.NP_number);

                            // modal
                            $('#buyer').modal({ keyboard: false });
                        }
                        else if (response.status == 'error') {
                            alert('Произошла ошибка при загрузке данных покупателя!');
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при загрузке данных покупателя!');
                    }
                });

                return false;
            });

            /*** Product Edit ***/
            var $editedProductRow;

            $(document).on('click', '.action-product-edit', function() {
                // set $editedProductRow for updating table row after save
                $editedProductRow = $(this).closest('tr');

                // prepare UI
                $("#product .alert").remove();
                $("#product-name, #product-vendor-code, #product-description, #product-price, #product-quantity-receipt, #product-quantity-realization").val('');
                $('#btn-product-save').text('Обновить');

                // load data
                $('body').modalmanager('loading');

                var itemId = $(this).data('product-id');
                var data = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),
                    item_id: itemId
                };

                $.ajax({
                    url: '{{ URL::to('/') }}/products/' + itemId,
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#product-id").val(item.id);
                            $("#product-name").val(item.name);
                            $("#product-vendor-code").val(item.vendor_code);
                            $("#product-description").val(item.description);
                            $("#product-price").val(item.price);
                            $("#product-quantity-receipt").val(item.quantity_receipt);
                            $("#product-quantity-realization").val(item.quantity_realization);

                            // modal
                            $('#product').modal({ keyboard: false });
                        }
                        else if (response.status == 'error') {
                            alert('Произошла ошибка при загрузке данных товара!');
                        }
                    },
                    error: function(){
                        alert('Произошла ошибка при загрузке данных товара!');
                    }
                });

                return false;
            });

            /**
             * Create Return from Purchase
             */

            // activate/deactivate button when products are selected
            $(document).on('change', '#products-table_wrapper .cb-select-item, #products-table_wrapper .cb-select-all', function() {
                if ($('#products-table .cb-select-item:checked').length) {
                    $('.btn-create-return-from-purchase').removeClass('disabled');
                } else {
                    $('.btn-create-return-from-purchase').addClass('disabled');
                }
            });

            // create return
            $(".btn-create-return-from-purchase").on('click', function(){
                if ($(this).hasClass('disabled')) return false;

                var params = JSON.stringify( orderProductSelectedRows() );
                window.open('purchases?return_products=' + params);
            });

            /**
             * Numeric validator
             */
            $("#order-discount-value, #order-shipping-cost").numeric({ negative: false });
            $("#buyer-np-number").numeric( { decimal: false, negative: false } );

            /**
             * Save (Ajax)
             */

            // Order Save
            $("#btn-order-save").click(function(){
                var products = [];

                // empty table has one row
                if (! $('#products-table tbody .dataTables_empty').length) {
                    $("#products-table tbody tr").each(function(){
                        var product = {
                            product_supplier_price_id: $(this).find(".product-name").data('product-supplier-price-id'),
                            product_name: unescapeHtml( $(this).find(".product-name").data('product-name-original') ),
                            // important, because integer without dot gives isDirty true
                            // against the same value id DB, etc.
                            price: parseFloat( $(this).find(".product-price").val() ).toFixed(2),
                            quantity: $(this).find(".product-num-spinner").val()
                        };
                        // empty table has one row
                        if (product.price) products.push(product);
                    });
                }

                var orderId = $("#order-id").val();
                var action = orderId == 0 ? 'store' : 'update';

                var orderData = {
                    _method: action == 'store' ? "POST" : "PUT",
                    _token: $('#csrf-token').val(),

                    id: orderId,
                    buyer_id: $("#order-buyer").val(),
                    payment_method_id: $("#order-payment-method").val(),
                    shipping_method_id: $("#order-shipping-method").val(),
                    NP_city_store: $("#order-NP-city-store").val(),
                    discount_value: $("#order-discount-value").val() == '' ?
                                    0 :
                                    parseFloat( $("#order-discount-value").val() ).toFixed(2),
                    discount_type: $("#order-discount-type").val(),
                    shipping_cost: parseFloat( $("#order-shipping-cost").val() ).toFixed(2),
                    grand_total: $(".order-grand-total").text(),

                    products: products
                };

                // 'loader' on
                $('#order').modal('loading');

                validateAndSaveItem('order', orderData, '#order', function() {

                    $.ajax({
                        url: action == 'store' ?
                             '{{ URL::to('/') }}/orders' :                   // store
                             '{{ URL::to('/') }}/orders/' + orderId,         // update
                        type: 'POST',
                        data: orderData,
                        success: function (response) {
                            if (response.status == 'success') {
                                // changes after 'store' action
                                if (action == 'store') {
                                    var item = response.item;
                                    $("#order #order-id").val(item.id);
                                    $('#order .modal-title').text(item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted);
                                    $("#order button[data-action]").removeClass('disabled');
                                    $('#btn-order-save').text('Обновить');
                                }

                                // refresh Datatable
                                ordersTable.draw(false);

                                // Message
                                $("#order .alert").remove();
                                $('#order').modal('loading').find('.modal-body').prepend(
                                        '<div class="alert alert-info fade in">' +
                                        'Сохранено! <button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                        '</div>');
                            }
                            else {
                                alert('Произошла ошибка при сохранении!');
                            }
                        },
                        error: function () {
                            alert('Произошла ошибка при сохранении!');
                        }
                    });

                });

            });

            // Buyer
            $("#btn-buyer-save").click(function(){
                var buyerId = $("#buyer-id").val();
                var action = buyerId == 0 ? 'store' : 'update';

                var buyerData = {
                    _method: action == 'store' ? "POST" : "PUT",
                    _token: $('#csrf-token').val(),

                    id: buyerId,
                    name: $("#buyer-name").val(),
                    surname: $("#buyer-surname").val(),
                    phone: $("#buyer-phone").val(),
                    email: $("#buyer-email").val(),
                    city: $("#buyer-city").val(),
                    address: $("#buyer-address").val(),
                    NP_number: $("#buyer-np-number").val()
                };

                // 'loader' on
                $('#buyer').modal('loading');

                validateAndSaveItem('buyer', buyerData, '#buyer', function() {

                    $.ajax({
                        url: action == 'store' ?
                             '{{ URL::to('/') }}/buyers' :               // store
                             '{{ URL::to('/') }}/buyers/' + buyerId,     // update
                        type: 'POST',
                        data: buyerData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // set buyer id on 'store'
                                if (action == 'store') $("#buyer-id").val(response.item.id);

                                // select2
                                select2load('#order-buyer', response.item);
                                $(".btn-buyer-edit").removeAttr('disabled');

                                // Message
                                $("#buyer .alert").remove();
                                $('#buyer').modal('loading').find('.modal-body').prepend(
                                    '<div class="alert alert-info fade in">' +
                                        'Сохранено! Данные покупателя обновлены в заказе! <button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                    '</div>');

                            }
                            else {
                                alert('Произошла ошибка при сохранении!');
                            }
                        },
                        error: function() {
                            alert('Произошла ошибка при сохранении!');
                        }
                    });

                });

            });

            // Product
            $("#btn-product-save").click(function(){
                var productId = $("#product-id").val();
                var action = 'update';

                var productData = {
                    _method: action == 'store' ? "POST" : "PUT",
                    _token: $('#csrf-token').val(),

                    id: productId,
                    name: $("#product-name").val(),
                    description: $("#product-description").val(),
                    vendor_code: $("#product-vendor-code").val(),
                    // important, because integer without dot gives isDirty true
                    // against the same value id DB, etc.
                    price: parseFloat( $("#product-price").val() ).toFixed(2),
                };

                // 'loader' on
                $('#product').modal('loading');

                validateAndSaveItem('product', productData, '#product', function(){

                    $.ajax({
                        url: action == 'store' ?
                             '{{ URL::to('/') }}/products' :                 // store
                             '{{ URL::to('/') }}/products/' + productId,     // update
                        type: 'POST',
                        data: productData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // no id loading on create, product is only edited

                                // update product info in orders table
                                $editedProductRow.find('.product-name').text( excerpt($("#product-name").val(), 40) );
                                $editedProductRow.find('.product-name').attr('title', $("#product-name").val());
                                $editedProductRow.find('.product-name').data('product-name-original', $("#product-name").val());

                                $editedProductRow.find('td:nth-child(3)').text( excerpt($("#product-vendor-code").val(), 11) );
                                $editedProductRow.find('td:nth-child(4)').text( excerpt($("#product-description").val(), 14) );
                                $editedProductRow.find('.product-price').val( $("#product-price").val() );
                                updateProductTotalAndRenderOrderTotals($editedProductRow.find('.product-price'));

                                // Message
                                $("#product .alert").remove();
                                $('#product').modal('loading').find('.modal-body').prepend(
                                    '<div class="alert alert-info fade in">' +
                                        'Обновлено в товаре и заказе!<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                    '</div>');
                            }
                            else {
                                alert('Произошла ошибка при сохранении!');
                            }
                        },
                        error: function() {
                            alert('Произошла ошибка при сохранении!');
                        }
                    });

                });

            });

            /**
             * Tabs
             */

            // Order products balances tab
            $('.order-products-balances-tab').click(function(){
                $("#order .alert").remove();
                $('#order-products-balances-tab').html('');

                var productsIds = [];
                $("#order #products-table tbody tr").each(function(){
                    var productId = $(this).find(".product-name").data('product-id');
                    // empty table has one row
                    if (productId) productsIds.push(productId);
                });

                if (productsIds.length == 0) {
                    alert('В заказе нет ни одного товара!');
                    return false;
                }

                var productsData = {
                    products: productsIds
                };

                $('#order').modal('loading');

                $.ajax({
                    url: '{{ URL::to('/') }}/orders/productsBalances',
                    type: 'GET',
                    data: productsData,
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#order').modal('loading');
                            $('#order-products-balances-tab').html(response.html);
                        }
                        else {
                            alert('Произошла ошибка при загрузке данных!');
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при загрузке данных!');
                    }
                });
            });

            // Order history tab
            $('.order-history-tab').click(function(){
                $("#order .alert").remove();
                $('#order-history-tab').html('');

                var orderId = $("#order-id").val();
                if (orderId == 0) return;

                $('#order').modal('loading');

                $.ajax({
                    url: '{{ URL::to('/') }}/orders/history/' + orderId,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#order').modal('loading');
                            $('#order-history-tab').html(response.html);
                        }
                        else {
                            alert('Произошла ошибка при загрузке данных!');
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при загрузке данных!');
                    }
                });
            });

            /**
             * Actions
             */

            initActionsHandler('#order', ordersTable, "{{ URL::to('/') }}/orders/action");

        });
    </script>
@endsection