<div class="col-3">
    <div class="card shadow-none border">
        <div class="card-body">
            <h4 class="card-title"><?php echo $this->item['name'] ?></h4>
            <p class="card-text"><?php echo $this->item['description'] ?></p>
            <div class="d-flex justify-content-end">  
                <?php if (array_key_exists('button', $this->item)): ?> 
                    <button class="<?php echo $this->item['button']['button-class']; ?>" id="<?php echo $this->item['button']['button-id']; ?>" 
                        <?php if (array_key_exists('button-modal-info', $this->item['button'])) echo $this->item['button']['button-modal-info']; ?> 
                        style="<?php if (array_key_exists('button-style', $this->item['button'])) echo $this->item['button']['button-style']; ?>">
                        <?php echo $this->item['button']['button-name'] ?>
                    </button>
                    <?php if (array_key_exists('modal-widget', $this->item['button'])) echo $this->renderWidget($this->item['button']['modal-widget']); ?>
                <?php endif; ?>
                <button data-name="<?php echo $this->item['name']; ?>" data-code="<?php echo $this->item['code']; ?>" class="btn <?php echo $this->item['status'] ? 'btn-secondary btn-uninstall' : 'btn-primary btn-install' ?> ">
                    <?php echo $this->item['status'] ? 'Uninstall' : 'Install' ?>
                </button>
            </div>
        </div>
    </div>
</div>
