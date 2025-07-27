<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addGroupClicked(this)'>
    Add group</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.resetGroupClicked(this)'>
    Reset</button>

<div class='select-groups form'>
    <p></p>
</div>

<div style="margin-top: 40px"></div>

<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addHavingClicked(this)'>
    Add having</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addHavingBracketsClicked(this)'>
    Add brackets</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.resetHavingClicked(this)'>
    Reset</button>

<div class='select-havings form'>
    <p></p>
</div>

<div class='item-template group-template form-group' style='display:none'>
    <p></p>
    <label class="checkbox-label">
        <input type="checkbox" class="group-toggle-checkbox toggle-checkbox item-toggle" checked 
            onchange='<?php echo $name; ?>.toggleGroupClicked(this)'>&nbsp;
        <input type="hidden" class="group-toggle" value="on" />
    </label>
    <select class='custom-select group-agg form-control'>
        <option value='sum'>sum</option>
        <option value='count'>count</option>
        <option value='count_distinct'>count distinct</option>
        <option value='avg'>avg</option>
        <option value='min'>min</option>
        <option value='max'>max</option>
    </select>
    <select class='custom-select group-field form-control'></select>
    <span class="btn-clone-wrapper">
        <button type='button' class='btn btn-light btn-clone' 
            onclick='<?php echo $name; ?>.cloneGroupClicked(this)'>Clone</button>
    </span>
    <span class="btn-delete-wrapper">
        <button type='button' class='btn btn-light btn-delete' 
            onclick='<?php echo $name; ?>.deleteItemClicked(this)'>Delete</button>
    </span>
    <input type="hidden" class="group-validity" value="1" />
</div>