@section('head-scripts')
    <!-- Dropzone -->
    <link rel="stylesheet" href="{{ asset('plugins/dropzone/basic.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/dropzone/dropzone.css') }}">
@endsection

@section('body-scripts')
    <!-- Dropzone -->
    <script src="{{ asset('plugins/dropzone/dropzone.js') }}"></script>

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

                order: [[1, 'asc']],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 5] },
                    { "width": '1em', "targets": 0 },
                ],

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '{{ route('users.search') }}'
                },

                "deferLoading": '{{ $total }}',
            });

                // redraw on collapse / expand menu
            $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
                itemsTable.columns.adjust().draw();
            });

            /**
             * Dropzone
             */
            Dropzone.autoDiscover = false;

            var thisDropZone;

            $("#photo-dropzone").dropzone({
                url: '{{ route('images.store') }}',
                maxFiles: 1,
                maxFilesize: 10,
                acceptedFiles: "image/jpeg,image/png,image/gif",
                thumbnailWidth: 120,
                thumbnailHeight: 120,
                addRemoveLinks: true,

                // Translations
                dictDefaultMessage: "Перетяните файлы сюда для загрузки.",
                dictFallbackMessage: "Ваш браузер не поддерживает перетягивание для загрузки файлов.",
                dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
                dictFileTooBig: "Файл слишком большой (@{{filesize}}MiB). Максимально допустимый размер файла: @{{maxFilesize}}MiB.",
                dictInvalidFileType: "Вы не можете загружать файлы данного типа.",
                dictResponseError: "Сервер ответил @{{statusCode}} кодом.",
                dictCancelUpload: "Отменить загрузку",
                dictCancelUploadConfirmation: "Вы уверены, что хотите отменить загрузку?",
                dictRemoveFile: "Удалить файл",
                dictRemoveFileConfirmation: null,
                dictMaxFilesExceeded: "Вы не можете загружать больше файлов.",

                init: function() {
                    thisDropZone = this;

                    this.on("addedfile", function() {
                        if (this.files[1] != null) {
                            this.removeFile(this.files[0]);
                        }
                    });

                    this.on("removedfile", function() {
                        $('#item-photo').val('');
                    });
                },

                sending: function (file, xhr, formData) {
                    formData.append("_token", $('#csrf-token').val());
                },

                success: function (file, response) {
                    if (response.success) {
                       $("#item-photo").val(response.filename);
                    } else {
                        file.previewElement.classList.add("dz-error");
                        file.previewElement.getElementsByClassName('dz-error-message')[0].innerHTML =
                                'Произошла ошибка при загрузке файла!';
                    }
                },
            });

            function dropzoneLoadImage(image){
                var mockFile = { name: image.name, size: image.size };

                thisDropZone.emit("addedfile", mockFile);
                thisDropZone.createThumbnailFromUrl(mockFile, image.path);
                thisDropZone.emit("complete", mockFile);
                thisDropZone.files.push(mockFile);

                $('#item-photo').val(image.filename);
            }

            /**
             * Modals
             */

            /*** Create Item ***/
            $(".btn-item-create").on('click', function(){
                // prepare UI
                $("#item-id").val(0);

                $("#item .alert").remove();
                $('#item-modal-title').text('Создать Пользователя');

                $("#item-name, #item-surname, #item-email, #item-password, #item-photo").val('');
                $("#item-role").val('1');
                Dropzone.forElement("#photo-dropzone").removeAllFiles(true);

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
                $('#item-modal-title').text('Редактировать Пользователя');

                $("#item-password, #item-photo").val('');
                $("#item button[data-action]").addClass('disabled');
                Dropzone.forElement("#photo-dropzone").removeAllFiles(true);

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
                    url: '{{ URL::to('/') }}/users/' + itemId,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.status == 'success') {
                            var item = response.item;

                            // load fields
                            $("#item-id").val(item.id);
                            $("#item-name").val(item.name);
                            $("#item-surname").val(item.surname);
                            $("#item-email").val(item.email);
                            $("#item-role").val(item.role_id);
                            if (item.hasOwnProperty('preloadedImage')) dropzoneLoadImage(item.preloadedImage);

                            // enable 'delete' action
                            if (currentUserId !== item.id)
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
                    email: $("#item-email").val(),
                    password: $("#item-password").val(),
                    role_id: $("#item-role").val(),
                    photo: $("#item-photo").val(),
                };

                // 'loader' on
                $('#item').modal('loading');

                validateAndSaveItem('user', itemData, '#item', function() {

                    $.ajax({
                        url: action == 'store' ?
                                '{{ URL::to('/') }}/users' :                // store
                                '{{ URL::to('/') }}/users/' + itemId,       // update
                        type: 'POST',
                        data: itemData,
                        success: function(response) {
                            if (response.status == 'success') {
                                // do after 'store' (not 'update')
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
                                $("#item-password").val("");
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
            initActionsHandler('#item', itemsTable, "{{ URL::to('/') }}/users/action");
        });
    </script>
@endsection