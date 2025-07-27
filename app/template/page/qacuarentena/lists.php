<?php

include $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$almacenes = new \AlmacenP\AlmacenP();
$productos = new \Articulos\Articulos();
$lotes = new \Lotes\Lotes();

$listaProductos = $productos->getAll();

$contenedorAlmacen = new \AlmacenP\AlmacenP();
$almacenes = $contenedorAlmacen->getAll();


?>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

<style>
    #list {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0px;
        z-index: 999;
    }

    #FORM {
        width: 100%;
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }


    .input-group-btn .btn {
        /* border-radius: 0px; */
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>


<div class="modal inmodal" id="modalVerRegistro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Detalle de producto en cuarentena</h4>
            </div>
            <form id="myform">
                <div class="modal-body"><div class="row">

                    <div class="col-md-4 col-lg-4">
                        <div class="form-group">
                            <label>Clave</label>
                            <input id="detalle-clave" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-8">
                        <div class="form-group">
                            <label>Descripción</label>
                            <input id="detalle-descripcion" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <div class="form-group">
                            <label>Clave del almacén</label>
                            <input id="detalle-clavealmacen" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-8">
                        <div class="form-group">
                            <label>Nombre del almacén</label>
                            <input id="detalle-nombrealmacen" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <div class="form-group">
                            <label>Lote</label>
                            <input id="detalle-lote" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-8">
                        <div class="form-group">
                            <label>Fecha de inicio</label>
                            <input id="detalle-fecha" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-8">
                        <div class="form-group">
                            <label>Responsable</label>
                            <input id="detalle-responsable" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-8">
                        <div class="form-group">
                            <label>Creado</label>
                            <input id="detalle-creado" type="text" class="form-control" readonly="true"/>
                        </div>
                    </div>
                    </div>
                    <input type="hidden" id="hiddenAction">
                    <input type="hidden" id="hiddenid_role">
                </div>
                <div class="modal-footer">                   
                    <button type="button" class="btn btn-primary"  data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="wrapper wrapper-content  animated " id="list">

    <h3>Cuarentena</h3>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row"> 
                        <div class="col-sm-12 col-md-6 col-lg-4" style="margin-top:5px">                        
                            <div class="input-group">                         
                                <input type="text" class="form-control input-md" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                                <div class="input-group-btn">
                                    <a href="#" onclick="ReloadGrid()">
                                        <button class="btn btn-primary" type="submit" class="btn btn-sm btn-primary">
                                            <i class="fa fa-search"></i> Buscar
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-2 col-lg-2" style="margin-top:5px">
                            <a href="#" onclick="agregar()">
                                <button class="btn btn-primary" type="button">
                                    <i class="fa fa-plus"></i> Nuevo
                                </button>
                            </a>
                        </div> 
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">

                    <div class="row">

                        <div class="col-md-4 col-lg-4">
                            <div class="form-group">
                                <label>Producto</label>
                                <select id="filtro-producto" class="form-control chosen-select" data-id="" data-nombre="">
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach( $listaProductos as $value ): ?>
                                        <option value="<?php echo $value->cve_articulo; ?>"><?php echo '['.$value->cve_articulo .'] '. $value->des_articulo; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!--<div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <label>Nombre del producto</label>
                                <select id="filtro-nombreproducto" class="form-control chosen-select">
                                    <?php /*foreach( $productos->getAll() AS $p ):*/ ?>
                                        <option value="<?php /*echo $p->id; ?>"><?php echo $p->nombre; */?></option>
                                    <?php /*endforeach;*/ ?>
                                </select>
                            </div>
                        </div>-->

                        <div class="col-md-4 col-lg-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select id="filtro-almacen" class="form-control chosen-select">
                                <option value="">Seleccione un almacen</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>"><?php echo $almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-4 b-r">
                            <div class="form-group">
                                <label>Lote</label>
                                <select id="filtro-lote" class="form-control chosen-select">
                                    <option value="">Seleccione un producto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 text-right" style="margin-top:5px">
                            <button id="exportExcel" class="btn btn-primary">
                                <i class="fa fa-file-excel-o"></i>
                                Excel
                            </button>
                            <button id="exportPDF" class="btn btn-danger">
                                <i class="fa fa-file-pdf-o"></i>
                                PDF
                            </button>
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




<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Agregar artículos a cuarentena</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-md-8 col-lg-8 b-r">
                                <label>Filtros</label>
                                <div class="input-group">                                    
                                    <input id="txtBuscar" type="text" placeholder="Buscar..." class="form-control" value="" maxlength="20" required="true">
                                    <div class="input-group-btn">
                                        <a href="#" onclick="ReloadGridBusqueda()" class="btn btn-sm btn-primary">
                                            Buscar
                                        </a>
                                    </div>

                                </div>                              
                            </div>

                            <div class="col-md-4 col-lg-4">
                                <div class="form-group">
                                    <label>Filtros</label>
                                    <select class="chosen-select form-control" id="cmbFiltro" name="filtro">
                                        <!--<option value="">Seleccione un filtro</option>-->
                                        <option value="1" selected>Producto</option>
                                        <option value="2">Almacén</option>                                        
                                        <option value="3">Lote</option>
                                        <option value="4">Fecha de ingreso</option>
                                        <option value="5">Todos</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-12 text-right" style="margin-bottom:15px" >
                                <a href="#" onclick="agregarACuarentena()" class="btn btn-sm btn-primary">
                                    Agregar a cuarentena
                                </a>
                                <a href="#" onclick="cancelar()" class="btn btn-sm btn-danger">
                                    Cancelar
                                </a>
                            </div>

                            <div class="ibox-content">                            
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table-busqueda"></table>
                                    <div id="grid-pager-busqueda"></div>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-12 text-right" style="margin-bottom:15px" >
                                <a href="#" onclick="agregarACuarentena()" class="btn btn-sm btn-primary">
                                    Agregar a cuarentena
                                </a>
                                <a href="#" onclick="cancelar()" class="btn btn-sm btn-danger">
                                    Cancelar
                                </a>
                            </div>


                        </div>

                    </div>
                </form>
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
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>


<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap-imageupload.min.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">


<script>
    $(document).ready(function(){
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
    });
</script>


<script type="text/javascript">

    function buscarLotesDelProducto(){
        $.ajax({
            url: '/api/qacuarentena/lista/index.php',
            dataType: "json",
            type: "POST",
            data : {
                action : 'lotesDelProducto',
                producto : $('#filtro-producto').val()
            },
            success: function(data){
                select = $('#filtro-lote');
                select.chosen('destroy');
                select.html('')
                select.append('<option value="">Seleccione un lote</option>');
                $.each( data.lotes, function (index, item){
                    select.append('<option value="'+item+'">'+item+'</option>');
                });
                select.chosen().trigger("chosen:updated");
                
            }
        });
    }

    $('#filtro-producto').change(buscarLotesDelProducto);



    $("#exportExcel").on("click", function(){        
        if($("#almacen").val() === '') return false;
        var options = {
            criterio: $("#filtro-lote").val(),
            fechaInicio: $("#filtro-almacen").val(),
            fechaFin: $("#fechaf").val(),
            status: $("#filtro-lote").val(),
            almacen: $("#almacen").val(),
            filtro: $("#filtro").val(),
            facturaInicio: $("#factura_inicio").val(),
            facturaFin: $("#factura_final").val()
        };

        var form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input");
            input4 = document.createElement("input");
            input5 = document.createElement("input");
            input6 = document.createElement("input");
            input7 = document.createElement("input");
            input8 = document.createElement("input");
            input9 = document.createElement("input");
            input10 = document.createElement("input");
            input1.setAttribute('name', 'nofooternoheader');
            input1.setAttribute('value', 'true');
            input2.setAttribute('name', 'action');
            input2.setAttribute('value', 'getDataExcel');
            input3.setAttribute('name', 'criterio');
            input3.setAttribute('value', options.criterio);
            input4.setAttribute('name', 'fechaInicio');
            input4.setAttribute('value', options.fechaInicio);
            input5.setAttribute('name', 'fechaFin');
            input5.setAttribute('value', options.fechaFin);
            input6.setAttribute('name', 'status');
            input6.setAttribute('value', options.status);
            input7.setAttribute('name', 'almacen');
            input7.setAttribute('value', options.almacen);
            input8.setAttribute('name', 'filtro');
            input8.setAttribute('value', options.filtro);
            input9.setAttribute('name', 'facturaInicio');
            input9.setAttribute('value', options.facturaInicio);
            input10.setAttribute('name', 'facturaFin');
            input10.setAttribute('value', options.facturaFin);
            form.setAttribute('action', '/api/qacuarentena/reportes/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            form.appendChild(input5);
            form.appendChild(input6);
            form.appendChild(input7);
            form.appendChild(input8);
            form.appendChild(input9);
            form.appendChild(input10);
            document.body.appendChild(form);
            form.submit()
    });

    $("#exportPDF").on("click", function(){
        if($("#almacen").val() === '') return false;
        var options = {
            criterio: $("#criteriob").val(),
            fechaInicio: $("#fechai").val(),
            fechaFin: $("#fechaf").val(),
            status: $("#status").val(),
            almacen: $("#almacen").val(),
            filtro: $("#filtro").val(),
            facturaInicio: $("#factura_inicio").val(),
            facturaFin: $("#factura_final").val(),
            action: 'getDataPDF'
        };
        var title = "Reporte de Pedidos";
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';

        $.ajax({
            url: "/api/administradorpedidos/lista/index.php",
            type: "POST",
            data: options,
            success: function(data, textStatus, xhr){
                var data = JSON.parse(data);
                var content_wrapper = document.createElement('div');                
                /*Detalle*/
                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<tr>'+
                    '<th style="border: 1px solid #ccc">No. Orde</th>' +
                    '<th style="border: 1px solid #ccc">No. OC Cliente</th>' +
                    '<th style="border: 1px solid #ccc">Prioridad</th>' +
                    '<th style="border: 1px solid #ccc">Status</th>' +
                    '<th style="border: 1px solid #ccc">Cliente</th>' +
                    '<th style="border: 1px solid #ccc">Dirección</th>' +
                    '<th style="border: 1px solid #ccc">Código Dane</th>' +
                    '<th style="border: 1px solid #ccc">Ciudad</th>' +
                    '<th style="border: 1px solid #ccc">Estado</th>' +
                    '<th style="border: 1px solid #ccc">Cantidad</th>' +
                    '<th style="border: 1px solid #ccc">Volumen</th>' +
                    '<th style="border: 1px solid #ccc">Peso</th>' +
                    '<th style="border: 1px solid #ccc">Fecha Registro</th>' +
                    '<th style="border: 1px solid #ccc">Fecha Entrega</th>' +
                    '<th style="border: 1px solid #ccc">Usuario Activo</th>' +
                    '<th style="border: 1px solid #ccc">% Surtido</th>' +
                    '</tr>';
                var body_content = '';

                data.header.forEach(function(item, index){
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.orden+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.orden_cliente+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.prioridad+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.status+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.cliente+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.direccion+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.dane+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.ciudad+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.estado+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.cantidad+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.volumen+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.peso+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.fecha_pedido+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.fecha_entrega+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.usuario+'</td> '+
                        '<td style="border: 1px solid #ccc; white-space:nowrap;">'+item.surtido+'</td> '+
                        '</tr>  ';                                                         

                });

                tbody.innerHTML = body_content;
                thead.innerHTML = head_content;
                table.appendChild(thead);
                table.appendChild(tbody);

                content_wrapper.appendChild(table);

                content = content_wrapper.innerHTML;

                /*Creando formulario para ser enviado*/

                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", "/api/reportes/generar/pdf.php");
                form.setAttribute("target", "_blank");

                var input_content = document.createElement('input');
                var input_title = document.createElement('input');
                var input_cia = document.createElement('input');
                input_content.setAttribute('type', 'hidden');
                input_title.setAttribute('type', 'hidden');
                input_cia.setAttribute('type', 'hidden');
                input_content.setAttribute('name', 'content');
                input_title.setAttribute('name', 'title');
                input_cia.setAttribute('name', 'cia');
                input_content.setAttribute('value', content);
                input_title.setAttribute('value', title);
                input_cia.setAttribute('value', cia);

                form.appendChild(input_content);
                form.appendChild(input_title);
                form.appendChild(input_cia);

                document.body.appendChild(form);
                form.submit();
            }
        });
    })




    function detalles( id ){
        $('#modalVerRegistro').modal('show');
        
        $.ajax({
            url: '/api/qacuarentena/lista/index.php',
            dataType: "json",
            type: "POST",
            data : {
                action : 'producto',
                id : id
            },
            success: function(data){
                $('#detalle-clave').val(data.data.clave_producto);
                $('#detalle-descripcion').val(data.data.producto);
                $('#detalle-clavealmacen').val(data.data.clave_almacen);
                $('#detalle-nombrealmacen').val(data.data.almacen);
                $('#detalle-lote').val(data.data.lote);
                $('#detalle-fecha').val(data.data.fecha_ingreso);
                $('#detalle-responsable').val(data.data.responsable);
                $('#detalle-creado').val(data.data.creado);
            }
        });

    }


    var itemEnCuarentena = [];

    /**
     * Undocumented function
     *
     * @return void
     */
    function agregarACuarentena(){
        var items = [];
        var selected = $("#grid-table-busqueda").jqGrid('getGridParam','selarrrow');
        itemEnCuarentena = [];

        $.each( selected, function( index, value){
            row = $("#grid-table-busqueda").jqGrid('getRowData',value);
            itemEnCuarentena.push({
                clave_producto : row.clave,
                producto : row.producto,
                clave_almacen : row.clave_almacen,
                almacen : row.almacen,
                lote : row.lote,
                fecha : row.fecha_ingreso
             });
        });


        $.ajax({
            url: '/api/qacuarentena/update/index.php',
            dataType: "json",
            type: "POST",
            data : {
                items : itemEnCuarentena
            },
            success: function(data){
                cancelar();
                ReloadGrid();
            }
        });

    }



    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
    });

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 20 );
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
            url:'/api/qacuarentena/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                action : 'productosEnCuarentena',
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colNames:['','Clave del producto','Nombre','Almacén','Lote', 'Fecha de ingreso', ''],
            colModel:[
                {name:'id',index:'id',width:0, editable:false, sortable:false, hidden:true},                
                {name:'clave',index:'clave',width:120, editable:false, sortable:false},
                {name:'producto',index:'producto',width:250, editable:false, sortable:false, resizable: false},
                {name:'almacen',index:'almacen',width:100, editable:false, sortable:false},
                {name:'lote',index:'lote',width:100, editable:false, sortable:false, resizable: false},
                {name:'fecha_ingreso',index:'fecha_ingreso',width:150, editable:false, sortable:false, resizable: false},
                {name:'myac',index:'', width:40, fixed:true, sortable:false, resize:false, formatter:imageFormatView, frozen : true},
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'clave',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $(grid_selector).jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );




        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////

        var grid_selector_busqueda = "#grid-table-busqueda";
        var pager_selector_busqueda = "#grid-pager-busqueda";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_busqueda).jqGrid( 'setGridWidth', $(".page-content").width() - 20 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector_busqueda).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector_busqueda).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        })

        $(grid_selector_busqueda).jqGrid({
            url:'/api/qacuarentena/lista/index.php',
            datatype: "json",
            height: 250,
            postData: {
                action : 'findProductos',
                criterio: $("#txtBuscar").val(),
                filtro: $("#cmbFiltro").val()
            },
            mtype: 'POST',
            colNames:['Clave del producto','Nombre','clave_almacen','Almacén','Lote', 'Fecha de ingreso'],
            colModel:[
                {name:'clave',index:'clave',width:120, editable:false, sortable:false},
                {name:'producto',index:'producto',width:210, editable:false, sortable:false, resizable: true},
                {name:'clave_almacen',index:'almacen',width:0, editable:false, sortable:false, hidden:true},
                {name:'almacen',index:'almacen',width:80, editable:false, sortable:false},               
                {name:'lote',index:'lote',width:70, editable:false, sortable:false, resizable: false},
                {name:'fecha_ingreso',index:'fecha_ingreso',width:120, editable:false, sortable:false, resizable: false},
            ],
            multiselect: true,
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector_busqueda,
            sortname: 'producto',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $(grid_selector_busqueda).jqGrid('navGrid', '#grid-pager-busqueda',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenUbicacion").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="editar(\''+serie+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\''+serie+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }


        function imageFormatView( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenUbicacion").val(serie);

            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="detalles(\''+serie+'\')"><i class="fa fa-search" alt="Vew"></i></a>&nbsp';
            

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
            $(grid_selector_busqueda).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
        $('#grid-table')
            .jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                    criterio: $("#txtCriterio").val(),
                    criterio: $("#txtCriterio").val(),
                }, datatype: 'json',
                page : 1
            })
            .trigger('reloadGrid',[{current:true}]);
    }



    function ReloadGridBusqueda() {
        $('#grid-table-busqueda')
            .jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtBuscar").val(),
                    filtro: $("#cmbFiltro").val()
                }, 
                datatype: 'json',
                page : 1
            })
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


    function cancelar() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");

        $('#grid-table-busqueda').jqGrid('clearGridData')

    }

    function agregar() {
        $("#_title").html('<h3>Agregar artículos a cuarentena</h3>');
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#list').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");
        $("#hiddenUbicacion").val("0");
        $('#checkbox2').iCheck('disable');
        $("#checkbox1").click(function() {
            $("#checkbox2").iCheck('enable');
        });
    }

    var l = $( '.ladda-button' ).ladda();
    l.click(function() {

        $("#btnCancel").hide();

        l.ladda( 'start' );

            $.post('/api/ubicaciones/update/index.php',
                {
                    idy_ubica : $("#hiddenUbicacion").val(),
                    cve_almac: $("#Almacen").val(),
                    cve_pasillo: $("#txtClavePasillo").val(),
                    cve_rack: $("#txtClaveRack").val(),
                    Seccion: $("#txtClaveSeccion").val(),
                    cve_nivel: $("#txtClaveNivel").val(),
                    num_largo: $("#txtLargoUbicacion").val(),
                    num_alto: $("#txtAltoUbicacion").val(),
                    num_ancho: $("#txtAnchoUbicacion").val(),
                    Ubicacion: $("#txtUbicacion").val(),
                    PesoMaximo: $("#txtPesoMax").val(),
                    orden_secuencia: $('input[name=radio1]:checked').val(),
                    CodigoCSD: $('input[name=checkbox1]:checked').val(),
                    Reabasto: $('input[name=checkbox2]:checked').val(),
                    action : $("#hiddenAction").val()
                },
                function(response){
                    console.log(response);
                }, "json")
                .always(function() {
                    l.ladda('stop');
                    $("#btnCancel").show();
                    cancelar()
                    ReloadGrid();
                });
    });

</script>