<?php
include_once  $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$bd = "SCTP";
$listaAlmacen = new \AlmacenP\AlmacenP();
$listaRutas = new \Ruta\Ruta();
$listaTransportes = new \Transporte\Transporte();



if(isSCTP()){
    $bd = "SCTP";
}

if(isLaCentral()){
    $bd = "lacentral";
}
?>

<!-- Mainly scripts -->
    <script src="/js/jquery-2.1.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

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
    <script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
    <!-- Select -->
    <script src="/js/plugins/chosen/chosen.jquery.js"></script>

    <script src="/js/plugins/imgloader/fileinput.min.js"></script>
    <script src="/js/plugins/imgloader/locales/es.js"></script>
    <!-- Jquery Validate -->
    <script src="/js/plugins/validate/jquery.validate.min.js"></script>

    <script src="/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>

<style>
    .ui-jqgrid tr.jqgrow td[aria-describedby="grid-table_myac"]{
        display: table;
        width: 100%;
        text-align: center;
    }
    .ui-jqgrid tr.jqgrow td[aria-describedby="grid-table_myac"] a{
        margin: 0 5px;
    }
</style>
<?php /* if(!isSCTP() && !isLaCentral()): ?>
    <div class="wrapper wrapper-content">
        <div class="alert alert-danger">
            Ésta sección no fue diseñada para tu empresa, por favor contacta con el administrador del sistema.
        </div>
    </div>
<?php  else: */ ?>
    <link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">


    <div class="wrapper wrapper-content  animated fadeInRight" id="arti">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Inventario en Ruta</h3>
                        <div class="row">
                            <div class="col-xs-2" style="margin-bottom: 15px;">
                                    <select name="country" id="almacenP" style="width:100%;" class="form-control">
                                    <option value="">Seleccione Almacén</option>
                                    <?php foreach( $listaAlmacen->getAll() AS $p ): ?>
                                    <option value="<?php echo $p->id; ?>"><?php echo "(".$p->clave.") - ".$p->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
<?php /* ?>
                            <div class="col-lg-4" style="margin-bottom: 15px;">
                                  <input type="text" class="form-control" id="ruta_search" placeholder="Ruta...">
                            </div>
<?php */ ?>
                            <div class="col-xs-2">
                                <select name="ruta_busqueda" id="ruta_busqueda" style="width:100%;" class="form-control">
                                    <option value="">Seleccione Ruta</option>
                                    <?php foreach( $listaRutas->getAllInventario($_SESSION['id_almacen']) AS $p ): ?>
                                    <option value="<?php echo $p->cve_ruta; ?>"><?php echo "( ".$p->cve_ruta." ) - ".$p->descripcion; ?></option>
                                    <?php endforeach; ?>
                                </select>
<?php 
/*
?>
<br>
                                <select name="transporte_busqueda" id="transporte_busqueda" style="width:100%;" class="form-control">
                                    <option value="">Seleccione Transporte</option>
                                    <?php foreach( $listaTransportes->getAll($_SESSION['id_almacen']) AS $p ): ?>
                                    <option value="<?php echo $p->id; ?>"><?php echo "( ".$p->ID_Transporte." ) - ".$p->Nombre." | Placa: ".$p->Placas; ?></option>
                                    <?php endforeach; ?>
                                </select>
<?php 
*/
?>
                            </div>
