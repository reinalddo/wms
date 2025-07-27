<?php
$listaAP = new \AlmacenP\AlmacenP();
//$model_almacen = $almacenes->getAll();
$R = new \Ruta\Ruta();
$rutas = $R->getAll();
$U = new \Usuarios\Usuarios();
$usuarios = $U->getAll();
$ciudadSql = \db()->prepare("SELECT DISTINCT(Ciudad) as ciudad  FROM th_dest_pedido ");
$ciudadSql->execute();
$ciudades = $ciudadSql->fetchAll(PDO::FETCH_ASSOC);
$AreaEmbarq = new \AreaEmbarque\AreaEmbarque();

if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    .table{
        margin-top: 2px;
        border: 1px solid #dddddd;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #dddddd;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    .table > thead > tr {
        background-color: #f7f7f7;
    }
    .table > thead > tr > th {
        font-size: 12px;
    }
    .table > tfoot > tr {
        background-color: #f7f7f7;
    }
    .table > tfoot > tr > th {
        font-weight: 400;
        font-size: 11px;
        font-family: Verdana,Arial,sans-serif;
    }
    .table > tfoot .sep{
        width: 1px;
        height: 15px;
        background-color: #dddddd;
        display: inline-block;
        margin: 0px 5px;
        position: relative;
        top:3px
    }
    .table .page{
        width: 50px;
        height: 20px;
        border: 1px solid #dddddd;
    }
    .table a {
        color: #000;
    }
  .fotos {
    width: 200px;    
  }
  
</style>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<!-- FooTable -->
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- ClientesxRuta -->
<script src="/js/plugins/footable/footable.all.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/footable/footable.all.min.js"></script>
<script src="/js/dragdrop.js"></script>
<!-- Drag & Drop Panel -->
<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<style type="text/css">
    .ui-jqgrid,
    .ui-jqgrid-view,
    .ui-jqgrid-hdiv,
    .ui-jqgrid-bdiv,
    .ui-jqgrid,
    .ui-jqgrid-htable,
    #grid-table,
    #grid-pager {
        max-width: 100%;
    }
</style>

