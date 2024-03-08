<script>
    $(document).ready(function () {
        $(document).on('click', '#submit-theme-upload', function () {
            $('#error-theme-text').html('');
            var check_error = false;
            // Get the file input element
            var fileInput = document.getElementById('theme_upload');
            var urlInput = $('#theme_url').val();
            var file = fileInput.files[0];

            if (!file && !urlInput) {
                $('#error-theme-text').html('Please choose your package file or enter urlInput.');
                check_error = true;
            } else {
                if(file)
                {
                    // Check file extension
                    var allowedExtensions = ['zip'];
                    var extension = file.name.split('.').pop().toLowerCase();
                    if (!allowedExtensions.includes(extension)) {
                        $('#error-theme-text').html('Only .zip files are allowed.');
                        check_error = true;
                    } else {
                        // Check file size
                        if (file.size > 20 * 1024 * 1024) { // 5MB in bytes
                            $('#error-theme-text').html('File size should be less than 20MB.');
                            check_error = true;
                        }
                    }
                }
            }


            if (!check_error) {
                $('#uploadTheme').modal('hide');
                $('#staticBackdropLabel').html(`Install Theme`);
                $('.progress-bar').css('width', '0%').attr("aria-valuenow", 0);
                $('#progess-status').html('Installing 0%');
                $('.progess-status').css("display", "flex");
                $('.progress').css("display", "flex");
                $('#modal-text').html('');
                $('#staticBackdrop').modal('show');

                var modalText = '';
                var total_time = 0;

                var formData = new FormData();
                formData.append('file_upload', file);
                formData.append('url', urlInput);
                formData.append('action', 'upload_file');
                ajaxInstall(formData);
            }
        });

        function ajaxInstall(formData, step = 0, totalStep = 0, timestamp = '' )
        {
            var startTime = Date.now();
            formData.append('step', step);
            formData.append('totalStep', totalStep);
            formData.append('timestamp', timestamp);
            $.ajax({
                    url: '<?php echo $this->link_install_theme ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    complete: function (respone) {
                        var res = respone.responseText.replace(/^\ufeff+/g, '');
                        res = JSON.parse(res);
                        console.log(res);
                        var endTime = Date.now();
                        var duration = endTime - startTime;
                        modalText = $('#modal-text').html();

                        if(res.status)
                        {
                            step = res.step ?? step;
                            totalStep = res.totalStep ?? totalStep;
                            let text_time = `Execute time: ${duration} s`;
                            var title = `<h4>${res.title}</h4>`;
                            modalText += `${title}<p> ${res.message.replace(/\\/g, '')}</p><p> ${text_time}</p>`;
                            $('#modal-text').html(modalText);
                            $('.modal-body').scrollTop($('#modal-text').height());

                            var person = step / totalStep * 100;
                            if(step < totalStep)
                            {
                                ajaxInstall(formData, step +1 ,totalStep, res.timestamp);
                            }
                            else
                            {
                                $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                            }
                            $('.progress-bar').css('width', person.toFixed(0) + '%').attr("aria-valuenow", person.toFixed(0));
                            $('#progess-status').html(`Installing ${person.toFixed(0)}%`);
                        } else {
                            $('.progress').css("display", "none");
                            $('.progess-status').css("display", "none");
                            var title = `<h4>${res.title}</h4>`;
                            modalText += title + res.message ?? '';
                            modalText += `<h4>Install failed! Total execute time: ${duration} ms</h4>`;
                            $('#modal-text').html(modalText);
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                    },
                    error: function(respone){
                        var res = respone.responseText.replace(/^\ufeff+/g, '');
                        res = JSON.parse(res);
                        if(!res.status)
                        {
                            $('.progress').css("display", "none");
                            $('.progess-status').css("display", "none");
                            modalText += '<h4>An error occurred, please try again later</h4>';
                            $('#modal-text').html(modalText);
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                    }
                });
        }

        $(document).on('click', '.btn-uninstall', function () {
            var code = $(this).data('code');
            var type = $(this).data('type');
            var solution = $(this).data('solution');
            var button = $(this);
            var result = confirm(`You are going to uninstall ${type}. Are you sure ?`);
            if (result) {
                $('#staticBackdropLabel').html(`Uninstall ${type} ${$(this).data('name')}`);
                $('.progress-bar').css('width', '0%').attr("aria-valuenow", 0);
                $('#progess-status').html('Uninstalling 0%');
                $('.progess-status').css("display", "flex");
                $('.progress').css("display", "flex");
                $('#modal-text').html('');
                $('#staticBackdrop').modal('show');
                var modalText = '';
                var total_time = 0;
                // call api prepare uninstall
                $.ajax({
                    url: '<?php echo $this->link_prepare_uninstall ?>/' + code,
                    data: {
                        'type': type,
                        'solution': solution,
                    },
                    type: 'POST',
                    complete: function (xhr_prepare_uninstall, status_prepare_uninstall) {
                        let cleaned_prepare_uninstall = xhr_prepare_uninstall.responseText.replace(/^\ufeff+/g, '');
                        let response_prepare_uninstall = JSON.parse(cleaned_prepare_uninstall);
                        let time_prepare_uninstall = response_prepare_uninstall.time;
                        total_time += time_prepare_uninstall;
                        let text_time_prepare_uninstall = `Execute time: ${time_prepare_uninstall.toFixed(2)} s`;
                        modalText += response_prepare_uninstall.message.replace(/\\/g, '') + text_time_prepare_uninstall;
                        $('#modal-text').html(modalText);
                        $('.modal-body').scrollTop($('#modal-text').height());
                        let package_path = response_prepare_uninstall.data;
                        if (response_prepare_uninstall.status == 'success') {
                            // if success, call api uninstall plugins 
                            $('.progress-bar').css('width', '33%').attr("aria-valuenow", 33);
                            $('#progess-status').html('Uninstalling 33%');
                            $.ajax({
                                url: '<?php echo $this->link_uninstall_plugins ?>',
                                type: 'POST',
                                data: {
                                    'type': type,
                                    'package': code,
                                    'solution': solution
                                },
                                complete: function (xhr_uninstall_plugins, status_uninstall_plugins) {
                                    let cleaned_uninstall_plugins = xhr_uninstall_plugins.responseText.replace(/^\ufeff+/g, '');
                                    let response_uninstall_plugins = JSON.parse(cleaned_uninstall_plugins);
                                    let time_uninstall_plugins = response_uninstall_plugins.time;
                                    total_time += time_uninstall_plugins;
                                    let text_time_uninstall_plugins = `Execute time: ${time_uninstall_plugins.toFixed(2)} s`;
                                    modalText += response_uninstall_plugins.message.replace(/\\/g, '') + text_time_uninstall_plugins;
                                    $('#modal-text').html(modalText);
                                    $('.modal-body').scrollTop($('#modal-text').height());
                                    if (response_uninstall_plugins.status == 'success') {
                                        $('.progress-bar').css('width', '67%').attr("aria-valuenow", 67);
                                        $('#progess-status').html('Uninstalling 67%');
                                        // if success, call api run composer update
                                        $.ajax({
                                            url: '<?php echo $this->link_composer_update ?>',
                                            type: 'POST',
                                            data: {
                                                'action': 'uninstall'
                                            },
                                            complete: function (xhr_composer_update, status_composer_update) {
                                                let cleaned_composer_update = xhr_composer_update.responseText.replace(/^\ufeff+/g, '');
                                                let response_composer_update = JSON.parse(cleaned_composer_update);
                                                let time_composer_update = response_composer_update.time;
                                                total_time += time_composer_update;
                                                let text_time_composer_update = `Execute time: ${time_composer_update.toFixed(2)} s`;
                                                modalText += response_composer_update.message.replace(/\\/g, '') + text_time_composer_update;

                                                if (response_composer_update.status == 'success') {
                                                    $('.progress-bar').css('width', '100%').attr("aria-valuenow", 100);
                                                    $('#progess-status').html('Uninstall successfully!');
                                                    modalText += `<h4>Uninstall successfully! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                    // $('.progress').css("display", "none");
                                                    // $('.progess-status').css("display", "none");
                                                    button.html('Install');
                                                    button.removeClass("btn-secondary btn-uninstall").addClass("btn btn-primary btn-install");
                                                    // showToast('success', 'Uninstall successfully!');
                                                } else {
                                                    modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                    $('.progress').css("display", "none");
                                                    $('.progess-status').css("display", "none");
                                                    // showToast('failed', 'Uninstall failed!');
                                                }
                                                $('#modal-text').html(modalText);
                                                $('.modal-body').scrollTop($('#modal-text').height());
                                                $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                            }
                                        });
                                    } else {
                                        $('.progress').css("display", "none");
                                        $('.progess-status').css("display", "none");
                                        modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                        $('#modal-text').html(modalText);
                                        $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                        // showToast('failed', 'Uninstall failed!');
                                    }
                                }
                            });
                        } else {
                            $('.progress').css("display", "none");
                            $('.progess-status').css("display", "none");
                            modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                            $('#modal-text').html(modalText);
                            // showToast('failed', 'Uninstall failed!');
                        }
                    }
                });
            }
        });

        $(document).on('click', '#modal-close', function () {
            location.reload(true);
        });

        function showToast(status, message) {
            let removeClass = status == 'success' ? 'alert-danger' : 'alert-success';
            let addClass = status == 'success' ? 'alert-success' : 'alert-danger';
            $('.toast-message').removeClass(removeClass).addClass(addClass);
            $('.toast-body').html(message);
            $('.toast-notification').toast('show');
        }
    });
</script>