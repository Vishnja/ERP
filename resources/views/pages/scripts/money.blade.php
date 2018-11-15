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

                order: [[2, 'desc']],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 8] },
                    { "width": '1em', "targets": 0 },
                    // hidden columns for generating styles for row
                    // 'income' / 'expense', 'status'
                    { "visible": false, "targets": [9, 10] },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('money.search') }}',
                    "data": function (data) {
                        data['record_type_filter'] = $('.record-type-filter button.active').data('type');
                        data['money_type_filter'] = $('.money-type-filter button.active').data('type');
                        data['status_filter'] = $('.status-filter button.active').data('type');
                        return data;
                    }
                },

                "createdRow": function ( row, data, index ) {
                    switch (data[9]) {
                        case 'income': $(row).addClass('money-type-income'); break;
                        case 'expense': $(row).addClass('money-type-expense'); break;
                    }
                    switch (data[10]) {
                        case 'cancelled': $(row).addClass('money-status-cancelled'); break;
                    }
                },

                "deferLoading": '{{ $total }}',
            });

            // redraw on collapse / expand menu
            $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
                itemsTable.columns.adjust().draw();
            });

            // filter by money type
            $('.table-data-filter button').on('click', function(){
                $(this).parent().find('button').removeClass("active");
                $(this).addClass("active");

                itemsTable.ajax.reload();
            });

            /**
             * Select2 and select "type" selects
             */

            // Contractor Type
            $("#item-contractor-type").change(function(){
                select2clear("#item-contractor");
                $("#item-contractor").select2("destroy").select2(contractorParams);
            });

            // Contractor
            var contractorParams = {
                language: "ru",
                ajax: {
                    url: function() {
                        return $("#item-contractor-type").val() == 'App\\Models\\Buyer' ?
                               "{{ route('buyers.selectSearch') }}" :
                               "{{ route('suppliers.selectSearch') }}"
                    },
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

            $("#item-contractor").select2(contractorParams);

            // Base Type
            $("#item-base-type").change(function(){
                select2clear("#item-base");
                $("#item-base").select2("destroy").select2(baseParams);
            });

            // Base
            var baseParams = {
                language: "ru",
                ajax: {
                    url: function() {
                        return $("#item-base-type").val() == 'App\\Models\\Order' ?
                               "{{ route('orders.selectSearch') }}" :
                               "{{ route('purchases.selectSearch') }}"
                    },
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

            $("#item-base").select2(baseParams);

            /**
             * Modals
             */

            // switch between record types
            function refreshOperationalFieldsVisibility(){
                if ($('#item-record-type').val() == 'operational') {
                    $('.operational-field').css('display', 'block');
                } else {
                    $('.operational-field').css('display', 'none');
                }
            }

            $('#item-record-type').on('change', function(){
                refreshOperationalFieldsVisibility();
            });

            /*** Create Item ***/
            $(".btn-item-create").on('click', function(){
                // prepare UI
                activateTab('purchase-tab');
                $("#item-id").val(0);

                $('#item-modal-title').text('Создать Документ');
                $("#item .alert").remove();

                $("#item-record-type").val('operational');
                refreshOperationalFieldsVisibility();

                $("#item-money-type").val('account');
                $("#item-income-expense-item").val('1');

                select2clear('#item-contractor');
                $("#item-contractor-type").val('App\\Models\\Buyer');
                select2clear('#item-base');
                $("#item-base-type").val('App\\Models\\Order');

                $("#item-comment").val('');
                $("#item-total").val('');

                $("#item button[data-action]").removeClass('crossed').addClass('disabled');
                $('#btn-item-save').text('Сохранить');

                // modal
                $('#item').modal({ keyboard: false });
                return false;
            });

            /*** Edit Item ***/
            $(document).on('click', '.btn-item-edit', function(){
                // prepare UI
                $("#item .alert").remove();

                $('#item-modal-title').text('Редактировать Документ');
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
                    url: '{{ URL::to('/') }}/money/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#item-id").val(item.id);
                            $('#item .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );

                            $("#item-record-type").val(item.record_type);
                            refreshOperationalFieldsVisibility();
                            $("#item-money-type").val(item.money_type);
                            $("#item-income-expense-item").val(item.income_expense_item_id);

                            $("#item-contractor-type").val(item.contractor_type);
                            select2load('#item-contractor', item.contractor);
                            $("#item-base-type").val(item.base_type);
                            select2load('#item-base', item.base);

                            $("#item-comment").val(item.comment);
                            $("#item-total").val(item.total);

                            $('#item .modal-footer-actions').html(item.action_buttons_html);

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
            $("#item-total").numeric({ negative: false });

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
                    serial: $("#item-serial").val(),
                    record_type: $("#item-record-type").val(),
                    money_type: $("#item-money-type").val(),
                    income_expense_item_id: $("#item-income-expense-item").val(),
                    contractor_id: $("#item-contractor").val(),
                    contractor_type: $("#item-contractor-type").val(),
                    base_id: $("#item-base").val(),
                    base_type: $("#item-base-type").val(),
                    comment: $("#item-comment").val(),
                    total: $("#item-total").val(),
                };

                // 'loader' on
                $('#item').modal('loading');

                validateAndSaveItem('money', itemData, '#item', function() {

                    $.ajax({
                        url: action == 'store' ?
                                '{{ URL::to('/') }}/money' :                // store
                                '{{ URL::to('/') }}/money/' + itemId,       // update
                        type: 'POST',
                        data: itemData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // post item id on 'store'
                                if (action == 'store') {
                                    var item = response.item;

                                    $("#item-id").val(item.id);
                                    $('#item .modal-title').text( item.serial + ' - (' + item.status_name + ') - ' + item.created_at_formatted );
                                    $("#return button[data-action]").removeClass('disabled');
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

            initActionsHandler('#item', itemsTable, "{{ URL::to('/') }}/money/action");

        });
    </script>
@endsection