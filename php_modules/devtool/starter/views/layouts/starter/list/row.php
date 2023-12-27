<div class="col-3">
    <div class="card shadow-none border">
        <div class="card-body">
            <h4 class="card-title"><?php echo $this->item['name'] ?></h4>
            <p class="card-text"><?php echo $this->item['description'] ?></p>
            <div class="text-end">
                <button class="btn <?php echo $this->item['status'] ? 'btn-secondary' : 'btn-primary' ?> ">
                    <?php echo $this->item['status'] ? 'Uninstall' : 'Install' ?>
                </button>
            </div>
        </div>
    </div>
</div>
