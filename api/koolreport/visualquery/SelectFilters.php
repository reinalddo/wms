<style>
    .select-filters .custom-select,
    .select-filters input[type="text"], 
    .select-filters input[type="number"] 
    {
        vertical-align: bottom;
    }


</style>

<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addFilterClicked(this)'>
    Add filter</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.addFilterBracketsClicked(this)'>
    Add brackets</button>
<button type='button' class='btn btn-light' onclick='<?php echo $name; ?>.resetFilterClicked(this)'>
    Reset</button>

<div class='select-filters form'>
    <p></p>
</div>
<div class='item-template filter-template form-group' style='display:none'>
    <label class="checkbox-label">
        <input type="checkbox" class="filter-toggle-checkbox toggle-checkbox item-toggle" checked 
            onchange='<?php echo $name; ?>.toggleFilterClicked(this)'>&nbsp;
        <input type="hidden" class="filter-toggle" value="on" />
    </label>
    <select class='custom-select filter-logic form-control'>
        <option value='and' selected> AND </option>
        <option value='or'> OR </option>
    </select>
    <select class='custom-select filter-field form-control' onchange='<?php echo $name; ?>.filterFieldChanged(this)'></select>
    <select class='custom-select filter-op form-control' onchange='<?php echo $name; ?>.filterOpChanged(this)'>
        <option value='=' selected> = </option>
        <option value='<>'> != </option>
        <option value='>'> > </option>
        <option value='>='> >= </option>
        <option value='<'>
            < </option>
        <option value='<='>
            <= </option>
        <option value='btw'> between </option>
        <option value='nbtw'> not between </option>
        <option value='ctn'> contains </option>
        <option value='nctn'> not contains </option>
        <option value='null'> null </option>
        <option value='nnull'> not null </option>
        <option value='in'> in </option>
        <option value='nin'> not in </option>
    </select>
    <input class='form-control filter-value-1' type='text' />
    <input class='form-control filter-value-2' style='display:none' type='text' />
    <input type="hidden" class="filter-bracket" value="expression" />
    <input type="hidden" class="filter-validity" value="1" />

    <div class="form-group bs4 filter-date-1" style="margin: 0; width: 300px; display:none">
        <div class="input-group date " id="dtp" data-target-input="nearest">
            <input type="text" data-target="#dtp" 
                class="form-control datetimepicker-input filter-date-value-1" />
            <div class="input-group-append" data-target="#dtp" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="glyphicon glyphicon-calendar fa fa-calendar"></i></div>
            </div>
        </div>
    </div>

    <div class="form-group bs4 filter-date-2" style="margin: 0; width: 270px; display:none;">
        <div class="input-group date" id="dtp" data-target-input="nearest">
            <input type="text" data-target="#dtp" class="form-control datetimepicker-input filter-date-value-2" />
            <div class="input-group-append" data-target="#dtp" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="glyphicon glyphicon-calendar fa fa-calendar"></i></div>
            </div>
        </div>
    </div>

    <div class="form-group bs3 filter-date-1" style="margin: 0; width: 270px; display:none; vertical-align: middle;">
        <div class='input-group date'>
            <input type='text' class="form-control filter-date-value-1" 
                style="width: 100%" />
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar fa fa-calendar"></span>
            </span>
        </div>
    </div>

    <div class="form-group bs3 filter-date-2" style="margin: 0; width: 270px; display:none; vertical-align: middle;">
        <div class='input-group date'>
            <input type='text' class="form-control filter-date-value-2" 
                style="width: 100%" />
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar fa fa-calendar"></span>
            </span>
        </div>
    </div>

    <span class="btn-clone-wrapper">
        <button type='button' class='btn btn-light btn-clone' 
            onclick='<?php echo $name; ?>.cloneFilterClicked(this)'>Clone</button>
    </span>
    <span class="btn-delete-wrapper">
        <button type='button' class='btn btn-light btn-delete' 
            onclick='<?php echo $name; ?>.deleteItemClicked(this)'>Delete</button>
    </span>
    
</div>
<div class="item-template bracket-template" style="display:none" data-type="open-bracket">
    <b>(</b>
    <input type="hidden" class="filter-bracket" value="(" />
    <span class="btn-delete-wrapper">
        <button type='button' class='btn btn-light btn-delete' 
            onclick='<?php echo $name; ?>.deleteBracketsClicked(this)'>Delete</button>
    </span>
</div>
<div class="item-template bracket-template" style="display:none" data-type="close-bracket">
    <b>)</b>
    <input type="hidden" class="filter-bracket" value=")" />
    <span class="btn-delete-wrapper">
        <button type='button' class='btn btn-light btn-delete' 
            onclick='<?php echo $name; ?>.deleteBracketsClicked(this)'>Delete</button>
    </span>
</div>