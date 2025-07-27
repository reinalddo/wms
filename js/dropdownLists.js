var _chkSelected = false;
var _arrChkSelected = Array();

function markCheck(_chk) {
    if ($(_chk).is(":checked")) {
        for(i=0;i<_arrChkSelected.length;i++){
            if (_arrChkSelected[i]==$(_chk).val()) return;
        }
        _arrChkSelected.push($(_chk).val());
    } else {
        removeItem = $(_chk).val();
        _arrChkSelected = jQuery.grep(_arrChkSelected, function(value) {
            return value != removeItem;
        });
    }
    //alert(_arrChkSelected.join("|"));
}

function setStyle(s, _this){
    if (s=="over") {
        if (_this.className != 'activeItem') {
            $(_this).removeClass();
            $(_this).addClass("sortable-item-over");
        }
    } else {
        if (_this.className != 'activeItem') {
            $(_this).removeClass();
            $(_this).addClass("sortable-item");
        }
    }
}

function enact(what){
    var p = what.parentNode;
    var els = p.getElementsByTagName('li');
    for(i=0;i<els.length;i++){
        els[i].className = 'sortable-item';
    }
    $('#example-2-1 ul.sortable-list').each(function(){
        $(this).children('li').removeClass();
        $(this).children('li').addClass("sortable-item");
    });
    _selectedID = what.id;
    what.className = 'activeItem';
}

function actualizaListas(){
    var availables = $('#all_accesories ul.sortable-list').children('li');
    var members = $('#selected_accesories ul.sortable-list').children('li');

    if (availables.length > 0) $('#btnAddAll').removeAttr('disabled', 'disabled'); else $('#btnAddAll').attr('disabled', 'disabled');
    if (availables.length > 0) $('#btnAddSel').removeAttr('disabled', 'disabled'); else $('#btnAddSel').attr('disabled', 'disabled') ;
    if (members.length > 0) $('#btnRemSel').removeAttr('disabled', 'disabled'); else $('#btnRemSel').attr('disabled', 'disabled');
    if (members.length > 0) $('#btnRemAll').removeAttr('disabled', 'disabled'); else $('#btnRemAll').attr('disabled', 'disabled');
    $('#example-2-1 ul.sortable-list').each(function(){
        $(this).children('li').removeClass();
        $(this).children('li').addClass("sortable-item");
    });
    $('#listExtensions').val(byAccesoriesStringToSave());
    $("#example-2-1 ul.sortable-list").sortable();
}

function byAccesoriesStringToSave() {
    var dataProviderTo = $('#selected_accesories ul.sortable-list').children('li');
    var listExt = dataProviderTo;
    var listas = new Array();
    for (i=0;i<listExt.length;i++) listas[i] = listExt[i].id;
    $('#byextension').val("true");
    return listas.join("|");
}
function addSel(__selectedID){
    var ___selectedID = __selectedID;
    var _selected_accesories = $('#selected_accesories ul.sortable-list').children('li');
    for (j=0;j<_selected_accesories.length;j++) if (_selected_accesories[j].id==___selectedID) return;
    var added = false;
    var deleted = false;
    var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
    for (i=0;i<user_clone.length;i++) {
        if (___selectedID == user_clone[i].id && added==false) {
            var _user_clone = user_clone[i];
            $('#selected_accesories .sortable-list').append(_user_clone);
            added = true;
        }
    }

    var user_del = $('#all_accesories ul.sortable-list').children('li');
    for (i=0;i<user_del.length;i++) {
        if (___selectedID == user_del[i].id && deleted==false) {
            var _user_del = user_del[i];
            $(_user_del).remove();
            deleted = true;
        }
    }
    _arrChkSelected = [];
}

