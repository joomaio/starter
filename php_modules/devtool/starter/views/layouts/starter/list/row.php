<div class="col-4">
    <div class="card shadow-none border">
        <div class="card-body">
            <h4 class="card-title"><?php echo $this->item['name'] ?></h4>
            <p class="card-text"><?php echo $this->item['description'] ?></p>
            <div class="d-flex justify-content-center">  
                <?php if (isset($this->buttons[$this->item['folder_name']]) && $this->buttons[$this->item['folder_name']]): ?> 
                    <button class="<?php echo $this->buttons[$this->item['folder_name']]['button-class']; ?>" id="<?php echo $this->buttons[$this->item['folder_name']]['button-id']; ?>" 
                        <?php if (array_key_exists('button-modal-info', $this->buttons[$this->item['folder_name']])) echo $this->buttons[$this->item['folder_name']]['button-modal-info']; ?> 
                        style="<?php if (array_key_exists('button-style', $this->buttons[$this->item['folder_name']])) echo $this->buttons[$this->item['folder_name']]['button-style']; ?>">
                        <?php echo $this->buttons[$this->item['folder_name']]['button-name'] ?>
                    </button>
                    <?php if (array_key_exists('modal-widget', $this->buttons[$this->item['folder_name']])) echo $this->renderWidget($this->buttons[$this->item['folder_name']]['modal-widget']); ?>
                <?php endif; ?>
                <button data-type="solution" data-solution="<?php echo $this->item['folder_name']; ?>" data-name="<?php echo $this->item['name']; ?>" data-code="<?php echo $this->item['folder_name']; ?>" class="btn <?php echo $this->item['status'] ? 'btn-secondary btn-uninstall' : 'btn-primary btn-install' ?> ">
                    <?php echo $this->item['status'] ? 'Uninstall Solution' : 'Install' ?>
                </button>
            </div>
            <div class="plugins-container">
                <?php if (array_key_exists('plugins', $this->item)): ?> 
                    <hr class="hr" />
                    <?php foreach($this->item['plugins'] as $plugin): ?>
                        <div class="plugin-item d-flex justify-content-between mb-2">
                            <span><?php echo $plugin['name'] ?></span>
                            <button data-type="plugin" data-solution="<?php echo $plugin['solution']; ?>" data-name="<?php echo $plugin['name']; ?>" data-code="<?php echo $plugin['folder_name']; ?>" class="btn btn-secondary btn-uninstall">Uninstall</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
