<style>
    .select-tables .custom-select {
        margin-right: 5px;
    }
</style>
<?php
use \koolreport\core\Utility as Util;
$distinct = Util::get($this->value, "selectDistinct",
    Util::get($this->defaultValue, "selectDistinct", false));
$tableNames = array_keys($this->tables);
// echo "tables="; print_r($tables); echo "<br>";
// echo "table links="; print_r($this->tableLinks); echo "<br>";
$options = "";
foreach ($tableNames as $tableName) {
    $options .= "<option value='$tableName'>$tableName</option>";
}
?>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addTableClicked(this)'>
    Add table</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.resetTableClicked(this)'>
    Reset</button>
<div class='select-tables form'>
    <p></p>
    <label class="checkbox-label">
        <input type="checkbox" class="distinct-checkbox" name="<?php echo $this->name; ?>_distinct" 
            <?php echo $distinct ? "checked" : ""; ?>
            onchange="<?php echo $name; ?>.distinctChkChanged(this)">Select distinct
    </label>
    <p></p>
</div>

<div class='dom-templates' style="display:none">
    <div class='item-template form-group-template form-group'></div>
    <select class='select-template custom-select form-control'></select>
    <input type="hidden" class="hidden-input-template" />
    <option class='option-template'></option>
    <span class="btn-delete-wrapper delete-template">
        <button type='button' class='btn btn-light btn-delete' onclick='<?php echo $name; ?>.deleteTableClicked(this)'>Delete</button>
    </span>
</div>