function remAll() {
    var added = false;
    var deleted = false;
    var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
    var user_del = $('#all_accesories ul.sortable-list').children('li');
    for (i=0;i<user_clone.length;i++) {
        var _user_clone = user_clone[i];
        if (user_del.length>0) {
            var _exists = false;
            for (j=0;j<user_del.length;j++) {
                if (user_clone[i].id == user_del[j].id) _exists = true;
            }
            if (_exists==false) $('#all_accesories .sortable-list').append(_user_clone);
        } else {
            var _user_clone = user_clone[i];
            $('#all_accesories .sortable-list').append(_user_clone);
        }
    }

    var user_del = $('#selected_accesories ul.sortable-list').children('li');
    for (i=0;i<user_del.length;i++) {
        var _user_del = user_del[i];
        $(_user_del).remove();
    }
    _selectedID = null;
}
$(document).ready(function(){

    $("#btnAddSel").click(function() {
        if (_arrChkSelected.length == 0) {
            if (_selectedID == null) return;
            var _selected_accesories = $('#selected_accesories ul.sortable-list').children('li');
            for (j=0;j<_selected_accesories.length;j++) if (_selected_accesories[j].id==_selectedID) return;
            var added = false;
            var deleted = false;
            var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
            for (i=0;i<user_clone.length;i++) {
                if (_selectedID == user_clone[i].id && added==false) {
                    var _user_clone = user_clone[i];
                    $('#selected_accesories .sortable-list').append(_user_clone);
                    added = true;
                }
            }

            var user_del = $('#all_accesories ul.sortable-list').children('li');
            for (i=0;i<user_del.length;i++) {
                if (_selectedID == user_del[i].id && deleted==false) {
                    var _user_del = user_del[i];
                    $(_user_del).remove();
                    $("#checkAccesorio" + user_del[i].id).attr('checked', false);
                    deleted = true;
                    _selectedID = null;
                }
            }
            actualizaListas();
        } else {
            for (k=0;k<_arrChkSelected.length;k++) {
                var ___selectedID = _arrChkSelected[k];
                var _selected_accesories = $('#selected_accesories ul.sortable-list').children('li');
                for (j=0;j<_selected_accesories.length;j++) if (_selected_accesories[j].id==___selectedID) return;
                var added = false;
                var deleted = false;
                var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
                for (i=0;i<user_clone.length;i++) {
                    if (___selectedID == user_clone[i].id && added==false) {
                        var _user_clone = user_clone[i];
                        $('#selected_accesories .sortable-list').append(_user_clone);
                        added = true;
                    }
                }

                var user_del = $('#all_accesories ul.sortable-list').children('li');
                for (i=0;i<user_del.length;i++) {
                    if (___selectedID == user_del[i].id && deleted==false) {
                        var _user_del = user_del[i];
                        $(_user_del).remove();
                        deleted = true;
                    }
                }
                $("#checkAccesorio" + ___selectedID).attr('checked', false);
            }

        }
        actualizaListas();
        _arrChkSelected = [];
    });

    $("#btnAddAll").click(function() {
        var added = false;
        var deleted = false;
        var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
        var user_del = $('#selected_accesories ul.sortable-list').children('li');
        for (i=0;i<user_clone.length;i++) {
            var _user_clone = user_clone[i];
            if (user_del.length>0) {
                var _exists = false;
                for (j=0;j<user_del.length;j++) {
                    if (user_clone[i].id == user_del[j].id) _exists = true;
                }
                if (_exists==false) $('#selected_accesories .sortable-list').append(_user_clone);
            } else {
                var _user_clone = user_clone[i];
                $('#selected_accesories .sortable-list').append(_user_clone);
            }
        }

        var user_del = $('#all_accesories ul.sortable-list').children('li');
        for (i=0;i<user_del.length;i++) {
            var _user_del = user_del[i];
            $(_user_del).remove();
            $("#checkAccesorio" + user_del[i].id).attr('checked', false);
        }
        _selectedID = null;
        actualizaListas();
        _arrChkSelected = [];
    });

    $("#btnRemSel").click(function() {
        if (_arrChkSelected.length == 0) {
            if (_selectedID == null) return;
            var _all_accesories = $('#all_accesories ul.sortable-list').children('li');
            for (j=0;j<_all_accesories.length;j++) if (_all_accesories[j].id==_selectedID) return;
            var added = false;
            var deleted = false;
            var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
            for (i=0;i<user_clone.length;i++) {
                if (_selectedID == user_clone[i].id && added==false) {
                    var _user_clone = user_clone[i];
                    $('#all_accesories .sortable-list').append(_user_clone);
                    added = true;
                }
            }

            var user_del = $('#selected_accesories ul.sortable-list').children('li');
            for (i=0;i<user_del.length;i++) {
                if (_selectedID == user_del[i].id && deleted==false) {
                    var _user_del = user_del[i];
                    $(_user_del).remove();
                    $("#checkAccesorio" + user_del[i].id).attr('checked', false);
                    deleted = true;
                    _selectedID = null;
                }
            }
            actualizaListas();
        } else {
            for (k=0;k<_arrChkSelected.length;k++) {
                _selectedID = _arrChkSelected[k];
                var _all_accesories = $('#all_accesories ul.sortable-list').children('li');
                for (j=0;j<_all_accesories.length;j++) if (_all_accesories[j].id==_selectedID) return;
                var added = false;
                var deleted = false;
                var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
                for (i=0;i<user_clone.length;i++) {
                    if (_selectedID == user_clone[i].id && added==false) {
                        var _user_clone = user_clone[i];
                        $('#all_accesories .sortable-list').append(_user_clone);
                        added = true;
                    }
                }

                var user_del = $('#selected_accesories ul.sortable-list').children('li');
                for (i=0;i<user_del.length;i++) {
                    if (_selectedID == user_del[i].id && deleted==false) {
                        var _user_del = user_del[i];
                        $(_user_del).remove();
                        deleted = true;
                        _selectedID = null;
                    }
                }
                $("#checkAccesorio" + _arrChkSelected[k]).attr('checked', false);
            }
        }
        actualizaListas();
        _arrChkSelected = [];
    });

    $("#btnRemAll").click(function() {
        var added = false;
        var deleted = false;
        var user_clone = $('#example-2-1 ul.sortable-list').children('li').clone(true);
        var user_del = $('#all_accesories ul.sortable-list').children('li');
        for (i=0;i<user_clone.length;i++) {
            var _user_clone = user_clone[i];
            if (user_del.length>0) {
                var _exists = false;
                for (j=0;j<user_del.length;j++) {
                    if (user_clone[i].id == user_del[j].id) _exists = true;
                }
                if (_exists==false) $('#all_accesories .sortable-list').append(_user_clone);
            } else {
                var _user_clone = user_clone[i];
                $('#all_accesories .sortable-list').append(_user_clone);
            }
        }

        var user_del = $('#selected_accesories ul.sortable-list').children('li');
        for (i=0;i<user_del.length;i++) {
            var _user_del = user_del[i];
            $(_user_del).remove();
            $("#checkAccesorio" + user_del[i].id).attr('checked', false);
        }
        _selectedID = null;
        actualizaListas();
        _arrChkSelected = [];
    });

    // Get items
    function getItems(exampleNr)
    {
        var columns = [];

        $(exampleNr + ' ul.sortable-list').each(function(){
            columns.push($(this).sortable('toArray').join(','));
        });

        return columns.join('|');
    }

    // Load items from cookie
    function loadItemsFromCookie(name)
    {
        if ( $.cookie(name) != null )
        {
            renderItems($.cookie(name));
        }
        else
        {
            alert('No items saved in "' + name + '".');
        }
    }

    // Render items
    function renderItems(items)
    {
        var html = '';

        var columns = items.split('|');

        for ( var c in columns )
        {
            html += '<div class="column left';

            if ( c == 0 )
            {
                html += ' first';
            }

            html += '"><ul class="sortable-list">';

            if ( columns[c] != '' )
            {
                var items = columns[c].split(',');

                for ( var i in items )
                {
                    html += '<li class="sortable-item" id="' + items[i] + '">Sortable item ' + items[i] + '</li>';
                }
            }

            html += '</ul></div>';
        }

        $('#example-2-4-renderarea').html(html);
    }

    $('#example-2-1 .sortable-list').sortable({
        connectWith: '#example-2-1 .sortable-list',
        stop: function(){
            var availables = $('#all_accesories ul.sortable-list').children('li');
            var members = $('#selected_accesories ul.sortable-list').children('li');

            if (availables.length > 0) $('#btnAddAll').removeAttr('disabled', 'disabled'); else $('#btnAddAll').attr('disabled', 'disabled');
            if (availables.length > 0) $('#btnAddSel').removeAttr('disabled', 'disabled'); else $('#btnAddSel').attr('disabled', 'disabled') ;
            if (members.length > 0) $('#btnRemSel').removeAttr('disabled', 'disabled'); else $('#btnRemSel').attr('disabled', 'disabled');
            if (members.length > 0) $('#btnRemAll').removeAttr('disabled', 'disabled'); else $('#btnRemAll').attr('disabled', 'disabled');
            $('#example-2-1 ul.sortable-list').each(function(){
                $("#checkAccesorio" + $(this).children('li').attr("id")).attr('checked', false);
                $(this).children('li').removeClass();
                $(this).children('li').addClass("sortable-item");
            });
            document.getElementById('listExtensions').value = byAccesoriesStringToSave();
            _selectedID = null;

            for (i=0;i<availables.length;i++) $("#checkAccesorio" + availables[i].id).attr('checked', false);
            for (i=0;i<members.length;i++) $("#checkAccesorio" + members[i].id).attr('checked', false);

            _arrChkSelected = [];
        }
    });
});