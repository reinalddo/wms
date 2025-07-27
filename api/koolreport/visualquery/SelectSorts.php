<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addSortClicked(this)'>
    Add sort</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.resetSortClicked(this)'>
    Reset</button>

<div class='select-sorts form'>
    <p></p>
</div>
<div class='item-template sort-template form-group' style='display:none'>
    <p></p>
    <label class="checkbox-label">
        <input type="checkbox" class="sort-toggle-checkbox toggle-checkbox item-toggle" checked 
            onchange='<?php echo $name; ?>.toggleSortClicked(this)'>&nbsp;
        <input type="hidden" class="sort-toggle" value="on" />
    </label>
    <select class='custom-select sort-field form-control'></select>
    <select class='custom-select sort-dir form-control'>
        <option value='asc'>asc</option>
        <option value='desc'>desc</option>
    </select>
    <span class="btn-clone-wrapper">
        <button type='button' class='btn btn-light btn-clone' 
            onclick='<?php echo $name; ?>.cloneSortClicked(this)'>Clone</button>
    </span>
    <span class="btn-delete-wrapper">
        <button type='button' class='btn btn-light btn-delete' 
            onclick='<?php echo $name; ?>.deleteItemClicked(this)'>Delete</button>
    </span>
    <input type="hidden" class="sort-validity" value="1" />
</div>


