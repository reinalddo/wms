<?php
$listaNCompa = new \Companias\Companias();
$listaCliente = new \Clientes\Clientes();
?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>

<!-- Mainly scripts -->

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Agregar Cortina de Entrada</h4>
            </div>
            <div class="modal-body">
                <label>Clave de la Ruta</label>
                <input id="ClavRuta" type="text" placeholder="Clave de la Ruta" class="form-control"><br>
                <label>Descripción de la Ruta</label>
                <input id="DescripRuta" type="text" placeholder="Descripción de la Ruta" class="form-control"><br>
                <label>Estado de la Ruta</label>
                <input id="StatusRuta" type="text" placeholder="Estado de la Ruta" class="form-control"><br>
                <label>Compañia</label>
                <div class="form-group">
                    <select name="country" id="txtNomCompa" style="width:100%;">
                        <option value="">Clave de la Compañia</option>
                        <?php foreach( $listaNCompa->getComp() AS $p ): ?>
                            <option value="<?php echo $p->cve_cia; ?>"><?php echo $p->des_cia; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label>Asignar Ruta</label>
                <div class="row row-no-margin">
                    <div class="form-group" id="clientesEditar">
                        <ol data-draggable="target" id="from" class="izquierda">
                        </ol>

                        <ol data-draggable="target" id="to" class="derecha">
                        </ol>
                    </div>
                </div>
                <input type="hidden" id="hiddenAction">
                <input type="hidden" id="hiddenRuta">
            </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                    <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                </div>

        </div>
    </div>
</div>

<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight">

    <h3>Rutas</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-4">
                            <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">

                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<!-- Select -->
<script src="/js/select2.js"></script>

<!-- Drag & Drop Panel -->
<script src="/js/dragdrop.js"></script>

