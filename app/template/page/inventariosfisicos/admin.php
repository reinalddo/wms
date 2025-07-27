<?php
$almacenes = new \AlmacenP\AlmacenP();
$model_almacen = $almacenes->getAll();
$usuarios = new \Usuarios\Usuarios();
$usuarios = $usuarios->getAll();


$mod=59;
$var1=179;
$var2=180;
$var3=181;
$var4=182;

$vere = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var1."' and id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);


$agre = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var2."' and id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);


$edita = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var3."' and id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);


$borra = \db()->prepare("select * from t_profiles as a where id_menu='".$mod."' and id_submenu='".$var4."' and id_role='".$_SESSION["perfil_usuario"]."'");
$borra->execute();
$borrar = $borra->fetchAll(PDO::FETCH_ASSOC);

?>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

<!-- Sweet Alert -->
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">


<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>

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
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Drag & Drop Panel -->
<script src="/js/dragdrop.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script src="/js/plugins/iCheck/icheck.min.js"></script>

<!-- Clock picker -->
<script src="/js/plugins/clockpicker/clockpicker.js"></script>

<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>



    <!-- Mainly scripts -->
    <!-- Barra de nuevo y busqueda -->
    <div class="wrapper wrapper-content  animated fadeInRight">
        <h3>Inventarios</h3>
        <div class="row">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <!--<li class="active"><a data-toggle="tab" href="#tab-1"> Programación de Inventario Físico</a></li>-->
                        <li class="active"><a data-toggle="tab" href="#tab-2">Administración de Inventario</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body">
                                <div class="ibox">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <div class="input-group date" id="data_1">
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="Fecha" type="text" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="email">Almacén</label>
                                                <select name="almacen" id="almacen" onchange="almacen()" class="chosen-select form-control">
												<option value="">Seleccione Almacén</option>
												<?php foreach( $model_almacen AS $almacen ): ?>
												<?php if($almacen->Activo == 1):?>
												<option value="<?php echo $almacen->clave."|".$almacen->id; ?>"><?php echo "($almacen->clave) ". $almacen->nombre; ?></option>
												<?php endif; ?>
												<?php endforeach; ?>
											</select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="email">Zona de Almacenaje</label>
                                                <select name="zona" id="zona" class="chosen-select form-control">

											</select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="email">Rack</label>
                                                <select name="rack" id="rack" class="chosen-select form-control">
											</select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input type="checkbox" name="recepcion" id="recepcion" class="i-checks" disabled>
                                                <label style="margin-left: 10px;">Áreas de Recepción</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <div class="form-group">
                                                <button id="cargarUbicaciones" disabled class="btn btn-primary" type="button">Cargar Ubicaciones</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ibox-content dragdrop">
                                        <div class="form-group">
                                            <div class="col-sm-12" style="margin-bottom: 30px">
                                                <input type="checkbox" id="selectAll">
                                                <label for="selectAll">Seleccionar Todo</label>
                                            </div>
                                        </div>
                                        <div class="form-group" id="dragUbicaciones">
                                            <div class="col-md-6 relative">
                                                <label for="email">Ubicaciones Disponibles</label>
                                                <ol data-draggable="target" id="fromU" class="wi">
                                                </ol>
                                                <button class="btn btn-primary floating-button" onclick="add('#fromU', '#toU')">>></button>
                                                <button class="btn btn-primary floating-button" onclick="remove('#toU', '#fromU')" style="margin-top: 40px"><<</button>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="email">Ubicaciones Asignadas</label>
                                                <ol data-draggable="target" id="toU" class="wi">
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button id="guardar" class="btn btn-primary pull-right" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Planificar Inventario</button>
                                        </div>


                                    </div>
                                </div>
                            </div>

                        </div>
                        <div id="tab-2" class="tab-pane active">
                            <div class="panel-body">
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

    <!-- Modal -->
    <div class="modal fade" id="conModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Aviso de confirmación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Los datos fueron guardados satisfactoriamente</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Administración de Inventarios Físico</h4>
                    </div>
                    <div class="modal-body">
                        <div class="ibox-content">
                            <div class="jqGrid_wrapper" id="detalle_wrapper">
                                <table id="grid-detalles"></table>
                                <div id="grid-pager2"></div>
                            </div>
                            <div class="hidden" id="contador">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <input type="hidden" id="hiddenInventario">
                                        <div class="form-group">
                                            <label>Seleccione conteo</label>
                                            <select name="conteo" id="conteo" class="form-control"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="jqGrid_wrapper" id="detalleconteo">
                                <table id="grid-table3"></table>
                                <div id="grid-pager3"></div>
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

    <div class="modal fade" id="asignarUsuario" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar Usuario a Inventario</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Folio Nº <span id="folionumero">0</span></label>
                        </div>
                        <div class="form-group">
                            <label>Usuarios Disponibles: </label>
                            <select id="usuario" class="form-control chosen-select">
						</select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="guardarUsuario" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coModalCiclico" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Administración de Inventario Ciclico</h4>
                    </div>
                    <div class="modal-body">
                        <div class="ibox-content">
                            <div class="jqGrid_wrapper" id="detalle_wrapper_ciclico">
                                <table id="grid-tableciclico2"></table>
                                <div id="grid-pagerciclico2"></div>
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

    <div class="modal fade" id="asignarUsuarioCiclico" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar Usuario a Inventario</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Folio Nº <span id="folionumero_ciclico">0</span></label>
                        </div>
                        <div class="form-group">
                            <label>Usuarios Disponibles: </label>
                            <select id="usuario_ciclico" class="form-control chosen-select">
						</select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="guardarUsuarioCiclico" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignarSuperviso" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar persona que Superviso</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Usuarios Disponibles: </label>
                            <input type="hidden" id="cod" name="cod" />
                            <select id="usuariosup" class="form-control chosen-select">
						</select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="guardarUsuarioSup" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="asignarSuperviso1" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar persona que Superviso</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Usuarios Disponibles: </label>
                            <input type="hidden" id="cod1" name="cod" />
                            <select id="usuariosup1" class="form-control chosen-select">
						</select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="guardarUsuarioSup" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script type="text/javascript">
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
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/inventariosfisicos/lista/index.php',
                datatype: "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#txtCriterio").val()
                },
                mtype: 'POST',
                colNames: ['Consecutivo', 'Almacén', 'Zona', 'Usuario', 'Fecha Inicio', 'Fecha Fin', 'Superviso', 'Diferencia', 'Status', 'Tipo', 'No. Inventario', 'Inventario Efectuado', 'Acciones'],
                colModel: [
                    {name: 'consecutivo',index: 'consecutivo',width: 100,sortable: false,editable: false}, 
                    {name: 'almacen',index: 'almacen',width: 150,editable: false,sortable: false}, 
                    {name: 'zona',index: 'zona',width: 150,editable: false,sortable: false}, 
                    {name: 'usuario',index: 'usuario',width: 150,editable: false,sortable: false}, 
                    {name: 'fecha_inicio',index: 'fecha_inicio',width: 150,editable: false,sortable: false}, 
                    {name: 'fecha_final',index: 'fecha_final',width: 150,editable: false,sortable: false}, 
                    {name: 'supervisor',index: 'supervisor',width: 220,editable: false,sortable: false}, 
                    {name: 'diferencia',index: 'diferencia',width: 100,editable: false,sortable: false}, 
                    {name: 'status',index: 'status',width: 100,editable: false,sortable: false}, 
                    {name: 'tipo',index: 'tipo',width: 100,editable: false,sortable: false}, 
                    {name: 'n_inventario',index: 'n_inventario',width: 100,editable: false,sortable: false}, 
                    {name: 'efectuado',index: 'efectuado',width: 200,editable: false,sortable: false}, 
                    {name: 'myac',index: '',width: 120,fixed: true,align: 'center',sortable: false,resize: false,formatter: imageFormat}, 
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'fecha_inicio',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table").jqGrid('navGrid', '#grid-pager', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });


            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
            function imageFormat(cellvalue, options, rowObject) {
                var serie = rowObject[0];
                var html = '';
                var estado = rowObject[8];
                var tipo = rowObject[9];

                if (tipo === "Físico") {
                    html = '<a href="#" onclick="detalleConteoFisico(\'' + serie + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';
                    if (estado === "Abierto") {
                        html += '<a href="#" onclick="asignarSupervisoFisico(\'' + serie + '\')"  title="Asignar Usuario"><i class="fa fa-user"></i></a>&nbsp;&nbsp;&nbsp;';
                        html += '<a href="#" onclick="asignarUsuarioFisico(\'' + serie + '\')"  title="Asignar Usuario y Contar"><i class="fa fa-user-plus"></i></a>&nbsp;&nbsp;&nbsp;';
                    }
                    html += `<a href="#" onclick="generarPDFFisico('` + serie + `')" title="Imprimir Reporte"><i class="fa fa-print"></i></a>`;
                }
                else if (tipo === 'Cíclico') {
                    html = '<a href="#" onclick="detalleCiclico(\'' + serie + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';
                    if (estado === "Abierto") {                        
                        html += '<a href="#" onclick="asignarSupervisoCiclico(\'' + serie + '\')"  title="Asignar Usuario"><i class="fa fa-user"></i></a>&nbsp;&nbsp;&nbsp;';
                        html += '<a href="#" onclick="asignarUsuarioCiclico(\'' + serie + '\')"  title="Asignar Usuario y Contar"><i class="fa fa-user-plus"></i></a>&nbsp;&nbsp;&nbsp;';
                    }
                    html += `<a href="#" onclick="generarPDFCiclico('` + serie + `')" title="Imprimir Reporte"><i class="fa fa-print"></i></a>`;
                }
                return html;

            }

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });
    </script>
    <script type="text/javascript">
        $('#data_1').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false,
            date: new Date()
        });

        $('#data_1').data("DateTimePicker").minDate(moment().add(-1, 'days'));

        $(document).on("click", "#guardar", function() {
            var ubicaciones = [];
            var usuarios = [];
            $("#toU").each(function() {
                var localRels = [];
                $(this).find('li').each(function() {
                    localRels.push($(this).attr('value') + " | " + $(this).attr('area'));
                });

                ubicaciones.push(localRels);
            });

            if ((ubicaciones.length <= 0) || ($('#zona').val() == "" && !document.getElementById("recepcion").checked) || ($('#almacen').val().split("|")[0] == "") || ($('#Fecha').val() == "")) {
                swal({
                    title: "Faltan campos",
                    text: "Por favor llene todos los campos para planificar un inventario",
                    //type: "success"
                });
            } else {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        almacen: $('#almacen').val().split("|")[0],
                        zona: $('#zona').val(),
                        fecha: $('#Fecha').val(),
                        ubicaciones: ubicaciones,
                        action: "guardarInventario"
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/inventariosfisicos/update/index.php',
                    success: function(data) {
                        if (data.success) {
                            swal({
                                title: "Inventario Planificado",
                                text: "El inventario ha sido enviado al administrador de inventario",
                                type: "success"
                            });
                        } else {
                            swal({
                                title: "Error",
                                text: "No se pudo planificar el inventario",
                                type: "error"
                            });
                        }
                    }
                }).always(function() {
                    $('#grid-table').jqGrid('clearGridData')
                        .jqGrid('setGridParam', {
                            postData: {
                                criterio: ''
                            },
                            datatype: 'json',
                            page: 1
                        })
                        .trigger('reloadGrid', [{
                            current: true
                        }]);
                    $("#selectAll").iCheck('uncheck')
                    $("#recepcion").iCheck('uncheck')
                    $("#fromU .itemlist").remove();
                    $("#toU .itemlist").remove();
                    $('#almacen').val("");
                    $('#zona').val("");
                    $('#rack').val("");
                    $("#Fecha").val(moment().format("DD-MM-YYYY"));
                    $('.chosen-select').trigger("chosen:updated");
                });
            }

        });

        function detalleConteoFisico(_codigo) 
        {
          $.ajax({
            url: '/api/inventariosfisicos/lista/index.php',
            type: 'GET',
            dataType: 'json',
            data: {
              action: 'getConteos',
              id: _codigo
            }
          }).done(function(data) 
          {
            $("#hiddenInventario").val(_codigo)
            var conteos = data.conteos;
            var select = $("#conteo");
            select.empty();
            var option = document.createElement('option');
            option.value = '';
            option.text = 'Seleccione';
            select.append(option);
            for (var i = 0; i < conteos.length; i++) 
            {
              option = document.createElement('option');
              option.value = conteos[i].conteo;
              option.text = conteos[i].conteo;
              select.append(option);
            }
            $.jgrid.gridUnload("#grid-detalles");
            loadDetailsConteo(_codigo);
            $("#contador").removeClass('hidden');
            $modal0 = $("#coModal");
            $modal0.modal('show');
          });
        }

        function detalleFisico(_codigo) 
        {
          $("#contador").addClass('hidden');
          $.jgrid.gridUnload("#grid-table3");
          loadArticleDetails(_codigo);
          $modal0 = $("#coModal");
          $modal0.modal('show');
        }

        function asignarUsuarioFisico(_codigo) {
            $.ajax({
                url: '/api/inventariosfisicos/lista/index.php',
                data: {
                    action: 'getPendingCount',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                if (data.total > 0) {
                    swal({
                        title: "Advertencia",
                        text: "Quedan artículos pendientes por contar. ¿Desea contar los artículos pendientes?",
                        type: "warning",
                        showCancelButton: true,
                        cancelButtonText: "No",
                        showConfirmButton: true,
                        confirmButtonText: "Sí",
                        allowOutsideClick: false
                    }, function(confirm) {
                        if (confirm) {
                            loadModalUserFisico(_codigo);
                        } else {
                            //detalleFisico(_codigo);
                        }
                    });
                } else {
                    //detalleFisico(_codigo);
                }
            });
        }

        function asignarSupervisoFisico(_codigo) {

            loadModalSupervisoFisico(_codigo);
        }



        function asignarSupervisoCiclico(_codigo) {

            loadModalSupervisoCiclico(_codigo);
        }

        function loadModalSupervisoCiclico(_codigo) {
            $.ajax({
                url: '/api/inventariosfisicos/lista/index.php',
                data: {
                    action: 'getAvailableUserss',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                var usuarios = data.usuarios;
                var usuario = document.getElementById('usuario');
                var select = document.createElement('option');
                usuario.innerHTML = null;
                select.text = 'Seleccione Usuario';
                select.value = '';
                usuario.add(select)

                for (var i = 0; i < usuarios.length; i++) {
                    var options = document.createElement('option');
                    options.value = usuarios[i].cve_usuario;
                    options.text = usuarios[i].nombre_completo;
                    usuario.add(options);
                }

                $("#cod1").val(_codigo);
                $("#usuariosup1").trigger("chosen:updated");

                $("#asignarSuperviso1").modal('show');
            });
        }



        function loadModalSupervisoFisico(_codigo) {
            $.ajax({
                url: '/api/inventariosfisicos/lista/index.php',
                data: {
                    action: 'getAvailableUserss',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                var usuarios = data.usuarios;
                var usuario = document.getElementById('usuario');
                var select = document.createElement('option');
                usuario.innerHTML = null;
                select.text = 'Seleccione Usuario';
                select.value = '';
                usuario.add(select)

                for (var i = 0; i < usuarios.length; i++) {
                    var options = document.createElement('option');
                    options.value = usuarios[i].cve_usuario;
                    options.text = usuarios[i].nombre_completo;
                    usuario.add(options);
                }

                $("#cod").val(_codigo);
                $("#usuariosup").trigger("chosen:updated");

                $("#asignarSuperviso").modal('show');
            });
        }


        $("#guardarUsuarioSup").on("click", function(e) {
            $.ajax({
                url: '/api/inventariosfisicos/update/index.php',
                data: {
                    action: 'guardarUsuarioSup',
                    id: $("#cod").val(),
                    usuario: $("#usuariosup").val()
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                $("#asignarUsuarioSuperviso").modal('hide');
                if (data.success) {
                    swal("Exito", "Supervisor asignado correctamente", "success");
                    swal({
                        title: "Exito",
                        text: "Supervisor asignado correctamente",
                        type: "success",
                        showCancelButton: false,
                        allowOutsideClick: false
                    }, function(confirm) {
                        detalleFisico($("#cod").html());
                    });

                } else {
                    swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
                }
            }).always(function() {
                $('#grid-table').jqGrid('clearGridData')
                    .jqGrid('setGridParam', {
                        postData: {
                            criterio: ''
                        },
                        datatype: 'json',
                        page: 1
                    })
                    .trigger('reloadGrid', [{
                        current: true
                    }]);
            })
        });


        $("#guardarUsuarioSup1").on("click", function(e) {
            $.ajax({
                url: '/api/inventariosfisicos/update/index.php',
                data: {
                    action: 'guardarUsuarioSup1',
                    id: $("#cod1").val(),
                    usuario: $("#usuariosup1").val()
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                $("#asignarUsuarioSuperviso1").modal('hide');
                if (data.success) {
                    swal("Exito", "Supervisor asignado correctamente", "success");
                    swal({
                        title: "Exito",
                        text: "Supervisor asignado correctamente",
                        type: "success",
                        showCancelButton: false,
                        allowOutsideClick: false
                    }, function(confirm) {
                        detalleFisico($("#cod1").html());
                    });

                } else {
                    swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
                }
            }).always(function() {
                $('#grid-table').jqGrid('clearGridData')
                    .jqGrid('setGridParam', {
                        postData: {
                            criterio: ''
                        },
                        datatype: 'json',
                        page: 1
                    })
                    .trigger('reloadGrid', [{
                        current: true
                    }]);
            })
        });

        $("#guardarUsuario").on("click", function(e) 
        {
            $.ajax({
                url: '/api/inventariosfisicos/update/index.php',
                data: {
                    action: 'guardarUsuario',
                    id: $("#folionumero").html(),
                    usuario: $("#usuario").val()
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                $("#asignarUsuario").modal('hide');
                if (data.success) {
                    swal("Exito", "Usuario asignado correctamente", "success");
                    swal({
                        title: "Exito",
                        text: "Usuario asignado correctamente",
                        type: "success",
                        showCancelButton: false,
                        allowOutsideClick: false
                    }, function(confirm) {
                        detalleFisico($("#folionumero").html());
                    });

                } else {
                    swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
                }
            }).always(function() {
                $('#grid-table').jqGrid('clearGridData')
                    .jqGrid('setGridParam', {
                        postData: {
                            criterio: ''
                        },
                        datatype: 'json',
                        page: 1
                    })
                    .trigger('reloadGrid', [{
                        current: true
                    }]);
            })
        });

        $("#cargarUbicaciones").on("click", function() {
            $("#fromU .itemlist").remove();
            $("#toU .itemlist").remove();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    zona: $("#zona").val(),
                    rack: $("#rack").val(),
                    area: document.getElementById("recepcion").checked,
                    almacen: $("#almacen").val().split("|")[1],
                    conProducto: true,
                    action: "traerUbicacionesDeZonas"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {

                    if (data.success == true) {


                        var arr = $.map(data.ubicaciones, function(el) {
                            return el;
                        })
                        arr.pop();
                        for (var i = 0; i < data.ubicaciones.length; i++) {
                            var ul = document.getElementById("fromU");
                            var li = document.createElement("li");
                            var checkbox = document.createElement("input");
                            checkbox.style.marginRight = "10px";
                            checkbox.setAttribute("type", "checkbox");
                            checkbox.setAttribute("value", data.ubicaciones[i].id_ubicacion);
                            checkbox.setAttribute("class", "drag");
                            checkbox.setAttribute("onclick", "selectParent(this)");
                            li.appendChild(checkbox);
                            li.appendChild(document.createTextNode(data.ubicaciones[i].ubicacion));
                            li.setAttribute("dayta-draggable", "item");
                            li.setAttribute("draggable", "false");
                            li.setAttribute("aria-draggable", "false");
                            li.setAttribute("aria-grabbed", "false");
                            li.setAttribute("tabindex", "0");
                            li.setAttribute("class", "itemlist");
                            li.setAttribute("onclick", "selectChild(this)");
                            li.setAttribute("value", data.ubicaciones[i].id_ubicacion);
                            li.setAttribute("area", data.ubicaciones[i].area === "true")
                            ul.appendChild(li);
                        }
                    }
                }
            })
        });

        function loadModalUserFisico(_codigo) {
            $.ajax({
                url: '/api/inventariosfisicos/lista/index.php',
                data: {
                    action: 'getAvailableUsers',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                var usuarios = data.usuarios;
                var usuario = document.getElementById('usuario');
                var select = document.createElement('option');
                usuario.innerHTML = null;
                select.text = 'Seleccione Usuario';
                select.value = '';
                usuario.add(select)

                for (var i = 0; i < usuarios.length; i++) {
                    var options = document.createElement('option');
                    options.value = usuarios[i].cve_usuario;
                    options.text = usuarios[i].nombre_completo;
                    usuario.add(options);
                }

                $("#usuario").trigger("chosen:updated");
                $("#folionumero").html(_codigo);
                $("#asignarUsuario").modal('show');
            });
        }

        function almacen() {
            if ($("#almacen").val() !== '') {
                $("#recepcion").iCheck('enable');
            }
            $('#zona')
                .find('option')
                .remove()
                .end();
            var value = $('#almacen').val().split("|");
            var clave = value[0],
                id = value[1];

            $("#fromU .itemlist").remove();
            $("#toU .itemlist").remove();

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    clave: clave,
                    action: "traerZonasDeAlmacenP"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacenp/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var options = $("#zona");
                        options.empty();
                        options.append(new Option("Seleccione", ""));
                        for (var i = 0; i < data.zonas.length; i++) {
                            options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
                        }
                        $('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        }

        $('#zona').on('change', function() {
            if ($(this).val() != "")
                $("#cargarUbicaciones").prop('disabled', false);
            else
                $("#cargarUbicaciones").prop('disabled', true);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    zona: $('#zona').val(),
                    action: "traerRackDeZonas",
                    conProducto: true
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/almacen/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var options = $("#rack");
                        options.empty();
                        options.append(new Option("Seleccione", ""));
                        for (var i = 0; i < data.racks.length; i++) {
                            options.append(new Option(data.racks[i].rack, data.racks[i].rack));
                        }
                        $('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });

            $('#recepcion, #selectAll').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });
            $("body").on("ifToggled", function(e) {
                if (e.target.checked && e.target.id === 'recepcion') {
                    $("#cargarUbicaciones").removeAttr("disabled");
                } else {
                    if ($("#zona").val() === '') {
                        $("#cargarUbicaciones").attr("disabled", "disabled");
                    }
                }
                if (e.target.checked && e.target.id === 'selectAll') {
                    $('#fromU li input[type="checkbox"].drag').each(function(i, e) {
                        e.checked = true;
                        e.parentElement.setAttribute('aria-grabbed', true);
                    });
                } else {
                    $('#fromU li input[type="checkbox"].drag').each(function(i, e) {
                        e.checked = false;
                        e.parentElement.setAttribute('aria-grabbed', false);
                    });
                }
            });
        });

        function add(from, to) {
            var elements = document.querySelectorAll(`${from} input.drag:checked`),
                li, newli;
            for (e of elements) {
                e.checked = false;
                li = e.parentElement;
                newli = li.cloneNode(true);
                newli.setAttribute("aria-grabbed", "false");
                document.querySelector(`${from}`).removeChild(li);
                document.querySelector(`${to}`).appendChild(newli);
            }
        }

        function remove(to, from) {
            var elements = document.querySelectorAll(`${to} input.drag:checked`),
                li, newli;
            for (e of elements) {
                e.checked = false;
                li = e.parentElement;
                newli = li.cloneNode(true);
                newli.setAttribute("aria-grabbed", "false");
                document.querySelector(`${to}`).removeChild(li);
                document.querySelector(`${from}`).insertBefore(newli, document.querySelector(`${from}`).firstChild);
            }
        }

        function selectParent(e) {
            if (e.checked) {
                e.parentNode.setAttribute("aria-grabbed", "true");
            } else {
                e.parentNode.setAttribute("aria-grabbed", "false");
            }
        }

        function selectChild(e) {
            if (e.getAttribute("aria-grabbed") == "true") {
                e.firstChild.checked = true;
            } else {
                e.firstChild.checked = false;
            }
        }
    </script>

    <script type="text/javascript">
        var loadArticleDetails;
        (function() {
            loadArticleDetails = function(codigo) {
                $.jgrid.gridUnload("#grid-detalles");
                var grid_selector = "#grid-detalles";
                var pager_selector = "#grid-pager2";

                //resize to fit page size
                $(window).on('resize.jqGrid', function() {
                        $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                    })
                    //resize on sidebar collapse/expand
                var parent_column = $(grid_selector).closest('[class*="col-"]');
                $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                    if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                        //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                        setTimeout(function() {
                            $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                        }, 0);
                    }
                })

                $(grid_selector).jqGrid({
                    url: '/api/inventariosfisicos/update/index.php',
                    datatype: "json",
                    postData: {
                        ID_Inventario: codigo,
                        action: "loadDetalle"
                    },
                    shrinkToFit: false,
                    height: '400px',
                    mtype: 'POST',
                    colNames: ['ID', 'Ubicacion', 'Clave', 'Descripción', 'Lote', 'Caducidad', 'Serie', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Conteo', 'Usuario', 'Unidad de Medida'],
                    colModel: [
                        {name: 'ID_Inventario',index: 'ID_Inventario',width: 60,sorttype: "int",editable: false,hidden: true}, 
                        {name: 'ubicacion',index: 'ubicacion',width: 200,editable: false,sortable: false}, 
                        {name: 'clave',index: 'clave',width: 120,editable: false,sortable: false}, 
                        {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false}, 
                        {name: 'lote',index: 'lote',width: 150,editable: false,sortable: false}, 
                        {name: 'caducidad',index: 'caducidad',width: 150,editable: false,sortable: false}, 
                        {name: 'serie',index: 'serie',width: 150,editable: false,sortable: false}, 
                        {name: 'stockTeorico',index: 'stockTeorico',width: 100,align: 'right',editable: false,sortable: false}, 
                        {name: 'stockFisico',index: 'stockFisico',width: 100,editrules: {integer: true},editable: true,edittype: 'text',sortable: false}, 
                        {name: 'diferencia',index: 'diferencia',width: 100,align: 'right',editable: false,sortable: false}, 
                        {name: 'conteo',index: 'conteo',align: 'right',width: 100,editable: false,sortable: false}, 
                        {name: 'usuario',index: 'usuario',width: 100,editable: false,sortable: false}, 
                        {name: 'unidad_medida',index: 'unidad_medida',width: 180,editable: false,sortable: false}
                    ],
           
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    viewrecords: true,
                    onSelectRow: function(id) {
                        var ID_Inventario = id.split('|')[3];
                        var grid = $('#grid-detalles').jqGrid('getRowData', id);

                        var stockFisico = parseInt(grid.stockTeorico) + parseInt(grid.diferencia);
                        if (stockFisico === 0 && parseInt(grid.diferencia) !== 0 && parseInt(grid.conteo) > 0) {
                            $('#grid-detalles').jqGrid(
                                'editRow',
                                id, {
                                    url: '/api/inventariosfisicos/update/index.php',
                                    extraparam: {
                                        action: 'stockFisico'
                                    },
                                    keys: true,
                                    successfunc: function() {
                                        alert(ID_Inventario);
                                        alert(grid)
                                    }
                                }
                            );
                        }

                        /**
                        
                         if (stockFisico === 0 && parseInt(grid.diferencia) !== 0 && parseInt(grid.conteo) > 0) {
                            $('#grid-detalles').jqGrid(
                                'editRow',
                                id, {
                                    url: '/api/inventariosfisicos/update/index.php',
                                    extraparam: {
                                        action: 'stockFisico'
                                    },
                                    keys: true,
                                    successfunc: function() {
                                        $('#grid-detalles').jqGrid('clearGridData')
                                            .jqGrid('setGridParam', {
                                                postData: {
                                                    action: 'loadDetalle',
                                                    ID_Inventario: ID_Inventario
                                                },
                                                datatype: 'json',
                                                page: 1
                                            })
                                            .trigger('reloadGrid', [{
                                                current: true
                                            }]);
                                        $('#grid-table').jqGrid('clearGridData')
                                            .jqGrid('setGridParam', {
                                                postData: {
                                                    criterio: ''
                                                },
                                                datatype: 'json',
                                                page: 1
                                            })
                                            .trigger('reloadGrid', [{
                                                current: true
                                            }]);
                                    }
                                }
                            );
                        }
                        
                         */
                    },
                });

                // Setup buttons
                $("#grid-detalles").jqGrid('navGrid', '#grid-pager2', {
                    edit: false,
                    add: false,
                    del: false,
                    search: false
                }, {
                    height: 200,
                    reloadAfterSubmit: true
                });


                $(window).triggerHandler('resize.jqGrid');
            }
        })();
        var loadDetailsConteo;
        (function() {
            loadDetailsConteo = function(codigo) {
                $.jgrid.gridUnload("#grid-table3");
                var grid_selector = "#grid-table3";
                var pager_selector = "#grid-pager3";

                //resize to fit page size
                $(window).on('resize.jqGrid', function() {
                        $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                    })
                    //resize on sidebar collapse/expand
                var parent_column = $(grid_selector).closest('[class*="col-"]');
                $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                    if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                        //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                        setTimeout(function() {
                            $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                        }, 0);
                    }
                })

                $(grid_selector).jqGrid({
                    url: '/api/inventariosfisicos/update/index.php',
                    datatype: "json",
                    postData: {
                        ID_Inventario: codigo,
                        action: "loadDetalle"
                    },
                    shrinkToFit: false,
                    height: 'auto',
                    mtype: 'POST',
                    colNames: ['ID', 'Ubicacion', 'Clave', 'Descripción', 'Lote', 'Caducidad', 'Serie', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Conteo', 'Usuario', 'Unidad de Medida'],
                    colModel: [
                        {name: 'ID_Inventario',index: 'ID_Inventario',width: 60,sorttype: "int",editable: false,hidden: true}, 
                        {name: 'ubicacion',index: 'ubicacion',width: 200,editable: false,sortable: false}, 
                        {name: 'clave',index: 'clave',width: 120,editable: false,sortable: false}, 
                        {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false}, 
                        {name: 'lote',index: 'lote',width: 150,editable: false,sortable: false}, 
                        {name: 'caducidad',index: 'caducidad',width: 150,editable: false,sortable: false}, 
                        {name: 'serie',index: 'serie',width: 150,editable: false,sortable: false}, 
                        {name: 'stockTeorico',index: 'stockTeorico',width: 150,editable: false,sortable: false}, 
                        {name: 'stockFisico',index: 'stockFisico',width: 150,editable: false,sortable: false}, 
                        {name: 'diferencia',index: 'diferencia',width: 150,editable: false,sortable: false}, 
                        {name: 'conteo',index: 'conteo',width: 100,editable: false,sortable: false}, 
                        {name: 'usuario',index: 'usuario',width: 100,editable: false,sortable: false}, 
                        {name: 'unidad_medida',index: 'unidad_medida',width: 180,editable: false,sortable: false}
                    ],
           
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    viewrecords: true
                });

                // Setup buttons
                $("#grid-table3").jqGrid('navGrid', '#grid-pager3', {
                    edit: false,
                    add: false,
                    del: false,
                    search: false
                }, {
                    height: 200,
                    reloadAfterSubmit: true
                });


                $(window).triggerHandler('resize.jqGrid');
            }
        })();
        $("#conteo").on("change", function(e) {
            reloadGridConteo($("#hiddenInventario").val(), e.target.value);
        });

        function reloadGridConteo(_codigo, _conteo) {
            $('#grid-table3').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        action: 'loadDetalle',
                        ID_Inventario: _codigo,
                        conteo: _conteo
                    },
                    datatype: 'json',
                    page: 1
                }).trigger('reloadGrid');
        }
    </script>

    <script type="text/javascript">
        function generarPDFFisico(consecutivo) {
            var form = document.createElement('form'),
                nobody = document.createElement('input'),
                folio = document.createElement('input'),
                action = document.createElement('input');

            form.setAttribute('target', '_blank');
            form.setAttribute('method', 'post');
            form.setAttribute('action', '/api/inventariosfisicos/update/index.php');
            nobody.setAttribute('type', 'hidden');
            nobody.setAttribute('name', 'nofooternoheader');
            nobody.setAttribute('value', 'true');
            folio.setAttribute('type', 'hidden');
            folio.setAttribute('name', 'id');
            folio.setAttribute('value', consecutivo);
            action.setAttribute('type', 'hidden');
            action.setAttribute('name', 'action');
            action.setAttribute('value', 'printReport');
            form.appendChild(nobody);
            form.appendChild(folio);
            form.appendChild(action);
            document.getElementsByTagName('body')[0].appendChild(form);
            form.submit();
        }

        function generarPDFCiclico(consecutivo) {
            var form = document.createElement('form'),
                nobody = document.createElement('input'),
                folio = document.createElement('input'),
                action = document.createElement('input');

            form.setAttribute('target', '_blank');
            form.setAttribute('method', 'post');
            form.setAttribute('action', '/api/inventariosciclicos/update/index.php');
            nobody.setAttribute('type', 'hidden');
            nobody.setAttribute('name', 'nofooternoheader');
            nobody.setAttribute('value', 'true');
            folio.setAttribute('type', 'hidden');
            folio.setAttribute('name', 'id');
            folio.setAttribute('value', consecutivo);
            action.setAttribute('type', 'hidden');
            action.setAttribute('name', 'action');
            action.setAttribute('value', 'printReport');
            form.appendChild(nobody);
            form.appendChild(folio);
            form.appendChild(action);
            document.getElementsByTagName('body')[0].appendChild(form);
            form.submit();
        }
    </script>
    <script type="text/javascript">
        function detalleCiclico(_codigo) {
            loadArticleDetailsCiclico(_codigo);
            $modal0 = $("#coModalCiclico");
            $modal0.modal('show');
        }

        function detallecCiclico(_codigo) {
            loadArticleDetailscCiclico(_codigo);
            $modal0 = $("#coModalCiclico");
            $modal0.modal('show');
        }


        function asignarUsuarioCiclico(_codigo) {
            $.ajax({
                url: '/api/inventariosciclicos/lista/index.php',
                data: {
                    action: 'getPendingCount',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                if (data.total > 0) {
                    swal({
                        title: "Advertencia",
                        text: "Quedan artículos pendientes por contar. ¿Desea contar los artículos pendientes?",
                        type: "warning",
                        showCancelButton: true,
                        cancelButtonText: "No",
                        showConfirmButton: true,
                        confirmButtonText: "Sí",
                        allowOutsideClick: false
                    }, function(confirm) {
                        if (confirm) {
                            detalleCiclico(_codigo);
                        } else {
                            loadModalUserCiclico(_codigo);
                        }
                    });
                } else {
                    loadModalUserCiclico(_codigo);
                }
            });
        }

        function loadModalUserCiclico(_codigo) {
            $.ajax({
                url: '/api/inventariosciclicos/lista/index.php',
                data: {
                    action: 'getAvailableUsers',
                    id: _codigo
                },
                dataType: 'json',
                method: 'GET'
            }).done(function(data) {
                var usuarios = data.usuarios;
                var usuario = document.getElementById('usuario_ciclico');
                var select = document.createElement('option');
                usuario.innerHTML = null;
                select.text = 'Seleccione Usuario';
                select.value = '';
                usuario.add(select)

                for (var i = 0; i < usuarios.length; i++) {
                    var options = document.createElement('option');
                    options.value = usuarios[i].cve_usuario;
                    options.text = usuarios[i].nombre_completo;
                    usuario.add(options);
                }

                $("#usuario_ciclico").trigger("chosen:updated");
                $("#folionumero_ciclico").html(_codigo);
                $("#asignarUsuarioCiclico").modal('show');
            });
        }

        $("#guardarUsuarioCiclico").on("click", function(e) {
            $.ajax({
                url: '/api/inventariosciclicos/update/index.php',
                data: {
                    action: 'guardarUsuario',
                    id: $("#folionumero_ciclico").html(),
                    usuario: $("#usuario_ciclico").val()
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                $("#asignarUsuarioCiclico").modal('hide');
                if (data.success) {
                    swal("Exito", "Usuario asignado correctamente", "success");
                    swal({
                        title: "Exito",
                        text: "Usuario asignado correctamente",
                        type: "success",
                        showCancelButton: false,
                        allowOutsideClick: false
                    }, function(confirm) {
                        detalleCiclico($("#folionumero_ciclico").html());
                    });

                } else {
                    swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
                }
            }).always(function() {
                $('#grid-table').jqGrid('clearGridData')
                    .jqGrid('setGridParam', {
                        postData: {
                            criterio: ''
                        },
                        datatype: 'json',
                        page: 1
                    })
                    .trigger('reloadGrid', [{
                        current: true
                    }]);
            })
        });
    </script>
    <script type="text/javascript">
        var loadArticleDetailsCiclico;
        (function() {
            loadArticleDetailsCiclico = function(codigo) {
                $.jgrid.gridUnload("#grid-tableciclico2");
                var grid_selector = "#grid-tableciclico2";
                var pager_selector = "#grid-pagerciclico2";

                //resize to fit page size
                $(window).on('resize.jqGrid', function() {
                        $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                    })
                    //resize on sidebar collapse/expand
                var parent_column = $(grid_selector).closest('[class*="col-"]');
                $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                    if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                        //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                        setTimeout(function() {
                            $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                        }, 0);
                    }
                })

                $(grid_selector).jqGrid({
                    url: '/api/inventariosciclicos/update/index.php',
                    datatype: "json",
                    postData: {
                        ID_PLAN: codigo,
                        action: "loadDetalle"
                    },
                    shrinkToFit: false,
                    height: 'auto',
                    mtype: 'POST',
                    colNames: ['ID', 'Clave', 'Descripción', 'Zona de almacenaje', 'Ubicación', 'Serie', 'Lote', 'Caducidad', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Datetime Inicio', 'Datetime Final', 'Conteo', 'Usuario'],
                    colModel: [
                        {name: 'ID_PLAN',index: 'ID_PLAN',width: 60,sorttype: "int",editable: false,hidden: true}, 
                        {name: 'clave',index: 'clave',width: 120,editable: false,sortable: false}, 
                        {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false}, 
                        {name: 'zona',index: 'zona',width: 200,editable: false,sortable: false}, 
                        {name: 'ubicacion',index: 'ubicacion',width: 200,editable: false,sortable: false}, 
                        {name: 'serie',index: 'serie',width: 150,editable: false,sortable: false}, 
                        {name: 'lote',index: 'lote',width: 150,editable: false,sortable: false}, 
                        {name: 'caducidad',index: 'caducidad',width: 150,editable: false,sortable: false}, 
                        {name: 'stockTeorico',index: 'stockTeorico',width: 150,editable: false,sortable: false}, 
                        {name: 'stockFisico',index: 'stockFisico',width: 150,editrules: {integer: true},editable: true,edittype: 'text',sortable: false}, 
                        {name: 'diferencia',index: 'diferencia',width: 150,editable: false,sortable: false}, 
                        {name: 'inicio',index: 'inicio',width: 150,editable: false,sortable: false}, 
                        {name: 'fin',index: 'fin',width: 150,editable: false,sortable: false}, 
                        {name: 'conteo',index: 'conteo',width: 100,editable: false,sortable: false}, 
                        {name: 'usuario',index: 'usuario',width: 100,editable: false,sortable: false}
                    ],
           
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    viewrecords: true,
                    editurl: '/api/inventariosciclicos/update/index.php',
                    loadComplete: function(id) {
                        console.log(id);
                    },
                    onSelectCell: function (rowid, celname, value, iRow, iCol) {
                        alert(rowid);
                        selectedCode = rowid;
                    },
                    beforeEditCell: function (rowid, cellname, value, iRow, iCol) {
                        originalRow = $("#list").jqGrid('getRowData', rowid);
                        alert(originalRow)
                    },
                    onSelectRow: function(id) {

                        var ID_PLAN = id.split('|')[3];
                        console.log(id);

                        $('#grid-tableciclico2').jqGrid(
                            'editRow',
                            id, {
                                keys: true,
                                oneditfunc: function() {
                                    console.log("editando");
                                },
                                
                                successfunc: function(e) {
                                    var grid = $('#grid-detalles').jqGrid('getRowData', id);
                                    //console.log(id);
                                    

                                    /*$.ajax({
                                        url: '/api/inventariosciclicos/update/index.php',
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            action: 'loadDetalle',
                                            ID_PLAN: ID_PLAN
                                        }
                                    }).done(function(data) {
                                        
                                    });*/

                                    /*$('#grid-tableciclico2').jqGrid('clearGridData')
                                        .jqGrid('setGridParam', {
                                            postData: { lala
                                                action: 'loadDetalle',
                                                ID_PLAN: ID_PLAN
                                            },
                                            datatype: 'json',
                                            page: 1
                                        })
                                        /*.trigger('reloadGrid', [{
                                            current: true
                                        }]);
                                    $('#grid-table').jqGrid('clearGridData')
                                        .jqGrid('setGridParam', {
                                            postData: {
                                                criterio: ''
                                            },
                                            datatype: 'json',
                                            page: 1
                                        })
                                        /*.trigger('reloadGrid', [{
                                            current: true
                                        }])*/
                                }
                            }
                        );
                    },
                });

                // Setup buttons
                $("#grid-tableciclico2").jqGrid('navGrid', '#grid-pager2', {
                    edit: false,
                    add: false,
                    del: false,
                    search: false
                }, {
                    height: 200,
                    reloadAfterSubmit: true
                });


                $(window).triggerHandler('resize.jqGrid');
            }

            loadArticleDetailsCiclicoc = function(codigo) {
                $.jgrid.gridUnload("#grid-tableciclico2");
                var grid_selector = "#grid-tableciclico2";
                var pager_selector = "#grid-pagerciclico2";

                //resize to fit page size
                $(window).on('resize.jqGrid', function() {
                        $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
                    })
                    //resize on sidebar collapse/expand
                var parent_column = $(grid_selector).closest('[class*="col-"]');
                $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                    if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                        //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                        setTimeout(function() {
                            $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                        }, 0);
                    }
                })

                $(grid_selector).jqGrid({
                    url: '/api/inventariosciclicos/update/index.php',
                    datatype: "json",
                    postData: {
                        ID_PLAN: codigo,
                        action: "loadDetalle"
                    },
                    shrinkToFit: false,
                    height: 'auto',
                    mtype: 'POST',
                    colNames: ['ID', 'Clave', 'Descripción', 'Zona de almacenaje', 'Ubicación', 'Serie', 'Lote', 'Caducidad', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Datetime Inicio', 'Datetime Final', 'Conteo', 'Usuario'],
                    colModel: [
                       {name: 'ID_PLAN',index: 'ID_PLAN',width: 60,sorttype: "int",editable: false,hidden: true}, 
                       {name: 'clave',index: 'clave',width: 120,editable: false,sortable: false}, 
                       {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false}, 
                       {name: 'zona',index: 'zona',width: 200,editable: false,sortable: false}, 
                       {name: 'ubicacion',index: 'ubicacion',width: 200,editable: false,sortable: false}, 
                       {name: 'serie',index: 'serie',width: 150,editable: false,sortable: false}, 
                       {name: 'lote',index: 'lote',width: 150,editable: false,sortable: false}, 
                       {name: 'caducidad',index: 'caducidad',width: 150,editable: false,sortable: false}, 
                       {name: 'stockTeorico',index: 'stockTeorico',width: 150,editable: false,sortable: false}, 
                       {name: 'stockFisico',index: 'stockFisico',width: 150,editrules: {integer: true},editable: true,edittype: 'text',sortable: false}, 
                       {name: 'diferencia',index: 'diferencia',width: 150,editable: false,sortable: false}, 
                       {name: 'inicio',index: 'inicio',width: 150,editable: false,sortable: false}, 
                       {name: 'fin',index: 'fin',width: 150,editable: false,sortable: false}, 
                       {name: 'conteo',index: 'conteo',width: 100,editable: false,sortable: false}, 
                       {name: 'usuario',index: 'usuario',width: 100,editable: false,sortable: false}
                    ],
                  
                    rowNum: 30,
                    rowList: [30, 40, 50],
                    pager: pager_selector,
                    viewrecords: true,
                });

                // Setup buttons
                $("#grid-tableciclico2").jqGrid('navGrid', '#grid-pager2', {
                    edit: false,
                    add: false,
                    del: false,
                    search: false
                }, {
                    height: 200,
                    reloadAfterSubmit: true
                });


                $(window).triggerHandler('resize.jqGrid');
            }

        })();
    </script>

    
    <style>
        .tab .ui-jqgrid .ui-jqgrid-htable {
            width: 100% !important;
        }
        
        .tab .ui-jqgrid .ui-jqgrid-btable {
            table-layout: inherit;
            margin: 0;
            outline-style: none;
            width: auto !important;
        }
        
        .tab .ui-state-default ui-jqgrid-hdiv {
            width: 100% !important;
        }
        
        .tab .ui-jqgrid-view {
            width: 100% !important;
        }
        
        .tab .ui-state-default ui-jqgrid-hdiv {
            width: 100% !important;
        }
        
        .tab .ui-jqgrid .ui-jqgrid-bdiv {
            width: 100% !important;
            height: 118px !important;
            height: 10px;
        }
        
        #detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top,
        #detalle_wrapper #gview_grid-table3,
        #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all,
        #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom,
        #detalle_wrapper #grid-table3,
        #detalle_wrapper .ui-jqgrid-bdiv {
            max-width: 100% !important;
            width: 100% !important;
        }
        
        .tab .ui-state-default,
        .ui-widget-content,
        .ui-widget-header .ui-state-default {
            width: 100% !important;
        }
        
        .tab .ui-widget-content {
            width: 100% !important;
        }
        
        #gview_grid-tablea3>div>.ui-jqgrid-hbox {
            padding-right: 0px !important;
        }
        
        #grid-tablea3 {
            width: 100% !important;
        }
        
        #gbox_grid-table3 {
            width: 100% !important;
        }
        
        div#gview_grid-table3 {
            width: 100% !important;
        }
        
        #gview_grid-table3 .ui-widget-content {
            width: 100% !important;
        }
        
        div#gview_grid-table3>.ui-jqgrid-hdiv {
            width: 100% !important;
        }
        
        #gview_grid-tableciclico2>div>.ui-jqgrid-hbox {
            padding-right: 0px !important;
        }
        
        #grid-tableciclico2 {
            width: 100% !important;
        }
        
        #gbox_grid-tableciclico2 {
            width: 100% !important;
        }
        
        div#gview_grid-tableciclico2 {
            width: 100% !important;
        }
        
        #gview_grid-tableciclico2 .ui-widget-content {
            width: 100% !important;
        }
        
        div#gview_grid-tableciclico2>.ui-jqgrid-hdiv {
            width: 100% !important;
        }
        
        #grid-pagerciclico2 {
            width: 100% !important;
        }
        
        #grid-pager3 {
            width: 100% !important;
        }
        
        div#gview_grid-table3>.ui-jqgrid-bdiv {
            width: 100% !important;
        }
        
        div#gview_grid-tableciclico2>.ui-jqgrid-bdiv {
            width: 100% !important;
        }
        
        #grid-tablea3_subgrid {
            width: 5% !important;
            text-align: center;
        }
        
        #dragUbicaciones li {
            cursor: pointer;
        }
        
        .wi {
            width: 90% !important;
        }
        
        .relative {
            position: relative;
        }
        
        .relative .floating-button {
            position: absolute;
            right: 0;
            top: 50%;
        }
        
        [aria-grabbed="true"] {
            background: #1ab394 !important;
            color: #fff !important;
        }
        
        #detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top,
        #detalle_wrapper #gview_grid-tableciclico2,
        #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all,
        #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom,
        #detalle_wrapper #grid-tableciclico2,
        #detalle_wrapper .ui-jqgrid-bdiv {
            max-width: 100% !important;
            width: 100% !important;
        }
        
        #detalle_wrapper .ui-jqgrid-hdiv.ui-state-default.ui-corner-top,
        #detalle_wrapper #gview_grid-detalles,
        #detalle_wrapper .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all,
        #detalle_wrapper .ui-jqgrid-pager.ui-state-default.ui-corner-bottom,
        #detalle_wrapper #grid-detalles,
        #detalle_wrapper .ui-jqgrid-bdiv {
            max-width: 100% !important;
            width: 100% !important;
        }
        
        .red {
            color: red;
        }
        
        .yellow {
            color: yellow;
        }
        
        .green {
            color: green;
        }
    </style>