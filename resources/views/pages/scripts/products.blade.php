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
                    { "orderable": false, "targets": [0, 7] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('products.search') }}'
                },

                "deferLoading": '{{ $total }}',
            });

                // redraw on collapse / expand menu
            $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
                itemsTable.columns.adjust().draw();
            });

            /**
             * Modals
             */

            /*** Create Item ***/
            $(".btn-item-create").on('click', function(){
                // prepare UI
                    // item tab
                activateTab('item-tab');
                    // clean other tabs
                $('#history-tab').html('');

                $("#item .alert").remove();
                $('#item-modal-title').text('Создать Товар');

                $("#item-id").val(0);
                $("#item-name, #item-description, #item-vendor-code, #item-price").val('');
                $("#item-quantity-receipt, #item-quantity-realization").val('0');

                $("#item button[data-action]").addClass('disabled');
                $('#btn-item-save').text('Сохранить');

                // modal
                $('#item').modal({ keyboard: false });
                return false;
            });

            /*** Edit Item ***/
            $(document).on('click', '.btn-item-edit', function(){
                // prepare UI
                    // item tab
                activateTab('item-tab');
                    // clean other tabs
                $('#history-tab').html('');

                $("#item .alert").remove();
                $('#item-modal-title').text('Редактировать Товар');

                $("#item-name, #item-description, #item-vendor-code, #item-price, #item-quantity-receipt, #item-quantity-realization").val('');
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
                    url: '{{ URL::to('/') }}/products/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#item-id").val(item.id);
                            $("#item-name").val(item.name);
                            $("#item-description").val(item.description);
                            $("#item-vendor-code").val(item.vendor_code);
                            $("#item-price").val(item.price);
                            $("#item-quantity-receipt").val(item.quantity_receipt);
                            $("#item-quantity-realization").val(item.quantity_realization);

                            $("#item button[data-action]").removeClass('disabled');

                            // modal
                            $('#item').modal({ keyboard: false });
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
             * Numeric validator
             */
            $("#item-price").numeric({ negative: false });

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
                    name: $("#item-name").val(),
                    description: $("#item-description").val(),
                    vendor_code: $("#item-vendor-code").val(),
                    // important, because integer without dot gives isDirty true
                    // against the same value id DB, etc.
                    price: parseFloat( $("#item-price").val() ).toFixed(2),
                };

                // 'loader' on
                $('#item').modal('loading');

                validateAndSaveItem('product', itemData, '#item', function() {

                    $.ajax({
                        url: action == 'store' ?
                                '{{ URL::to('/') }}/products' :                // store
                                '{{ URL::to('/') }}/products/' + itemId,       // update
                        type: 'POST',
                        data: itemData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // after 'store'
                                if (action == 'store') {
                                    $("#item-id").val(response.item.id);
                                    $("#item button[data-action]").removeClass('disabled');
                                    $('#btn-item-save').text('Обновить');
                                }

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
            initActionsHandler('#item', itemsTable, "{{ URL::to('/') }}/products/action");

            /**
             * History Tab
             */
            $('.history-tab').click(function(){
                $("#item .alert").remove();
                $('#history-tab').html('');

                var itemId = $("#item-id").val();
                if (itemId == 0) return;

                $('#item').modal('loading');

                $.ajax({
                    url: '{{ URL::to('/') }}/products/history/' + itemId,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#item').modal('loading');
                            $('#history-tab').html(response.html);
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

        });
    </script>
@endsection