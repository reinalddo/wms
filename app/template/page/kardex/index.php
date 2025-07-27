<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaArticulos = new \Articulos\Articulos();


$cve_almacen = $_SESSION['id_almacen'];

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

$movimiento_list = \db()->prepare("SELECT * from t_tipomovimiento WHERE Activo = 1");
$movimiento_list->execute();
$movimientos = $movimiento_list->fetchAll(PDO::FETCH_ASSOC);

$kardex_consolidado = \db()->prepare("SELECT cve_conf, Valor FROM t_configuraciongeneral WHERE cve_conf = 'kardex_consolidado' AND id_almacen = $cve_almacen");
$kardex_consolidado->execute();
$kardex_consolidado = $kardex_consolidado->fetch()["Valor"];

    $cve_proveedor = "";
    if(isset($_SESSION['id_proveedor']))
    {
        $cve_proveedor = $_SESSION['id_proveedor'];
    }

    if(isset($_SESSION['cve_proveedor']))
    {
        $cve_proveedor = $_SESSION['cve_proveedor'];

        if($cve_proveedor)
        {
            $almacen_proveedor = \db()->prepare("SELECT cve_almacen FROM c_usuario WHERE cve_proveedor = '$cve_proveedor'");
            $almacen_proveedor->execute();
            $cve_almacen = $almacen_proveedor->fetch()["cve_almacen"];

            $almacen_id = \db()->prepare("SELECT id FROM c_almacenp WHERE clave = '$cve_almacen'");
            $almacen_id->execute();
            $cve_almacen = $almacen_id->fetch()["id"];    
        }
    }

?>
  <input type="hidden" id="kardex_consolidado" value="<?php echo $kardex_consolidado; ?>">
  <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
  <input type="hidden" id="cve_almacen" value="<?php echo $cve_almacen; ?>">

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
<script src="/js/moment.js"></script>
<script src="/js/plugins/footable/footable.all.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>


<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight" id="rut">
    <h3>Kardex | Trazabilidad</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-4">
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
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="articulos_list">Artículos en Kardex:</label> 
                                <select class="form-control chosen-select" id="articulos_list" name="articulos_list">
                                    <option value="">Seleccione Artículo</option>
                                    <?php 
                                    foreach( $listaArticulos->getAllKardex($cve_almacen) AS $r ): 
                                    ?>
                                    <option value="<?php echo $r->cve_articulo; ?>"><?php echo "( ".$r->cve_articulo." ) - ".$r->des_articulo; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="lotes_list">Lote|Serie:</label> 
                                <select class="form-control chosen-select" id="lotes_list" name="lotes_list">
                                    <option value="">Seleccione Lote|Serie</option>
                                    <?php /*
                                    foreach( $vendedores->getAllVendedor() AS $ch ): 
                                    ?>
                                    <option value="<?php echo $ch->cve_usuario; ?>"><?php echo "( ".$ch->cve_usuario." ) - ".$ch->nombre_completo; ?></option>
                                    <?php endforeach;*/ ?>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechaI" type="text" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <div class="input-group date" id="data_2">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechaF" type="text" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Movimiento</label>
                                <div class="input-group date">
                                    <select class="form-control" id="movimiento" name="movimiento">
                                        <option value="">Seleccione</option>
                                    <?php 
                                    foreach( $movimientos AS $m ): 
                                    ?>
                                    <option value="<?php echo $m["id_TipoMovimiento"]; ?>"><?php echo $m["nombre"]; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div id="busquedaOC"> <br>
                                <div class="input-group" style="display: block;">
                                    <input type="text" class="form-control" name="OCBusq" style="margin: 6px;" id="OCBusq" placeholder="Buscar OC...">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div id="busqueda"> <br>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 6px;" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button  onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary" style="margin: 6px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="#" onclick="GenerarReporte()" id="reporte_kardex" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> Descargar Reporte Kardex PDF</a>
                        </div>

                        <div class="col-md-3">
                            <a href="#" onclick="GenerarReporteExcel()" id="reporte_kardex_excel" class="btn btn-primary"><i class="fa fa-file-excel-o"></i> Descargar Reporte Kardex Excel</a>
                        </div>

                        <div class="col-md-3">
                            <div class="checkbox">
                            <label for="kardex_consolidado_chk" style="cursor: pointer;">
                            <input type="checkbox" name="kardex_consolidado_chk" id="kardex_consolidado_chk" <?php if ($kardex_consolidado == 1) echo "checked"; ?> style="cursor: pointer;"> Generar Kardex Consolidado
                            </label>
                            </div>
                        </div>
                    
                    </div>
                     <div class="row" style="display:none;">
                      <div class="col-lg-2">
                        <div class="checkbox" id="chb_asignar">
                          <label for="btn-asignarTodo">
                            <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Seleccionar Todo
                          </label>
                        </div>
                      </div>
                    </div> 

                     <div class="row" style="display:none;">
                      <div class="col-lg-2">
                        <div class="checkbox" id="chb_asignar">
                          <label for="btn-reporteCajas">
                            <input type="checkbox" name="reporteCajas" id="btn-reporteCajas">Generar Kardex Consolidado (Cajas)
                          </label>
                        </div>
                      </div>
                    </div> 

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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<div class="modal inmodal" id="DetalleExistenciaCajas" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" style="position: relative;/* height: 100%; */overflow-y: scroll;overflow-x: hidden;width: 1000px;height: 1000px;max-height: 80%;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="CerrarDetalleCajas()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Detalle de Cajas</h4>
                <br>
                <h3>LP: <span id="LP_cajas"></span></h3>
            </div>
            <div class="modal-body">

                <div class="form-group" style="overflow-x:scroll;">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table3"></table>
                            <div id="grid-pager3"></div>
                        </div>
                </div>



            </div>
            <div class="modal-footer">
                <a href="#" onclick="CerrarDetalleCajas()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>


