<script>
    $(document).ready(function(){
        $(document).on('click', '.btn-install', function(){
            $('#staticBackdropLabel').html(`Install Solution ${$(this).data('name')}`);
            $('#loading-text').html('Installing');
            $('.loading').css("display", "flex");
            $('#modal-text').html('');
            $('#staticBackdrop').modal('show');
            var button = $(this);

            var code = $(this).data('code');
            var modalText = '';
            // call api prepare install
            $.ajax({
                url: '<?php echo $this->link_prepare_install?>/' + code,
                type: 'POST',
                complete: function(xhr_prepare_install, status_prepare_install) {
                    let response_prepare_install = JSON.parse(xhr_prepare_install.responseText);
                    let time_prepare_install = response_prepare_install.time;
                    let text_time_prepare_install = `Execute time: ${time_prepare_install.toFixed(2)} s`;
                    modalText += response_prepare_install.message.replace(/\\/g, '') + text_time_prepare_install;
                    $('#modal-text').html(modalText);
                    let solution = response_prepare_install.data;
                    if (response_prepare_install.status == 'success') {
                        // if success, call api download solution
                        $.ajax({
                            url: '<?php echo $this->link_download_solution?>',
                            type: 'POST',
                            data: {
                                'solution': solution
                            },
                            complete: function(xhr_download_solution, status_download_solution) {
                                let response_download_solution = JSON.parse(xhr_download_solution.responseText);
                                let solution_path = response_download_solution.data;
                                let time_download_solution = response_download_solution.time;
                                let text_time_download_solution = `Execute time: ${time_download_solution.toFixed(2)} s`;
                                modalText += response_download_solution.message.replace(/\\/g, '') + text_time_download_solution;
                                $('#modal-text').html(modalText);
                                if (response_prepare_install.status == 'success') {
                                    //if success, call api unzip solution folder
                                    $.ajax({
                                        url: '<?php echo $this->link_unzip_solution?>',
                                        type: 'POST',
                                        data: {
                                            'solution_path': solution_path
                                        },
                                        complete: function(xhr_unzip_solution, status_unzip_solution) {
                                            let response_unzip_solution = JSON.parse(xhr_unzip_solution.responseText);
                                            let solution_folder = response_unzip_solution.data;
                                            let time_unzip_solution = response_unzip_solution.time;
                                            let text_time_unzip_solution = `Execute time: ${time_unzip_solution.toFixed(2)} s`;
                                            modalText += response_unzip_solution.message.replace(/\\/g, '') + text_time_unzip_solution;
                                            $('#modal-text').html(modalText);
                                            if (response_unzip_solution.status == 'success') {
                                                // if success, call api install plugins
                                                $.ajax({
                                                    url: '<?php echo $this->link_install_plugins?>',
                                                    type: 'POST',
                                                    data: {
                                                        'solution_path': solution_folder,
                                                        'solution': code,
                                                    },
                                                    complete: function(xhr_install_plugins, status_install_plugins) {
                                                        let response_install_plugins = JSON.parse(xhr_install_plugins.responseText);
                                                        let time_install_plugins = response_install_plugins.time;
                                                        let text_time_install_plugins = `Execute time: ${time_install_plugins.toFixed(2)} s`;
                                                        modalText += response_install_plugins.message.replace(/\\/g, '') + text_time_install_plugins;
                                                        $('#modal-text').html(modalText);
                                                        if (response_install_plugins.status == 'success') {
                                                            // if success, call api generate data structure
                                                            $.ajax({
                                                                url: '<?php echo $this->link_generate_data_structure?>',
                                                                type: 'POST',
                                                                complete: function(xhr_generate_data_structure, status_generate_data_structure) {
                                                                    let response_generate_data_structure = JSON.parse(xhr_generate_data_structure.responseText);
                                                                    let time_generate_data_structure = response_generate_data_structure.time;
                                                                    let text_time_generate_data_structure = `Execute time: ${time_generate_data_structure.toFixed(2)} s`;
                                                                    modalText += response_generate_data_structure.message.replace(/\\/g, '') + text_time_generate_data_structure;
                                                                    $('#modal-text').html(modalText);
                                                                    if (response_generate_data_structure.status == 'success') {
                                                                        // if success, call api run composer update
                                                                        $.ajax({
                                                                            url: '<?php echo $this->link_composer_update?>',
                                                                            type: 'POST',
                                                                            data: {
                                                                                'action': 'install'
                                                                            },
                                                                            complete: function(xhr_composer_update, status_composer_update) {
                                                                                let response_composer_update = JSON.parse(xhr_composer_update.responseText);
                                                                                let time_composer_update = response_composer_update.time;
                                                                                let text_time_composer_update = `Execute time: ${time_composer_update.toFixed(2)} s`;
                                                                                modalText += response_composer_update.message.replace(/\\/g, '') + text_time_composer_update;
                                                                                let total_time = time_prepare_install + time_download_solution + time_unzip_solution + time_install_plugins + time_generate_data_structure + time_composer_update;
                                                                                let text_total_time = `Total execute time: ${total_time.toFixed(2)} s</h4>`;
                                                                                if (response_composer_update.status == 'success') {
                                                                                    modalText += `<h4>Install successfully! ${text_total_time}`;
                                                                                    $('.loading').css("display", "none");
                                                                                    button.html('Uninstall');
                                                                                    button.removeClass("btn-primary btn-install").addClass("btn-secondary btn-uninstall");
                                                                                    showToast('success', 'Install successfully!');
                                                                                } else {
                                                                                    modalText += `<h4>Install failed!${text_total_time}`;
                                                                                    $('.loading').css("display", "none");
                                                                                    showToast('failed', 'Install failed!');
                                                                                }
                                                                                $('#modal-text').html(modalText);
                                                                            }
                                                                        });
                                                                    } else {
                                                                        $('.loading').css("display", "none");
                                                                        showToast('failed', 'Install failed!');
                                                                    }
                                                                }
                                                            })
                                                        } else {
                                                            $('.loading').css("display", "none");
                                                            showToast('failed', 'Install failed!');
                                                        }
                                                    }
                                                })
                                            } else {
                                                $('.loading').css("display", "none");
                                                showToast('failed', 'Install failed!');
                                            }
                                        }
                                    });
                                } else {
                                    $('.loading').css("display", "none");
                                    showToast('failed', 'Install failed!');
                                }
                            }
                        });
                    } else {
                        $('.loading').css("display", "none");
                        showToast('failed', 'Install failed!');
                    }
                }
            });
        })

        $(document).on('click', '.btn-uninstall', function(){
            var code  = $(this).data('code');
            var button = $(this);
            var result = confirm("You are going to uninstall solution. Are you sure ?");
            if (result) {
                $('#staticBackdropLabel').html(`Uninstall Solution ${$(this).data('name')}`);
                $('#loading-text').html('Uninstalling');
                $('.loading').css("display", "flex");
                $('#modal-text').html('');
                $('#staticBackdrop').modal('show');
                var modalText = '';
                // call api prepare uninstall
                $.ajax({
                    url: '<?php echo $this->link_prepare_uninstall?>/' + code,
                    type: 'POST',
                    complete: function(xhr_prepare_uninstall, status_prepare_uninstall) {
                        response_prepare_uninstall = JSON.parse(xhr_prepare_uninstall.responseText);
                        let time_prepare_uninstall = response_prepare_uninstall.time;
                        let text_time_prepare_uninstall = `Execute time: ${time_prepare_uninstall.toFixed(2)} s`;
                        modalText += response_prepare_uninstall.message.replace(/\\/g, '') + text_time_prepare_uninstall;
                        $('#modal-text').html(modalText);
                        let solution = response_prepare_uninstall.data;
                        if (response_prepare_uninstall.status == 'success') {
                            // if success, call api uninstall plugins 
                            $.ajax({
                                url: '<?php echo $this->link_uninstall_plugins?>',
                                type: 'POST',
                                data: {
                                    'solution': solution
                                },
                                complete: function(xhr_uninstall_plugins, status_uninstall_plugins) {
                                    let response_uninstall_plugins = JSON.parse(xhr_uninstall_plugins.responseText);
                                    let time_uninstall_plugins = response_uninstall_plugins.time;
                                    let text_time_uninstall_plugins = `Execute time: ${time_uninstall_plugins.toFixed(2)} s`;
                                    modalText += response_uninstall_plugins.message.replace(/\\/g, '') + text_time_uninstall_plugins;
                                    $('#modal-text').html(modalText);
                                    if (response_uninstall_plugins.status == 'success') {
                                        // if success, call api run composer update
                                        $.ajax({
                                            url: '<?php echo $this->link_composer_update?>',
                                            type: 'POST',
                                            data: {
                                                'action': 'uninstall'
                                            },
                                            complete: function(xhr_composer_update, status_composer_update) {
                                                let response_composer_update = JSON.parse(xhr_composer_update.responseText);
                                                let time_composer_update = response_composer_update.time;
                                                let text_time_composer_update = `Execute time: ${time_composer_update.toFixed(2)} s`;
                                                modalText += response_composer_update.message.replace(/\\/g, '') + text_time_composer_update;
                                                let total_time = time_prepare_uninstall + time_uninstall_plugins + time_composer_update;
                                                let text_total_time = `Total execute time: ${total_time.toFixed(2)} s</h4>`;

                                                if (response_composer_update.status == 'success') {
                                                    modalText += `<h4>Install successfully! ${text_total_time}`;
                                                    $('.loading').css("display", "none");
                                                    button.html('Install');
                                                    button.removeClass("btn-secondary btn-uninstall").addClass("btn btn-primary btn-install");
                                                    showToast('success', 'Uninstall successfully!');
                                                } else {
                                                    modalText += `<h4>Install failed!${text_total_time}`;
                                                    $('.loading').css("display", "none");
                                                    showToast('failed', 'Uninstall failed!');
                                                }
                                                $('#modal-text').html(modalText);
                                            }
                                        });
                                    } else {
                                        showToast('failed', 'Uninstall failed!');
                                    }
                                }
                            });
                        } else {
                            showToast('failed', 'Uninstall failed!');
                        }
                    }
                });
            }
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