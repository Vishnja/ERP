@section('head-scripts')
@endsection

@section('body-scripts')
    <script>
        $(function () {
            /**
             * DataTables
             */

            /*** Purchases ***/
            var purchasesTable = $('#purchases-table').DataTable({
                "language": {
                    "url": "./plugins/datatables/localization/Russian.json"
                },
                "dom": '<"top"<"col-sm-6"l><"col-sm-6"f><"col-sm-12"i>>' +
                'rt' +
                '<"bottom"<"col-sm-6"i><"col-sm-6"p>>',

                order: [[2, 'desc']],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 8] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('purchases.search') }}',
                    "data": function (data) {
                        data['filter'] = $('.table-data-filter button.active').data('type');
                        return data;
                    }
                },

                // same statuses as in orders
                "createdRow": function ( row, data, index ) {
                    switch (data[7]) {
                        case 'Открытый': $(row).addClass('purchase-status-open'); break;
                        case 'Отменен': $(row).addClass('purchase-status-cancelled'); break;
                        case 'Выполнен': $(row).addClass('purchase-status-fulfilled'); break;
                    }
                },

                "deferLoading": '{{ $total }}',
            });

            // redraw on collapse / expand menu
            $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
                purchasesTable.columns.adjust().draw();
            });

            // filter by order status
            $('.table-data-filter button').on('click', function(){
                $('.table-data-filter button').removeClass("active");
                $(this).addClass("active");

                purchasesTable.ajax.reload();
            });

            /*** Products ***/

            // both tables have same params
            var productsDatatableParams = {
                "language": { "url": "./plugins/datatables/localization/Russian.json" },

                "dom": 'rt',

                "ordering": false,
                "columnDefs": [
                    { "width": '1em', "targets": 0 },
                    { "width": '30%', "targets": 1 },
                ],

                "scrollY":        "200px",
                "scrollCollapse": true,
                "paging":         false,
            };

            var purchaseProductsTable = $('#purchase-products-table').DataTable(productsDatatableParams);
            var returnProductsTable = $('#return-products-table').DataTable(productsDatatableParams);

            // column width fix
            $('#purchase').on('shown', function(){
                purchaseProductsTable.columns.adjust().draw(false);
            });

            $('#return').on('shown', function(){
                returnProductsTable.columns.adjust().draw(false);
            });

            // delete row
            $(document).on('click', '#purchase-products-table button.remove-row', function(e){
                purchaseProductsTable.row( $(this).parents('tr') ).remove().draw();
                renderPurchaseProductsTotals();
                return false;
            });

            $(document).on('click', '#return-products-table button.remove-row', function(e){
                returnProductsTable.row( $(this).parents('tr') ).remove().draw();
                renderReturnProductsTotals();
                return false;
            });

            // product total spinner
            $(document).on('change', '#purchase input.product-num-spinner', function(){
                var $productTotal = $(this).parents('tr').find('.product-total');
                var productPrice = $(this).parents('tr').find('.product-price').text();
                var total = $(this).val() * productPrice;
                $productTotal.text( total.toFixed(2) );
                renderPurchaseProductsTotals();
            });

            $(document).on('change', '#return input.product-num-spinner', function(){
                var $productTotal = $(this).parents('tr').find('.product-total');
                var productPrice = $(this).parents('tr').find('.product-price').text();
                var total = $(this).val() * productPrice;
                $productTotal.text( total.toFixed(2) );
                renderReturnProductsTotals();
            });

            // products totals
            function renderPurchaseProductsTotals() {
                // total (sum)
                var productsTotal = 0;

                $("#purchase .product-total").each(function() {
                    productsTotal += parseFloat( $(this).text() );
                });

                $("#purchase .products-grand-total").text(productsTotal.toFixed(2));
            }

            function renderReturnProductsTotals() {
                // total (sum)
                var productsTotal = 0;

                $("#return .product-total").each(function() {
                    productsTotal += parseFloat( $(this).text() );
                });

                $("#return .products-grand-total").text(productsTotal.toFixed(2));
            }

            /**
             * Select2
             */

            // Product
            var productSelectParams = {
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
            };

            $("#purchase-product").select2(productSelectParams);
            $("#return-product").select2(productSelectParams);

            // Product 'select' custom event handler
            $('#purchase-product').on("select2:select", function(e) {
                // selected option data
                addPurchaseProductRows($("#purchase-product").select2('data')[0], '#purchase-products-table');
                renderPurchaseProductsTotals();

                // datatables bug, table is misaligned
                setTimeout(function(){
                    $('#purchase-products-table').DataTable().columns.adjust().draw(false);

                    // and scroll to bottom
                    var $scrollBody = $(purchaseProductsTable.table().node()).parent();
                    $scrollBody.scrollTop($scrollBody.get(0).scrollHeight);
                }, 10);

                select2clear("#purchase-product");
            });

            $('#return-product').on("select2:select", function(e) {
                // selected option data
                addPurchaseProductRows($("#return-product").select2('data')[0], '#return-products-table');
                renderReturnProductsTotals();

                // datatables bug, table is misaligned
                setTimeout(function(){
                    $('#return-products-table').DataTable().columns.adjust().draw(false);

                    // and scroll to bottom
                    var $scrollBody = $(returnProductsTable.table().node()).parent();
                    $scrollBody.scrollTop($scrollBody.get(0).scrollHeight);
                }, 10);

                select2clear("#return-product");
            });

            /**
             * Modals
             */

            /*** Create Purchase ***/
            $(".btn-purchase-create").on('click', function(){
                // prepare UI
                activateTab('purchase-tab');
                $("#purchase-id").val(0);

                $('#purchase-modal-title').text('Создать Закупку');
                $("#purchase .alert").remove();

                $("#purchase-type").val('receipt');
                purchaseProductsTable.clear().draw(false);
                renderPurchaseProductsTotals();

                $("#purchase button[data-action]").removeClass('crossed').addClass('disabled');
                $('.btn-create-return-from-purchase').addClass('disabled');
                $('#btn-purchase-save').text('Сохранить');

                // modal
                $('#purchase').modal({ keyboard: false });
                return false;
            });

            /*** Edit Purchase ***/
            $(document).on('click', '.btn-purchase-edit', function(){
                // prepare UI
                activateTab('purchase-tab');
                $('#purchase-modal-title').text('Редактировать Закупку');
                $("#purchase .alert").remove();

                $("#purchase-status").removeAttr('disabled');
                $("#purchase button[data-action]").removeAttr('disabled');
                purchaseProductsTable.clear();

                $('.btn-create-return-from-purchase').addClass('disabled');
                $('#btn-purchase-save').text('Обновить');

                // load data
                $('body').modalmanager('loading');

                var itemId = $(this).closest('tr').find('.cb-select-item').data('item-id');
                var data = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),
                    item_id: itemId
                };

                $.ajax({
                    url: '{{ URL::to('/') }}/purchases/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#purchase-id").val(item.id);

                            $('#purchase .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );

                            $("#purchase-type").val(item.type);
                            $("#purchase-status").val(item.status_id);
                            // products table
                            addPurchaseProductRows(item.products, '#purchase-products-table');
                            renderPurchaseProductsTotals();
                            $('#purchase .modal-footer-actions').html(item.action_buttons_html);

                            // modal
                            $('#purchase').modal({ keyboard: false });
                        }
                        else if (response.status == 'error') {
                            alert('Произошла ошибка при загрузке данных!');
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при загрузке данных!');
                    }
                });

                return false;
            });

            /*** Create Return ***/
            function createReturn(products, type){
                // default param value
                type = typeof type !== 'undefined' ? type : 'receipt';

                // if products are empty array return
                if (products && products.constructor === Array && products.length === 0) return;

                // prepare UI
                activateTab('return-tab');
                $("#return-id").val(0);

                $('#return-modal-title').text('Создать Возврат');
                $("#return .alert").remove();
                $("#return-type").val(type);
                returnProductsTable.clear().draw(false);
                if (products) addPurchaseProductRows(products, '#return-products-table');
                renderReturnProductsTotals();

                $("#return button[data-action]").removeClass('crossed').addClass('disabled');
                $('#btn-return-save').text('Сохранить');

                // modal
                $('#return').modal({ keyboard: false });
                return false;
            }

            $(".btn-return-create").on('click', function(){
                createReturn();
            });

            /*** Edit Return ***/
            $(document).on('click', '.btn-return-edit', function(){
                // prepare UI
                activateTab('return-tab');
                $('#return-modal-title').text('Редактировать Возврат');
                $("#return .alert").remove();

                returnProductsTable.clear();

                $('#btn-return-save').text('Обновить');

                // load data
                $('body').modalmanager('loading');

                var itemId = $(this).closest('tr').find('.cb-select-item').data('item-id');
                var data = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),
                    item_id: itemId
                };

                $.ajax({
                    url: '{{ URL::to('/') }}/purchases/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#return-id").val(item.id);

                            $('#return .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );

                            // products table
                            addPurchaseProductRows(item.products, '#return-products-table');
                            renderReturnProductsTotals();
                            $('#return .modal-footer-actions').html(item.action_buttons_html);

                            // modal
                            $('#return').modal({ keyboard: false });
                        }
                        else if (response.status == 'error') {
                            alert('Произошла ошибка при загрузке данных!');
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при загрузке данных!');
                    }
                });

                return false;
            });

            /**
             * Create Return from Purchase
             */

            // activate/deactivate button when products are selected
            $(document).on('change', '#purchase-products-table_wrapper .cb-select-item, #purchase-products-table_wrapper .cb-select-all', function() {
                if ($('#purchase-products-table .cb-select-item:checked').length) {
                    $('.btn-create-return-from-purchase').removeClass('disabled');
                } else {
                    $('.btn-create-return-from-purchase').addClass('disabled');
                }
            });

            // create return
            $(".btn-create-return-from-purchase").on('click', function(){
                if ($(this).hasClass('disabled')) return false;
                createReturn( purchaseProductSelectedRows(), $('#purchase-type').val() );
            });

            /**
             * Create Return from Order (via get params)
             */
            var purchaseProducts = {!! $purchaseProducts !!};
            if (purchaseProducts) createReturn( purchaseProducts );

            /**
             * Save (Ajax)
             */

            // Purchase
            $("#btn-purchase-save").click(function(){
                var products = [];

                $("#purchase-products-table tbody tr").each(function(){
                    var product = {
                        product_supplier_price_id: $(this).find(".cb-select-item").data('item-id'),
                        product_name: unescapeHtml( $(this).find(".product-name").data('product-name-original') ),
                        purchase_price: $(this).find(".product-price").text(),
                        supplier_name: unescapeHtml( $(this).find(".supplier-name").attr('title') ),
                        quantity: $(this).find(".product-num-spinner").val()
                    };
                    // empty table has one row
                    if (product.purchase_price) products.push(product);
                });

                var itemId = $("#purchase-id").val();
                var action = itemId == 0 ? 'store' : 'update';

                var itemData = {
                    _method: action == 'store' ? "POST" : "PUT",
                    _token: $('#csrf-token').val(),

                    id: itemId,
                    purchase_or_return: 'purchase',
                    type: $("#purchase-type").val(),
                    total: $("#purchase .products-grand-total").text(),

                    products: products
                };

                // 'loader' on
                $('#purchase').modal('loading');

                validateAndSaveItem('purchase', itemData, '#purchase', function() {

                    $.ajax({
                        url: action == 'store' ?
                             '{{ URL::to('/') }}/purchases' :                // store
                             '{{ URL::to('/') }}/purchases/' + itemId,       // update
                        type: 'POST',
                        data: itemData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // post item id on 'store'
                                if (action == 'store') {
                                    var item = response.item;
                                    $("#purchase-id").val(item.id);
                                    $('#purchase .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );
                                    $("#purchase button[data-action]").removeClass('disabled');
                                    $('#btn-purchase-save').text('Обновить');
                                }

                                // refresh Datatable
                                purchasesTable.draw(false);

                                // Message
                                $("#purchase .alert").remove();
                                $('#purchase').modal('loading').find('.modal-body').prepend(
                                        '<div class="alert alert-info fade in">' +
                                        'Сохранено!<button type="button" class="close" data-dismiss="alert">&times;</button>' +
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

            // Return
            $("#btn-return-save").click(function(){
                var products = [];

                $("#return-products-table tbody tr").each(function(){
                    var product = {
                        product_supplier_price_id: $(this).find(".cb-select-item").data('item-id'),
                        product_name: unescapeHtml( $(this).find(".product-name").data('product-name-original') ),
                        purchase_price: $(this).find(".product-price").text(),
                        supplier_name: unescapeHtml( $(this).find(".supplier-name").attr('title') ),
                        quantity: $(this).find(".product-num-spinner").val()
                    };
                    // empty table has one row
                    if (product.purchase_price) products.push(product);
                });

                var itemId = $("#return-id").val();
                var action = itemId == 0 ? 'store' : 'update';

                var itemData = {
                    _method: action == 'store' ? "POST" : "PUT",
                    _token: $('#csrf-token').val(),

                    id: itemId,
                    purchase_or_return: 'return',
                    type: $("#return-type").val(),
                    total: $("#return .products-grand-total").text(),

                    products: products
                };

                // 'loader' on
                $('#return').modal('loading');

                validateAndSaveItem('return', itemData, '#return', function() {

                    $.ajax({
                        url: action == 'store' ?
                             '{{ URL::to('/') }}/purchases' :                // store
                             '{{ URL::to('/') }}/purchases/' + itemId,       // update
                        type: 'POST',
                        data: itemData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // post item id on 'store'
                                if (action == 'store') {
                                    var item = response.item;
                                    $("#return-id").val(response.item.id);
                                    $('#return .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );
                                    $("#return button[data-action]").removeClass('disabled');
                                    $('#btn-order-save').text('Обновить');
                                }

                                // refresh Datatable
                                purchasesTable.draw(false);

                                // Message
                                $("#return .alert").remove();
                                $('#return').modal('loading').find('.modal-body').prepend(
                                        '<div class="alert alert-info fade in">' +
                                        'Сохранено!<button type="button" class="close" data-dismiss="alert">&times;</button>' +
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
             * History tab
             */

            // Purchase
            $('.purchase-history-tab').click(function(){
                $("#purchase .alert").remove();
                $('#purchase-history-tab').html('');

                var purchaseId = $("#purchase-id").val();
                if (purchaseId == 0) return;

                $('#purchase').modal('loading');

                $.ajax({
                    url: '{{ URL::to('/') }}/purchases/history/' + purchaseId,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#purchase-history-tab').html(response.html);
                            $('#purchase').modal('loading');
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

            // Purchase
            $('.return-history-tab').click(function(){
                $("#return .alert").remove();
                $('#return-history-tab').html('');

                var returnId = $("#return-id").val();
                if (returnId == 0) return;

                $('#return').modal('loading');

                $.ajax({
                    url: '{{ URL::to('/') }}/purchases/history/' + returnId,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#return-history-tab').html(response.html);
                            $('#return').modal('loading');
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

            // enable/disable mass action buttons
            initMassActionButtonsStateRefresh('#purchases-table');

            // action handler
            $(document).on('click', '*[data-action]', function(){

                if ($(this).hasClass('disabled')) return false;

                var action = $(this).data('action');
                var itemsIds;
                var $parent = $(this).parent();

                // items
                var parentIsModal = false;

                if ($parent.is("td")) {
                    itemsIds = $(this).closest('tr').find('.cb-select-item').data('item-id');
                }
                else if ($parent.is("div.modal-footer-actions")) {
                    parentIsModal = true;
                    itemsIds = $(this).closest('#purchase').length ?
                               $('#purchase-id').val() : $('#return-id').val();
                }
                else if ($parent.is("h1")) {
                    itemsIds = tableSelectedItemsIds('#purchases-table');
                }

                // delete confirmation
                if (action == 'delete') {
                    if (! confirm("Подтвердите удаление!")) return false;
                }

                var itemsData = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),

                    action: action,
                    ids: itemsIds
                };

                $.ajax({
                    url: "{{ URL::to('/') }}/purchases/action",
                    type: 'GET',
                    data: itemsData,
                    success: function(response) {
                        if (response.status == 'success') {
                            if (parentIsModal) {
                                // close modal
                                if (action == 'delete') {
                                    $('#purchase').modal('hide');
                                    $('#return').modal('hide');
                                }
                                // refresh action buttons if parent is modal
                                else {
                                    $parent.html(response.action_buttons_html);
                                }
                            }

                            // refresh Datatable
                            purchasesTable.draw(false);
                        }
                        else {
                            alert('Произошла ошибка при совершении действия!');
                        }
                    },
                    error: function(){
                        alert('Произошла ошибка при совершении действия!');
                    }
                });

                return false;
            });

        });
    </script>
@endsection