</div>


<script>
    $('#btn-import').on('click', function() {
        $('#btn-import').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/rutas/importar',
            type: 'POST',
            data: new FormData($('#form-import')[0]),
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.progress').show();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            // Custom XMLHttpRequest
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) 
                {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) 
                        {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) 
                            {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                setTimeout(
                    function()
                    {
                        if (data.status == 200) 
                        {
                            swal("Exito", data.statusText, "success");
                            $('#importar').modal('hide');
                            ReloadGrid();
                        }
                        else 
                        {
                            swal("Error", data.statusText, "error");
                        }
                    },
                    1000
                )
            },
        });
    });
</script>

<script type="text/javascript">
    $('#avanzada').on('click', function() {
        $("#busqueda")[0].style.display = 'none';
    });
    $('#simple').on('click', function() {
        $("#busqueda")[0].style.display = 'block';
    });

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
                    if($("#cve_proveedor").val() == '')
                        document.getElementById('almacenes').value = data.codigo.id;
                    else 
                        document.getElementById('almacenes').value = $("#cve_almacen").val();
                    $('.chosen-select').chosen();
                    almacen();
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
            url: '/api/kardex/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                kardex_consolidado: $("#kardex_consolidado").val(),
                criterio: $("#txtCriterio").val(),
                OCBusq: $("#OCBusq").val(),
                cve_articulo: $("#articulos_list").val(),
                lote: $("#lotes_list").val(),
                fechaI: $("#fechaI").val(),
                fechaF: $("#fechaF").val(),
                movimiento: $("#movimiento").val(),
                cve_proveedor: $("#cve_proveedor").val(),
                almacen: ($("#cve_proveedor").val()=='')?($("#almacenes").val()):($("#cve_almacen").val())
            },
            mtype: 'POST',
            colNames: ['Acciones', 'Fecha', "Clave", 'Articulo', 'Lote|Serie', 'Caducidad', 'Pallet|Contenedor', 'License Plate (LP)', 'Movimiento', 'Clave Origen', 'Almacén Origen', 'Origen', 'Almacén Destino', 'Destino', 'BL','Stock Inicial', 'Ajuste', 'Stock Final', 'Usuario', 'OC | Factura', 'Cantidad Cajas'],
            colModel: [
                {name: 'acc',index: 'acc',width: 80,editable: false,sortable: false, align: 'center', formatter: imageFormatCheck, hidden: <?php if($kardex_consolidado == 1) echo "false";else echo "true"; ?>},
                {name: 'fecha',index: 'fecha',width: 150,editable: false,sortable: false, align: 'center', hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'clave',index: 'clave',width: 150,editable: false,sortable: false, hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'des_articulo',index: 'des_articulo',width:200,editable: false,sortable: false, hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'lote',index: 'lote',width: 150,editable: false,sortable: false, hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'caducidad',index: 'caducidad',width: 80,editable: false,sortable: false, align: 'center', hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'pallet',index: 'pallet',width: 150,editable: false,sortable: false},
                {name: 'lp',index: 'lp', align: 'right',width: 150,editable: false,sortable: false},
                {name: 'movimiento',index: 'movimiento',width: 150,editable: false,sortable: false},
                {name: 'clave_origen',index: 'clave_origen',width: 150,editable: false,sortable: false, hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'almacen_origen',index: 'almacen_origen',width: 150,editable: false,sortable: false},
                {name: 'origen',index: 'origen',width: 150,editable: false,sortable: false},
                {name: 'almacen_destino',index: 'almacen_destino',width: 150,editable: false,sortable: false},
                {name: 'destino',index: 'destino',width: 150,editable: false,sortable: false},
                {name: 'bl',index: 'bl',width: 150,editable: false,sortable: false, hidden: true},
                {name: 'stockinicial',index: 'stockinicial',width: 150,editable: false,sortable: false, align: 'right', hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'ajuste',index: 'ajuste',width: 150,editable: false,sortable: false, align: 'right', hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'cantidad',index: 'cantidad',width: 150,editable: false,sortable: false, align: 'right', hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>},
                {name: 'usuario',index: 'usuario',width: 120,editable: false,sortable: false},
                {name: 'oc',index: 'oc',width: 120,editable: false,sortable: false, hidden: <?php if($kardex_consolidado == 1) echo "true";else echo "false"; ?>}, 
                {name: 'cant_cajas',index: 'cant_cajas',width: 120,editable: false,sortable: false, hidden: <?php if($kardex_consolidado == 1) echo "false";else echo "true"; ?>}, 
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'Clave',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data){console.log("SUCCESS: ", data);},
            loadError: function(data){console.log("ERROR: ", data);}
            //loadComplete: almacenPrede()

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

        function imageFormatCheck(cellvalue, options, rowObject) 
        {
                var fecha        = rowObject[1];
                var cve_articulo = rowObject[2];
                var descripcion  = rowObject[3];
                var pallet       = rowObject[6];
                var lp           = rowObject[7];
                var movimiento   = rowObject[8];
                var alm_origen   = rowObject[10];
                var BL_origen    = rowObject[11];
                var alm_destino  = rowObject[12];
                var BL_destino   = rowObject[13];
                var cantidad     = rowObject[17];
                var usuario      = rowObject[18];
                var cajas        = rowObject[20];

                var html = '';
                //if($("#permiso_consultar").val() == 1)
                    //html += '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="check_reporte permiso_consultar" title="Agregar a Reporte" data-fecha = "'+fecha+'" data-cve_articulo = "'+cve_articulo+'" data-descripcion = "'+descripcion+'" data-pallet = "'+pallet+'" data-lp = "'+lp+'" data-movimiento = "'+movimiento+'" data-alm_origen = "'+alm_origen+'" data-alm_destino = "'+alm_destino+'" data-cantidad = "'+cantidad+'" data-cajas = "'+cajas+'" data-blorigen = "'+BL_origen+'" data-bldestino = "'+BL_destino+'" data-usuario = "'+usuario+'">';
                if($("#permiso_consultar").val() == 1)
                    html += '<a href="#" onclick="verDetalleCajas(\'' + lp + '\')"><i class="fa fa-archive" alt="Ver Detalle Cajas" title="Ver Detalle Cajas"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

                return html;
        }

        function imageFormat(cellvalue, options, rowObject) 
        {
            var serie = rowObject[1];
            var clave_ruta = rowObject[2];
            $("#hiddenRuta").val(serie);
            var html = '';
            html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="select_clientes(\'' + clave_ruta + '\')"><i class="fa fa-times" alt="Eliminar Clientes Ruta" title="Eliminar Clientes Ruta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="select_transporte(\'' + clave_ruta + '\')"><i class="fa fa-truck" alt="Catálogo de Transportes" title="Catálogo de Transportes"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="select_chofer(\'' + clave_ruta + '\', 1)"><i class="fa fa-male" alt="Asignar Chofer" title="Asignar Chofer"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="select_choferEliminar(\'' + clave_ruta + '\', 0)"><i class="fa fa-user-times" alt="Eliminar Chofer" title="Eliminar Chofer"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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



//*******************************************************************************************************************
//*******************************************************************************************************************
    $(function($) {
        var grid_selector = "#grid-table3";
        var pager_selector = "#grid-pager3";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $("#coModal").width() - 60 );
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', $("#coModal").width() - 60 );
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url:'/api/kardex/index.php',
            datatype: "local",
            shrinkToFit: false,
      height:'auto',
            mtype: 'POST',
            colNames:['Ubicacion', 'LP', 'Caja', 'Clave Articulo', 'Descripción','Lote', 'Caducidad', 'Piezas Por Caja'],
            colModel:[
                {name:'ubicacion',index:'ubicacion',width:150, editable:false, sortable:false},
                {name:'LP',index:'LP',width:150, editable:false, sortable:false},
                {name:'CJ',index:'CJ',width:250, editable:false, sortable:false},
                {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},
                {name:'lote',index:'lote',width:150, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:150, editable:false, sortable:false, align:'center'},
                {name:'cantidad',index:'cantidad', width:150, editable:false, sortable:false, align:'right'}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data){console.log("Detalle Caja Data = ", data);},
            loadError: function(data){console.log("Detalle Caja ERROR = ", data);},
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        // Setup buttons
        $("#grid-table3").jqGrid('navGrid', '#grid-pager3',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );


        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var id_usuario = rowObject[0];
            // var estado = rowObject[1];
            //var correl = rowObject[4];
            //var url = "x/?serie="+poblacion+"&correl="+correl;
            //var url2 = "v/?serie="+poblacion+"&correl="+correl;
            $("#hiddenIDUsuario").val(id_usuario);
            //$("#hiddenIDEstado").val(estado);
            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            /* html += '<a href="#" onclick="editar(\''+id_usuario+'\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';*/
            html += '<a href="#" onclick="agregar(\''+id_usuario+'\')"><i class="fa fa-plus" alt="Nuevo"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

//*******************************************************************************************************************
//*******************************************************************************************************************


    function verDetalleCajas(lp)
    {

        console.log("DetalleKardexCajas");

      $('#grid-table3').jqGrid('clearGridData').jqGrid('setGridParam', {postData:
      {
        action: 'loadDetalleCajas',
        almacen: $("#almacenes").val(),
        lp: lp
      }, datatype: 'json'}).trigger('reloadGrid',[{current:true}]);

        $("#LP_cajas").text(lp);
        $('#DetalleExistenciaCajas').show();
    }
    function CerrarDetalleCajas()
    {
        $('#DetalleExistenciaCajas').hide();
    }

        function ReloadGrid() 
        {
            //console.log("Fecha Inicio = ", $("#fechaI").val());
            //console.log("Fecha Fin = ", $("#fechaF").val());
            console.log("criterio:", $("#txtCriterio").val());
            console.log("cve_articulo:", $("#articulos_list").val());
            console.log("lote:", $("#lotes_list").val());
            console.log("fechaI:", $("#fechaI").val());
            console.log("fechaF:", $("#fechaF").val());
            console.log("almacen:", ($("#cve_proveedor").val()=='')?($("#almacenes").val()):($("#cve_almacen").val()));

            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        kardex_consolidado: $("#kardex_consolidado").val(),
                        criterio: $("#txtCriterio").val(),
                        OCBusq: $("#OCBusq").val(),
                        cve_articulo: $("#articulos_list").val(),
                        lote: $("#lotes_list").val(),
                        fechaI: $("#fechaI").val(),
                        fechaF: $("#fechaF").val(),
                        movimiento: $("#movimiento").val(),
                        cve_proveedor: $("#cve_proveedor").val(),
                        almacen: ($("#cve_proveedor").val()=='')?($("#almacenes").val()):($("#cve_almacen").val())
                    },
                    success: function(data){console.log("data:", data);},
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        function almacen() 
        {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        kardex_consolidado: $("#kardex_consolidado").val(),
                        criterio: $("#txtCriterio").val(),
                        OCBusq: $("#OCBusq").val(),
                        cve_articulo: $("#articulos_list").val(),
                        lote: $("#lotes_list").val(),
                        fechaI: $("#fechaI").val(),
                        fechaF: $("#fechaF").val(),
                        movimiento: $("#movimiento").val(),
                        cve_proveedor: $("#cve_proveedor").val(),
                        almacen: ($("#cve_proveedor").val()=='')?($("#almacenes").val()):($("#cve_almacen").val()),
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

        function ReloadGrid1() 
        {
            $('#grid-table2').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio1").val(),
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
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

        function borrar(_codigo) 
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    ID_Ruta: _codigo,
                    action: "tieneCliente"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType)  
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    if (data.success == true) 
                    {
                        swal({
                            title: "¡Alerta!",
                            text: "La ruta esta siendo usada en este momento",
                            type: "warning",
                            showCancelButton: false,
                        });
                    } else {
                        swal({
                            title: "¿Está seguro que desea borrar la ruta?",
                            text: "Está a punto de borrar una ruta y esta acción no se puede deshacer",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Borrar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: false
                        }, function() {
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                data: {
                                    ID_Ruta: _codigo,
                                    action: "delete"
                                },
                                beforeSend: function(x) {
                                    if (x && x.overrideMimeType) {
                                        x.overrideMimeType("application/json;charset=UTF-8");
                                    }
                                },
                                url: '/api/ruta/update/index.php',
                                success: function(data) {
                                    if (data.success == true) 
                                    {
                                        ReloadGrid();
                                        ReloadGrid1();
                                        swal("Borrada", "La ruta ha sido borrada exitosamente", "success");
                                    } else {
                                        swal("Error", "Ocurrió un error al eliminar la ruta", "error");
                                    }
                                }
                            });
                        });
                    }
                }
            });
        }

        $("#articulos_list").change(function(){
            $("#lotes_list").val("");
            select_lotes($(this).val());
            ReloadGrid();

        });

        $("#lotes_list").change(function(){

            ReloadGrid();

        });


        $("#status").change(function() {
            if ($("#status")[0].checked) 
            {
                $("#mostrar2")[0].style.display = 'block';
                $("#mostrar1")[0].style.display = 'none';
                $("#status_send").val("A");
            } 
            else 
            {
                $("#mostrar1")[0].style.display = 'block';
                $("#mostrar2")[0].style.display = 'none';
                $("#status_send").val("B");
            }
        });

        function GenerarReporte()
        {
            var rep = [], k = 0;
            $(".check_reporte").each(function(i,j){
                if($(this).is(":checked"))
                {
                    console.log("i = ", i, " value = ", j.value);
                    rep.push({
                                fecha: $(this).data("fecha"),
                                cve_articulo: $(this).data("cve_articulo"),
                                descripcion: $(this).data("descripcion"),
                                pallet: $(this).data("pallet"),
                                lp: $(this).data("lp"),
                                movimiento: $(this).data("movimiento"),
                                alm_origen: $(this).data("alm_origen"),
                                alm_destino: $(this).data("alm_destino"),
                                cantidad: $(this).data("cantidad"),
                                blorigen: $(this).data("blorigen"),
                                bldestino: $(this).data("bldestino"),
                                usuario: $(this).data("usuario"),
                                cajas: $(this).data("cajas")
                             });
                    k++;
                }
            });
            
            //var reporte_cajas = ($("#btn-reporteCajas").is(":checked"))?1:0;
/*
            if($("#kardex_consolidado").val() == 1)
            {
            console.log("reporte = ", rep);
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;

            //var reporte = rep.join("==|==");
            //return;
            //$("#reporte_kardex_excel").attr("href", "/kardex/reporte_kardex?reporte="+JSON.stringify(rep));
            $("#reporte_kardex").attr("target","_blank");
            //$("#reporte_kardex").attr("href", "/api/koolreport/export/reportes/kardex/reporte-kardex?reporte_cajas="+reporte_cajas+"&reporte="+JSON.stringify(rep)+"&cve_cia="+cve_cia);
            $("#reporte_kardex").attr("href", "/api/koolreport/export/reportes/kardex/reporte-kardex?kardex_consolidado=1");

            setTimeout(function(){$("#reporte_kardex").attr("target","");}, 2000);


            //***************************************************************************************************************************
            }
            else
            {
*/
            var kardex_consolidado = $("#kardex_consolidado").val();
            var criterio = $("#txtCriterio").val();
            var OCBusq = $("#OCBusq").val();
            var cve_articulo = $("#articulos_list").val();
            var lote = $("#lotes_list").val();
            var fechaI = $("#fechaI").val();
            var fechaF = $("#fechaF").val();
            var cve_proveedor = $("#cve_proveedor").val();
            var almacen = $("#almacenes").val();
            var movimiento = $("#movimiento").val();

            if(cve_articulo == '' && lote == '' && fechaI == '' && fechaF == '' && criterio == '' && OCBusq == '' && kardex_consolidado == 0)
            {
                swal("Filtros Vacíos", "Debe Seleccionar algún filtro para generar el Reporte", "error");
                return;
            }
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;

            //$("#reporte_kardex").attr("href", "/api/koolreport/excel/kardex/export.php?almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF);
            $("#reporte_kardex").attr("target","_blank");
            //$("#reporte_kardex").attr("href", "/api/koolreport/export/reportes/kardex/reporte-kardex?kardex_consolidado="+kardex_consolidado+"&almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF+"&cve_cia="+cve_cia+"&cve_proveedor="+cve_proveedor+"&OCBusq="+OCBusq);
            $("#reporte_kardex").attr("href", "/api/koolreport/export/reportes/kardex/reporte-kardex?kardex_consolidado="+kardex_consolidado+"&almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF+"&cve_proveedor="+cve_proveedor+"&OCBusq="+OCBusq+"&tipomovimiento="+movimiento+"&cve_cia="+cve_cia);

            setTimeout(function(){$("#reporte_kardex").attr("target","");}, 2000);
            //}

        }

        function GenerarReporteExcel()
        {
            var rep = [], k = 0;
            $(".check_reporte").each(function(i,j){
                if($(this).is(":checked"))
                {
                    console.log("i = ", i, " value = ", j.value);
                    rep.push({
                                fecha: $(this).data("fecha"),
                                cve_articulo: $(this).data("cve_articulo"),
                                descripcion: $(this).data("descripcion"),
                                pallet: $(this).data("pallet"),
                                lp: $(this).data("lp"),
                                movimiento: $(this).data("movimiento"),
                                alm_origen: $(this).data("alm_origen"),
                                alm_destino: $(this).data("alm_destino"),
                                cantidad: $(this).data("cantidad"),
                                blorigen: $(this).data("blorigen"),
                                bldestino: $(this).data("bldestino"),
                                usuario: $(this).data("usuario"),
                                cajas: $(this).data("cajas")
                             });
                    k++;
                }
            });

            var kardex_consolidado = $("#kardex_consolidado").val();
            //if((!$("#btn-asignarTodo").is(":checked") && rep.length > 0) || reporte_cajas == 1)
            //$("#reporte_kardex_excel").attr("href", "/kardex/reporte_kardex?kardex_consolidado=1");

            //***************************************************************************************************************************
            var criterio = $("#txtCriterio").val();
            var OCBusq = $("#OCBusq").val();
            var cve_articulo = $("#articulos_list").val();
            var lote = $("#lotes_list").val();
            var fechaI = $("#fechaI").val();
            var fechaF = $("#fechaF").val();
            var cve_proveedor = $("#cve_proveedor").val();
            var movimiento = $("#movimiento").val();
            var almacen = $("#almacenes").val();

            if(cve_articulo == '' && lote == '' && fechaI == '' && fechaF == '' && criterio == '' && OCBusq == '' && kardex_consolidado == 0)
            {
                swal("Filtros Vacíos", "Debe Seleccionar algún filtro para generar el Reporte", "error");
                return;
            }
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;

           //console.log("/api/koolreport/excel/kardex/exportSpreadsheet.php?almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF);

            $("#reporte_kardex_excel").attr("href", "/kardex/reporte_kardex?kardex_consolidado="+kardex_consolidado+"&almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF+"&cve_proveedor="+cve_proveedor+"&OCBusq="+OCBusq+"&movimiento="+movimiento);

            //$("#reporte_kardex_excel").attr("href", "/api/koolreport/excel/kardex/exportSpreadsheet.php?almacen="+almacen+"&criterio="+criterio+"&cve_articulo="+cve_articulo+"&lote="+lote+"&fechaI="+fechaI+"&fechaF="+fechaF);
            $("#reporte_kardex_excel").attr("target","_blank");

            setTimeout(function(){$("#reporte_kardex_excel").attr("target","");}, 2000);

        }

        function select_transporte(clave_ruta)
        {
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_clave").text(clave_ruta);
            $('#modalTransportes').show();
        }

        function select_lotes(cve_articulo)
        {
            console.log("clave_articulo = ", cve_articulo);

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_articulo: cve_articulo,
                    action: "getLotesArticulosKardex"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/kardex/index.php',
                success: function(data) {
                    console.log("SUCCESS SELECT", data);
                        $('#lotes_list').empty();
                        $('#lotes_list').append(data.lotes);
                        $('#lotes_list').trigger("chosen:updated");
                }, 
                error: function(data) {
                    console.log("ERROR SELECT", data);
                }
            });
        }

        function select_chofer(clave_ruta, asignados_si_no)
        {

            select_operadores(clave_ruta, asignados_si_no);
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_claveElimin").text(clave_ruta);
            $('#modalChofer').show();
        }

        function select_choferEliminar(clave_ruta, asignados_si_no)
        {
            select_operadores(clave_ruta, asignados_si_no);
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_claveEliminar").text(clave_ruta);
            $('#modalChoferEliminar').show();
        }

        function select_clientes(clave_ruta)
        {
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_clave").text(clave_ruta);
            $('#modalClientes').show();

            console.log("clave_ruta", clave_ruta);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    Cve_Ruta: clave_ruta,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {

                    //console.log("SUCCESS", data);
                    $('#clientes').empty();
                    $('#clientes').append(data.clientes);
                    $('#clientes').trigger("chosen:updated");

                }
            });

        }

        function editar(_codigo) 
        {
            $("#hiddenRuta").val(_codigo);
            $("#_title").html('<h3>Editar Ruta '+$("#cve_ruta").val()+'</h3>');
            $("#emailMessage").html("");
            $("#CodeMessage").html("");
            $("#cve_ruta").prop('disabled', true);

            //$(".itemlist").remove();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    ID_Ruta: _codigo,
                    action: "load"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    if (data.success == true) 
                    {
                        if(data.venta_preventa == 0)
                            $("#venta_preventa").prop('checked', false);
                        else 
                            $("#venta_preventa").prop('checked', true);

                        if (data.status == "A")
                        {
                            $("#mostrar2").show();
                            $("#mostrar1").hide();
                            $("#status").prop('checked', true);
                            $("#status_send").val("A");
                        }
                        else
                        {
                            $("#mostrar1").show();
                            $("#mostrar2").hide();
                            $("#status").prop('checked', false);
                            $("#status_send").val("B");
                        }

                        //$('#codigo').prop('disabled', true);
                        $("#cve_ruta").val(data.cve_ruta);
                        $("#descripcion").val(data.descripcion);
                        $("#cve_almacenp").val(data.cve_almacenp);
                        console.log("status = ", data.status);
                        console.log("Activo = ", data.Activo);

                        for (var i = 0; i < data.clientes.length; i++) 
                        {
                            var rels = $("input[id='clientes']")
                                .map(function() {
                                    if ($(this).val() == data.clientes[i]['id_cliente'])
                                        $(this).prop("checked", true);
                                });
                        }

                        l.ladda('stop');
                        $("#btnCancel").show();
                        $('#list').removeAttr('class').attr('class', '');
                        $('#list').addClass('animated');
                        $('#list').addClass("fadeOutRight");
                        $('#rut').hide();

                        $('#FORM').show();
                        $('#FORM').removeAttr('class').attr('class', '');
                        $('#FORM').addClass('animated');
                        $('#FORM').addClass("fadeInRight");
                        $("#hiddenAction").val("edit");
                    }
                }
            });
        }

    function cancelar() {
        $(':input', '#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');

        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#rut').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    $("#venta_preventa").change(function()
    {
        if($("#venta_preventa").is(":checked"))
            console.log("ON");
        else
            console.log("OFF");
    });

    function agregar() 
    {

        $("#_title").html('<h3>Agregar Ruta</h3>');
        $("#cve_ruta").prop('disabled', false);
        $(':input', '#FORM')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');

        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeOutRight");
        $('#rut').hide();

        $('#FORM').show();
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeInRight");
        $("#hiddenAction").val("add");


        l.ladda('stop');
        $("#btnCancel").show();
        $("#cve_ruta").val("");
        $("#descripcion").val("");
        $("#status").val("");
        $("#cve_almacenp").val("");
        $("#hiddenRuta").val("0");
    }

    function traeModal() 
    {
        $("#myModalClientes .modal-title span").text($("#cve_ruta").val());
        $('#myModalClientes').show();
    }

    function minimizar() 
    {
        $('#myModalClientes').hide();
    }

    function EliminarTransporte()
    {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Agente = ", $('#agentes').val());
        //$('#transportes').val("");

        if($('#transportes').val())
        {
                        swal({
                            title: "¿Está seguro de Eliminar este Transporte?",
                            text: "",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Borrar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: false
                        }, function() {

                        //************************************************************************************************
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            data: {
                                cve_ruta: $("#ruta_clave_select").val(),
                                id_transporte: $("#transportes").val(),
                                action: "EliminarTransporte"
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) 
                                {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/ruta/update/index.php',
                            success: function(data) {
                                console.log("SUCCESS = ", data);
                                if (data.success == true) 
                                {
                                    swal("Éxito", 'El Transporte ha sido eliminado', "success");
                                    location.reload();
                                    //$("#btnCancel").show();
                                }
                                else 
                                {
                                    //alert(data.err);
                                    swal("Error", 'El Transporte no Existe en esta ruta', "error");
                                    l.ladda('stop');
                                    $("#btnCancel").show();
                                }
                            },
                            error: function(data)
                            {
                                console.log("ERROR = ", data);
                            }
                        });
                        minimizarT();
                        //**********************************************************************************************

                        });
        }
        
    }

    function AsignarTransporte()
    {
        //console.log("Ruta = ", $("#ruta_clave_select").val(), "Transpote = ", $('#transportes').val());
        //$('#transportes').val("");

        if($('#transportes').val())
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ruta: $("#ruta_clave_select").val(),
                    id_transporte: $("#transportes").val(),
                    action: "AsignarTransporte"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    console.log("SUCCESS = ", data);
                    if (data.success == true) 
                    {
                        location.reload();
                        //$("#btnCancel").show();
                    }
                    else 
                    {
                        //alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function(data)
                {
                    console.log("ERROR = ", data);
                }
            });
            minimizarT();
        }
        
    }

    function minimizarT() 
    {
        $('#modalTransportes').hide();
    }


    function AsignarChofer()
    {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Agente = ", $('#agentes').val());
        //$('#transportes').val("");

        //return;

        if($('#agentes').val())
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ruta: $("#ruta_clave_select").val(),
                    id_agente: $("#agentes").val(),
                    action: "AsignarChofer"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    console.log("SUCCESS = ", data);
                    if (data.success == true) 
                    {
                        location.reload();
                        //$("#btnCancel").show();
                    }
                    else 
                    {
                        //alert(data.err);
                        swal("Error", 'El Agente ya fué asignado a esta ruta', "error");
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function(data)
                {
                    console.log("ERROR = ", data);
                }
            });
            minimizarChofer();
        }
        
    }

    function EliminarCliente()
    {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Cliente = ", $('#clientes').val());
        //$('#transportes').val("");
//return;
        if($('#clientes').val())
        {
                        swal({
                            title: "¿Está seguro de Eliminar este Cliente?",
                            text: "",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Borrar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: false
                        }, function() {

                        //************************************************************************************************
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            data: {
                                cve_ruta: $("#ruta_clave_select").val(),
                                id_cliente: $("#clientes").val(),
                                action: "EliminarCliente"
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) 
                                {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/ruta/update/index.php',
                            success: function(data) {
                                console.log("SUCCESS = ", data);
                                if (data.success == true) 
                                {
                                    swal("Éxito", 'Cliente Eliminado de la Ruta', "success");
                                    location.reload();
                                    //$("#btnCancel").show();
                                }
                                else 
                                {
                                    //alert(data.err);
                                    swal("Error", 'El Cliente no está asignado a esta ruta', "error");
                                    l.ladda('stop');
                                    $("#btnCancel").show();
                                }
                            },
                            error: function(data)
                            {
                                console.log("ERROR = ", data);
                            }
                        });
                        minimizarChofer();
                        //**********************************************************************************************

                        });
        }
        
    }

    function EliminarChofer()
    {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Agente = ", $('#EliminarAgentes').val());
        //$('#transportes').val("");

        if($('#EliminarAgentes').val())
        {
                        swal({
                            title: "¿Está seguro de Eliminar este Agente?",
                            text: "Al eliminar al Agente se eliminará toda asignación de Visitas.",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Borrar",
                            cancelButtonText: "Cancelar",
                            closeOnConfirm: false
                        }, function() {

                        //************************************************************************************************
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            data: {
                                cve_ruta: $("#ruta_clave_select").val(),
                                id_agente: $("#EliminarAgentes").val(),
                                action: "EliminarChofer"
                            },
                            beforeSend: function(x) {
                                if (x && x.overrideMimeType) 
                                {
                                    x.overrideMimeType("application/json;charset=UTF-8");
                                }
                            },
                            url: '/api/ruta/update/index.php',
                            success: function(data) {
                                console.log("SUCCESS = ", data);
                                if (data.success == true) 
                                {
                                    location.reload();
                                    //$("#btnCancel").show();
                                }
                                else 
                                {
                                    //alert(data.err);
                                    swal("Error", 'El Agente no está asignado a esta ruta', "error");
                                    l.ladda('stop');
                                    $("#btnCancel").show();
                                }
                            },
                            error: function(data)
                            {
                                console.log("ERROR = ", data);
                            }
                        });
                        minimizarChofer();
                        //**********************************************************************************************

                        });
        }
        
    }


    $("#btn-asignarTodo").on("click", function(){
      $("input[type=checkbox].check_reporte").each(function(i, e){
        if($("#btn-asignarTodo").prop("checked") == false)
        {
          $("input[type=checkbox].check_reporte").prop("checked", true);
          //
          if($("input[type=checkbox].check_reporte").prop("checked") == true){$("input[type=checkbox].check_reporte").prop("checked", false);}
          else{$("input[type=checkbox].check_reporte").prop("checked", true);}
        }
        else
        {
          $("input[type=checkbox].check_reporte").prop("checked", false);
          //
          if($("input[type=checkbox].check_reporte").prop("checked") == true){$("input[type=checkbox].check_reporte").prop("checked", false);}
          else{$("input[type=checkbox].check_reporte").prop("checked", true);}
        }
      });
    });

    $("#kardex_consolidado_chk").click(function(){
        var kardex_consolidado = 0; if($("#kardex_consolidado_chk").is(":checked")) kardex_consolidado = 1;

        $.ajax({
            url: '/api/configuraciongeneral/update/index.php',
            data: {
                action: 'kardex_consolidado_chk',//'saveSurtidoCompleto',
                id_almacen: $("#cve_almacen").val(),
                kardex_consolidado: kardex_consolidado
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data){
            if(data.success)
            {
                //swal("Éxito", "Cambios Realizados", "success");
                window.location.reload();
            }
            else
                swal("Error", "Ha ocurrido un error, intente nuevamente por favor", "error");
        });
    });

    function minimizarChofer() 
    {
        $('#modalChofer').hide();
        $('#modalChoferEliminar').hide();
    }

    function minimizarCliente() 
    {
        $('#modalClientes').hide();
    }



    var l = $('#btnSave').ladda();
    l.click(function() {

        if ($("#cve_almacenp").val() == "") return;
        if ($("#cve_ruta").val() == "") return;
        if ($("#descripcion").val() == "") return;

        var venta_preventa = 0;
        if($("#venta_preventa").is(":checked"))
            venta_preventa = 1;

        $("#btnCancel").hide();
        l.ladda('start');
        if ($("#hiddenAction").val() == "add") 
        {
            var rels = $("input[id='clientes']")
                .map(function() {
                    if ($(this).is(":checked"))
                        return $(this).val();
                }).get();

            if ($("#status").is('checked'))
                $("#status").val("A");
            else
                $("#status").val("B");

            if($("#status_send").val() == "")
               $("#status_send").val("B");
/*
            console.log("#hiddenRuta = ", $("#hiddenRuta").val());
            console.log("#cve_ruta = ", $("#cve_ruta").val());
            console.log("#descripcion = ", $("#descripcion").val());
            console.log("#status_send = ", $("#status_send").val());
            console.log("#cve_almacenp = ", $("#cve_almacenp").val());
            console.log("clientes = ", rels);
*/
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    ID_Ruta: $("#hiddenRuta").val(),
                    cve_ruta: $("#cve_ruta").val(),
                    descripcion: $("#descripcion").val(),
                    status: $("#status_send").val(),
                    cve_almacenp: $("#cve_almacenp").val(),
                    venta_preventa: venta_preventa,
                    clientes: rels,
                    action: "add"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    console.log("SUCCESS = ", data);
                    if (data.success == true) 
                    {
                        location.reload();
                        $("#btnCancel").show();
                    }
                    else 
                    {
                        alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function(data)
                {
                    console.log("ERROR = ", data);
                }
            });
        }
        else 
        {
            var rels = $("input[id='clientes']")
                .map(function() {
                    if ($(this).is(":checked"))
                        return $(this).val();
                }).get();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    ID_Ruta: $("#hiddenRuta").val(),
                    cve_ruta: $("#cve_ruta").val(),
                    descripcion: $("#descripcion").val(),
                    status: $("#status_send").val(),
                    cve_almacenp: $("#cve_almacenp").val(),
                    venta_preventa: venta_preventa,
                    clientes: rels,
                    action: "edit"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    if (data.success == true) 
                    {
                        location.reload();
                        $("#btnCancel").show();
                    } 
                    else 
                    {
                        alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                }
            });
        } 
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


<script>
    function recovery(_codigo) 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Ruta: _codigo,
                action: "recovery"
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ruta/update/index.php',
            success: function(data) {
                if (data.success == true)
                {
                    ReloadGrid();
                    ReloadGrid1();
                }
            }
        });
    }
</script>

<script>
    $("#cve_ruta").keyup(function(e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) 
        {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);
            var cve_ruta = $(this).val();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ruta: cve_ruta,
                    action: "exists"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    if (data.success == false) 
                    {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    } 
                    else 
                    {
                        $("#CodeMessage").html(" Clave de ruta ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }
            });
        } 
        else 
        {
            $("#CodeMessage").html("Por favor, ingresar una Clave de ruta válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#cve_ruta").keyup(function(e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");
        if (claveCodeRegexp.test(claveCode)) 
        {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

        }
        else
        {
            $("#CodeMessage").html("Por favor, ingresar una Clave de ruta válida");
            $("#btnSave").prop('disabled', true);
        }
    });
  
    $(document).ready(function() {
      setTimeout(function(){
        if($("#kardex_consolidado_chk").is(":checked")) $("#movimiento").val(6);
        ReloadGrid();
      }, 1000);
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