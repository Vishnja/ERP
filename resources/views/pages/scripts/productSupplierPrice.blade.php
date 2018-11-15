@section('head-scripts')
@endsection

@section('body-scripts')
    <script>
        $(function () {
            /**
             * DataTables
             */

            /*** items ***/
            var itemsTable = $('#items-table').DataTable({
                "language": {
                    "url": "./plugins/datatables/localization/Russian.json"
                },
                "dom": '<"top"<"col-sm-6"l><"col-sm-6"f><"col-sm-12"i>>' +
                       'rt' +
                       '<"bottom"<"col-sm-6"i><"col-sm-6"p>>',

                order: [[1, 'asc']],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 5] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('productSupplierPrice.search') }}'
                },

                "deferLoading": '{{ $total }}',
            });

                // redraw on collapse / expand menu
            $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
                itemsTable.columns.adjust().draw();
            });

            /**
             * Select2
             */

            // Product
            $("#item-product").select2({
                language: "ru",
                ajax: {
                    url: "{{ route('products.selectSearch') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,     // search term
                            page: params.page
                        };
                    }
                },
                minimumInputLength: 1
            });

            // Product 'select' custom event handler
            $('#item-product').on("select2:select", function(e) {
                // selected option data
                $("#item-price").val($("#item-product").select2('data')[0]['price']);
            });

            // Supplier
            $("#item-supplier").select2({
                language: "ru",
                ajax: {
                    url: "{{ route('suppliers.selectSearch') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,     // search term
                            page: params.page
                        };
                    }
                },
                minimumInputLength: 1
            });


            /**
             * Modals
             */

            /*** Create Item ***/
            $(".btn-item-create").on('click', function(){
                // prepare UI
                $("#item .alert").remove();
                select2clear('#item-product');
                select2clear('#item-supplier');
                $("#item-purchase-price, #item-price").val('');
                $("#item-id").val(0);
                $('#item-modal-title').text('Создать Цену');
                $('#btn-item-save').text('Сохранить');

                // modal
                $('#item').modal({ keyboard: false });
                return false;
            });

            /*** Edit Item ***/
            $(document).on('click', '.btn-item-edit', function(){
                // prepare UI
                $("#item .alert").remove();
                select2clear('#item-product');
                select2clear('#item-supplier');
                $("#item-purchase-price, #item-price").val('');
                $('#item-modal-title').text('Редактировать Цену');
                $('#btn-item-save').text('Обновить');

                // load data
                $('body').modalmanager('loading');

                var itemId = $(this).closest('tr').find('.cb-select-item').data('item-id');
                var data = {
                    _method: "GET",
                    _token: $('#csrf-token').val(),
                    item_id: itemId
                };

                $.ajax({
                    url: '{{ URL::to('/') }}/productSupplierPrice/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#item-id").val(item.id);
                            select2load('#item-product', item.product);
                            select2load('#item-supplier', item.supplier);
                            $("#item-purchase-price").val(item.purchase_price);
                            $("#item-price").val(item.price);

                            // modal
                            $('#item').modal({ keyboard: false });
                        }
                        else if (response.status == 'error') {
                            alert('Произошла ошибка при загрузке данных!');
                            console.log(response);
                        }
                    }
                });

                return false;
            });

            /**
             * Numeric validator
             */
            $("#item-purchase-price").numeric({ negative: false });

            /**
             * Save (Ajax)
             */
            $("#btn-item-save").click(function(){
                var itemId = $("#item-id").val();
                var action = itemId == 0 ? 'store' : 'update';

                var itemData = {
                    _method: action == 'store' ? "POST" : "PUT",
                    _token: $('#csrf-token').val(),

                    id: itemId,
                    product_id: $("#item-product").val(),
                    supplier_id: $("#item-supplier").val(),
                    purchase_price: $("#item-purchase-price").val(),
                };

                // 'loader' on
                $('#item').modal('loading');

                validateAndSaveItem('productSupplierPrice', itemData, '#item', function() {

                    $.ajax({
                        url: action == 'store' ?
                                '{{ URL::to('/') }}/productSupplierPrice' :                // store
                                '{{ URL::to('/') }}/productSupplierPrice/' + itemId,       // update
                        type: 'POST',
                        data: itemData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // post item id on 'store'
                                if (action == 'store') $("#item-id").val(response.item.id);

                                // refresh Datatable
                                itemsTable.draw(false);

                                // Message
                                $("#item .alert").remove();
                                $('#item').modal('loading').find('.modal-body').prepend(
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
             * Actions
             */
            $(document).on('click', '.btn-item-delete', function(){
                var itemId = $(this).closest('tr').find('.cb-select-item').data('item-id');

                var itemData = {
                    _method: "DELETE",
                    _token: $('#csrf-token').val(),
                    id: itemId
                };

                $.ajax({
                    url: "{{ URL::to('/') }}/productSupplierPrice/" + itemId,
                    type: 'POST',
                    data: itemData,
                    success: function(response) {
                        if (response.status == 'success') {
                            // refresh Datatable
                            itemsTable.draw(false);

                            alert('Удалено!');
                        }
                        else {
                            alert('Произошла ошибка при удалении!');
                            console.log(response);
                        }
                    }
                });

                return false;
            });

        });
    </script>
@endsection