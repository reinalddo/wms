<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaDiaO = new \Venta\Venta();

$listTransportes = new \Transporte\Transporte();
$vendedores = new \Usuarios\Usuarios();


$cve_almacen = $_SESSION['cve_almacen'];
$listTransportes = \db()->prepare("SELECT *  FROM t_transporte WHERE Activo = 1 and id_almac = (SELECT id FROM c_almacenp WHERE clave = '$cve_almacen')");
$listTransportes->execute();
$listTransportes = $listTransportes->fetchAll(PDO::FETCH_ASSOC);

$vere = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=45 and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=46 and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=47 and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=48 and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

$nuevos_pedidos = new \NuevosPedidos\NuevosPedidos();

?>
<!--
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
-->
<!-- Menu de recuperacion -->
<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recuperar Ruta</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar por Ruta...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGrid1()">
                                    <button type="submit" id="buscarA" class="btn btn-sm btn-primary">Buscar
                                </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table2"></table>
                            <div id="grid-pager2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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

<!-- ClientesxRuta -->
<script src="/js/plugins/footable/footable.all.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>


<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/select2.js"></script>
<script src="/js/plugins/footable/footable.all.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>


<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<!-- FooTable -->
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet" />
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right 
    {
        position: absolute;
        left: auto;
        right: 0;
    }
</style>

<input type="hidden" name="folios_grupo" id="folios_grupo" value="">


