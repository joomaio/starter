<tr>
    <td>
        <input class="checkbox-item" type="checkbox" name="ids[]" value="<?php echo $this->item['id']; ?>">
    </td>
    <td>
        <a href="<?php echo $this->link_view . '/' . strtolower($this->item['filter_link']); ?>">
            <i class="fa-solid fa-eye me-2"></i><?php echo  $this->item['name']  ?>
        </a>
    </td>
    <td><?php echo $this->item['created_at'] ?></td>
    <td>
        <a class="fs-4 me-1" href="<?php echo $this->link_form.'/'. $this->item['id']; ?>">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
    </td>
</tr>
