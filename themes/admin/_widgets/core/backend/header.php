<nav class="navbar navbar-expand navbar-light navbar-bg" style="box-shadow: inherit;">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>
    <?php if ($this->title_page_edit) { ?>
        <div class="d-flex w-100">
            <h2 class="m-0 flex-grow-1 pe-1">
                <?php echo $this->ui->field('title'); ?>
            </h2>
            <div class="me-2">
                <a href="<?php echo $this->link_list ?>">
                    <button type="button" class="btn btn-outline-secondary">Cancel</button>
                </a>
            </div>
            <div class="me-2">
                <button id="save_and_close_header" type="submit" class="btn btn-outline-success btn_save_close">Save & Close</button>
            </div>
            <div class="me-2">
                <button id="apply_header" type="submit" class="btn btn-outline-success btn_apply">Apply</button>
            </div>
            <?php if ($this->link_preview): ?>
            <div class="me-2">
                <a class="btn btn-outline-success" href="<?php echo $this->link_preview; ?>">
                    Preview
                </a>
            </div>
            <?php endif; ?>
        </div>

    <?php } else { ?>
        <h2 class="m-0 d-flex align-items-center"><?php echo $this->title_page; ?></h2>
        <?php if($this->button_header) : ?>
            <div class="ms-auto d-flex">
                <?php
                    if (is_array($this->button_header)) 
                    {
                        foreach($this->button_header as $button)
                        {
                            echo  '<a href="'. $button['link'].'" class="'. $button['class'].'">
                                '. $button['title'].'
                            </a>';
                        }
                    }
                ?>
            </div>
        <?php endif; ?>
    <?php } ?>
</nav>