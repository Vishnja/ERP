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
             * Dropzone
             */
            Dropzone.autoDiscover = false;

            var thisDropZone;

            var preloadedImage = {!! $user->preloadedImage  !!};

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

            function dropzoneLoadImage(image) {
                var mockFile = { name: image.name, size: image.size };

                thisDropZone.emit("addedfile", mockFile);
                thisDropZone.createThumbnailFromUrl(mockFile, image.path);
                thisDropZone.emit("complete", mockFile);
                thisDropZone.files.push(mockFile);

                $('#item-photo').val(image.filename);
            }

            if (preloadedImage) dropzoneLoadImage(preloadedImage);

            /**
             * Save (Ajax)
             */

            // Item validate empty fields
            function itemValidate() {
                return true;
            }

            // Item
            $("#btn-item-save").click(function(){
                /*
                var itemId = $("#item-id").val();
                var action = 'update';

                var itemData = {
                    _method: "PUT",
                    _token: $('#csrf-token').val(),

                    id: itemId,
                    name: $("#item-name").val(),
                    surname: $("#item-surname").val(),
                    email: $("#item-email").val(),
                    role_id: $("#item-role").val(),
                    photo: $("#item-photo").val(),
                };

                // password setter
                if ($("#item-password").val()) itemData['password'] = $("#item-password").val();

                $("body").modalmanager('loading');

                $.ajax({
                    url: '{{ URL::to('/') }}/users/' + itemId,       // update
                    type: 'POST',
                    data: itemData,
                    success: function(response) {
                        if (response.status == 'success') {
                            // Message
                            $("#item-form .alert").remove();
                            $("body").modalmanager('loading');
                            $("#item-form").prepend(
                                '<div class="alert alert-info fade in">' +
                                    'Сохранено!<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                '</div>');
                        }
                        else {
                            alert('Произошла ошибка при сохранении!');
                            console.log(response);
                        }
                    }
                });

                return false;
                */
            });

        });
    </script>
@endsection