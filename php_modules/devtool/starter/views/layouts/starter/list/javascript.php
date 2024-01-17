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
            $.ajax({
                url: '<?php echo $this->link_install?>/' + code,
                type: 'POST',
                complete: function(xhr, status) {
                    $('.loading').css("display", "none");
                    let response = JSON.parse(xhr.responseText);
                    $('#modal-text').html(response.message.replace(/\\/g, ''));
                    if (response.status == 'success') {
                        button.html('Uninstall');
                        button.removeClass("btn-primary btn-install").addClass("btn-secondary btn-uninstall");
                        $('.toast-message').removeClass('alert-danger').addClass('alert-success');
                        $('.toast-body').html('Install successfully!');
                    } else {
                        $('.toast-message').removeClass('alert-success').addClass('alert-danger');
                        $('.toast-body').html('Install failed!');
                    }
                    $('.toast-notification').toast('show');
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
                $.ajax({
                    url: '<?php echo $this->link_uninstall?>/' + code,
                    type: 'POST',
                    complete: function(xhr, status) {
                        $('.loading').css("display", "none");
                        let response = JSON.parse(xhr.responseText);
                        $('#modal-text').html(response.message.replace(/\\/g, ''));
                        if (response.status == 'success') {
                            button.html('Install');
                            button.removeClass("btn-secondary btn-uninstall").addClass("btn btn-primary btn-install");
                            $('.toast-message').removeClass('alert-danger').addClass('alert-success');
                            $('.toast-body').html('Uninstall successfully!');
                        } else {
                            $('.toast-message').removeClass('alert-success').addClass('alert-danger');
                            $('.toast-body').html('Uninstall failed!');
                        }
                        $('.toast-notification').toast('show');
                    }
                });
            }
        })
    });
</script>