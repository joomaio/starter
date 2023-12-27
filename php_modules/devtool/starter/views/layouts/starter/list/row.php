<div class="col-3">
    <div class="card shadow-none border">
        <div class="card-body">
            <h4 class="card-title"><?php echo $this->item['name'] ?></h4>
            <p class="card-text"><?php echo $this->item['description'] ?></p>
            <div class="text-end">  
                <button data-code="<?php echo $this->item['code']; ?>" class="btn <?php echo $this->item['status'] ? 'btn-secondary btn-uninstall' : 'btn-primary btn-install' ?> ">
                    <?php echo $this->item['status'] ? 'Uninstall' : 'Install' ?>
                </button>
            </div>
        </div>
    </div>
</div>
