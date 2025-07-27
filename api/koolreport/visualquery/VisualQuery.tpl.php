<style>
    .custom-select,
    .custom-control,
    .filter-template input[type="text"],
    .filter-template input[type="number"] {
        display: inline-block;
        width: auto;
    }

    .visual-query-form {
        padding: 15px;
    }

    .tab-pane {
        border: solid 1px #dee2e6;
        margin-top: -2px;
        min-height: 60px;
        padding: 15px;
    }

    .item-template {
        cursor: pointer;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .hidden-input {
        display: none;
    }

    .disabled-input {
        opacity: 0.5;
        pointer-events: none;
    }

    .item-template {
        padding-left: 10px;
    }

    .item-template:hover,
    .item-template:active {
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
    }

    .btn-clone,
    .btn-delete {
        visibility: hidden;
    }

    .item-template:hover .btn-clone,
    .item-template:hover .btn-clone,
    .item-template:hover .btn-delete,
    .item-template:active .btn-delete {
        visibility: visible;
    }

    .item-template.disable-table:hover .btn-clone,
    .item-template.disable-table:hover .btn-clone,
    .item-template.disable-table:hover .btn-delete,
    .item-template.disable-table:active .btn-delete {
        visibility: hidden;
    }

    label.checkbox-label input[type=checkbox] {
        width: 25px;
        position: relative;
        vertical-align: middle;
        /* bottom: 1px; */
    }

    .multiselect-group.invalid-item,
    .multiselect-option.invalid-item label,
    .multiselect-native-select.invalid-item .multiselect-selected-text {
        text-decoration: line-through;
        color: darkred;
    }

    /* .select-filters .multiselect-container.dropdown-menu {
        min-width: 17rem;
    } */

    krwidget[widget-name="<?php echo $this->name; ?>"] .multiselect-group.dropdown-item-text 
    {
        font-weight: bold;
    }

    krwidget[widget-name="<?php echo $this->name; ?>"] .multiselect-filter input.multiselect-search 
    {
        width: 50%;
    }

    krwidget[widget-name="<?php echo $this->name; ?>"] .multiselect-option.dropdown-item input[type=radio] 
    {
        visibility: hidden;
    }

    krwidget[widget-name="<?php echo $this->name; ?>"] .multiselect-option.dropdown-item label 
    {
        padding-left: 10px;
    }
</style>
<!-- <style>body { visibility: hidden; opacity: 0; }</style> -->
<?php
$queryTabs = [
    [
        'title' => 'Tables',
        'text' => 'Select and join tables here',
        'include' => "SelectTables.php",
    ],
    [
        'title' => 'Filters',
        'text' => 'Add filters here (filters and brackets are draggable)',
        'include' => "SelectFilters.php",
    ],
    [
        'title' => 'Groups',
        'text' => 'Set groups by fields here (groups, havings, and brackets are draggable)',
        'include' => "SelectGroups.php",
    ],
    [
        'title' => 'Sorts',
        'text' => 'Add sorts here (sorts are draggable)',
        'include' => "SelectSorts.php",
    ],
    [
        'title' => 'Limit',
        'text' => 'Set row\'s offset and limit here',
        'include' => "SelectLimit.php",
    ],
];
?>
<div class="visual-query-form">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <?php
        $value = $this->value ?? $this->defaultValue;
        foreach ($queryTabs as $i => $tab) {
            $title = $tab['title'];
            $active = $this->activeTab === strtolower($title) ? 'active' : '';
            echo "<li class='nav-item'>
                <a class='nav-link $active' id='$name-$title-tab' data-toggle='tab' href='#$name-$title'
                    role='tab' aria-controls='profile' aria-selected='false'>$title</a>
            </li>";
        }
        ?>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <?php
        foreach ($queryTabs as $i => $tab) {
            $title = $tab['title'];
            $text = $tab['text'];
            $include = $tab['include'];
            $active = $this->activeTab === strtolower($title) ? 'active' : '';
            echo "<div class='tab-pane $active' id='$name-$title' role='tabpanel' 
                aria-labelledby='$name-$title-tab'>$text <br>";
            include $include;
            echo "</div>";
        }
        ?>
    </div>

    <div class="modal fade" id="<?php echo $this->name; ?>_confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    Confirm deleting
                </div>
                <div class="modal-body">
                    Are you sure to delete this item?
                </div>
                <div class="modal-footer">
                    <a class="btn btn-secondary btn-ok" 
                    data-dismiss="modal" onclick="<?php echo $this->name; ?>.confirmDeleteClicked()">Delete</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <input type='hidden' name='visualqueries[]' value='<?php echo $this->name; ?>' />
    <input type='hidden' name='<?php echo $this->name; ?>_schema' value='<?php echo json_encode($this->schema); ?>' />
    <input type='hidden' name='<?php echo $this->name; ?>_activeTab' value='<?php echo $this->activeTab; ?>' />
</div>
<script type="text/javascript">
    var vqTheme = '<?php echo $this->themeBase; ?>';
    KoolReport.widget.init(<?php echo json_encode($this->getResources()); ?>, function() {
        <?php echo $this->name; ?>_data = {
            name: '<?php echo $this->name; ?>',
            tableNames: <?php echo json_encode($tableNames); ?>,
            tables: <?php echo json_encode($this->tables); ?>,
            tableLinks: <?php echo json_encode($this->tableLinks); ?>,
            defaultValue: <?php echo json_encode($this->defaultValue); ?>,
            value: <?php echo json_encode($this->value); ?>,
            separator: '<?php echo $this->separator; ?>',
        }
        <?php echo $name; ?> = KoolReport.VisualQuery.create(<?php echo $name; ?>_data);
        <?php $this->clientSideReady(); ?>

        document.body.style.visibility = 'visible';
        document.body.style.opacity = 1;
    });
</script>
<noscript>
    <style>
        body {
            visibility: visible;
            opacity: 1;
        }
    </style>
</noscript>