<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight" id="rut">
    <h3>Descargas</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="almacenes">Almacen: </label>
                                <select class="form-control" id="almacenes" name="almacenes" onchange="almacen()">
                                    <option value=" ">Seleccione el Almacen</option>
                                    <?php foreach( $almacenes->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="rutas_list">Rutas:</label> 
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
                                    <option value="todas">Todas las Rutas</option>
                                    <?php 
                                    foreach( $listaRuta->getAll($cve_almacen) AS $r ): 
                                    ?>
                                    <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( ".$r->cve_ruta." ) - ".$r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="diao_list">Día Operativo:</label> 
                                <select class="form-control chosen-select" id="diao_list" name="diao_list">
                                    <option value="">Seleccione Día Operativo</option>
                                    <?php 
                                    foreach( $listaDiaO->getAllDiaO($cve_almacen) AS $r ): 
                                    ?>
                                    <option value="<?php echo $r->DiaO; ?>"><?php echo $r->DiaO; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">

                            <div class="form-group">
                                <label>Fecha Inicial:</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechaini" type="text" class="form-control" value=""><?php // echo $fecha_semana; ?>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-2">

                            <div class="form-group">
                                <label>Fecha Final:</label>
                                <div class="input-group date" id="data_2">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechafin" type="text" class="form-control">
                                </div>
                            </div>


                        </div>

                    </div>
                    <div class="row">


                        <div class="col-md-4">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 10px;" id="txtCriterio" placeholder="Folio | Articulo | Ruta">
                                    <div class="input-group-btn">
                                        <button  onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary" style="margin: 10px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php 
                        /*
                        ?>
                        <div class="col-md-8" style="display: none;">
                            <?php if($ag[0]['Activo']==1){ ?>
                                <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button" style="margin: 10px;"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <?php } ?>
                            <a href="/api/v2/rutas/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos" style="margin: 0px;"><i class="fa fa-search"></i>&nbsp;&nbsp;Rutas inactivas</button>
                        </div>
                        <?php 
                        */
                        ?>
                    </div>
                    <br>
                    <a href="#" id="generarExcelDescarga" class="btn btn-primary" style="margin: 10px;" >
                        <span class="fa fa-file-excel-o"></span>  Reporte de Descarga
                    </a>
                    <br>
                        <div class="row" style="text-align: center;display: none;">
                            <br>
                            <b>Importe: </b><span id="timporte"></span><b> | </b><b>IVA: </b><span id="tiva"></span><b> | </b>
                            <b>Descuento: </b><span id="tdescuento"></span><b> | </b><b>Total: </b><span id="ttotal"></span><b> | </b>
                            <b>Total C: </b><span id="ttotalc"></span><b> | </b><b>Total P: </b><span id="ttotalp"></span><b> | </b>
                            <b>Promo C: </b><span id="tpromoc"></span><b> | </b><b>Promo P: </b><span id="tpromop"></span><b> | </b>
                            <b>Obseq C: </b><span id="tobseqc"></span><b> | </b><b>Obseq P: </b><span id="tobseqp"></span>
                        </div>
                        <div class="row" style="text-align: center;display: none;">
                            <br>
                            <b>Credito: </b><span id="tcredito">0.00</span><b> | </b>
                            <b>Cobranza: </b><span id="tcobranza">0.00</span><b> | </b>
                            <b>Adeudo: </b><span id="tadeudo">0.00</span>
                        </div>

                    <br>


                </div>
                <div class="ibox-content">
                    <div class="tabbable" id="tabs-131708">
                    <!--
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#panel-928563" id="simple" data-toggle="tab">Vista Simple</a>
                            </li>
                            <li>
                                <a href="#panel-594076" id="avanzada" data-toggle="tab">Vista Avanzada</a>
                            </li>
                        </ul>
                    -->
                        <div class="tab-content">
                            <div class="tab-pane active" id="panel-928563">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table"></table>
                                    <div id="grid-pager"></div>
                                </div>
                            </div>
                            <?php 
                            /*
                            ?>
                            <div class="tab-pane" id="panel-594076">
                                <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                                  <table class="footable table table-stripped toggle-arrow-tiny" style="min-width:600px;" data-paging="true" data-filtering="true" data-sorting="true" data-expand-first="true" data-paging-size="8">
                                      <thead>
                                          <tr>
                                              <th>Clave</th>
                                              <th>Almacen</th>
                                              <th>Nombre de la Ruta</th>
                                              <th>Status</th>
                                              <th data-breakpoints="all">Clave / Cliente</th>
                                              <th>Acciones</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php foreach( $listaRuta->getRutas() AS $p ): ?>
                                              <tr>
                                                  <td><?php echo $p->cve_ruta; ?></td>
                                                  <td> <?php echo $p->almacenp; ?></td>
                                                  <td><?php echo $p->descripcion; ?></td>
                                                  <td><?php echo $p->status; ?></td>
                                                  <td>
                                                    <table class="table-responsive">
                                                      <tr>
                                                        <td ALIGN="right" style="margin-right: 50px;">
                                                           <?php foreach( $listaRuta->traerClientesxRuta($p->ID_Ruta) AS $r ): ?>
                                                            <?php echo $r['clave']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                                                            <?php endforeach; ?>
                                                        </td>
                                                        <td>
                                                            <?php foreach( $listaRuta->traerClientesxRuta($p->ID_Ruta) AS $r ): ?>
                                                            <?php echo $r['razon']; ?><br>
                                                            <?php endforeach; ?>
                                                        </td>
                                                      </tr>
                                                    </table>
                                                  </td>
                                                  <td>
                                                      <a href="#" onclick="editar('<?php echo $p->ID_Ruta; ?>')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                                      <a href="#" onclick="borrar('<?php echo $p->ID_Ruta; ?>')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                                  </td>
                                              </tr>
                                          <?php endforeach; ?>
                                      </tbody>
                                      <tfoot>
                                          <tr>
                                              <td colspan="5">
                                                  <ul class="pagination pull-right"></ul>
                                              </td>
                                          </tr>
                                      </tfoot>
                                  </table>
                                </div>
                            </div>
                            <?php 
                            */
                            ?>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importar" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Importar</h4>
                </div>
                <div class="modal-body">
                    <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Seleccionar archivo excel para importar</label>
                            <input type="file" name="file" id="file" class="form-control"  required>
                        </div>
                    </form>
                    <div class="col-md-12">
                        <div class="progress" style="display:none">
                            <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                <div class="percent">0%</div >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div style="text-align: right">
                        <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#avanzada').on('click', function() {
        $("#busqueda")[0].style.display = 'none';
    });
    $('#simple').on('click', function() {
        $("#busqueda")[0].style.display = 'block';
    });

    var click_from_grupos = false;

    //almacenPrede();
    function almacenPrede() {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    document.getElementById('almacenes').value = data.codigo.id;
                    $('.chosen-select').chosen();
                    //almacen();
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
almacenPrede();

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function($) {
        //almacenPrede();
        console.log("almacen:", $("#almacenes").val());
        
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
            //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            //url: '/api/reportesRutas/lista/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                ruta: $("#rutas_list").val(),
                diao: $("#diao_list").val(),
                fechaini: $("#fechaini").val(),
                fechafin: $("#fechafin").val(),
                almacen: $("#almacenes").val(),

            },
            mtype: 'POST',
            colNames: ["Dia O", 'Fecha', 'Clave Artículo', 'Descripción', 'Cantidad', 'Folio'],
            colModel: [
                {name: 'dia_o',index: 'dia_o',width: 50,editable: false,sortable: false, align: 'right'},//1
                {name: 'fecha',index: 'fecha',width: 80,editable: false,sortable: false, align: 'center'},//2
                {name: 'clave',index: 'clave',width: 100,editable: false,sortable: false, align: 'left'},//3
                {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false, align: 'left'},//4
                {name: 'cantidad',index: 'cantidad',width: 100,editable: false,sortable: false, align: 'right'},//5
                {name: 'folio',index: 'folio',width: 120,editable: false,sortable: false, align: 'left'},//6
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'ID_Venta',
            viewrecords: true,
            //loadError: function(data){console.log("ERROR: ", data);},
            sortorder: "desc",
            loadComplete: function(data){
                console.log("Cobranza RES: ", data);
                console.log("Cobranza SQL Totales: ", data.sql_conteos);

/*
                $("#timporte").text(data.timporte);
                $("#tiva").text(data.tiva);
                $("#tdescuento").text(data.tdescuento);
                $("#ttotal").text(data.ttotal);
                $("#ttotalc").text(data.ttotalc);
                $("#ttotalp").text(data.ttotalp);
                $("#tpromoc").text(data.tpromoc);
                $("#tpromop").text(data.tpromop);
                $("#tobseqc").text(data.tobseqc);
                $("#tobseqp").text(data.tobseqp);
*/
                //$("#tcredito").text(data.total_credito);
                //$("#tcobranza").text(data.total_cobranza);
                //$("#tadeudo").text(data.total_adeudo);
            }, 
            loadError: function(data){
                console.log("Cobranza ERROR: ", data);
            }
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager', {
            edit: false,
            add: false,
            del: false,
            search: false
        }, {
            reloadAfterSubmit: true
        });


        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        function imageFormat(cellvalue, options, rowObject) 
        {
            var id_venta = rowObject[1];
            var html = '';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
            }, 0);
        }

        function beforeDeleteCallback(e) {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() {
            var grid = $(grid_selector);
            $.ajax({
                url: "index.php",
                dataType: "json",
                success: function(data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function() {}
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
        function styleCheckbox(table) {}

        //unlike navButtons icons, action icons in rows seem to be hard-coded
        //you can change them like this in here if you want
        function updateActionIcons(table) {}

        //replace icons with FontAwesome icons like above

        function updatePagerIcons(table) {}

        function enableTooltips(table) {
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
        }

        $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

        $("#diao_list").change(function(){

            ReloadGrid();

        });


        $("#rutas_list").change(function()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ruta: $(this).val(),
                    action: "extraer_diaso"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/reportesRutas/lista/filtros_select.php',
                success: function(data) {
                    //console.log("SUCCESS DIAS_O = ", data);
                    $("#diao_list").empty();
                    $("#diao_list").append(data);
                    $("#diao_list").trigger("chosen:updated");
                    //$('.chosen-select').chosen();
                },
                error: function(data)
                {
                    console.log("ERROR = ", data);
                }
            });
            //setTimeout(function(){ReloadGrid();}, 2000);
            ReloadGrid();
        });

        function ReloadGrid() 
        {
            console.log("almacen INIT: ", $("#almacenes").val());
            setTimeout(function(){
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        ruta: $("#rutas_list").val(),
                        diao: $("#diao_list").val(),
                        fechaini: $("#fechaini").val(),
                        fechafin: $("#fechafin").val(),
                        almacen: $("#almacenes").val()
                    },
                    url: '/api/reportesRutas/lista/index_descarga.php',
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
            }, 2000);
        }

        function almacen() 
        {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        ruta: $("#rutas_list").val(),
                        diao: $("#diao_list").val(),
                        fechaini: $("#fechaini").val(),
                        fechafin: $("#fechafin").val(),
                        almacen: $("#almacenes").val(),
                    },
                    datatype: 'json',
                    page: 1,
                    fromServer: true
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);

            /*
            var filtering = FooTable.get('.footable').use(FooTable.Filtering), // get the filtering component for the table
                filter = $("#almacenes option:selected").text(); // get the value to filter by
            if (filter == 'Seleccione el Almacen') 
            { // if the value is "none" remove the filter
                filtering.removeFilter('Almacen');
            } 
            else 
            { // otherwise add/update the filter.
                filtering.addFilter('Almacen', filter);
            }
            filtering.filter();
            */
        }

        function downloadxml(url) 
        {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) 
        {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;

    $("#generarExcelDescarga").click(function(){

        if($("#rutas_list").val() == "")// || $("#diao_list").val() == ""
        {
            swal("Error", "Debe Ingresar una Ruta ", "error");//y Dia Operativo
            return;
        }
        var criterio = $("#txtCriterio").val(),
            ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            fechaini = $("#fechaini").val(),
            fechafin = $("#fechafin").val(),
            almacen = $("#almacenes").val();

        $(this).attr("href", "/api/koolreport/excel/descargas/export.php?almacen="+almacen+"&ruta="+ruta+"&diao="+diao+"&fechaini="+fechaini+"&fechafin="+fechafin+"&criterio="+criterio);

    });


</script>

<script>
    $(document).ready(function() {
        $("#inactivos").on("click", function() {
            $modal0 = $("#coModal");
            $modal0.modal('show');
            ReloadGrid1();
        });
        // $("#cve_almacenp").select2();
        $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
    });
</script>



<script type="text/javascript">
    // IE9 fix
    if (!window.console) 
    {
        var console = {
            log: function() {},
            warn: function() {},
            error: function() {},
            time: function() {},
            timeEnd: function() {}
        }
    }

    jQuery(function($) {
        $('.footable').footable();
    });

    $("#txtCriterio").keyup(function(event) {
        if (event.keyCode == 13) 
        {
            $("#buscarA").click();
        }
    });

        $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        $('#data_2').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

</script>                                
<style>
    <?php if($edit[0]['Activo']==0) { ?>
        .fa-edit {
            display: none;
        }
    <?php } ?>
    <?php if($borrar[0]['Activo']==0) { ?>
        .fa-eraser {
            display: none;
        }
    <?php } ?>

</style>