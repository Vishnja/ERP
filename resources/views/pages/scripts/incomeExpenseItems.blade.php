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
                "dom": '<"top"<"col-sm-6"l><"col-sm-6"><"col-sm-12"i>>' +
                       'rt' +
                       '<"bottom"<"col-sm-6"i><"col-sm-6"p>>',

                order: [[1, 'desc']],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 3] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('incomeExpenseItems.search') }}'
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
                $("#item .alert").remove();
                $("#item-name").val('');
                $("#item-type").val('income');
                $("#item-id").val(0);
                $('#item-modal-title').text('Создать Статью Прихода / Расхода');
                $('#btn-item-save').text('Сохранить');

                // modal
                $('#item').modal({ keyboard: false });
                return false;
            });

            /*** Edit Item ***/
            $(document).on('click', '.btn-item-edit', function(){
                // prepare UI
                $("#item .alert").remove();
                $("#item-name").val('');
                $('#item-modal-title').text('Редактировать Статью Прихода / Расхода');
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
                    url: '{{ URL::to('/') }}/incomeExpenseItems/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#item-id").val(item.id);
                            $("#item-name").val(item.name);
                            $("#item-type").val(item.type);

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
                    type: $("#item-type").val(),
                };

                // 'loader' on
                $('#item').modal('loading');

                // validation
                validateAndSaveItem('incomeExpenseItem', itemData, '#item', function() {

                    $.ajax({
                        url: action == 'store' ?
                                '{{ URL::to('/') }}/incomeExpenseItems' :                // store
                                '{{ URL::to('/') }}/incomeExpenseItems/' + itemId,       // update
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
                    url: "{{ URL::to('/') }}/incomeExpenseItems/" + itemId,
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