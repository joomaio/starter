<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <div class="sidebar-brand d-flex justify-content-between">
            <a class="text-decoration-none text-white" href="<?php echo $this->url; ?>">
                <svg class="sidebar-brand-icon align-middle" width="32px" height="32px" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter" color="#FFFFFF" style="margin-left: -3px">
                    <path d="M12 4L20 8.00004L12 12L4 8.00004L12 4Z"></path>
                    <path d="M20 12L12 16L4 12"></path>
                    <path d="M20 16L12 20L4 16"></path>
                </svg>
                <span class="sidebar-brand-text align-items-middle ms-2">
                    SDM
                </span>
            </a>
            <h3>
                <a class="text-decoration-none open-dashboard-menu text-white dropdown-toggle" id="open-dropdown-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-gauge"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdown-menu">
                    <?php foreach($this->menu as $index => $row): ?>
                        <?php if(isset($row['childs']) && $row['childs']) : ?>
                            <?php foreach($row['childs'] as $key => $child) : ?>
                                <li class="sidebar-item <?php echo isset($child['class']) ? $child['class'] : ''; ?>">
                                    <a href="<?php echo $child['link']; ?>" class="submenu-link">
                                        <span class="align-middle">
                                            <?php echo $child['title'] ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <li>
                            <a href="<?php echo isset($row['link']) ? $row['link'] : ''; ?>" 
                                class="<?php echo (isset($row['childs']) && is_array($row['childs']) && $row['childs']) ? 'link-collapse collapsed' : '';?>" 
                                <?php echo (isset($row['childs']) && is_array($row['childs']) && $row['childs']) ? 'data-bs-target="#tab_'. $index .'" role="button" data-bs-toggle="collapse" aria-expanded="false" ' : '' ?> 
                            >
                                <span class="align-middle">
                                    <?php echo $row['title'] ?>
                                    <?php if (isset($row['childs']) && is_array($row['childs']) && $row['childs']) : ?>
                                        <i id="icon" class="fa-solid <?php echo (isset($row['class']) && strpos($row['class'] , 'active') !== false) ? 'fa-caret-up' : 'fa-caret-down' ?> icon-collapse float-end mt-1"></i>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </h3>
            
        </div>
        <ul class="sidebar-nav fs-4">
            <?php
            foreach ($this->menu_shortcut as $index => $row) {?>
                <li class="sidebar-item <?php echo isset($row['class']) ? $row['class'] : ''; ?> ">
                    <a href="<?php echo isset($row['link']) ? $row['link'] : ''; ?>" 
                        class="sidebar-link <?php echo (isset($row['childs']) && is_array($row['childs']) && $row['childs']) ? 'link-collapse collapsed' : '';?>" 
                        <?php echo (isset($row['childs']) && is_array($row['childs']) && $row['childs']) ? 'data-bs-target="#tab_'. $index .'" role="button" data-bs-toggle="collapse" aria-expanded="false" ' : '' ?> 
                    >
                        <?php echo isset($row['icon']) ? $row['icon'] : '' ?> 
                        <span class="align-middle">
                            <?php echo $row['title'] ?>
                            <?php if (isset($row['childs']) && is_array($row['childs']) && $row['childs']) : ?>
                                <i id="icon" class="fa-solid <?php echo (isset($row['class']) && strpos($row['class'] , 'active') !== false) ? 'fa-caret-up' : 'fa-caret-down' ?> icon-collapse float-end mt-1"></i>
                            <?php endif; ?>
                        </span>
                    </a>
                    <?php if (isset($row['childs']) && is_array($row['childs']) && $row['childs']) : ?>
                        <ul id="tab_<?php echo $index ?>" class="sidebar-dropdown list-unstyled collapse <?php echo (isset($row['class']) && strpos($row['class'] , 'active') !== false) ? 'show' : '' ?>" >
                            <?php foreach($row['childs'] as $key => $child) :
                             ?>
                            <li class="sidebar-item <?php echo isset($child['class']) ? $child['class'] : ''; ?>">
                                <a href="<?php echo $child['link']; ?>" class="sidebar-link submenu-link">
                                    <span class="align-middle">
                                        <i class="fa-solid fa-arrow-right"></i><?php echo $child['title'] ?>
                                    </span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>
<script>
    $('.link-collapse').on('click', function() {
        $('.icon-collapse', this).toggleClass('fa-caret-down fa-caret-up');
    });
    
</script>