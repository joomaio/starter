<?php if ($this->message) { ?>
    <div class="toast message-toast toast-notification">
    <div class="d-flex message-body <?php echo (0 === strpos($this->message, 'Error')) ? 'alert-danger' : 'alert-success'; ?>">
        <div class="toast-body">
            <?php echo $this->message;?>
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
        
    </div>
    <script>
        $(document).ready(function() {
            $('.toast-notification').toast('show');
        })
    </script>
<?php
}
?>