<!-- Administrador de pedidos -->
<div class="wrapper wrapper-content  animated fadeInRight" id="list">
    <h3>Activos</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox-title">
                <div class="row">
                    <div class="col-lg-4">
                        <label>Buscar</label>
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar...">
                            <div class="input-group-btn">
                                <a href="#" onclick="buscar()">
                                    <button type="submit" class="btn btn-sm btn-primary" id="buscarP" val="">Buscar</button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <!--button id="exportalo" class="btn btn-primary pull-right" >
                            <span class="fa fa-upload"></span>Exportar
                        </button-->
                    </div>
                </div>
                
            </div>
            <div class="ibox-content">
                <div class="jqGrid_wrapper">
                    <div class="text-left">
                        <label class="text-right"><input type="checkbox" id="btn-asignar-todo" /> Seleccionar todo</label>
                    </div>
                    <div class="table-responsive">
                        <table id="grid-table" class="table" style="table-layout: auto;width: 100%;"></table>
                        <div id="grid-pager"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="asignacionActivo" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Asignación de Activo fijo <span id="clave_articulo">--</span></h4>
                </div>
                <div class="modal-body">
                    <input id="id_serie" value="" hidden>
                    <div class="row">
                        <label>Clientes</label>
                        <select id="cve_clientes" class="form-control chosen-select">
                            <option value="">Seleccionar Cliente</option>
                        </select>
                    </div>
                    <div class="row">
                        <label>Nombre del trabajador a asignar</label>
                        <input type="text" class="form-control input-sm" name="nombre" id="nombre" placeholder="Nombre">
                    </div>
                    <div class="row">
                        <label>RFC del trabajador a asignar</label>
                        <input type="text" class="form-control input-sm" name="rfc" id="rfc" placeholder="RFC">
                    </div>
                    <div class="row">
                        <label>Clave del trabajador a asignar</label>
                        <input type="text" class="form-control input-sm" name="clave" id="clave" placeholder="Clave">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btnAsignar" type="button" class="btn btn-primary pull-right" onclick="asignarActivo()"><span class="fa fa-sign-out"></span> Asignar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function($) 
    {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () 
            {
                $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
            }
        );
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) 
            {
                if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) 
                {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() 
                    {
                        $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                    }, 0);
                }
            }
        );
        var lastsel;

        $(grid_selector).jqGrid({
            url:'/api/activos/lista/index.php',
            datatype: "json",
            contentType: "application/json",
            shrinkToFit: false,
            height:'auto',
            mtype: 'POST',
            colNames:[ "Acciones","Seleccionar","Id","Clave de articulo", "Descripcion", "Numero de serie", "Clave de activo", "Fecha de ingreso"],
            colModel:[
                {name:'acciones',index:'acciones', width:100, fixed:true, sortable:false, resize:false,align: 'center',formatter:imageFormat},
                {name:'Seleccionar',index:'Seleccionar', align:'center', width:80, fixed:true, sortable:false, resize:false, formatter: agregarcheck},
                {name:'id',index:'id', width: 50, editable:false, sortable:false, hidden: false},
                {name:'cve_articulo',index:'cve_articulo', width: 150, editable:false, sortable:false, hidden: false},
                {name:'des_articulo',index:'des_articulo', width: 250, editable:false, sortable:false, hidden: false},
                {name:'numero_serie',index:'numero_serie', width: 150, editable:false, sortable:false},
                {name:'Cve_Activo',index:'Cve_Activo', width: 150, editable:false, sortable:false},
                {name:'fecha_ingreso',index:'fecha_ingreso', width: 150, editable:false, sortable:false},
            ],
            rowNum:30,
            rowList:[30,40,50,100],
            pager: pager_selector,
            sortname: 'id',
            viewrecords: true,
            sortorder: "asc",
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        function imageFormat(cellvalue,options,rowObject )
        {
            //html = '<a href="#" onclick="imprimirActivo(\''+rowObject[6]+'\')"><i class="fa fa-print" title="Imprimir etiqueta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp';
            html = '<a href="#" onclick="asignar(\''+rowObject[2]+'\')"><i class="fa fa-user" title="Asignar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp';
            //html += '<a href="#" onclick="informeResguardo(\''+rowObject[2]+'\')"><i class="fa fa-file-pdf-o" title="Informe de Resguardo"></i></a>&nbsp;&nbsp;&nbsp;&nbsp';
            return html;
        }
      
        function agregarcheck(cell, options, row){
            var id = row[0];
            var html =`<input type="checkbox" name="my_check">`;
            return html;
        }
    }); 
  
    $("#btn-asignar-todo").on('click', function(e)
    {
        var $checkboxes = $('td[aria-describedby="grid-table_Seleccionar"] input[type="checkbox"]');
        if(e.target.checked)
        {
            if($checkboxes.length > 0)
            {
                $checkboxes.each(function(i,v)
                {
                    v.checked = true;
                });
            }
        } 
        else 
        {
            if($checkboxes.length > 0)
            {
                $checkboxes.each(function(i,v)
                {
                    v.checked = false;
                });
            }
        }
    });
    
    function imprimirActivo(data) 
    {
        if(data != ""){
            var myGrid = $('#grid-table'),
                i,
                rowData,
                folios = [],
                rowIds = myGrid.jqGrid("getDataIDs"),
                n = rowIds.length;

            for (i = 0; i < n; i++) {
                rowData = myGrid.jqGrid("getRowData", rowIds[i]);
                if (rowData.embarcar=='Yes') {
                    folios.push(rowData.pedido);
                }
            }

            form = document.createElement('form'),
            form.setAttribute('method', 'post');
            form.setAttribute('action', '/reportes/pdf/Activos');
            form.setAttribute('target', '_blank');
            nofooter = document.createElement('input');
            nofooter.setAttribute('name', 'nofooternoheader');
            nofooter.setAttribute('value', '1');
            form.appendChild(nofooter);
            codigo = document.createElement('input');
            codigo.setAttribute('name', 'claveActivo');
            codigo.setAttribute('value', data);
            form.appendChild(codigo);
            document.getElementsByTagName('body')[0].appendChild(form);
            form.submit();
        }
        else 
        {
            swal("Error", "Este Activo fijo no se encuentra asignado", "error");
        }
    }
  
    function buscar() {
        $('#grid-table').jqGrid('clearGridData')
          .jqGrid('setGridParam', {postData: {
              //almacen: $("#almacen").val(),
              criterio: $("#buscar").val()
              //action: 'loadGrid'
          }, datatype: 'json'})
          .trigger('reloadGrid',[{current:true}]);
    }
  
    function asignar(id)
    {
        console.log(id);
        fillSelectArti();
        $("#clave_articulo").html(" "+id);
        $("#id_serie").val(id);
        $("#asignacionActivo").modal('show');
    }
  
    
    function fillSelectArti()
    {  
        $.ajax({
            url: "/api/activos/lista/index.php",
            type: "POST",
            dataType: "json",
            data: {
                action: 'getClientes'
            },
            success: function(data){
                var option = "<option value =''>Seleccione un Cliente("+data.cliente.length+")</option>";
                data.cliente.forEach(function(element)
                {
                    option += '<option value = "'+element.clave_prov+'">'+element.nombre+'</option>';
                });
                cve_clientes.innerHTML = option;
                $(cve_clientes).trigger("chosen:updated");
            }
        });
    }
  
    function asignarActivo()
    {
        console.log("asignacion activo",$("#id_serie").val());
        if($("#cve_clientes").val() != "" && $("#nombre").val() != "" &&  $("#rfc").val() != "" && $("#clave").val() != "")
        {
            $.ajax({
                url: "/api/activos/lista/index.php",
                type: "POST",
                dataType: "json",
                data: {
                    id: $("#id_serie").val(),
                    cve_cliente: $("#cve_clientes").val(),
                    nombre: $("#nombre").val(),
                    rfc: $("#rfc").val(),
                    clave: $("#clave").val(),
                    action: 'asignarActivo'
                },
                success: function(data){
                    console.log(data);
                    if(data.success)
                    { 
                        $('#grid-table').trigger( 'reloadGrid' );
                        $("#asignacionActivo").modal('hide');
                        swal("Asignado", "El activo fijo ha sido asignado", "success");
                    }
                    else  
                    {
                        swal("Error", "Este activo fijo ya ha sido asignado con anterioridad en el pedido "+data["folio"], "error");
                    }
                }
            });
        }
        else
        {
            swal("Error", "Favor de llenar todos los campos", "error");
        }
    }
  
    function informeResguardo(id)
    {
        console.log("informeResguardo",id);
      
        var form = document.createElement('form'),
            input_nofooter = document.createElement('input'),
            input_folio = document.createElement('input');

        form.setAttribute('method', 'post');
        form.setAttribute('action', '/reportes/pdf/resguardo');
        form.setAttribute('target', '_blank');

        input_nofooter.setAttribute('name', 'nofooternoheader');
        input_nofooter.setAttribute('value', '1');

        input_folio.setAttribute('name', 'folio');
        input_folio.setAttribute('value', id);

        form.appendChild(input_nofooter);
        form.appendChild(input_folio);

        document.getElementsByTagName('body')[0].appendChild(form);
        form.submit();
    }
</script>