<script type="text/javascript">

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })
        //resize on sidebar collapse/expand
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
            url:'/api/ruta/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['','Clave de la Ruta','Descripción de la Ruta','Status','Descripción de la Compañia',""],
            colModel:[
                //{name:'id',index:'id', width:60, sorttype:"int", editable: false},
                {name:'ID_Ruta',index:'ID_Ruta',width:0, editable:false, sortable:false, hidden: true},
                {name:'cve_ruta',index:'cve_ruta',width:10, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:40, editable:false, sortable:false},
                {name:'status',index:'status',width:5, editable:false, sortable:false},
                {name:'des_cia',index:'des_cia',width:25, editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:imageFormat},
            ],
            rowNum:30,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'ID_Ruta',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            $("#hiddenRuta").val(serie);
            //var correl = rowObject[4];
            //var url = "x/?serie="+serie+"&correl="+correl;
            //var url2 = "v/?serie="+serie+"&correl="+correl;
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        function aceSwitch( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate( cellvalue, options, cell ) {
            setTimeout(function(){
                $(cell) .find('input[type=text]')
                    .datepicker({format:'yyyy-mm-dd' , autoclose:true});
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if(form.data('styled')) return false;

            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);

            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data){
                    grid.trigger("reloadGrid",[{current:true}]);
                },
                error: function(){}
            });
        }

        function beforeEditCallback(e) {
            var form = $(e[0]);
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_edit_form(form);
        }

        //it causes some flicker when reloading or navigating grid
        //it may be possible to have some custom formatter to do this as the grid is being created to prevent this
        //or go back to default browser checkbox styles for the grid
        function styleCheckbox(table) {
        }

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {
        }

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {
        }

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({container:'body'});
            $(table).find('.ui-pg-div').tooltip({container:'body'});
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                criterio: $("#txtCriterio").val(),
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
    }

    function downloadxml( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function borrar(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Ruta : _codigo,
                action : "delete"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ruta/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    ReloadGrid();
                }
            }
        });
    }

    function editar(_codigo) {
        $("#hiddenRuta").val(_codigo);
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Editar Contenedor</h4>');
        //$modal0 = $("#myModal");
        //$modal0.modal('show');
        $(".itemlist").remove();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Ruta : _codigo,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ruta/update/index.php',
            success: function(data) {
                if (data.success == true) {

                    //$('#codigo').prop('disabled', true);
                    $("#ClavRuta").val(data.cve_ruta);
                    $("#DescripRuta").val(data.descripcion);
                    $("#StatusRuta").val(data.status);
                    $("#txtNomCompa").select2("val",data.cve_cia);
                    l.ladda('stop');
                    $("#btnCancel").show();
                    $modal0 = $("#myModal");
                    $modal0.modal('show');
                    $("#hiddenAction").val("edit");

                    var arr = $.map(data.Clientes, function(el) { return el; })
                    for (var i=0; i<arr.length; i++)
                    {
                        if(arr[i].cve_ruta == data.cve_ruta)
                        {
                            var ul = document.getElementById("to");
                            var li = document.createElement("li");
                            li.appendChild(document.createTextNode(arr[i].razon_social));
                            li.setAttribute("data-draggable", "item");
                            li.setAttribute("draggable", "true");
                            li.setAttribute("aria-grabbed","false");
                            li.setAttribute("tabindex","0");
                            li.setAttribute("class","itemlist");
                            li.setAttribute("value",arr[i].id);

                            ul.appendChild(li);
                        }
                        else if (arr[i].cve_ruta.length == 0 && arr[i].cve_ruta != data.cve_ruta )
                        {
                            var ul = document.getElementById("from");
                            var li = document.createElement("li");
                            li.appendChild(document.createTextNode(arr[i].razon_social));
                            li.setAttribute("data-draggable", "item");
                            li.setAttribute("draggable", "true");
                            li.setAttribute("aria-grabbed","false");
                            li.setAttribute("tabindex","0");
                            li.setAttribute("class","itemlist");
                            li.setAttribute("value",arr[i].id);

                            ul.appendChild(li);
                        }
                    }
                }
            }
        });
    }

    function agregar() {
        $("#modaltitle").html('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><i class="fa fa-laptop modal-icon"></i><h4 class="modal-title">Agregar Ruta</h4>');
        $modal0 = $("#myModal");
        $modal0.modal('show');
        $(".itemlist").remove();
        l.ladda('stop');
        $("#hiddenAction").val("add");
        $("#btnCancel").show();
        $("#ClavRuta").val("");
        $("#DescripRuta").val("");
        $("#StatusRuta").val("");
        $("#txtNomCompa").val("");
        $("#hiddenRuta").val("0");


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "loadClientes"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/clientes/update/index.php',
            success: function(data) {
                if (data.success == true) {
                    var arr = $.map(data, function(el) { return el; })
                    arr.pop();
                    for (var i=0; i<arr.length; i++)
                    {
                        var ul = document.getElementById("from");
                        var li = document.createElement("li");
                        li.appendChild(document.createTextNode(arr[i].razon_social));
                        li.setAttribute("data-draggable", "item");
                        li.setAttribute("draggable", "true");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("value",arr[i].id);

                        ul.appendChild(li);

                    }
                }
            }
        });

    }
    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        l.ladda( 'start' );

        var rels = [];

        $("#to").each(function() {
            var localRels = [];

            $(this).find('li').each(function(){
                localRels.push( $(this).attr('value') );
            });

            rels.push(localRels);
        });

        $.post('/api/ruta/update/index.php',
         {
             ID_Ruta : $("#hiddenRuta").val(),
             cve_ruta : $("#ClavRuta").val(),
             descripcion : $("#DescripRuta").val(),
             status : $("#StatusRuta").val(),
             cve_cia : $("#txtNomCompa").val(),
             action : $("#hiddenAction").val(),
             clientes:rels
         },
         function(response){
         console.log(response);

         }, "json")
         .always(function() {
         $("#ClavRuta").val("");
         $("#DescripRuta").val("");
         $("#StatusRuta").val("");
         $("#txtNomCompa").val("");
         $('[name="Clientes[]"]').val();
         l.ladda('stop');
         $("#btnCancel").show();
         $modal0.modal('hide');
         ReloadGrid();
         });

        /*$.post( "/api/ruta/update/index.php",
        {
         clientes:rels,
         action : $("#hiddenAction").val(),
         ID_Ruta : $("#hiddenRuta").val(),
         cve_ruta : $("#ClavRuta").val(),
         descripcion : $("#DescripRuta").val(),
         status : $("#StatusRuta").val(),
         cve_cia : $("#txtNomCompa").val(),

        } ,function( data ) {
         alert(data);
         });*/

    });

</script>
<script>
    $(document).ready(function(){
        $("#txtNomCompa").select2();

        $("#minimum").select2({
            minimumInputLength: 2
        });

        $("#minimum2").select2({
            minimumInputLength: 2
        });

    });
</script>




