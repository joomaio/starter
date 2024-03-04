<script>
    $(document).ready(function () {
        $(document).on('click', '#submit-upload', function () {
            $('#error-text').html('');
            var check_error = false;
            // Get the file input element
            var fileInput = document.getElementById('package_upload');
            var urlInput = $('#package_url').val();
            var file = fileInput.files[0];

            if (!file && !urlInput) {
                $('#error-text').html('Please choose your package file or enter urlInput.');
                check_error = true;
            } else {
                if(file)
                {
                    // Check file extension
                    var allowedExtensions = ['zip'];
                    var extension = file.name.split('.').pop().toLowerCase();
                    if (!allowedExtensions.includes(extension)) {
                        $('#error-text').html('Only .zip files are allowed.');
                        check_error = true;
                    } else {
                        // Check file size
                        if (file.size > 20 * 1024 * 1024) { // 5MB in bytes
                            $('#error-text').html('File size should be less than 20MB.');
                            check_error = true;
                        }
                    }
                }
            }


            if (!check_error) {
                $('#upload_package').modal('hide');
                $('#staticBackdropLabel').html(`Install Package`);
                $('.progress-bar').css('width', '0%').attr("aria-valuenow", 0);
                $('#progess-status').html('Installing 0%');
                $('.progess-status').css("display", "flex");
                $('.progress').css("display", "flex");
                $('#modal-text').html('');
                $('#staticBackdrop').modal('show');

                var modalText = '';
                var total_time = 0;

                var formData = new FormData();
                formData.append('package', file);
                formData.append('package_url', urlInput);
                formData.append('action', 'upload_file');
                $.ajax({
                    url: '<?php echo $this->link_unzip_solution ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    complete: function (xhr_unzip_solution, status_unzip_solution) {
                        let cleaned_unzip_solution  = xhr_unzip_solution.responseText.replace(/^\ufeff+/g, '');
                        let response_unzip_solution = JSON.parse(cleaned_unzip_solution);
                        // console.log(response_unzip_solution);
                        let solution_folder = response_unzip_solution.data;
                        let time_unzip_solution = response_unzip_solution.time;
                        total_time += time_unzip_solution;
                        let text_time_unzip_solution = `Execute time: ${time_unzip_solution.toFixed(2)} s`;
                        modalText += response_unzip_solution.message.replace(/\\/g, '') + text_time_unzip_solution;
                        $('#modal-text').html(modalText);
                        $('.modal-body').scrollTop($('#modal-text').height());
                        if (response_unzip_solution.status == 'success') {
                            $('.progress-bar').css('width', '20%').attr("aria-valuenow", 20);
                            $('#progess-status').html('Installing 20%');
                            // if success, call api prepare install
                            $.ajax({
                                url: '<?php echo $this->link_prepare_install ?>/' + response_unzip_solution.info.folder_name,
                                type: 'POST',
                                data: {
                                    'type': response_unzip_solution.info.type,
                                    'solution': response_unzip_solution.info.solution,
                                    'info': response_unzip_solution.info,
                                    'require': response_unzip_solution.info.require,
                                    'action': 'upload'
                                },
                                complete: function (xhr_prepare_install, status_prepare_install) {
                                    // console.log(xhr_prepare_install.responseText);
                                    let cleaned_prepare_install  = xhr_prepare_install.responseText.replace(/^\ufeff+/g, '');
                                    let response_prepare_install = JSON.parse(cleaned_prepare_install);
                                    let time_prepare_install = response_prepare_install.time;
                                    total_time += time_prepare_install;
                                    let text_time_prepare_install = time_prepare_install ? `Execute time: ${time_prepare_install.toFixed(2)} s` : '';
                                    modalText += response_prepare_install.message.replace(/\\/g, '') + text_time_prepare_install;
                                    $('#modal-text').html(modalText);
                                    $('.modal-body').scrollTop($('#modal-text').height());
                                    let solution = response_prepare_install.data;
                                    if (response_prepare_install.status == 'success') {
                                        $('.progress-bar').css('width', '40%').attr("aria-valuenow", 40);
                                        $('#progess-status').html('Installing 40%');
                                        // if success, call api install plugins
                                        $.ajax({
                                            url: '<?php echo $this->link_install_plugins ?>',
                                            type: 'POST',
                                            data: {
                                                'package_path': solution_folder,
                                                'type': response_unzip_solution.info.type,
                                                'solution': response_unzip_solution.info.solution,
                                                'package': response_unzip_solution.info.folder_name,
                                                'action': 'upload'
                                            },
                                            complete: function (xhr_install_plugins, status_install_plugins) {
                                                // console.log(xhr_install_plugins.responseText);
                                                let cleaned_install_plugins  = xhr_install_plugins.responseText.replace(/^\ufeff+/g, '');
                                                let response_install_plugins = JSON.parse(cleaned_install_plugins);
                                                let time_install_plugins = response_install_plugins.time;
                                                total_time += time_install_plugins;
                                                let text_time_install_plugins = time_prepare_install ? `Execute time: ${time_install_plugins.toFixed(2)} s` : '';
                                                modalText += response_install_plugins.message.replace(/\\/g, '') + text_time_install_plugins;
                                                $('#modal-text').html(modalText);
                                                $('.modal-body').scrollTop($('#modal-text').height());
                                                if (response_install_plugins.status == 'success') {
                                                    $('.progress-bar').css('width', '60%').attr("aria-valuenow", 60);
                                                    $('#progess-status').html('Installing 60%');
                                                    // if success, call api generate data structure
                                                    $.ajax({
                                                        url: '<?php echo $this->link_generate_data_structure ?>',
                                                        type: 'POST',
                                                        data: {
                                                            'upload': true
                                                        },
                                                        complete: function (xhr_generate_data_structure, status_generate_data_structure) {
                                                            let cleaned_generate_data_structure = xhr_generate_data_structure.responseText.replace(/^\ufeff+/g, '');
                                                            let response_generate_data_structure = JSON.parse(cleaned_generate_data_structure);
                                                            let time_generate_data_structure = response_generate_data_structure.time;
                                                            total_time += time_generate_data_structure;
                                                            let text_time_generate_data_structure = time_prepare_install ? `Execute time: ${time_generate_data_structure.toFixed(2)} s` : '';
                                                            modalText += response_generate_data_structure.message.replace(/\\/g, '') + text_time_generate_data_structure;
                                                            $('#modal-text').html(modalText);
                                                            $('.modal-body').scrollTop($('#modal-text').height());
                                                            if (response_generate_data_structure.status == 'success') {
                                                                $('.progress-bar').css('width', '80%').attr("aria-valuenow", 80);
                                                                $('#progess-status').html('Installing 80%');
                                                                // if success, call api run composer update
                                                                $.ajax({
                                                                    url: '<?php echo $this->link_composer_update ?>',
                                                                    type: 'POST',
                                                                    data: {
                                                                        'action': 'upload'
                                                                    },
                                                                    complete: function (xhr_composer_update, status_composer_update) {
                                                                        let cleaned_composer_update = xhr_composer_update.responseText.replace(/^\ufeff+/g, '');
                                                                        let response_composer_update = JSON.parse(cleaned_composer_update);
                                                                        let time_composer_update = response_composer_update.time;
                                                                        total_time += time_composer_update;
                                                                        let text_time_composer_update = time_prepare_install ? `Execute time: ${time_composer_update.toFixed(2)} s` : '';
                                                                        modalText += response_composer_update.message.replace(/\\/g, '') + text_time_composer_update;
                                                                        if (response_composer_update.status == 'success') {
                                                                            $('.progress-bar').css('width', '100%').attr("aria-valuenow", 100);
                                                                            $('#progess-status').html('Install successfully!');
                                                                            modalText += `<h4>Install successfully! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                                        } else {
                                                                            modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                                            $('.progress').css("display", "none");
                                                                            $('.progess-status').css("display", "none");
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
                                                            }
                                                        }
                                                    })
                                                } else {
                                                    $('.progress').css("display", "none");
                                                    $('.progess-status').css("display", "none");
                                                    modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                    $('#modal-text').html(modalText);
                                                    $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                                }
                                            }
                                        });
                                    } else {
                                        $('.progress').css("display", "none");
                                        $('.progess-status').css("display", "none");
                                        modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                        $('#modal-text').html(modalText);
                                        $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                    }
                                }
                            });

                        } else {
                            $('.progress').css("display", "none");
                            $('.progess-status').css("display", "none");
                            modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                            $('#modal-text').html(modalText);
                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                        }
                    }
                });
            }
        });

        $(document).on('click', '.btn-install', function () {
            $('#staticBackdropLabel').html(`Install Solution ${$(this).data('name')}`);
            $('.progress-bar').css('width', '0%').attr("aria-valuenow", 0);
            $('#progess-status').html('Installing 0%');
            $('.progess-status').css("display", "flex");
            $('.progress').css("display", "flex");
            $('#modal-text').html('');
            $('#staticBackdrop').modal('show');
            var button = $(this);

            var code = $(this).data('code');
            var modalText = '';
            var total_time = 0;
            // call api prepare install
            $.ajax({
                url: '<?php echo $this->link_prepare_install ?>/' + code,
                type: 'POST',
                complete: function (xhr_prepare_install, status_prepare_install) {
                    let cleaned_prepare_install = xhr_prepare_install.responseText.replace(/^\ufeff+/g, '');
                    let response_prepare_install = JSON.parse(cleaned_prepare_install);
                    let time_prepare_install = response_prepare_install.time;
                    total_time += time_prepare_install;
                    let text_time_prepare_install = `Execute time: ${time_prepare_install.toFixed(2)} s`;
                    modalText += response_prepare_install.message.replace(/\\/g, '') + text_time_prepare_install;
                    $('#modal-text').html(modalText);
                    $('.modal-body').scrollTop($('#modal-text').height());
                    let solution = response_prepare_install.data;
                    if (response_prepare_install.status == 'success') {
                        $('.progress-bar').css('width', '16%').attr("aria-valuenow", 16);
                        $('#progess-status').html('Installing 16%');
                        // if success, call api download solution
                        $.ajax({
                            url: '<?php echo $this->link_download_solution ?>',
                            type: 'POST',
                            data: {
                                'solution': solution
                            },
                            complete: function (xhr_download_solution, status_download_solution) {
                                let cleaned_download_solution = xhr_download_solution.responseText.replace(/^\ufeff+/g, '');
                                let response_download_solution = JSON.parse(cleaned_download_solution);
                                let solution_path = response_download_solution.data;
                                let time_download_solution = response_download_solution.time;
                                total_time += time_download_solution;
                                let text_time_download_solution = `Execute time: ${time_download_solution.toFixed(2)} s`;
                                modalText += response_download_solution.message.replace(/\\/g, '') + text_time_download_solution;
                                $('#modal-text').html(modalText);
                                $('.modal-body').scrollTop($('#modal-text').height());
                                if (response_download_solution.status == 'success') {
                                    //if success, call api unzip solution folder
                                    $('.progress-bar').css('width', '33%').attr("aria-valuenow", 33);
                                    $('#progess-status').html('Installing 33%');
                                    $.ajax({
                                        url: '<?php echo $this->link_unzip_solution ?>',
                                        type: 'POST',
                                        data: {
                                            'package': solution_path
                                        },
                                        complete: function (xhr_unzip_solution, status_unzip_solution) {
                                            // console.log(xhr_unzip_solution.responseText);
                                            let cleaned_unzip_solution = xhr_unzip_solution.responseText.replace(/^\ufeff+/g, '');
                                            let response_unzip_solution = JSON.parse(cleaned_unzip_solution);
                                            let solution_folder = response_unzip_solution.data;
                                            let time_unzip_solution = response_unzip_solution.time;
                                            total_time += time_unzip_solution;
                                            let text_time_unzip_solution = `Execute time: ${time_unzip_solution.toFixed(2)} s`;
                                            modalText += response_unzip_solution.message.replace(/\\/g, '') + text_time_unzip_solution;
                                            $('#modal-text').html(modalText);
                                            $('.modal-body').scrollTop($('#modal-text').height());
                                            if (response_unzip_solution.status == 'success') {
                                                $('.progress-bar').css('width', '50%').attr("aria-valuenow", 50);
                                                $('#progess-status').html('Installing 50%');
                                                // if success, call api install plugins
                                                $.ajax({
                                                    url: '<?php echo $this->link_install_plugins ?>',
                                                    type: 'POST',
                                                    data: {
                                                        'package_path': solution_folder,
                                                        'type': 'solution',
                                                        'package': code,
                                                        'solution': code,
                                                    },
                                                    complete: function (xhr_install_plugins, status_install_plugins) {
                                                        // console.log(xhr_install_plugins.responseText);
                                                        let cleaned_install_plugins = xhr_install_plugins.responseText.replace(/^\ufeff+/g, '');
                                                        let response_install_plugins = JSON.parse(cleaned_install_plugins);
                                                        let time_install_plugins = response_install_plugins.time;
                                                        total_time += time_install_plugins;
                                                        let text_time_install_plugins = `Execute time: ${time_install_plugins.toFixed(2)} s`;
                                                        modalText += response_install_plugins.message.replace(/\\/g, '') + text_time_install_plugins;
                                                        $('#modal-text').html(modalText);
                                                        $('.modal-body').scrollTop($('#modal-text').height());
                                                        if (response_install_plugins.status == 'success') {
                                                            $('.progress-bar').css('width', '67%').attr("aria-valuenow", 67);
                                                            $('#progess-status').html('Installing 67%');
                                                            // if success, call api generate data structure
                                                            $.ajax({
                                                                url: '<?php echo $this->link_generate_data_structure ?>',
                                                                type: 'POST',
                                                                complete: function (xhr_generate_data_structure, status_generate_data_structure) {
                                                                    let cleaned_generate_data_structure = xhr_generate_data_structure.responseText.replace(/^\ufeff+/g, '');
                                                                    let response_generate_data_structure = JSON.parse(cleaned_generate_data_structure);
                                                                    let time_generate_data_structure = response_generate_data_structure.time;
                                                                    total_time += time_generate_data_structure;
                                                                    let text_time_generate_data_structure = `Execute time: ${time_generate_data_structure.toFixed(2)} s`;
                                                                    modalText += response_generate_data_structure.message.replace(/\\/g, '') + text_time_generate_data_structure;
                                                                    $('#modal-text').html(modalText);
                                                                    $('.modal-body').scrollTop($('#modal-text').height());
                                                                    if (response_generate_data_structure.status == 'success') {
                                                                        $('.progress-bar').css('width', '84%').attr("aria-valuenow", 84);
                                                                        $('#progess-status').html('Installing 84%');
                                                                        // if success, call api run composer update
                                                                        $.ajax({
                                                                            url: '<?php echo $this->link_composer_update ?>',
                                                                            type: 'POST',
                                                                            data: {
                                                                                'action': 'install'
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
                                                                                    $('#progess-status').html('Install successfully!');
                                                                                    modalText += `<h4>Install successfully! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                                                    // $('.progress').css("display", "none");
                                                                                    // $('.progess-status').css("display", "none");
                                                                                    button.html('Uninstall');
                                                                                    button.removeClass("btn-primary btn-install").addClass("btn-secondary btn-uninstall");
                                                                                    // showToast('success', 'Install successfully!');
                                                                                } else {
                                                                                    modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                                                    $('.progress').css("display", "none");
                                                                                    $('.progess-status').css("display", "none");
                                                                                    // showToast('failed', 'Install failed!');
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
                                                                    }
                                                                }
                                                            })
                                                        } else {
                                                            $('.progress').css("display", "none");
                                                            $('.progess-status').css("display", "none");
                                                            modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                            $('#modal-text').html(modalText);
                                                            $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                                        }
                                                    }
                                                })
                                            } else {
                                                $('.progress').css("display", "none");
                                                $('.progess-status').css("display", "none");
                                                modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                $('#modal-text').html(modalText);
                                                $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                            }
                                        }
                                    });
                                } else {
                                    $('.progress').css("display", "none");
                                    $('.progess-status').css("display", "none");
                                    modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                    $('#modal-text').html(modalText);
                                    $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                                }
                            }
                        });
                    } else {
                        $('.progress').css("display", "none");
                        $('.progess-status').css("display", "none");
                        modalText += `<h4>Install failed! Total execute time: ${total_time.toFixed(2)} s</h4>`;
                        $('#modal-text').html(modalText);
                        $('.modal-footer').html(`<button id="modal-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`);
                    }
                }
            });
        })

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