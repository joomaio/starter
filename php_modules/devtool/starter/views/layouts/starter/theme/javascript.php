<script>
    function isJSON(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
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
                        if(!isJSON(res))
                        {
                            alert('An error occurred, please try again later');
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                        res = JSON.parse(res);
                        console.log(res);
                        var endTime = Date.now();
                        var duration = endTime - startTime;
                        var modalText = $('#modal-text').html();

                        if(res.status)
                        {
                            step = res.step ?? step;
                            totalStep = res.totalStep ?? totalStep;
                            let text_time = `Execute time: ${duration} ms`;
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
                        if(!isJSON(res))
                        {
                            alert('An error occurred, please try again later');
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                        res = JSON.parse(res);
                    }
                });
        }

        $(document).on('click', '.btn-theme-uninstall', function () {
            var code = $(this).data('code');
            var name = $(this).data('name');
            var button = $(this);
            var result = confirm(`You are going to uninstall theme ${name}. Are you sure ?`);
            if (result) {
                $('#staticBackdropLabel').html(`Uninstall Theme ${$(this).data('name')}`);
                $('.progress-bar').css('width', '0%').attr("aria-valuenow", 0);
                $('#progess-status').html('Uninstalling 0%');
                $('.progess-status').css("display", "flex");
                $('.progress').css("display", "flex");
                $('#modal-text').html('');
                $('#staticBackdrop').modal('show');
                var modalText = '';
                var total_time = 0;
                formData = new FormData();
                formData.append('theme', code);
                // call api prepare uninstall
                ajaxUninstall(formData);
            }
        });

        function ajaxUninstall(formData, step = 0, totalStep = 0, timestamp = '' )
        {
            var startTime = Date.now();
            formData.append('step', step);
            formData.append('totalStep', totalStep);
            formData.append('timestamp', timestamp);
            $.ajax({
                    url: '<?php echo $this->link_uninstall_theme ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    complete: function (respone) {
                        var res = respone.responseText.replace(/^\ufeff+/g, '');
                        if(!isJSON(res))
                        {
                            alert('An error occurred, please try again later');
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                        res = JSON.parse(res);
                        var endTime = Date.now();
                        var duration = endTime - startTime;
                        var modalText = $('#modal-text').html();
                        step = res.step ?? step;
                        totalStep = res.totalStep ?? totalStep;

                        if(res.status)
                        {
                            let text_time = `Execute time: ${duration} ms`;
                            var title = `<h4>${res.title}</h4>`;
                            title = !step && !totalStep ? '' : title;
                            modalText += `${title}<p> ${res.message.replace(/\\/g, '')}</p><p> ${text_time}</p>`;
                            $('#modal-text').html(modalText);
                            $('.modal-body').scrollTop($('#modal-text').height());

                            var person = step / totalStep * 100;
                            if(step < totalStep)
                            {
                                ajaxUninstall(formData, step +1 ,totalStep, res.timestamp);
                            }
                            else
                            {
                                $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                            }
                            $('.progress-bar').css('width', person.toFixed(0) + '%').attr("aria-valuenow", person.toFixed(0));
                            $('#progess-status').html(`Uninstall ${person.toFixed(0)}%`);
                        } else {
                            $('.progress').css("display", "none");
                            $('.progess-status').css("display", "none");
                            var title = `<h4>${res.title}</h4>`;
                            title = !step && !totalStep ? '' : title;
                            modalText += title + res.message ?? '';
                            modalText += `<h4>Uninstall failed! Total execute time: ${duration} ms</h4>`;
                            $('#modal-text').html(modalText);
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                    },
                    error: function(respone){
                        var res = respone.responseText.replace(/^\ufeff+/g, '');
                        if(!isJSON(res))
                        {
                            alert('An error occurred, please try again later');
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                    }
                });
        }
        
    });
</script>