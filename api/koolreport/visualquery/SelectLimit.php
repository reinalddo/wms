<?php

use \koolreport\core\Utility as Util;

$limit = Util::get(
    $this->value,
    'limit',
    Util::get($this->defaultValue, 'limit', [])
);
$limitToggle = Util::get($limit, 'toggle', false);
$limitOffset = Util::get($limit, 'offset', "");
$limitLimit = Util::get($limit, 'limit', "");
?>
<div class='select-limit form form-check'>
    <p></p>
    <label class="checkbox-label">
        <input type="checkbox" class="item-toggle" <?php echo $limitToggle ? "checked" : ""; ?> 
            name="<?php echo $this->name; ?>_limit_toggle" onchange="<?php echo $name; ?>.limitChkChanged(this)">
    </label>
    <!-- <label class="form-check-label" for="inlineFormCheck">
        Enable offset/limit
    </label><br> -->
    <input type="number" class="form-control custom-control" 
        name="<?php echo $this->name; ?>_limit_offset" placeholder=" Offset" value="<?php echo $limitOffset; ?>" />
    <input type="number" class="form-control custom-control" 
        name="<?php echo $this->name; ?>_limit_limit" placeholder=" Limit" value="<?php echo $limitLimit; ?>" />
</div>