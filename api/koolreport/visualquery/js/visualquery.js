var KoolReport = KoolReport || {};
KoolReport.VisualQuery = KoolReport.VisualQuery || (function (global) {
    var vq = {};

    vq.getState = function () {
        var selectTablesDiv = this.selectTablesDiv;
        var selector = this.selector
        var selectDivs = selectTablesDiv.querySelectorAll(`select.select-table`);
        var selectTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);

        var selectDivs = selectTablesDiv.querySelectorAll(`select.select-fields`);
        var selectFields = Array.from(selectDivs).reduce((acc, select) => {
            var table = select.dataset.table;
            var options = Array.from(select.selectedOptions);
            return acc.concat(options.map(option => table + '.' + option.value));
        }, []);

        var filterDivs = this.dom.querySelectorAll('.select-filters .filter-template');
        var filters = Array.from(filterDivs).map(filterDiv => {
            var field = filterDiv.querySelector('.filter-field').selectedOptions[0].value;
            var op = filterDiv.querySelector('.filter-op').selectedOptions[0].value;
            var value1 = filterDiv.querySelector('.filter-value-1').value;
            var value2 = filterDiv.querySelector('.filter-value-2').value;
            var type = filterDiv.querySelector('.filter-type').value;
            return [field, op, value1, value2, type];
        });

        var groupDivs = this.dom.querySelectorAll('.select-groups .group-template');
        var groups = Array.from(groupDivs).map(groupDiv => {
            var field = groupDiv.querySelector('.group-field').selectedOptions[0].value;
            var agg = groupDiv.querySelector('.group-agg').selectedOptions[0].value;
            return [field, agg];
        });

        var sortDivs = this.dom.querySelectorAll('.select-sorts .sort-template');
        var sorts = Array.from(sortDivs).map(sortDiv => {
            var fieldOpt = sortDiv.querySelector('.sort-field').selectedOptions[0];
            var field = fieldOpt.value;
            var agg = fieldOpt.dataset.agg || '';
            var dir = sortDiv.querySelector('.sort-dir').selectedOptions[0].value;
            return [field, dir, agg];
        });

        var offset = this.limitChk.checked ? this.offsetInput.value : null;
        var limit = this.limitChk.checked ? this.limitInput.value : null;

        var data = {
            ajaxCall: 1,
            fetchData: 1,
            selectFields: selectFields,
            selectTables: selectTables,
            filters: filters,
            groups: groups,
            sorts: sorts,
            offset: offset,
            limit: limit,
        };
        return data;
    }

    vq.fetchDataClicked = function (btn) {
        var that = this;
        function reqListener() {
            // console.log(this.responseText);
            var res = this.responseText;
            var startMark = `###${that.name}-begin###`;
            var endMark = `###${that.name}-end###`;
            var start = res.indexOf(startMark);
            var end = res.indexOf(endMark);
            that.resultStr = res.substring(start + startMark.length, end);
            console.log(that.resultStr);
            var result = JSON.parse(that.resultStr);
            that.resultData = result.data;
            that.resultColumns = result.columns;

            // that.buildDataTable(that.resultColumns, that.resultData);            
        }

        // var data = new FormData();
        // data.append('ajaxCall', 1);
        // data.append('fetchFields', 1);
        var data = this.getState();
        console.log('fetchData request data = ', data);
        var oReq = new XMLHttpRequest();
        oReq.open("POST", location.href);
        oReq.addEventListener("load", reqListener);
        oReq.setRequestHeader("Content-Type", "application/json");
        oReq.send(JSON.stringify(data));
    }

    vq.addTableAndFields = function (selectedTable, selectedFields) {
        var tables = this.tables;
        var selectTablesDiv = this.selectTablesDiv;
        var selects = selectTablesDiv.querySelectorAll('select.select-table');
        var currentTables = Array.from(selects).map(v => {
            v.disabled = true;
            return v.selectedOptions[0].value;
        });
        // console.log('selected tables = ', tables);
        var linkedTables = [];
        if (currentTables.length === 0) {
            var join = "";
            linkedTables = this.tableNames;
        } else {
            var join = "join ";
        }
        for (var i = 0; i < currentTables.length; i += 1) {
            var currentTable = currentTables[i];
            // if (linkedTables.indexOf(table) === -1)
            //     linkedTables.push(table);
            var tmpLinkedTables = this.tableLinks[currentTable];
            // for (var j=0; j<tmpLinkedTables.length; j+=1) {
            for (var p in tmpLinkedTables)
                if (tmpLinkedTables.hasOwnProperty(p)) {
                    var tmpLinkedTable = p;
                    if (linkedTables.indexOf(tmpLinkedTable) === -1
                        && currentTables.indexOf(tmpLinkedTable) === -1)
                        linkedTables.push(tmpLinkedTable);
                }
        }
        // console.log('linked tables = ', linkedTables);
        var tableSelect = this.selectTpl.cloneNode(true);
        var hiddenInput = this.hiddenInputTpl.cloneNode(true);
        
        // tableSelect.name = this.name + '_selectTables[]';
        hiddenInput.name = this.name + '_selectTables[]';
        tableSelect.dataset.id = hiddenInput.dataset.id = this.selectId;
        tableSelect.classList.add('select-table');
        tableSelect.addEventListener('change', this.tableChanged.bind(this));
        for (var i = 0; i < linkedTables.length; i += 1) {
            var linkTable = linkedTables[i];
            var meta = tables[linkTable]['{meta}'];
            var tableAlias = meta.alias || linkTable;
            var option = this.optionTpl.cloneNode(true);
            option.value = linkTable;
            if (selectedTable === option.value) {
                option.selected = true;
            } 
            if (! selectedTable && i === 0) {
                option.selected = true;
                selectedTable = option.value;
            }
            option.textContent = tableAlias;
            tableSelect.appendChild(option);
        }

        hiddenInput.value = selectedTable;

        var table0 = selectedTable ? selectedTable : linkedTables[0];
        var fieldSelect = this.selectTpl.cloneNode(true);
        fieldSelect.name = this.name + '_selectFields[]';
        fieldSelect.id = `select-fields-${this.selectId}`;
        fieldSelect.classList.add('select-fields');
        fieldSelect.setAttribute('multiple', true);
        fieldSelect.dataset.table = table0;
        var fields = Object.keys(tables[table0]);
        for (var i = 0; i < fields.length; i += 1) {
            if (fields[i] === "{meta}") continue;
            var field = tables[table0][fields[i]];
            // console.log("field = ", field);
            var option = this.optionTpl.cloneNode(true);
            option.value = table0 + '.' + fields[i];
            if (selectedFields
                && selectedFields.indexOf(option.value) !== -1)
                option.selected = true;
            option.textContent = field.alias || fields[i];
            fieldSelect.appendChild(option);
        }

        var formGroup = this.formGroupTpl.cloneNode(true);
        formGroup.textContent = join;
        formGroup.appendChild(tableSelect);
        formGroup.appendChild(hiddenInput);
        formGroup.appendChild(fieldSelect);
        selectTablesDiv.appendChild(formGroup);
        $(`${this.selector} #select-fields-${this.selectId}`).multiselect({
            numberDisplayed: 5,
            includeSelectAllOption: true,
            enableFiltering: true,
        });
        this.selectId += 1;
    }

    vq.addTableClicked = function (btn) {
        // console.log(btn);
        this.addTableAndFields();
    }

    vq.tableChanged = function (e) {
        // console.log(e);
        this.selectFiltersDiv.innerHTML = "<p></p>";
        this.selectSortsDiv.innerHTML = "<p></p>";
        this.selectGroupsDiv.innerHTML = "<p></p>";

        var tables = this.tables;
        var select = e.currentTarget;
        var hiddenInput = select.nextElementSibling;

        var selectId = select.dataset.id;
        var selectedTable = select.selectedOptions[0].value;
        hiddenInput.value = selectedTable;
        var fieldSelect = this.dom.querySelector(`#select-fields-${selectId}`);
        fieldSelect.innerHTML = "";
        var fields = Object.keys(tables[selectedTable]);
        fieldSelect.dataset.table = selectedTable;
        for (var i = 0; i < fields.length; i += 1) {
            if (fields[i] === "{meta}") continue;
            var option = this.optionTpl.cloneNode(true);
            var field = tables[selectedTable][fields[i]];
            option.value = selectedTable + "." + fields[i];
            option.textContent = field.alias || fields[i];
            fieldSelect.appendChild(option);
        }
        $(`${this.selector} #select-fields-${selectId}`).multiselect('rebuild');
    }

    vq.fetchTableFieldsClicked = function (btn) {
        var that = this;
        function reqListener() {
            // console.log(this.responseText);
            var res = this.responseText;
            var startMark = `###${that.name}-begin###`;
            var endMark = `###${that.name}-end###`;
            var start = res.indexOf(startMark);
            var end = res.indexOf(endMark);
            var result = res.substring(start + startMark.length, end);
            result = JSON.parse(result);
            // console.log(JSON.stringify(result, undefined, 4));

            var tableFields = result.tableFields;
            var selectTablesDiv = that.selectTablesDiv;
            var selects = selectTablesDiv.querySelectorAll('select.select-table');
            for (var i = 0; i < selects.length; i += 1) {
                var select = selects[i];
                var table = select.selectedOptions[0].value;
                var fields = tableFields[table];
                var options = Object.keys(fields).reduce(
                    (acc, v) => acc + `<option value='${v}'>${v}</option>`, "");
                var id = select.dataset.id;
                var fieldSelect = this.dom.querySelector(`#select-fields-${id}`);
                fieldSelect.dataset.table = table;
                var selector = this.selector;
                $(`${selector} #select-fields-${id}`).html("");
                $(`${selector} #select-fields-${id}`).append(options);
                $(`${selector} #select-fields-${id}`).multiselect('rebuild');
            }
            tableFieldsResult = result;
        }
        // var data = new FormData();
        // data.append('ajaxCall', 1);
        // data.append('fetchFields', 1);
        var selects = selectTablesDiv.querySelectorAll('select.select-table');
        var tables = Array.from(selects).map(v => v.selectedOptions[0].value);
        // data.append('selectTables', tables);
        var data = {
            ajaxCall: 1,
            fetchTableFields: 1,
            selectTables: tables
        };
        var oReq = new XMLHttpRequest();
        oReq.open("POST", location.href);
        oReq.addEventListener("load", reqListener);
        oReq.setRequestHeader("Content-Type", "application/json");
        oReq.send(JSON.stringify(data));
    }

    vq.resetTableClicked = function (btn) {
        // console.log(btn);
        this.selectTablesDiv.innerHTML = "<p></p>";
        this.selectFiltersDiv.innerHTML = "<p></p>";
        this.selectSortsDiv.innerHTML = "<p></p>";
        this.selectGroupsDiv.innerHTML = "<p></p>";
        // this.selectFiltersDiv.innerHTML = "<p></p>";
    }

    vq.addSort = function (sort) {
        var sortField, sortDir;
        if (sort) {
            sortField = sort[0];
            sortDir = sort[1];
        }
        var newSort = this.sortTplDiv.cloneNode(true);
        if (this.autoFetchFields) {
            var tableFields = tableFieldsResult.tableFields;
        } else {
            var selectDivs = this.selectTablesDiv.querySelectorAll('select.select-table');
            // var selectedTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);
            var tableFields = Array.from(selectDivs).map(selectDiv => {
                var selectId = selectDiv.dataset.id;
                var table = selectDiv.selectedOptions[0].value;
                var tableAlias = selectDiv.selectedOptions[0].textContent;
                var fieldsDiv = this.dom.querySelector(`#select-fields-${selectId}`);
                var fields = Array.from(fieldsDiv.selectedOptions).map(option => option.value);
                var fieldAliases = Array.from(fieldsDiv.selectedOptions).map(option => option.textContent);
                var values = fields;
                return {
                    table: table,
                    tableAlias: tableAlias,
                    fields: fields,
                    fieldAliases: fieldAliases,
                    values: values,
                };
            });
            var groupDivs = this.dom.querySelectorAll('.select-groups .group-template');
            var groupFields = Array.from(groupDivs).map(groupDiv => {
                var field = groupDiv.querySelector('.group-field').selectedOptions[0].value;
                var agg = groupDiv.querySelector('.group-agg').selectedOptions[0].value;
                return agg + '(' + field + ')';
            });
            var groupAliases = Array.from(groupDivs).map(groupDiv => {
                var alias = groupDiv.querySelector('.group-field').selectedOptions[0].textContent;
                var agg = groupDiv.querySelector('.group-agg').selectedOptions[0].value;
                return agg + '(' + alias + ')';
            });
            tableFields.push({
                table: 'group',
                fields: groupFields,
                fieldAliases: groupAliases,
            });
        }
        var options = '';
        for (var i = 0; i < tableFields.length; i += 1) {
            var table = tableFields[i].table;
            var tableAlias = tableFields[i].tableAlias || table;;
            var fields = tableFields[i].fields;
            var fieldAliases = tableFields[i].fieldAliases || fields;
            var values = tableFields[i].values || fields;
            var tableOptions = "";
            for (var j = 0; j < fields.length; j += 1) {
                var field = fields[j];
                var fieldAlias = fieldAliases[j];
                var value = values[j];
                var selected = value === sortField ? 'selected' : '';
                tableOptions += `<option value='${value}' ${selected}>${fieldAlias}</option>`;
            }
            if (tableOptions) {
                options += `<optgroup label="${tableAlias}">${tableOptions}</optgroup>`;
            }
        }
        var selectSort = newSort.querySelector('.sort-field');
        selectSort.name = this.name + '_sort_fields[]';
        selectSort.innerHTML = options;

        var dirSelect = newSort.querySelector('.sort-dir');
        dirSelect.name = this.name + '_sort_directions[]';
        if (sortDir) dirSelect.value = sortDir;

        newSort.style.display = '';
        this.selectSortsDiv.appendChild(newSort);
    }

    vq.addSortClicked = function (btn) {
        // console.log(btn);
        this.addSort();
    }

    vq.resetSortClicked = function (btn) {
        // console.log(btn);
        this.selectSortsDiv.innerHTML = "<p></p>";
    }

    vq.limitChkChanged = function (chk) {
        this.offsetInput.disabled = this.limitInput.disabled = chk.checked ? false : true;
    }

    vq.addGroup = function (group) {
        var groupField, groupOp;
        if (group) {
            groupField = group[0];
            groupOp = group[1];
        }
        var tables = this.tables;
        var newGroup = this.groupTplDiv.cloneNode(true);
        if (this.autoFetchFields) {
            var tableFields = tableFieldsResult.tableFields;
        } else {
            var selectDivs = this.selectTablesDiv.querySelectorAll('select.select-table');
            var selectedTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);
            var tableFields = {};
            for (var i = 0; i < selectedTables.length; i += 1) {
                var table = selectedTables[i];
                tableFields[table] = tables[table];
            }
        }
        var options = '';
        for (var table in tableFields) {
            var tableOptions = "";
            var tableInfo = tableFields[table];
            var meta = tableInfo['{meta}'] || {};
            var tableAlias = meta.alias || table;
            for (var field in tableFields[table]) {
                if (field === "{meta}") continue;
                var fieldAlias = tableInfo[field].alias || field;
                var selected = table + '.' + field === groupField ? 'selected' : '';
                tableOptions +=
                    `<option value='${table}.${field}' ${selected}>${fieldAlias}</option>`;
            }
            options += `<optgroup label="${tableAlias}">${tableOptions}</optgroup>`;
        }
        var selectGroup = newGroup.querySelector('.group-field');
        selectGroup.name = this.name + '_group_fields[]';
        selectGroup.innerHTML = options;

        var opSelect = newGroup.querySelector('.group-agg');
        opSelect.name = this.name + '_group_aggregates[]';
        if (groupOp) opSelect.value = groupOp;

        newGroup.style.display = '';
        this.selectGroupsDiv.appendChild(newGroup);
    }

    vq.addGroupClicked = function (btn) {
        // console.log(btn);
        this.addGroup();
    }

    vq.resetGroupClicked = function (btn) {
        // console.log(btn);
        this.selectGroupsDiv.innerHTML = "<p></p>";
    }

    vq.addFilter = function (filter) {
        var filterField, op, value1, value2, type;
        if (filter) {
            filterField = filter[0];
            op = filter[1];
            value1 = filter[2];
            value2 = filter[3];
            type = filter[4];
        }
        var tables = this.tables;
        var newFilter = this.filterTplDiv.cloneNode(true);
        if (this.autoFetchFields) {
            var tableFields = tableFieldsResult.tableFields;
        } else {
            var selectDivs = this.selectTablesDiv.querySelectorAll('select.select-table');
            var selectedTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);
            var tableFields = {};
            for (var i = 0; i < selectedTables.length; i += 1) {
                var table = selectedTables[i];
                tableFields[table] = tables[table];
            }
        }
        var options = '';
        for (var table in tableFields) {
            var tableOptions = "";
            var tableInfo = tableFields[table];
            var meta = tableInfo['{meta}'] || {};
            var tableAlias = meta.alias || table;
            for (var field in tableInfo) {
                if (field === "{meta}") continue;
                var fieldAlias = tableInfo[field].alias || field;
                var selected = table + '.' + field === filterField ? 'selected' : '';
                tableOptions +=
                    `<option value='${table}.${field}' ${selected}>${fieldAlias}</option>`;
            }
            options += `<optgroup label="${tableAlias}">${tableOptions}</optgroup>`;
        }
        var selectFilter = newFilter.querySelector('.filter-field');
        selectFilter.name = this.name + '_filter_fields[]';
        selectFilter.innerHTML = options;

        var opInput = newFilter.querySelector('.filter-op');
        opInput.name = this.name + '_filter_operators[]';
        if (filter) opInput.value = op;

        var value1Input = newFilter.querySelector('.filter-value-1');
        value1Input.name = this.name + '_filter_value1s[]';
        if (filter) value1Input.value = value1;

        var value2Input = newFilter.querySelector('.filter-value-2');
        value2Input.name = this.name + '_filter_value2s[]';
        if (filter) {
            value2Input.value = value2;
            value2Input.style.display = op === 'btw' || op === 'nbtw' ? '' : 'none';
        }

        var typeSelect = newFilter.querySelector('.filter-type');
        typeSelect.name = this.name + '_filter_types[]';
        if (filter) typeSelect.value = type;

        if (this.selectFiltersDiv.querySelectorAll('.filter-template').length === 0) {
            typeSelect.style.display = 'none';
        }

        newFilter.style.display = '';
        this.selectFiltersDiv.appendChild(newFilter);
    }

    vq.addFilterClicked = function (btn, type) {
        // console.log(btn);
        this.addFilter();
    }

    vq.resetFilterClicked = function (btn) {
        // console.log(btn);
        this.selectFiltersDiv.innerHTML = "<p></p>";
    }

    vq.filterOpChanged = function (sel) {
        console.log(sel);
        var op = sel.selectedOptions[0].value;
        var filter = sel.parentElement;
        var filterValue2 = filter.querySelector('.filter-value-2');
        filterValue2.style.display = op === 'btw' || op === 'nbtw' ? '' : 'none';
    }

    function downloadObjectAsJson(exportObj, exportName) {
        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportObj));
        var downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", exportName + ".json");
        document.body.appendChild(downloadAnchorNode); // required for firefox
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
    }

    function download(content, fileName, contentType) {
        var a = document.createElement("a");
        var file = new Blob([content], { type: contentType });
        a.href = URL.createObjectURL(file);
        a.download = fileName;
        a.click();
    }

    function readSingleFile(e) {
        var file = e.target.files[0];
        if (!file) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            var contents = e.target.result;
            displayContents(contents);
        };
        reader.readAsText(file);
    }

    function displayContents(contents) {
        var element = document.getElementById('file-content');
        element.textContent = contents;
    }

    // document.getElementById('file-input')
    // .addEventListener('change', readSingleFile, false);
    // <input type="file" id="file-input" />
    // <h3>Contents of the file:</h3>
    // <pre id="file-content"></pre>

    vq.buildSelectTables = function (tables, fields) {
        for (var i = 0; i < tables.length; i += 1) {
            var table = tables[i];
            var tableFields = fields.filter(field => {
                var fieldTable = field.split(".")[0];
                return fieldTable === table;
            });
            this.addTableAndFields(table, tableFields);
        }
    }

    vq.buildSelectFilters = function (filters) {
        for (var i = 0; i < filters.length; i += 1) {
            var filter = filters[i];
            this.addFilter(filter);
        }
    }

    vq.buildSelectGroups = function (groups) {
        for (var i = 0; i < groups.length; i += 1) {
            var group = groups[i];
            this.addGroup(group);
        }
    }

    vq.buildSelectSorts = function (sorts) {
        for (var i = 0; i < sorts.length; i += 1) {
            var sort = sorts[i];
            this.addSort(sort);
        }
    }

    vq.buildSelectLimit = function (offset, limit) {
        this.offsetInput.value = offset;
        this.limitInput.value = limit;
    }

    vq.update = function () {
        this.stateInput.value = JSON.stringify(this.getState());
    }

    var func = function () {
    };

    var init = function (data) {
        for (var p in data)
            if (data.hasOwnProperty(p))
                this[p] = data[p];

        var selector = this.selector = `krwidget[widget-name="${this.name}"]`;
        $(`${selector} .select-label-column`).multiselect({
            numberDisplayed: 5,
            includeSelectAllOption: true,
            enableFiltering: true,
        });
        $(`${selector} .select-data-columns`).multiselect({
            numberDisplayed: 5,
            includeSelectAllOption: true,
            enableFiltering: true,
        });

        this.autoFetchFields = false;
        this.reorderEventListenerAdded = false;
        this.selectId = 0;

        var dom = this.dom = document.querySelector(selector);
        this.stateInput = dom.querySelector(`input[name="${this.name + '_input'}"]`);
        this.selectTablesDiv = dom.querySelector('.select-tables');
        var domTpl = dom.querySelector(`.dom-templates`);
        this.formGroupTpl = domTpl.querySelector(`.form-group-template`);
        this.selectTpl = domTpl.querySelector(`.select-template`);
        this.optionTpl = domTpl.querySelector(`.option-template`);
        this.selectSortsDiv = dom.querySelector(`.select-sorts`);
        this.sortTplDiv = dom.querySelector(`.sort-template`);
        this.limitChk = dom.querySelector(`input[name="${this.name}_limit_enabled"]`);
        this.offsetInput = dom.querySelector(`input[name="${this.name}_offset"]`);
        this.limitInput = dom.querySelector(`input[name="${this.name}_limit"]`);
        this.selectGroupsDiv = dom.querySelector(`.select-groups`);
        this.groupTplDiv = dom.querySelector(`.group-template`);
        this.selectFiltersDiv = dom.querySelector(`.select-filters`);
        this.filterTplDiv = dom.querySelector(`.filter-template`);
        this.hiddenInputTpl = dom.querySelector(`.hidden-input-template`);

        var value = this.value ? this.value : (this.defaultValue ? this.defaultValue : null);

        if (value) {
            this.buildSelectTables(value.selectTables, value.selectFields);
            this.buildSelectFilters(value.filters);
            this.buildSelectGroups(value.groups);
            this.buildSelectSorts(value.sorts);
            this.buildSelectLimit(value.offset, value.limit);
        }
    }

    var visualqueryFunctions = (function () {
        return function () {
            this.func = func;
            this.init = init;
            for (var p in vq)
                if (vq.hasOwnProperty(p))
                    this[p] = vq[p];
        };
    })();

    var VisualQuery = function () { };
    visualqueryFunctions.call(VisualQuery.prototype);

    return {
        create: function (vq_data) {
            var vq = new VisualQuery();
            vq.init(vq_data);
            return vq;
        }
    }
})(window);