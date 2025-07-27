var KoolReport = KoolReport || {};
KoolReport.VisualQuery = KoolReport.VisualQuery || (function (global) {

    function getAncestor(el, selector) {
        while (el && !el.matches(selector)) el = el.parentElement;
        return el;
    }

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
            return acc.concat(options.map(option => table + this.separator + option.value));
        }, []);

        var filterDivs = this.dom.querySelectorAll('.select-filters .filter-template');
        var filters = Array.from(filterDivs).map(filterDiv => {
            var field = filterDiv.querySelector('.filter-field').selectedOptions[0].value;
            var op = filterDiv.querySelector('.filter-op').selectedOptions[0].value;
            var value1 = filterDiv.querySelector('.filter-value-1').value;
            var value2 = filterDiv.querySelector('.filter-value-2').value;
            var logic = filterDiv.querySelector('.filter-logic').value;
            return {
                field: field,
                operator: op,
                value1: value1,
                value2: value2,
                logic: logic
            };
        });

        var groupDivs = this.dom.querySelectorAll('.select-groups .group-template');
        var groups = Array.from(groupDivs).map(groupDiv => {
            var field = groupDiv.querySelector('.group-field').selectedOptions[0].value;
            var agg = groupDiv.querySelector('.group-agg').selectedOptions[0].value;
            return [field, agg];
        });

        var havingDivs = this.dom.querySelectorAll('.select-havings .filter-template');
        var havings = Array.from(havingDivs).map(havingDiv => {
            var field = havingDiv.querySelector('.filter-field').selectedOptions[0].value;
            var op = havingDiv.querySelector('.filter-op').selectedOptions[0].value;
            var value1 = havingDiv.querySelector('.filter-value-1').value;
            var value2 = havingDiv.querySelector('.filter-value-2').value;
            var logic = filterDiv.querySelector('.filter-logic').value;
            return {
                field: field,
                operator: op,
                value1: value1,
                value2: value2,
                logic: logic
            };
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
            havings: havings,
            sorts: sorts,
            offset: offset,
            limit: limit,
        };
        return data;
    };

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
    };

    vq.addTableAndFields = function (selectedTable, selectedFields) {
        var tables = this.tables;
        var selectTablesDiv = this.selectTablesDiv;
        var selects = selectTablesDiv.querySelectorAll('select.select-table');
        var currentTables = Array.from(selects).map(v => {
            // v.disabled = true;
            return v.selectedOptions[0].value;
        });
        if (currentTables.length === Object.keys(this.tables).length) {
            return;
        }
        Array.from(selects).forEach(v => {
            var itemTpl = v.parentElement;
            itemTpl.classList.add("disable-table");
            v.classList.add("disabled-input");
        });
        // console.log('selected tables = ', tables);
        var linkedTables = [];
        if (currentTables.length === 0) {
            var join = "";
            linkedTables = this.tableNames;
        } else {
            // var join = "join ";
            var join = "";
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
        // var hiddenInput = this.hiddenInputTpl.cloneNode(true);

        // tableSelect.name = this.name + '_selectTables[]';
        // hiddenInput.name = this.name + '_selectTables[]';
        // hiddenInput.dataset.id = this.selectId;
        tableSelect.name = this.name + '_selectTables[]';
        tableSelect.dataset.id = this.selectId;

        tableSelect.classList.add('select-table');
        tableSelect.addEventListener('change', this.tableChanged.bind(this));

        for (var i = 0; i < linkedTables.length; i += 1) {
            var linkTable = linkedTables[i];
            var meta = tables[linkTable]['{meta}'] || {};
            var tableAlias = meta.alias || linkTable;
            var option = this.optionTpl.cloneNode(true);
            option.value = linkTable;
            if (selectedTable === option.value) {
                option.selected = true;
            }
            if (!selectedTable && i === 0) {
                option.selected = true;
                selectedTable = option.value;
            }
            option.textContent = tableAlias;
            tableSelect.appendChild(option);
        }

        // hiddenInput.value = selectedTable;

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
            option.value = table0 + this.separator + fields[i];
            if (selectedFields
                && selectedFields.indexOf(option.value) !== -1)
                option.selected = true;
            option.textContent = field.alias || fields[i];
            fieldSelect.appendChild(option);
        }

        var btnDelete = this.deleteTpl.cloneNode(true);

        var formGroup = this.formGroupTpl.cloneNode(true);
        formGroup.textContent = join;
        formGroup.appendChild(tableSelect);
        // formGroup.appendChild(hiddenInput);
        formGroup.appendChild(fieldSelect);
        formGroup.appendChild(btnDelete);
        selectTablesDiv.appendChild(formGroup);
        $(`${this.selector} #select-fields-${this.selectId}`).multiselect({
            numberDisplayed: 5,
            includeSelectAllOption: true,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            onChange: (option, checked) => {
                console.log(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
                this.checkValidities();
            },
            onSelectAll: () => {
                console.log("select all");
                this.checkValidities();
            },
            onDeselectAll: () => {
                console.log("deselect all");
                this.checkValidities();
            },
        });
        this.selectId += 1;
    };

    vq.addTableClicked = function (btn) {
        // console.log(btn);
        this.addTableAndFields();
    };

    vq.tableChanged = function (e) {
        // console.log(e);
        // this.selectFiltersDiv.innerHTML = "<p></p>";
        // this.selectSortsDiv.innerHTML = "<p></p>";
        // this.selectGroupsDiv.innerHTML = "<p></p>";

        var tables = this.tables;
        var select = e.currentTarget;
        // var hiddenInput = select.nextElementSibling;

        var selectId = select.dataset.id;
        var selectedTable = select.selectedOptions[0].value;
        // hiddenInput.value = selectedTable;
        var fieldSelect = this.dq(`#select-fields-${selectId}`);
        fieldSelect.innerHTML = "";
        var fields = Object.keys(tables[selectedTable]);
        fieldSelect.dataset.table = selectedTable;
        for (var i = 0; i < fields.length; i += 1) {
            if (fields[i] === "{meta}") continue;
            var option = this.optionTpl.cloneNode(true);
            var field = tables[selectedTable][fields[i]];
            option.value = selectedTable + this.separator + fields[i];
            option.textContent = field.alias || fields[i];
            fieldSelect.appendChild(option);
        }
        $(`${this.selector} #select-fields-${selectId}`).multiselect('rebuild');

        this.checkValidities();
    };

    vq.isValidFilterTable = function (tableName) {
        if (this.validFilterTableFields[tableName]) return true;
        for (var table in this.validFilterTableFields) {
            var tableInfo = this.validFilterTableFields[table];
            if (tableInfo["{meta}"] && tableInfo["{meta}"].alias === tableName) return true;
        }
        return false;
    };

    vq.isValidHavingTable = function (tableName) {
        if (this.validHavingTableFields[tableName]) return true;
        for (var table in this.validHavingTableFields) {
            var tableInfo = this.validHavingTableFields[table];
            if (tableInfo["{meta}"] && tableInfo["{meta}"].alias === tableName) return true;
        }
        return false;
    };

    vq.isValidFilterField = function (value) {
        var values = value.split(this.separator);
        var tableName = values[0];
        var fieldName = values[1];
        if (this.validFilterTableFields[tableName]
            && this.validFilterTableFields[tableName][fieldName]) return true;
        return false;
    };

    vq.isValidGroupField = function (value) {
        var values = value.split(this.separator);
        var tableName = values[0];
        var fieldName = values[1];
        if (this.validGroupTableFields[tableName]
            && this.validGroupTableFields[tableName][fieldName]) return true;
        return false;
    };

    vq.getValueGroupField = function (value) {
        var values = value.split(this.separator);
        var tableName = values[0];
        var fieldName = values[1];
        return this.validGroupTableFields[tableName][fieldName];
    }

    vq.isValidHavingField = function (value, tableName) {
        var fieldName = value.replace(tableName + this.separator, "");
        if (this.validHavingTableFields[tableName]) {
            var tableInfo = this.validHavingTableFields[tableName];
            if (tableInfo[fieldName]) return true;
            for (var f in tableInfo) {
                if (tableInfo[f].alias === fieldName) return true;
            }
        }
        return false;
    };

    vq.isValidSortField = function (value, tableName) {
        var fieldName = value.replace(tableName + this.separator, "");
        if (this.validSortTableFields[tableName]
            && this.validSortTableFields[tableName][fieldName]) return true;
        return false;
    };

    vq.checkValidities = function () {
        this.buildValidFilterTableFields();

        var selectFilterFields = this.selectFiltersDiv.querySelectorAll(".filter-field");
        selectFilterFields.forEach(selectFilterField => {
            this.checkFilterFieldValidity(selectFilterField);
            this.markFilterFieldsValidities(selectFilterField);
        });

        var selectGroupFields = this.selectGroupsDiv.querySelectorAll(".group-field");
        selectGroupFields.forEach(selectGroupField => {
            this.checkGroupFieldValidity(selectGroupField);
            this.markGroupFieldsValidities(selectGroupField);
        });

        //Only build valid having fields after marking groups' invalidity
        this.buildValidHavingTableFields(); 

        var selectHavingFields = this.selectHavingsDiv.querySelectorAll(".filter-field");
        selectHavingFields.forEach(selectHavingField => {
            this.checkHavingFieldValidity(selectHavingField);
            this.markHavingFieldsValidities(selectHavingField);
        });

        var selectSortFields = this.selectSortsDiv.querySelectorAll(".sort-field");
        selectSortFields.forEach(selectSortField => {
            this.checkSortFieldValidity(selectSortField);
            this.markSortFieldsValidities(selectSortField);
        });
    };

    vq.checkFilterFieldValidity = function (selectFilterField) {
        var multiSelectWrapper = getAncestor(selectFilterField, ".multiselect-native-select");
        var itemTplWrapper = getAncestor(selectFilterField, ".item-template");
        if (this.isValidFilterField(selectFilterField.value)) {
            multiSelectWrapper.classList.remove("invalid-item");
            itemTplWrapper.querySelector("input.filter-validity").value = 1;
        } else {
            multiSelectWrapper.classList.add("invalid-item");
            itemTplWrapper.querySelector("input.filter-validity").value = 0;
        }
    };

    vq.markFilterFieldsValidities = function (selectFilterField) {
        var wrapper = getAncestor(selectFilterField, ".multiselect-native-select");
        var groups = wrapper.querySelectorAll(".multiselect-group");
        groups.forEach(group => {
            var tableName = group.textContent.trim();
            if (!this.isValidFilterTable(tableName)) group.classList.add("invalid-item");
            else group.classList.remove("invalid-item");
        });
        var options = [];
        if (vqTheme === "bs3") {
            options = wrapper.querySelectorAll("li:not(.multiselect-item)");
        } else {
            options = wrapper.querySelectorAll(".multiselect-option");
        }
        options.forEach(option => {
            var input = option.querySelector("input");
            if (this.isValidFilterField(input.value)) option.classList.remove("invalid-item");
            else option.classList.add("invalid-item");
        });
    };

    vq.checkGroupFieldValidity = function (selectGroupField) {
        var itemTplWrapper = getAncestor(selectGroupField, ".item-template");
        var selectAggregate = itemTplWrapper.querySelector(".group-agg");
        var aggregate = selectAggregate.value;

        var multiSelectWrapper = getAncestor(selectGroupField, ".multiselect-native-select");
        var itemTplWrapper = getAncestor(selectGroupField, ".item-template");
        var isValid = true;
        if (this.isValidGroupField(selectGroupField.value)) {
            var fieldInfo = this.getValueGroupField(selectGroupField.value);
            var fieldType = fieldInfo.type || "string";
            if (aggregate === "sum" && fieldType !== "number") isValid = false;
        } else {
            isValid = false;
        }
        if (isValid) {
            multiSelectWrapper.classList.remove("invalid-item");
            itemTplWrapper.querySelector("input.group-validity").value = 1;
        } else {
            multiSelectWrapper.classList.add("invalid-item");
            itemTplWrapper.querySelector("input.group-validity").value = 0;
        }
    };

    vq.markGroupFieldsValidities = function (selectGroupField) {
        var itemTplWrapper = getAncestor(selectGroupField, ".item-template");
        var selectAggregate = itemTplWrapper.querySelector(".group-agg");
        var aggregate = selectAggregate.value;

        var wrapper = getAncestor(selectGroupField, ".multiselect-native-select");

        var groups = wrapper.querySelectorAll(".multiselect-group");
        groups.forEach(group => {
            var tableName = group.textContent.trim();
            if (!this.isValidFilterTable(tableName)) group.classList.add("invalid-item");
            else group.classList.remove("invalid-item");
        });
        var options = [];
        if (vqTheme === "bs3") {
            options = wrapper.querySelectorAll("li:not(.multiselect-item)");
        } else {
            options = wrapper.querySelectorAll(".multiselect-option");
        }
        options.forEach(option => {
            var input = option.querySelector("input");
            var isValid = true;
            if (this.isValidGroupField(input.value)) {
                var fieldInfo = this.getValueGroupField(input.value);
                var fieldType = fieldInfo.type || "string";
                if (aggregate === "sum" && fieldType !== "number") isValid = false;
            } else {
                isValid = false;
            }
            if (isValid) option.classList.remove("invalid-item");
            else option.classList.add("invalid-item");
        });
    };

    vq.checkHavingFieldValidity = function (selectHavingField) {
        var multiSelectWrapper = getAncestor(selectHavingField, ".multiselect-native-select");
        var itemTplWrapper = getAncestor(selectHavingField, ".item-template");
        if (this.isValidHavingField(selectHavingField.value,
            selectHavingField.selectedOptions[0].dataset.table)) {
            multiSelectWrapper.classList.remove("invalid-item");
            itemTplWrapper.querySelector("input.filter-validity").value = 1;
        } else {
            multiSelectWrapper.classList.add("invalid-item");
            itemTplWrapper.querySelector("input.filter-validity").value = 0;
        }
    };

    vq.markHavingFieldsValidities = function (selectHavingField) {
        var wrapper = getAncestor(selectHavingField, ".multiselect-native-select");
        var havings = wrapper.querySelectorAll(".multiselect-group");
        havings.forEach(having => {
            var tableName = having.textContent.trim();
            if (!this.isValidHavingTable(tableName)) having.classList.add("invalid-item");
            else having.classList.remove("invalid-item");
        });
        var options = [];
        if (vqTheme === "bs3") {
            options = wrapper.querySelectorAll("li:not(.multiselect-item)");
        } else {
            options = wrapper.querySelectorAll(".multiselect-option");
        }
        options.forEach(option => {
            var input = option.querySelector("input");
            var havingOptions = Array.from(selectHavingField.options);
            var havingOption = havingOptions.filter(option => {
                return option.textContent.trim() === input.value || option.value === input.value;
            })[0];
            if (havingOption && this.isValidHavingField(havingOption.value, havingOption.dataset.table))
                option.classList.remove("invalid-item");
            else
                option.classList.add("invalid-item");
        });
    };

    vq.checkSortFieldValidity = function (selectSortField) {
        var multiSelectWrapper = getAncestor(selectSortField, ".multiselect-native-select");
        var itemTplWrapper = getAncestor(selectSortField, ".item-template");
        if (this.isValidHavingField(selectSortField.value,
            selectSortField.selectedOptions[0].dataset.table)) {
            multiSelectWrapper.classList.remove("invalid-item");
            itemTplWrapper.querySelector("input.sort-validity").value = 1;
        } else {
            multiSelectWrapper.classList.add("invalid-item");
            itemTplWrapper.querySelector("input.sort-validity").value = 0;
        }
    };

    vq.markSortFieldsValidities = function (selectSortField) {
        var wrapper = getAncestor(selectSortField, ".multiselect-native-select");
        var sorts = wrapper.querySelectorAll(".multiselect-group");
        sorts.forEach(sort => {
            var tableName = sort.textContent.trim();
            if (!this.isValidHavingTable(tableName)) sort.classList.add("invalid-item");
            else sort.classList.remove("invalid-item");
        });
        var options = [];
        if (vqTheme === "bs3") {
            options = wrapper.querySelectorAll("li:not(.multiselect-item)");
        } else {
            options = wrapper.querySelectorAll(".multiselect-option");
        }
        options.forEach(option => {
            var input = option.querySelector("input");
            var sortOptions = Array.from(selectSortField.options);
            var sortOption = sortOptions.filter(option => {
                return option.textContent.trim() === input.value || option.value === input.value;
            })[0];
            if (sortOption && this.isValidSortField(sortOption.value, sortOption.dataset.table))
                option.classList.remove("invalid-item");
            else
                option.classList.add("invalid-item");
        });
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
                var fieldSelect = this.dq(`#select-fields-${id}`);
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
    };

    vq.resetTableClicked = function (btn) {
        // console.log(btn);
        this.selectTablesDiv.innerHTML = "<p></p>";
        this.checkValidities();
    };

    vq.newSort = function (sort) {

        var sortField, sortDir, toggle;
        if (sort) {
            var sortField = sort.field || sort[0];
            var sortDir = sort.direction || sort[1];
            toggle = sort.toggle;
            if (typeof toggle === 'undefined') toggle = true;
        }
        var newSort = this.sortTplDiv.cloneNode(true);
        var tableFields;
        if (this.autoFetchFields) {
            var tableFields = tableFieldsResult.tableFields;
        } else {
            if (!this.validSortTableFields) this.buildValidHavingTableFields();
            tableFields = this.validSortTableFields;
        }
        var tableFields = this.validSortTableFields;
        var options = '';
        for (var table in tableFields) {
            var tableOptions = "";
            var tableInfo = tableFields[table];
            var meta = tableInfo['{meta}'] || {};
            var tableAlias = meta.alias || table;
            for (var field in tableInfo) {
                if (field === "{meta}") continue;
                var fieldInfo = tableInfo[field];
                var fieldAlias = fieldInfo.alias || field;
                // console.log(table, field, filterField);
                var value = table === '{group}' ? field : table + this.separator + field;
                var selected = value === sortField ? 'selected' : '';
                tableOptions +=
                    `<option value='${value}' ${selected} data-table='${table}'>${fieldAlias}</option>`;
            }
            if (tableOptions) options += `<optgroup label="${tableAlias}">${tableOptions}</optgroup>`;
        }

        var selectSortField = newSort.querySelector('.sort-field');
        selectSortField.name = this.name + '_sort_fields[]';
        selectSortField.innerHTML = options;
        $(selectSortField).multiselect({
            nonSelectedText: 'Select a field',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            onChange: (option, checked) => {
                // console.log(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
                this.checkSortFieldValidity(selectSortField);
            },
        });

        var dirSelect = newSort.querySelector('.sort-dir');
        dirSelect.name = this.name + '_sort_directions[]';
        if (sortDir) dirSelect.value = sortDir;

        var validityInput = newSort.querySelector('input.sort-validity');
        validityInput.name = this.name + '_sort_validities[]';

        var toggleInputChk = newSort.querySelector('.sort-toggle-checkbox');
        var toggleInput = newSort.querySelector('.sort-toggle');
        toggleInput.name = `${this.name}_sort_toggles[]`;
        if (typeof toggle !== 'undefined' && toggle === false) {
            toggleInputChk.checked = false;
            this.toggleSortClicked(toggleInputChk);
        }

        newSort.style.display = '';

        return newSort;
    };

    vq.addSort = function (sort) {
        var newSort = this.newSort(sort);
        this.selectSortsDiv.appendChild(newSort);
    };

    vq.addSortClicked = function (btn) {
        // console.log(btn);
        this.addSort();
    };

    vq.resetSortClicked = function (btn) {
        // console.log(btn);
        this.selectSortsDiv.innerHTML = "<p></p>";
    };

    vq.limitChkChanged = function (checkbox) {
        var div = getAncestor(checkbox, ".select-limit");
        this.toggleInputsIn(div, checkbox);
        // this.offsetInput.disabled = this.limitInput.disabled = chk.checked ? false : true;
    };

    vq.distinctChkChanged = function (chk) {

    };

    vq.groupOperatorChanged = function (e) {
        this.buildValidHavingTableFields();
        var operatorSelect = e.currentTarget;
        var itemTpl = getAncestor(operatorSelect, ".item-template");
        // var selectGroupField  =itemTpl.querySelector(".group-field");
        this.checkValidities();
    };

    vq.newGroup = function (group) {
        var groupField, groupOp, toggle;
        if (group) {
            var groupField = group.field || group[0];
            var groupOp = group.aggregate || group[1];
            toggle = group.toggle;
            if (typeof toggle === 'undefined') toggle = true;
        }
        var newGroup = this.groupTplDiv.cloneNode(true);
        var tableFields;
        if (this.autoFetchFields) {
            tableFields = tableFieldsResult.tableFields;
        } else {
            // var tables = this.tables;
            // var selectDivs = this.selectTablesDiv.querySelectorAll('select.select-table');
            // var selectedTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);
            // var tableFields = {};
            // for (var i = 0; i < selectedTables.length; i += 1) {
            //     var table = selectedTables[i];
            //     tableFields[table] = tables[table];
            // }
            if (!this.validGroupTableFields) this.buildValidFilterTableFields();
            tableFields = this.validGroupTableFields;
        }
        // console.log('tableFields', tableFields);
        var options = '';
        var numericOptions = '';
        for (var table in tableFields) {
            var tableOptions = "";
            var tableNumericOptions = "";
            var tableInfo = tableFields[table];
            var meta = tableInfo['{meta}'] || {};
            var tableAlias = meta.alias || table;
            for (var field in tableFields[table]) {
                var fieldMeta = tableFields[table][field];
                // console.log("fieldMeta", fieldMeta);
                if (field === "{meta}") continue;

                var fieldAlias = tableInfo[field].alias || field;
                var selected = table + this.separator + field === groupField ? 'selected' : '';
                tableOptions += `<option value='${table}.${field}' ${selected}>${fieldAlias}</option>`;
                if (fieldMeta.type === "number") {
                    tableNumericOptions += `<option value='${table}.${field}' ${selected} data-table='${table}'>
                        ${fieldAlias}</option>`;
                };
            }
            if (tableOptions)
                options += `<optgroup label="${tableAlias}">${tableOptions}</optgroup>`;
            if (tableNumericOptions)
                numericOptions += `<optgroup label="${tableAlias}">${tableNumericOptions}</optgroup>`;
        }
        this.options = options;
        this.numericOptions = numericOptions;

        var opSelect = newGroup.querySelector('.group-agg');
        opSelect.name = this.name + '_group_aggregates[]';
        opSelect.addEventListener('change', this.groupOperatorChanged.bind(this));
        if (groupOp) opSelect.value = groupOp;
        opSelect.dataset.lastValue = opSelect.value;

        var selectGroupField = newGroup.querySelector('.group-field');
        selectGroupField.name = this.name + '_group_fields[]';
        // selectGroupField.innerHTML = opSelect.value === "sum" ? numericOptions : options;
        selectGroupField.innerHTML = options;
        $(selectGroupField).multiselect({
            nonSelectedText: 'Select a field',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            onChange: (option, checked) => {
                // console.log(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
                this.checkValidities();
            },
        });
        this.checkGroupFieldValidity(selectGroupField);
        this.markGroupFieldsValidities(selectGroupField);

        var validityInput = newGroup.querySelector('input.group-validity');
        validityInput.name = this.name + '_group_validities[]';

        var toggleInputChk = newGroup.querySelector('.group-toggle-checkbox');
        var toggleInput = newGroup.querySelector('.group-toggle');
        toggleInput.name = `${this.name}_group_toggles[]`;
        if (typeof toggle !== 'undefined' && toggle === false) {
            toggleInputChk.checked = false;
            this.toggleGroupClicked(toggleInputChk);
        }

        newGroup.style.display = '';

        return newGroup;
    }

    vq.addGroup = function (group) {
        var newGroup = this.newGroup(group);
        this.selectGroupsDiv.appendChild(newGroup);
    }

    vq.addGroupClicked = function (btn) {
        // console.log(btn);
        this.addGroup();
    }

    vq.resetGroupClicked = function (btn) {
        // console.log(btn);
        this.selectGroupsDiv.innerHTML = "<p></p>";
        this.checkValidities();
    }

    vq.addHavingClicked = function (btn) {
        // console.log(btn);
        this.addFilter(this.selectHavingsDiv);
    };

    vq.resetHavingClicked = function (btn) {
        // console.log(btn);
        this.selectHavingsDiv.innerHTML = "<p></p>";
    };

    vq.buildValidFilterTableFields = function () {
        var tables = this.tables;
        var selectDivs = this.selectTablesDiv.querySelectorAll('select.select-table');
        var selectedTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);
        var tableFields = {};
        for (var i = 0; i < selectedTables.length; i += 1) {
            var table = selectedTables[i];
            tableFields[table] = tables[table];
        }
        // console.log('Valid filter fields = ', tableFields);
        this.lastValidFilterTableFields = this.validFilterTableFields;
        this.validGroupTableFields = this.validFilterTableFields = tableFields;
    };

    vq.buildValidHavingTableFields = function () {
        var selectDivs = this.selectTablesDiv.querySelectorAll('select.select-table');
        // var selectedTables = Array.from(selectDivs).map(v => v.selectedOptions[0].value);
        var tableFields = Array.from(selectDivs).map(selectDiv => {
            var selectId = selectDiv.dataset.id;
            var table = selectDiv.selectedOptions[0].value;
            var tableAlias = selectDiv.selectedOptions[0].textContent;
            var fieldsDiv = this.dq(`#select-fields-${selectId}`);
            var fields = Array.from(fieldsDiv.selectedOptions).map(option => option.value.split(this.separator)[1]);
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
        // console.log('buildValidHavingTableFields tableFields = ', tableFields);

        var groupDivs = Array.from(this.dom.querySelectorAll('.select-groups .group-template'));
        groupDivs = groupDivs.filter(groupDiv => {
            var filter = groupDiv.querySelector(".toggle-checkbox").checked;
            filter = filter && !groupDiv.querySelector(
                ".multiselect-native-select.invalid-item");
            return filter;
        });
        var groupFields = groupDivs.map(groupDiv => {
            var field = groupDiv.querySelector('.group-field').selectedOptions[0].value;
            var agg = groupDiv.querySelector('.group-agg').selectedOptions[0].value;
            return agg + '(' + field + ')';
        });
        var groupAliases = groupDivs.map(groupDiv => {
            var alias = groupDiv.querySelector('.group-field').selectedOptions[0].textContent;
            var agg = groupDiv.querySelector('.group-agg').selectedOptions[0].value;
            return agg + '(' + alias + ')';
        });
        tableFields.push({
            table: '{group}',
            fields: groupFields,
            fieldAliases: groupAliases,
        });
        var tableFieldsObj = {};
        // console.log("tableFields = ", tableFields);
        for (var i = 0; i < tableFields.length; i += 1) {
            var table = tableFields[i];
            var tableName = table.table;
            var tableObj = {
                "{meta}": { alias: table.tableAlias }
            };
            var fields = table.fields; var fieldAliases = table.fieldAliases;
            for (var j = 0; j < fields.length; j += 1) {
                var field = fields[j];
                tableObj[field] = {
                    alias: fieldAliases[j],
                    type: this.tables[tableName] ? this.tables[tableName][field].type : "string",
                };
            }
            tableFieldsObj[table.table] = tableObj;
        }
        tableFields = tableFieldsObj;
        // console.log('Valid having fields = ', tableFields);
        this.lastValidHavingTableFields = this.validHavingTableFields;
        this.validSortTableFields = this.validHavingTableFields = tableFields;
    };

    vq.newFilter = function (div, filter) {
        var isFilter = div === this.selectFiltersDiv;
        var isHaving = !isFilter;
        var filterField, filterFieldType, op, value1, value2, logic, toggle = true;
        if (filter) {
            filterField = filter.field || filter[0];
            op = filter.operator || filter[1];
            value1 = filter.value1 || filter[2];
            value2 = filter.value2 || filter[3];
            logic = filter.logic || filter[4];
            toggle = filter.toggle;
            if (typeof toggle === 'undefined') toggle = true;
        }
        var newFilter = this.filterTplDiv.cloneNode(true);
        var tableFields;
        if (this.autoFetchFields) {
            var tableFields = tableFieldsResult.tableFields;
        } else {
            if (isFilter) {
                if (!this.validFilterTableFields) this.buildValidFilterTableFields();
                tableFields = this.validFilterTableFields;
            } else if (isHaving) {
                if (!this.validHavingTableFields) this.buildValidHavingTableFields();
                tableFields = this.validHavingTableFields;
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
                var fieldInfo = tableInfo[field];
                var fieldAlias = fieldInfo.alias || field;
                var fieldType = fieldInfo.type || 'string';
                // console.log(table, field, filterField);
                var value = table === '{group}' ? field : table + this.separator + field;
                var selected = value === filterField ? 'selected' : '';
                if (!filterFieldType || value === filterField)
                    filterFieldType = fieldType;

                tableOptions +=
                    `<option value='${value}' ${selected} data-table='${table}' data-type='${fieldType}'>
                        ${fieldAlias}</option>`;
            }
            if (tableOptions) options += `<optgroup label="${tableAlias}">${tableOptions}</optgroup>`;
        }

        var inputName = isFilter ? "_filter" : "_having";
        inputName = this.name + inputName;

        var selectFilterField = newFilter.querySelector('.filter-field');
        selectFilterField.name = `${inputName}_fields[]`;
        selectFilterField.innerHTML = options;
        $(selectFilterField).multiselect({
            nonSelectedText: 'Select a field',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            onChange: (option, checked) => {
                // console.log(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
                isFilter ? this.checkFilterFieldValidity(selectFilterField) :
                    this.checkHavingFieldValidity(selectFilterField);
            },
        });

        var opInput = newFilter.querySelector('.filter-op');
        opInput.name = `${inputName}_operators[]`;
        if (filter) opInput.value = op;

        var value1Input = newFilter.querySelector('.filter-value-1');
        value1Input.name = `${inputName}_value1s[]`;
        if (filter) {
            value1Input.value = value1;
            value1Input.style.display = op === 'null' || op === 'nnull' ? 'none' : '';
        }

        var value2Input = newFilter.querySelector('.filter-value-2');
        value2Input.name = `${inputName}_value2s[]`;
        if (filter) {
            value2Input.value = value2;
            value2Input.style.display = op === 'btw' || op === 'nbtw' ? '' : 'none';
        }

        var date1 = newFilter.querySelector(`.${vqTheme}.filter-date-1`);
        var date1Input = date1.querySelector(`.filter-date-value-1`);
        date1Input.name = `${inputName}_value1s[]`;
        var date1Container = date1.querySelector('.input-group');
        $(date1Container).datetimepicker({
            // format: "YYYY-MM-DD HH:mm:ss"
        });
        // $(date1Container).data("datetimepicker").options({"format": "DD/MM/YYYY"});
        // $(date1Container).datetimepicker('destroy')

        if (vqTheme === 'bs4') {
            var id = date1Container.id = `${inputName}_date1_${this.filterCount}`;
            var date1Icon = date1.querySelector('.input-group-append');
            date1Icon.dataset.target = date1Input.dataset.target = `#${id}`;
        }

        var date2 = newFilter.querySelector(`.${vqTheme}.filter-date-2`);
        var date2Input = date2.querySelector(`.filter-date-value-2`);
        date2Input.name = `${inputName}_value2s[]`;
        var date2Container = date2.querySelector('.input-group');
        $(date2Container).datetimepicker({
            // format: "YYYY-MM-DD HH:mm:ss"
        });
        if (vqTheme === 'bs4') {
            var id = date2Container.id = `${inputName}_date2_${this.filterCount}`;
            var date2Icon = date2.querySelector('.input-group-append');
            date2Icon.dataset.target = date2Input.dataset.target = `#${id}`;
        }

        if (filterFieldType === "date" || filterFieldType === "datetime") {
            if (typeof value1 !== "undefined") date1Input.value = value1;
            if (typeof value2 !== "undefined") date2Input.value = value2;
        }

        this.filterCount += 1;

        var logicSelect = newFilter.querySelector('.filter-logic');
        logicSelect.name = `${inputName}_logics[]`;
        if (filter) logicSelect.value = logic;

        var bracketInput = newFilter.querySelector('input.filter-bracket');
        bracketInput.name = `${inputName}_brackets[]`;

        var validityInput = newFilter.querySelector('input.filter-validity');
        validityInput.name = `${inputName}_validities[]`;

        var toggleInputChk = newFilter.querySelector('.filter-toggle-checkbox');
        var toggleInput = newFilter.querySelector('.filter-toggle');
        toggleInput.name = `${inputName}_toggles[]`;
        // console.log(toggle);
        if (!toggle) {
            toggleInputChk.checked = false;
            this.toggleFilterClicked(toggleInputChk);
        }

        this.decideInputsType(newFilter);

        if (this.selectFiltersDiv.querySelectorAll('.filter-template').length === 0) {
            // typeSelect.style.display = 'none';
        }

        newFilter.style.display = '';

        return newFilter;
    };

    vq.addFilter = function (div, filter) {
        var newFilter = this.newFilter(div, filter);
        div.appendChild(newFilter);
    };

    vq.addFilterClicked = function (btn, type) {
        // console.log(btn);
        this.addFilter(this.selectFiltersDiv);
    };

    vq.toggleInputsIn = function (div, checkbox) {
        if (!checkbox) checkbox = div.querySelector('.item-toggle');
        // console.log(rowDiv);
        div.querySelectorAll("input").forEach(input => {
            if (input !== checkbox) {
                // input.disabled = !checkbox.checked;
                if (checkbox.checked) input.classList.remove("disabled-input");
                else input.classList.add("disabled-input");
            }
        });
        div.querySelectorAll("select").forEach(select => {
            if (select !== checkbox) {
                // select.disabled = !checkbox.checked;
                if (checkbox.checked) select.classList.remove("disabled-input");
                else select.classList.add("disabled-input");
            }
        });
        div.querySelectorAll(".input-label").forEach(label => {
            if (checkbox.checked) label.classList.remove("disabled-input");
            else label.classList.add("disabled-input");
        });
        div.querySelectorAll(".multiselect-native-select").forEach(multiselect => {
            if (checkbox.checked) multiselect.classList.remove("disabled-input");
            else multiselect.classList.add("disabled-input");
        });

        // div.querySelectorAll(".btn-delete").forEach(btn => {
        //     if (checkbox.checked) btn.classList.remove("disabled-input");
        //     else btn.classList.add("disabled-input");
        // });

        // var drp = rowDiv.querySelector('krwidget[widget-type="koolreport/inputs/DateRangePicker"]');
        // if (drp) {
        //     if (checkbox.checked) drp.classList.remove("disabled-input");
        //     else drp.classList.add("disabled-input");
        // }
        // var dtp = rowDiv.querySelector('krwidget[widget-type="koolreport/inputs/DateTimePicker"]');
        // if (dtp) {
        //     if (checkbox.checked) dtp.classList.remove("disabled-input");
        //     else dtp.classList.add("disabled-input");
        // }
    }

    vq.toggleFilterClicked = function (checkbox) {
        var div = getAncestor(checkbox, ".filter-template");
        div.querySelector(".filter-toggle").value = checkbox.checked ? "on" : "off";
        this.toggleInputsIn(div, checkbox);
    };

    vq.toggleGroupClicked = function (checkbox) {
        var div = getAncestor(checkbox, ".group-template");
        div.querySelector(".group-toggle").value = checkbox.checked ? "on" : "off";
        this.toggleInputsIn(div, checkbox);
        this.checkValidities();
    };

    vq.toggleSortClicked = function (checkbox) {
        var div = getAncestor(checkbox, ".sort-template");
        div.querySelector(".sort-toggle").value = checkbox.checked ? "on" : "off";
        this.toggleInputsIn(div, checkbox);
    };

    vq.cloneFilterClicked = function (btn) {
        var itemTpl = getAncestor(btn, ".item-template");
        var isFilter = itemTpl.parentNode.classList.contains("select-filters");
        var div = isFilter ? this.selectFiltersDiv : this.selectHavingsDiv;

        var iq = selector => itemTpl.querySelector(selector);
        var filter = {
            toggle: iq(".toggle-checkbox").checked,
            logic: iq(".filter-logic").value,
            field: iq(".filter-field").value,
            operator: iq(".filter-op").value,
            value1: iq(".filter-value-1").value,
            value2: iq(".filter-value-2").value,
            logic: iq(".filter-logic").value
        };

        var itemTplClone = this.newFilter(div, filter);
        itemTplClone.style.paddingLeft = itemTpl.style.paddingLeft;

        if (itemTpl.nextSibling) {
            itemTpl.parentNode.insertBefore(itemTplClone, itemTpl.nextSibling);
        } else {
            itemTpl.parentNode.appendChild(itemTplClone);
        }
    };

    vq.cloneGroupClicked = function (btn) {
        var itemTpl = getAncestor(btn, ".item-template");

        var iq = selector => itemTpl.querySelector(selector);
        var group = {
            toggle: iq(".toggle-checkbox").checked,
            field: iq(".group-field").value,
            aggregate: iq(".group-agg").value,
        };

        var itemTplClone = this.newGroup(group);

        if (itemTpl.nextSibling) {
            itemTpl.parentNode.insertBefore(itemTplClone, itemTpl.nextSibling);
        } else {
            itemTpl.parentNode.appendChild(itemTplClone);
        }
    };

    vq.cloneSortClicked = function (btn) {
        var itemTpl = getAncestor(btn, ".item-template");

        var iq = selector => itemTpl.querySelector(selector);
        var sort = {
            toggle: iq(".toggle-checkbox").checked,
            field: iq(".sort-field").value,
            direction: iq(".sort-dir").value,
        };

        var itemTplClone = this.newSort(sort);

        if (itemTpl.nextSibling) {
            itemTpl.parentNode.insertBefore(itemTplClone, itemTpl.nextSibling);
        } else {
            itemTpl.parentNode.appendChild(itemTplClone);
        }
    };

    vq.confirmDeleteClicked = function () {
        if (typeof this.deleteItemCallback === "function")
            this.deleteItemCallback();
        this.deleteItemCallback = null;
    };

    vq.deleteTableClicked = function (btn) {
        this.deleteItemCallback = function () {
            var rowDiv = getAncestor(btn, ".item-template");
            var selectTablesFields = rowDiv.parentElement;
            selectTablesFields.removeChild(rowDiv);
            var lastChild = selectTablesFields.lastChild;
            if (lastChild && lastChild.classList
                && lastChild.classList.contains("item-template")) {
                lastChild.classList.remove("disable-table");
                var selectTableDiv = lastChild.querySelector("select.select-table");
                selectTableDiv.classList.remove("disabled-input");
            }
            this.checkValidities();
        };
        $('#' + this.name + '_confirm-delete').modal('show');
    };

    vq.deleteItemClicked = function (btn) {
        this.deleteItemCallback = function () {
            var itemTpl = getAncestor(btn, ".item-template");
            itemTpl.parentNode.removeChild(itemTpl);
        };
        $('#' + this.name + '_confirm-delete').modal('show');
    };

    vq.deleteBracketsClicked = function (btn) {
        // console.log('deleteBracketsClicked', btn);
        $('#' + this.name + '_confirm-delete').modal('show');
        this.deleteItemCallback = function () {
            var div = getAncestor(btn, ".select-filters,.select-havings");
            var bracketEl = getAncestor(btn, ".bracket-template");
            var tmpEl = bracketEl;
            var level = 0;
            var type = bracketEl.dataset.type;
            if (type === "open-bracket") {
                while (tmpEl = tmpEl.nextElementSibling) {
                    if (tmpEl.dataset.type === "open-bracket") level += 1;
                    else if (tmpEl.dataset.type === "close-bracket") {
                        if (level === 0) {
                            bracketEl.parentElement.removeChild(bracketEl);
                            tmpEl.parentElement.removeChild(tmpEl);
                        } else {
                            level -= 1;
                        }
                    }
                }
            } else if (type === "close-bracket") {
                while (tmpEl = tmpEl.previousElementSibling) {
                    if (tmpEl.dataset.type === "close-bracket") level += 1;
                    else if (tmpEl.dataset.type === "open-bracket") {
                        if (level === 0) {
                            bracketEl.parentElement.removeChild(bracketEl);
                            tmpEl.parentElement.removeChild(tmpEl);
                        } else {
                            level -= 1;
                        }
                    }
                }
            }
            if (div) this.indentBrackets(div);
        };
    };

    vq.addOpenBracket = function (div) {
        var isFilter = div === this.selectFiltersDiv;
        var openBracket = this.openBracketTplDiv.cloneNode(true);
        var openBracketInput = openBracket.querySelector("input.filter-bracket");
        openBracketInput.name = isFilter ?
            this.name + "_filter_brackets[]" : this.name + "_having_brackets[]";
        openBracket.style.display = '';
        div.appendChild(openBracket);
    };

    vq.addCloseBracket = function (div) {
        var isFilter = div === this.selectFiltersDiv;
        var closeBracket = this.closeBracketTplDiv.cloneNode(true);
        var closeBracketInput = closeBracket.querySelector("input.filter-bracket");
        closeBracketInput.name = isFilter ?
            this.name + "_filter_brackets[]" : this.name + "_having_brackets[]";
        closeBracket.style.display = '';
        div.appendChild(closeBracket);
    };

    vq.addFilterBracketsClicked = function (btn) {
        this.addOpenBracket(this.selectFiltersDiv);
        this.addCloseBracket(this.selectFiltersDiv);
    };

    vq.addHavingBracketsClicked = function (btn) {
        this.addOpenBracket(this.selectHavingsDiv);
        this.addCloseBracket(this.selectHavingsDiv);
    };

    vq.resetFilterClicked = function (btn) {
        // console.log(btn);
        this.selectFiltersDiv.innerHTML = "<p></p>";
    };

    vq.filterOpChanged = function (select) {
        // console.log(select);
        var filter = getAncestor(select, '.item-template');
        this.decideInputsType(filter);
    };

    vq.findFieldInTables = function (field) {
        var tables = this.tables;
        for (var tableName in tables) {
            var table = tables[tableName];
            for (var fieldName in table) {
                if (tableName + this.separator + fieldName === field)
                    return table[fieldName];
            }
        }
    };

    vq.decideInputsType = function (filter) {
        var opSelect = filter.querySelector('.filter-op');
        var op = opSelect.selectedOptions[0].value;
        var fieldSelect = filter.querySelector('.filter-field');
        var fieldType = fieldSelect.selectedOptions[0].dataset.type || "string";
        var showDateInputs = true;
        if (fieldType !== 'datetime' && fieldType !== "date") {
            showDateInputs = false;
        } else {
            if (op === 'ctn' || op === 'nctn' || op === 'in' || op === 'nin')
                showDateInputs = false;
        }
        var filterText1 = filter.querySelector('.filter-value-1');
        var filterText2 = filter.querySelector('.filter-value-2');
        var filterDate1 = filter.querySelector(`.${vqTheme}.filter-date-1`);
        var filterDate2 = filter.querySelector(`.${vqTheme}.filter-date-2`);
        var date1Container = filterDate1.querySelector(".input-group.date");
        var date2Container = filterDate2.querySelector(".input-group.date");
        var filterDateValue1 = filterDate1.querySelector('.filter-date-value-1');
        var filterDateValue2 = filterDate2.querySelector('.filter-date-value-2');
        if (showDateInputs) {
            if (fieldType === "date") {
                $(date1Container).data("datetimepicker").options({ "format": "YYYY-MM-DD" });
                $(date2Container).data("datetimepicker").options({ "format": "YYYY-MM-DD" });
            } else if (fieldType === "datetime") {
                $(date1Container).data("datetimepicker").options({ "format": "YYYY-MM-DD HH:mm:ss" });
                $(date2Container).data("datetimepicker").options({ "format": "YYYY-MM-DD HH:mm:ss" });
            }
            filterText1.disabled = filterText2.disabled = true;
            filterDateValue1.disabled = filterDateValue2.disabled = false;
            filterText1.style.display = filterText2.style.display = "none";
            if (op !== 'null' && op !== 'nnull') {
                filterDate1.style.display = "inline-block";
                filterDate2.style.display = op === 'btw' || op === 'nbtw' ?
                    "inline-block" : "none";
            } else {
                filterDate1.style.display = filterDate2.style.display = 'none';
            }
        } else {
            filterText1.disabled = filterText2.disabled = false;
            filterDateValue1.disabled = filterDateValue2.disabled = true;
            filterDate1.style.display = filterDate2.style.display = "none";
            if (op !== 'null' && op !== 'nnull') {
                filterText1.style.display = "inline-block";
                filterText2.style.display = op === 'btw' || op === 'nbtw' ?
                    "inline-block" : "none";
            } else {
                filterText1.style.display = filterText2.style.display = 'none';
            }
            filterText1.type = filterText2.type = fieldType === "number" ? "number" : "text";
        }
    };

    vq.filterFieldChanged = function (select) {
        var filter = getAncestor(select, '.item-template');
        this.decideInputsType(filter);
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
        tables = tables || [];
        fields = fields || [];
        for (var i = 0; i < tables.length; i += 1) {
            var table = tables[i];
            var tableFields = fields.filter(field => {
                var fieldTable = field.split(this.separator)[0];
                return fieldTable === table;
            });
            this.addTableAndFields(table, tableFields);
        }
    };

    vq.buildSelectFilters = function (filters) {
        filters = filters || [];
        this.filterCount = 0;
        for (var i = 0; i < filters.length; i += 1) {
            var filter = filters[i];
            if (filter === "(") {
                this.addOpenBracket(this.selectFiltersDiv);
            } else if (filter === ")") {
                this.addCloseBracket(this.selectFiltersDiv);
            } else {
                this.addFilter(this.selectFiltersDiv, filter);
            }
        }
    };

    vq.buildSelectGroups = function (groups, havings) {
        groups = groups || [];
        havings = havings || [];
        for (var i = 0; i < groups.length; i += 1) {
            var group = groups[i];
            this.addGroup(group);
        }

        for (var i = 0; i < havings.length; i += 1) {
            var filter = havings[i];
            if (filter === "(") {
                this.addOpenBracket(this.selectHavingsDiv);
            } else if (filter === ")") {
                this.addCloseBracket(this.selectHavingsDiv);
            } else {
                this.addFilter(this.selectHavingsDiv, filter);
            }
        }
    };

    vq.buildSelectSorts = function (sorts) {
        sorts = sorts || [];
        for (var i = 0; i < sorts.length; i += 1) {
            var sort = sorts[i];
            this.addSort(sort);
        }
    };

    vq.buildSelectLimit = function (limit) {
        var div = this.dq('.select-limit');
        this.toggleInputsIn(div);
        // this.offsetInput.value = limit.offset;
        // this.limitInput.value = limit.limit;
    };

    vq.tabClicked = function (e) {
        // console.log('tab clicked');
        // console.log(vq, this);
        var tab = e.currentTarget;
        var title = tab.textContent.trim().toLowerCase();
        this.activeTabInput.value = title;
    };

    vq.update = function () {
        this.stateInput.value = JSON.stringify(this.getState());
    };

    vq.areBracketsValid = function (div) {
        var level = 0;
        var children = div.children;
        for (var i = 0; i < children.length; i += 1) {
            var child = children[i];
            if (child.dataset.type === "open-bracket") {
                level += 1;
            } else if (child.dataset.type === "close-bracket") {
                level -= 1;
                if (level < 0) return false;
            }
        }
        return true;
    };

    vq.indentBrackets = function (div) {
        var level = 0;
        var children = div.children;
        for (var i = 0; i < children.length; i += 1) {
            var child = children[i];
            if (child.dataset.type === "open-bracket") {
                child.style.paddingLeft = (level * 25 + 10) + "px";
                level += 1;
            } else if (child.dataset.type === "close-bracket") {
                level -= 1;
                child.style.paddingLeft = (level * 25 + 10) + "px";
            } else {
                child.style.paddingLeft = (level * 25 + 10) + "px";
            }
        }
    };

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
        var dq = this.dq = selector => dom.querySelector(selector);
        this.stateInput = dq(`input[name="${this.name + '_input'}"]`);
        this.selectTablesDiv = dq('.select-tables');
        var domTpl = dq(`.dom-templates`);
        this.formGroupTpl = domTpl.querySelector(`.form-group-template`);
        this.selectTpl = domTpl.querySelector(`.select-template`);
        this.optionTpl = domTpl.querySelector(`.option-template`);
        this.deleteTpl = domTpl.querySelector(`.delete-template`);

        this.selectSortsDiv = dq(`.select-sorts`);
        this.sortTplDiv = dq(`.sort-template`);
        this.limitChk = dq(`input[name="${this.name}_limit_toggle"]`);
        this.offsetInput = dq(`input[name="${this.name}_limit_offset"]`);
        this.limitInput = dq(`input[name="${this.name}_limit_limit"]`);
        this.selectGroupsDiv = dq(`.select-groups`);
        this.groupTplDiv = dq(`.group-template`);
        this.selectHavingsDiv = dq(`.select-havings`);
        this.selectFiltersDiv = dq(`.select-filters`);
        this.filterTplDiv = dq(`.filter-template`);
        this.openBracketTplDiv = dq(`.bracket-template[data-type="open-bracket"]`);
        this.closeBracketTplDiv = dq(`.bracket-template[data-type="close-bracket"]`);
        this.hiddenInputTpl = dq(`.hidden-input-template`);
        this.activeTabInput = dq(`input[name="${this.name}_activeTab"]`);
        this.tabs = dom.querySelectorAll(`a[role="tab"]`);
        this.tabs.forEach(tab => {
            tab.addEventListener('click', this.tabClicked.bind(this));
        });

        var value = this.value ? this.value : (this.defaultValue ? this.defaultValue : null);

        if (value) {
            this.buildSelectTables(value.selectTables, value.selectFields);
            this.buildSelectFilters(value.filters);
            this.buildSelectGroups(value.groups, value.havings);
            this.buildSelectSorts(value.sorts);
            this.buildSelectLimit(value.limit);

            var filtersSelector = `${selector} .select-filters.form`;
            $(filtersSelector).sortable({
                start: function (event, ui) { },
                change: function (event, ui) { },
                update: function (event, ui) {
                    console.log('filter sortable update', this);
                    if (!this.areBracketsValid(this.selectFiltersDiv)) {
                        $(`${selector} .select-filters.form`).sortable('cancel');
                    }
                    this.indentBrackets(this.selectFiltersDiv);
                }.bind(this)
            });
            $(filtersSelector).disableSelection();
            this.indentBrackets(this.selectFiltersDiv);

            var havingsSelector = `${selector} .select-havings.form`;
            $(havingsSelector).sortable({
                start: function (event, ui) { },
                change: function (event, ui) { },
                update: function (event, ui) {
                    console.log('having sortable update', this);
                    if (!this.areBracketsValid(this.selectHavingsDiv)) {
                        $(`${selector} .select-filters.form`).sortable('cancel');
                    }
                    this.indentBrackets(this.selectHavingsDiv);
                }.bind(this)
            });
            $(havingsSelector).disableSelection();
            this.indentBrackets(this.selectHavingsDiv);

            var groupSelector = `${selector} .select-groups.form`;
            $(groupSelector).sortable();
            $(groupSelector).disableSelection();

            var sortSelector = `${selector} .select-sorts.form`;
            $(sortSelector).sortable();
            $(sortSelector).disableSelection();
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