<?php /* ?>
                            <div class="col-lg-4" style="margin-bottom: 15px;">
                                  <input type="text" class="form-control" id="producto_search" placeholder="Producto...">
                            </div>
<?php */ ?>
                            <div class="col-xs-2" style="margin-bottom: 15px;">
                                <div class="input-group">
                                  <input type="text" class="form-control" id="search" placeholder="Buscar...">
                                  <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" onclick="search()">Buscar</button>
                                  </span>
                                </div>
                            </div>
                            <div class="col-xs-3" style="margin-bottom: 15px;">
                                <div class="input-group">
                                    <b>Total Cajas: </b><span id="total_cajas"></span> <b> | Total Piezas: </b><span id="total_piezas"></span>
                                </div>
                            </div>
                            <div class="col-xs-3" style="margin-bottom: 15px;">
                                    <a href="#" id="generarExcel" class="btn btn-primary" style="margin: 10px;float: right; display: none;">
                                        <span class="fa fa-file-excel-o"></span> Reporte de Inventario en Ruta
                                    </a>
                                    <button id="generarExcelBtn" disabled class="btn btn-primary" style="margin: 10px;float: right;">
                                        <span class="fa fa-file-excel-o"></span> Reporte de Inventario en Ruta
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

    <script type="text/javascript">

    function almacenPrede() {

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function (data) {
                if (data.success == true) {

                    $('#almacenP').val(data.codigo.id).trigger("chosen:updated");
                    console.log("almacen Prede = ", $("#almacenP").val());

                }
            },
            error: function (res) {
                window.console.log(res);
            }
        });
    }

        $(function($) {
            console.log("almacen antes = ", $("#almacenP").val());
            almacenPrede();
            console.log("almacen despues = ", $("#almacenP").val());
            $("#almacenP").val($("#almacenP").val());
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
                url:'/api/articulosrutas/lists/index.php',
                datatype: "json",
                shrinkToFit: false,
                height:'auto',
                width: '1330',
                postData: {
                    action: 'getStock',
                    fecha_search: "",//$("#fecha_search").val(),
                    ruta: $("#ruta_busqueda").val(),
                    almacen: $("#almacenP").val(),
                    search: $("#search").val()
                },
                mtype: 'POST',
                colNames:[
                          'Acciones',
                          'Folio',
                          'Ruta',
                          'Transporte',
                          'Tipo Transporte',
                          'Descripción Ruta',
                          'Stop',
                          'Clave Destinatario',
                          'Destinatario',
                          'Pedido',
                          'Fecha Envío',
                          'Fecha Entrega',
                          'Clave',
                          'Descripción',
                          'Stock Cajas',
                          'Stock Piezas',
                          //'Stock Inicial',
                          //'Stock Inicial Pz',
                          'Stock Final',
                          'Stock Final Pz'
                ],

                colModel:[
                    {name:'myac',index:'myac', align:'center', width:100, fixed:true, sortable:false, resize:false, formatter: getActions, hidden: true},
                    {name:'folio',index:'folio',width:80, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'cve_ruta',index:'cve_ruta',width:80, editable:false, sortable:false, resizable: false, hidden: false},
                    {name:'transporte',index:'transporte',width:120, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'tipo_transporte',index:'tipo_transporte',width:120, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'ruta',index:'ruta',width:170, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'orden_stop',index:'orden_stop',width:80, editable:false, sortable:false, resizable: false, align: 'right', hidden: true},
                    {name:'cve_destinatario',index:'cve_destinatario',width:150, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'destinatario',index:'destinatario',width:200, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'pedido',index:'pedido',width:110, editable:false, sortable:false, resizable: false, hidden: true},
                    {name:'fecha',index:'fecha',width:110, editable:false, sortable:false, resizable: false, align: 'center', hidden: true},
                    {name:'fecha_ent',index:'fecha_ent',width:110, editable:false, sortable:false, resizable: false, align: 'center', hidden: true},
                    {name:'clave',index:'clave',width:110, editable:false, sortable:false, resizable: false},
                    {name:'descripcion',index:'descripcion',width:200, editable:false, sortable:false, resizable: false},
                    {name:'stock_ini',index:'stock_ini',width:130, editable:false, sortable:false, resizable: false, align: 'right'},
                    {name:'stock_ini_pz',index:'stock_ini_pz',width:130, editable:false, sortable:false, resizable: false, align: 'right'},
                    {name:'stock_fin',index:'stock_fin',width:130, editable:false, sortable:false, resizable: false, align: 'right'},
                    {name:'stock_fin_pz',index:'stock_fin_pz',width:130, editable:false, sortable:false, resizable: false, align: 'right', hidden: true}
                ],
                rowNum:30,
                rowList:[30,40,50],
                pager: pager_selector,
                sortname: 'descripcion',
                viewrecords: true,
                loadComplete: function(data){console.log("SUCCESS: ", data); $("#total_cajas").text(data.total_cajas); $("#total_piezas").text(data.total_piezas);},
                loadError: function(data){console.log("ERROR: ", data)},
                sortorder: "asc"
            });

            // Setup buttons
            $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                    {edit: false, add: false, del: false, search: false},
                                    {height: 200, reloadAfterSubmit: true}
                                   );

        function getActions(cell, options, row)
        {
            var id = row[1];
            var folio = row[4];
            //onclick="printExcel(${id},'${folio}')"
//            var html = `<a href="#" onclick="verDetalle(${id},'${folio}')" title="Ver Detalle"><i class="fa fa-search"></i></a>
//                <a href="/embarques/exportar?id=${id}&folios=${folio}" title="Imprimir ASN"><i class="fa fa-file-excel-o"></i></a>
//                <a href="#" onclick="printPDF(${id})" title="Lista de Embarque"><i class="fa fa-file-pdf-o"></i></a>`;
            var html = `<a href="#" onclick="#" title="Ver Detalle"><i class="fa fa-search"></i></a>
                <a href="#" title="Imprimir ASN"><i class="fa fa-file-excel-o"></i></a>
                <a href="#" onclick="#" title="Lista de Embarque"><i class="fa fa-file-pdf-o"></i></a>`;
            return html;
        }

            $(window).triggerHandler('resize.jqGrid');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        //setTimeout(function(){search();}, 2000);

        $("#generarExcel").click(function(){

            var almacen = $("#almacenP").val(),
                ruta = $("#ruta_busqueda").val(), 
                search = $("#search").val();

            console.log("almacen="+almacen+"&ruta="+ruta+"&search="+search);

            $(this).attr("href", "/api/koolreport/excel/inventario_en_ruta/export.php?almacen="+almacen+"&ruta="+ruta+"&search="+search+"");

        });

        $("#ruta_busqueda").change(function()
        {
            if($(this).val() == '')
            {
                $("#generarExcelBtn").show();
                $("#generarExcel").hide();
            }
            else
            {
                $("#generarExcelBtn").hide();
                $("#generarExcel").show();
            }

            search();
        });

        function search() {
            //console.log("fecha_search = ", $("#fecha_search").val());
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {postData: {
                    action: 'getStock',
                    fecha_search: "",//$("#fecha_search").val(),
                    almacen: $("#almacenP").val(),
                    ruta: $("#ruta_busqueda").val(),
                    search: $("#search").val()
                }, datatype: 'json', page : 1})
                .trigger('reloadGrid',[{current:true}]);
        }
    </script>
<?php //endif; ?>