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
                    { "orderable": false, "targets": [0, 8] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('buyers.search') }}'
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
                $("#item-id").val(0);

                $("#item .alert").remove();
                $('#item-modal-title').text('Создать Покупателя');

                $("#item-name, #item-surname, #item-phone, #item-email, #item-city, #item-address, #item-np-number").val('');

                $("#item button[data-action]").addClass('disabled');
                $('#btn-item-save').text('Сохранить');

                // modal
                $('#item').modal({ keyboard: false });
                return false;
            });

            /*** Edit Item ***/
            $(document).on('click', '.btn-item-edit', function(){
                // prepare UI
                $("#item .alert").remove();
                $('#item-modal-title').text('Редактировать Покупателя');

                $("#item-name, #item-surname, #item-phone, #item-email, #item-city, #item-address, #item-np-number").val('');

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
                    url: '{{ URL::to('/') }}/buyers/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#item-id").val(item.id);
                            $("#item-name").val(item.name);
                            $("#item-surname").val(item.surname);
                            $("#item-phone").val(item.phone);
                            $("#item-email").val(item.email);
                            $("#item-city").val(item.city);
                            $("#item-address").val(item.address);
                            $("#item-np-number").val(item.NP_number);

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
                    surname: $("#item-surname").val(),
                    phone: $("#item-phone").val(),
                    email: $("#item-email").val(),
                    city: $("#item-city").val(),
                    address: $("#item-address").val(),
                    NP_number: $("#item-np-number").val()
                };

                // 'loader' on
                $('#item').modal('loading');

                // validation
                validateAndSaveItem('buyer', itemData, '#item', function() {

                    $.ajax({
                        url: action == 'store' ?
                                '{{ URL::to('/') }}/buyers' :                // store
                                '{{ URL::to('/') }}/buyers/' + itemId,       // update
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
            initActionsHandler('#item', itemsTable, "{{ URL::to('/') }}/buyers/action");

        });
    </script>
@endsection