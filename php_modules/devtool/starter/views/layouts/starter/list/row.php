<div class="col-3">
    <div class="card shadow-none border">
        <div class="card-body">
            <h4 class="card-title"><?php echo $this->item['name'] ?></h4>
            <p class="card-text"><?php echo $this->item['description'] ?></p>
            <div class="d-flex justify-content-end">  
                <?php if (array_key_exists('button-create-user', $this->item)): ?> 
                    <?php echo $this->renderWidget($this->item['button-create-user']['widget']); ?>
                <?php endif; ?>
                <button data-name="<?php echo $this->item['name']; ?>" data-code="<?php echo $this->item['code']; ?>" class="btn <?php echo $this->item['status'] ? 'btn-secondary btn-uninstall' : 'btn-primary btn-install' ?> ">
                    <?php echo $this->item['status'] ? 'Uninstall' : 'Install' ?>
                </button>
            </div>
        </div>
    </div>
</div>
