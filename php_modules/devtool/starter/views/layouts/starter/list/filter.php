<form id="filter_form" class="row pe-0 pb-2" action="<?php echo $this->link_list ?>" method="POST">
    <div class="col-lg-10 col-sm-12">
        <div class="input-group input-group-navbar">
            <div class="pe-2 pb-2">
                <?php $this->ui->field('search');  ?>
            </div>
            <input type="hidden" name="clear_filter" id="input_clear_filter">
            <div class="pe-2 pb-2">
                <button type='Submit' data-bs-toggle="tooltip" title="Filter" class=" align-middle btn border border-1" type="button">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </div>
    </div>
</form>