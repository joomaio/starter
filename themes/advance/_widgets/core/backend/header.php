<nav class="navbar navbar-expand navbar-light navbar-bg" style="box-shadow: inherit;">
    <?php if ($this->title_page_edit) { ?>
        <div class="d-flex w-100">
            <h2 class="m-0 flex-grow-1 pe-1">
                <?php echo $this->ui->field('title'); ?>
            </h2>
            <button class="btn btn-outline-success btn_apply d-none"></button>
            <button class="btn btn-outline-success btn_save_close d-none"></button>
        </div>

    <?php } else { ?>
        <h2 class="m-0 d-flex align-items-center"><?php echo $this->title_page; ?></h2>
    <?php } ?>
</nav>