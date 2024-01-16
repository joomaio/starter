<script>
    $(document).ready(function(){
        $('.btn-install').on('click', function(){
            $('#staticBackdropLabel').html(`Install Plugin ${$(this).data('name')}`);
            $('#staticBackdrop').modal('show');
            var code = $(this).data('code');
            $.ajax({
                url: '<?php echo $this->link_install?>/' + code,
                type: 'POST',
                success: function (response) {
                    $('#modal-text').html(response);
                },
                error: function (error) {
                    $('#modal-text').html('Error occurred: ' + JSON.stringify(error));
                }
            });
        })

        $('.btn-uninstall').on('click', function(){
            var code  = $(this).data('code');
            var result = confirm("You are going to uninstall solution. Are you sure ?");
            if (result) {
                $('#staticBackdropLabel').html(`Uninstall Plugin ${$(this).data('name')}`);
                $('#staticBackdrop').modal('show');
                $.ajax({
                    url: '<?php echo $this->link_uninstall?>/' + code,
                    type: 'POST',
                    success: function (response) {
                        $('#modal-text').html(response);
                    },
                    error: function (error) {
                        $('#modal-text').html('Error occurred: ' + JSON.stringify(error));
                    }
                });
            }
        })
    });
</script>