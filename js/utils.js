var utils = new Utils();

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function Utils(){

 	this.crearGrip = function(idTable, idPage, url, colNames, dataJson, colModel, callback){

    	var grid_selector = idTable;
        var pager_selector = idPage;

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:url,
            datatype: "json",
            contentType: "application/json",
            shrinkToFit: false,
            height:'auto',
            postData: dataJson,
            mtype: 'POST',
            colNames:colNames,
            colModel:colModel,
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'id',
            viewrecords: true,
            sortorder: "desc",
            loadComplete : callback
        });

        // Setup buttons
        $(idTable).jqGrid('navGrid', idPage,
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    };

    this.reloadGrip = function(idTable, data){
        $(idTable).jqGrid('clearGridData')
        .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
        .trigger('reloadGrid',[{current:true}]);
    }

    this.clearVar = function(string){
        return string.replace(new RegExp("'", 'g'), "`");
    }
}

function TableData(number){

    console.log("TABLE DATA OK ");
    number = number || "";

    var TABLE = null,
        ID = null,
        SAVE = false,
        language = {
            "lengthMenu": "Mostrando _MENU_ registros",
            "zeroRecords": "Sin Registros",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No Existen Registros",
            "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
            "sSearch": "",
            "searchPlaceholder": "Buscar",
            "sProcessing":      "Cargando...",
            "paginate": {
                "first":      "Primero",
                "last":       "Ultimo",
                "next":       "Siguiente",
                "previous":   "Anterior"
            }   
        };

    this.init = function(id, _buttons, _order){

        console.log("TABLE DATA OK ");

        var order = true;

        ID = id;

        if(_order === false)
            order = false;

        TABLE = $('#'+ID).DataTable({
            "processing": true,
            dom: 'lrtip',
            buttons : _buttons,
            "language": "",
            "pagingType": "simple_numbers",
            "lengthMenu": true,
            "scrollX": true,
            "serverSide": false,
            //"dom": '<f<t><"#df"<"pull-left" i><"pull-right"p><"pull-right"l>>>',
            "bLengthChange": true,
            "blengthMenu": [[10, 20, 30, -1], [10, 20, 30, "All"]],
            retrieve: true,
            destroy: true,
            bFilter:true,
            responsive: true,
            ordering: order
        });

        if($(".dt-button.buttons-excel"))
            $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');

        if($(".dt-button.buttons-pdf"))
            $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');

        return TABLE;

    };

    this.destroy = function(){

        if(TABLE)
            TABLE.destroy();
    };
}

function TableDataRest(data){

    var TABLE = null,
        ID = null,
        SAVE = false,
        language = {
            "lengthMenu": "Mostrando _MENU_ registros",
            "zeroRecords": "Sin Registros",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No Existen Registros",
            "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
            "sSearch": "",
            "searchPlaceholder": "Buscar",
            "sProcessing":      "Cargando...",
            "paginate": {
                "first":      "Primero",
                "last":       "Ultimo",
                "next":       "Siguiente",
                "previous":   "Anterior"
            }   
        };

    this.init = function(id, _buttons, _order, data){
        console.log("TABLE DATA OK ");

        var order = true;

        ID = id;

        if(_order === false)
            order = false;

        TABLE = $('#'+ID).DataTable({
            "processing": true,
            dom: 'Bfrtip',
            data: data,
            buttons : _buttons,
            "language": language,
            "pagingType": "simple_numbers",
            "lengthMenu": false,
            "scrollX": true,
            "serverSide": false,
            "bLengthChange": false,
            retrieve: true,
            destroy: true,
            bFilter:true,
            responsive: true,
            ordering: order
        });

        if($(".dt-button.buttons-excel"))
            $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');

        if($(".dt-button.buttons-pdf"))
            $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');

        return TABLE;

    };

    this.destroy = function(){

        if(TABLE)
            TABLE.destroy();
    };
}

function ExportDataGrid(){

    this.exportExcel = function(ID, fileName){

        $("#"+ID).jqGrid("exportToExcel",{
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : fileName,
            maxlength : 40 
        });

        //swal("Descargando Excel", "Su descarga empezara en breve", "success");
    };

    this.exportPDF = function(ID, fileName, _typePage){

        var typePage = _typePage || "A4";

        $("#grid-table").jqGrid("exportToPdf",{
            orientation: 'landscape',
            pageSize: typePage,
            description: '',
            customSettings: null,
            download: 'open',
            includeLabels : true,
            includeGroupHeader : true,
            includeFooter: true,
            fileName : fileName
        });

        //swal("Descargando PDF", "Su descarga empezara en breve", "success");
    };
}