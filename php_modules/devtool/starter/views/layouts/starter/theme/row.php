<div class="col-4">
    <div class="card shadow-none border">
        <div class="card-body">
            <h4 class="card-title"><?php echo $this->item['title'] ?></h4>
            <p class="card-text"><?php echo $this->item['description'] ?></p>
            <div class="d-flex justify-content-end">  
                <?php if (isset($this->buttons[$this->item['folder']]) && $this->buttons[$this->item['folder']]): ?> 
                <?php endif; ?>
                <button data-type="solution" data-solution="<?php echo $this->item['folder']; ?>" data-name="<?php echo $this->item['title']; ?>" data-code="<?php echo $this->item['folder']; ?>" class="btn btn-secondary btn-theme-uninstall">
                    Uninstall
                </button>
            </div>
        </div>
    </div>
</div>
