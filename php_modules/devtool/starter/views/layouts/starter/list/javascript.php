<script>
    $(document).ready(function(){
        $('.btn-install').on('click', function(){
            var code = $(this).data('code');
            $('#form_install').attr('action', '<?php echo $this->link_install?>/' + code);
            $('#form_install').submit();
        })

        $('.btn-uninstall').on('click', function(){
            var code  = $(this).data('code');
            var result = confirm("You are going to uninstall solution. Are you sure ?");
            if (result) {
                $('#form_uninstall').attr('action', '<?php echo $this->link_uninstall?>/' + code)
                $('#form_uninstall').submit();
            }
        })
    });
</script>