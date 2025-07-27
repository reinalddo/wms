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

$vere = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='{$mod}' AND id_submenu='{$var1}' AND id_role='".$_SESSION["perfil_usuario"]."'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='{$mod}' AND id_submenu='{$var2}' AND id_role='".$_SESSION["perfil_usuario"]."'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='{$mod}' AND id_submenu='{$var3}' AND id_role='".$_SESSION["perfil_usuario"]."'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("SELECT * FROM t_profiles AS a WHERE id_menu='{$mod}' AND id_submenu='{$var4}' AND id_role='".$_SESSION["perfil_usuario"]."'");
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
<input type="hidden" id="conteo_ciclico" value="">
<input type="hidden" id="conteo_fisico" value="">
<div class="wrapper wrapper-content  animated fadeInRight">
    <h3>Inventarios</h3>
    <div class="row">
        <div class="col-md-12"></div>
        <div class="col-md-12" >
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
                                                        <option value="<?php echo $almacen->clave; ?>"><?php echo "($almacen->clave) ". $almacen->nombre; ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="email">Zona de Almacenaje</label>
                                            <select name="zona" id="zona" class="chosen-select form-control"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="email">Rack</label>
                                            <select name="rack" id="rack" class="chosen-select form-control"></select>
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
                                            <ol data-draggable="target" id="fromU" class="wi"></ol>
                                            <button class="btn btn-primary floating-button" onclick="add('#fromU', '#toU')">>></button>
                                            <button class="btn btn-primary floating-button" onclick="remove('#toU', '#fromU')" style="margin-top: 40px"><<</button>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email">Ubicaciones Asignadas</label>
                                            <ol data-draggable="target" id="toU" class="wi"></ol>
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
                            <div class="row">                             
                                <div class="form-group col-md-4">                                    
                                    <label>Almacen</label>
                                    <select class="form-control" id="almacen2">
                                        <option value="">Almacen</option>
                                        <?php foreach( $almacen->getAll() AS $almacen ): ?>
                                        <option value="<?php echo $almacen->clave; ?>"><?php echo "($almacen->clave) $almacen->nombre"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sd-6 col-md-4 col-lg-3">
                                    <label for="status">Estatus</label>
                                    <select id="status"  class="form-control"><!-- chosen-select -->
                                        <option value="A" selected>Abierto</option>
                                        <option value="T">Cerrado</option>
                                    </select>
                                </div>
                                <div class="input-group col-md-4">
                                    <button type="button" class="btn btn-primary" style="margin-top: 23px;" onclick="ReloadGrid()" >
                                      <i class="fa fa-search"></i> Buscar
                                    </button>
                                </div>
                           </div>
                            <div class="row" style="margin-top:15px">  
                                <div class="col-md-12">
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

<div class="modal inmodal" id="coModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 90%">
        <div class="modal-content animated bounceInRight">
            <!-- Modal content-->
            <div class="modal-content" style="width: 100%;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Administración de Inventario <span id="tipo_inv"></span> N° <label id="numero_inventario">0</label></h4>
                    <label id="status_inventario"></label>
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
                                    <div class="form-group">
                                        <label>Total de Articulos</label>
                                        <input name="conteo" id="total_u" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <input type="hidden" id="hiddenInventario">
                                    <div class="form-group">
                                        <label>Seleccione conteo</label>
                                        <select name="conteo" id="conteo" class="form-control"></select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>Producto | Clave</label>
                                        <input name="conteo" id="criterio" class="form-control" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row center" id="textoConteoCierre" style="display : none;">
                            <h4>Conteo de Cierre</h4>
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
                        <label>Inventario Nº <span id="folionumero">0</span></label>
                    </div>
                    <div class="form-group">
                        <label>Usuarios Disponibles: </label>
                        <select id="usuario" class="form-control chosen-select"></select>
                    </div>
                    <div class="form-group">
                        <button type="button" id="guardarUsuario" class="btn btn-primary">Agregar</button>
                    </div>
                    <div class="form-group">
                        <div class="jqGrid_wrapper" id="usuarios">
                            <table id="table-usuarios" width="100%" class="w100"></table>
                            <div id="table-usuarios-pager"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-default" data-dismiss="modal" >Cancelar</button>
                        <button type="button" id="asignar_usuarios" class="btn btn-primary" >Asignar Usuarios</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-inventario-ciclico" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Administración de Inventario Cíclico</h4>
                </div>
                <div class="modal-body">
                    <div class="ibox-content0">
                        <div class="table-responsive">

                            <div class="row">

                                <div class="col-lg-3">
                                    <input type="hidden" id="hiddenInventario">
                                    <div class="form-group">
                                        <label>Seleccione conteo</label>
                                        <select name="conteo_ciclico_select" id="conteo_ciclico_select" class="form-control"></select>
                                    </div>
                                </div>

                            </div>

                            <table id="dt-inventario-cilico" class="table" style="table-layout: auto;width: 100%;  ">
                                <thead>
                                    <tr style="background-color: #f1f1f1;">
                                        <th scope="col" style="width: 80px !important;min-width: 80px !important;">Clave</th>
                                        <th scope="col" style="width: 250px !important;min-width: 220px !important;">Descripción</th>
                                        <th scope="col">Ubicación</th>
                                        <th scope="col">Lote|Serie</th>
                                        <th scope="col">Caducidad</th>
                                        <th scope="col" style="width: 120px !important;min-width: 120px !important;">Stock Teórico</th>
                                        <th scope="col" style="width: 120px !important;min-width: 120px !important;">Stock Fisico</th>
                                        <th scope="col">Diferencia</th>
                                        <th scope="col">Conteo</th>
                                        <th scope="col" style="width: 150px !important;min-width: 150px !important;">Usuario</th>
                                        <th scope="col" style="width: 150px !important;min-width: 150px !important;">Unidad de medida</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-info"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button id="btn-guardar-stock" type="button" class="btn btn-primary pull-right" onclick="guardarStockCiclico()" >Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-inventario-fisico" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Administración de Inventario Físico</h4>
                </div>
                <div class="modal-body">
                    <div class="ibox-content0">
                        <div class="table-responsive">

                            <div class="row">

                                <div class="col-lg-3">
                                    <input type="hidden" id="hiddenInventario">
                                    <div class="form-group">
                                        <label>Seleccione conteo</label>
                                        <select name="conteo_fisico_select" id="conteo_fisico_select" class="form-control"></select>
                                    </div>
                                </div>

                            </div>

                            <table id="dt-inventario-fisico" class="table" style="table-layout: auto;width: 100%;  ">
                                <thead>
                                    <tr style="background-color: #f1f1f1;">
                                        <th scope="col" style="width: 80px !important;min-width: 80px !important;">Clave</th>
                                        <th scope="col" style="width: 250px !important;min-width: 220px !important;">Descripción</th>
                                        <th scope="col">Ubicación</th>
                                        <th scope="col">Lote|Serie</th>
                                        <th scope="col">Caducidad</th>
                                        <th scope="col" style="width: 120px !important;min-width: 120px !important;">Stock Teórico</th>
                                        <th scope="col" style="width: 120px !important;min-width: 120px !important;">Stock Fisico</th>
                                        <th scope="col">Diferencia</th>
                                        <th scope="col">Conteo</th>
                                        <th scope="col" style="width: 150px !important;min-width: 150px !important;">Usuario</th>
                                        <th scope="col" style="width: 150px !important;min-width: 150px !important;">Unidad de medida</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-info"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button id="btn-guardar-stock" type="button" class="btn btn-primary pull-right" onclick="guardarStockFisico()" >Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
/*
?>
<div class="modal fade" id="modal-inventario-fisico" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Administración de Inventario Físico N° <span id="folionumero2">0</span></h4>
                </div>
                <div class="modal-body">
                    <div class="ibox-content0">
                        <div class="table-responsive">
                            <table id="dt-inventario-fisico" class="table" style="table-layout: auto;width: 100%;  ">
                                <thead>
                                    <tr style="background-color: #f1f1f1;">
                                        <th scope="col">Clave</th>
                                        <th scope="col" style="width: 220px !important;min-width: 220px !important;">Descripción</th>
                                        <th scope="col">Ubicación</th>
                                        <th scope="col">Serie</th>
                                        <th scope="col">Lote</th>
                                        <th scope="col">Caducidad</th>
                                        <th scope="col" style="width: 120px !important;min-width: 120px !important;">Stock Teórico</th>
                                        <th scope="col" style="width: 120px !important;min-width: 120px !important;">Stock Fisico</th>
                                        <th scope="col">Diferencia</th>
                                        <th scope="col">Conteo</th>
                                        <th scope="col" style="width: 150px !important;min-width: 150px !important;">Usuario</th>
                                        <th scope="col" style="width: 150px !important;min-width: 150px !important;">Unidad de medida</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-info"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button id="btn-guardar-stock-fisico" type="button" class="btn btn-primary pull-right" onclick="this.disabled=true; XguardarStockFisicoX(); " >Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
*/
?>
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
                        <select id="usuario_ciclico" class="form-control chosen-select"></select>
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


<div class="modal fade" id="asignarUsuarioFisico" role="dialog">
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
                        <label>Folio Nº <span id="folionumero_fisico">0</span></label>
                    </div>
                    <div class="form-group">
                        <label>Usuarios Disponibles: </label>
                        <select id="usuario_fisico" class="form-control chosen-select"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" id="guardarUsuarioFisico" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-asignar-supervisor" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm" style="max-width: 400px !important;width: 400px;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Asignar persona que supervisó el inventario</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Usuario:</label>
                        <select id="txt-supervisor" class="form-control chosen-select"></select>
                        <!--<input id="txt-supervisor" type="text" class="form-control"/>-->
                    </div>
                    <div class="form-group">
                        <label>Contraseña: </label>
                        <input type="password" class="form-control" id="txt-supervisor-pass"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
                    <button type="button" id="btn-asignar-supervisor" onclick="asignarSupervisor()" class="btn btn-primary">Asignar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-asignar-supervisor1" role="dialog">
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
                        <select id="txt-supervisor1" class="form-control chosen-select"></select>
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

 <div class="modal fade" id="modal-imprimir-inventario" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Imprimir Stock</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>¿Que Stock Desea Imprimir? </label>
                        <input id="status_pdf" hidden></input>
                        <input id="consecutivo" hidden></input>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" id="imprimir_fisico" class="btn btn-primary" onclick="generarPDF_StockFisico_ParaLlenar()">Imprimir Formato Stock Fisico</button>
                    <button type="button" id="imprimir_teorico" class="btn btn-primary" onclick="generarPDF_StockTeorico()">Imprimir Stock Teorico</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var nro_inventario
    var _conteos = [];
    var _tipo_inventario = '';

    function cargarDeatllesInventarioCiclico(id_plan, conteo = 0)
    {
        nro_inventario = id_plan;
        _tipo_inventario = 'ciclico';
        console.log("cargarDeatllesInventarioCiclico->id_plan = ", id_plan);
        console.log("cargarDeatllesInventarioCiclico->conteo = ", conteo);
        if(!conteo || conteo == '--') conteo = 0;
        $.ajax({
            type: "GET",
            dataType: "JSON",
            url: '/inventario/detalles-inventario-ciclico/'+id_plan+'-'+conteo,
            data: {
                id_plan : id_plan,
                conteo : conteo,
                action: "cargarDeatllesInventarioCiclico"
            },
            success: function(data) 
            {
                console.log("cargarDeatllesInventarioCiclico->success = ", data);
                if (data.status == 200) 
                {
                    var row = '', i = 0, readonly = '' , value = '', style = '', style_none = '', value_reg = '';
                    var lastValue = $('#conteo_ciclico_select option:last-child').text();
                    console.log("lastValueSelect = ", lastValue);
                    $.each(data.data, function(index, item){
                        i++;
                        readonly = ''; value = ''; style = ''; style_none = ''; value_reg = '';
                        if(item.conteo == 0) {readonly = 'readonly'; value = item.stockTeorico; style_none = 'display: none;';}
                        //if(item.diferencia == 0) {style = ' style="display:none" ';}
                        if(item.Status == 'T' || (item.conteo < lastValue && item.conteo > 0)) {style_none = 'display: none;'; value_reg = item.Cantidad_reg; }

                        row += '<tr '+style+'>'+
                        '<td>'+item.clave+'</td>'+
                        '<td>'+item.descripcion+'</td>'+
                        //'<td>'+item.zona+'</td>'+
                        '<td>'+item.ubicacion+'</td>'+
                        '<td>'+item.lote+'</td>'+
                        '<td>'+item.caducidad+'</td>'+
                        '<td><span id="txt-stock-teorico-ciclico-'+i+'">'+item.stockTeorico+'</span></td>'+
                        '<td><input id="stock-ciclico'+i+'" class="stock-ciclico" data-index="'+i+'" data-id="'+item.id+'"  type="text" value="'+value+'" autocomplete="off" '+readonly+' style="max-width: 100%;text-align: right;margin: 1px; '+style_none+'"/>'+value_reg+'</td>'+
                        '<td><span id="txt-diferencia-ciclico-'+i+'">'+item.diferencia+'</td>'+
                        '<td>'+item.conteo+'</td>'+
                        '<td>'+item.usuario+'</td>'+
                        '<td>'+item.unidad_medida+'</td>'+
                        '</tr>';
                    });

                    $('#dt-inventario-cilico tbody').html(row);
                    addEventListenerConteosCiclicos();


                }
                else 
                {
                    swal({title: "Error",text: "No se pudo planificar el inventario",type: "error"});
                }
            }, error: function(data){
                console.log("cargarDeatllesInventarioCiclico->ERROR = ", data);
            }
        })
    }


    $("#conteo_ciclico_select").change(function(){

        var id_plan_cont = $(this).val().split("-");
        var id_plan = id_plan_cont[0];
        var conteo = id_plan_cont[1];

        cargarDeatllesInventarioCiclico(id_plan, conteo);

        //console.log("val_select_conteo = ", );
        $("#conteo_ciclico_select").val($(this).val());
    });

    /**
     * Undocumented function
     *
     * @return void
     */
    function addEventListenerConteosCiclicos()
    {
        var stocksFisicoControls = $('.stock-ciclico');
        $.each(stocksFisicoControls, function(index, control){
            $(control).keyup(function(e){
                actulizarValoresConteoCiclico(this);
                if(e.keyCode == 13) 
                {
                    textboxes = $("input.stock-ciclico");
                    currentBoxNumber = textboxes.index(this);
                    if (textboxes[currentBoxNumber + 1] != null) 
                    {
                        nextBox = textboxes[currentBoxNumber + 1];
                        console.log("nextBox = ", nextBox);
                        nextBox.focus();
                        nextBox.select();
                        event.preventDefault();
                        return false;
                    } 
                    else 
                    {
                        swal("Exito", "Conteo realizado, proceda a guardar los cambios para finalizar", "info");
                    }
                }
            });
        });
    }

    /**
     */
    function actulizarValoresConteoCiclico( control )
    {
        var txtStockFisico = $(control);
        console.log("txtStockFisico = ", txtStockFisico);
        var data_id = txtStockFisico.data('index');
        var stockTeorico = $('#txt-stock-teorico-ciclico-'+data_id).text();
        var diferencia = txtStockFisico.val() - stockTeorico;
        $('#txt-diferencia-ciclico-'+data_id).text(diferencia);
    }
        
    /**
     * Guardar el conteo del inventario Ciclico
     *
     * @return void
     */
    function guardarStockCiclico(cerrar_modal = false)
    {
        var stocksFisicoControls = $('.stock-ciclico'),
            _conteos = [];
        $.each(stocksFisicoControls, function(index, item){
            var control = $(item);
            //console.log("control = ", control);
            _conteos.push({
                id : control.data('id'),
                value : control.val()
            });
        });

        console.log("Conteos = ", _conteos);
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: '/api/v2/inventario/guardar-conteo-ciclico',
            data: {
                conteos : _conteos,
                action: "guardarStockCiclico"
            },
            success: function(data) {
                console.log("guardarStockCiclico = ", data);
                if(data.status == 200)
                {
                    //if(cerrar_modal)
                    //{
                        $('#modal-inventario-ciclico').modal('hide');
                        if(data.cerrado == true) showModalSupervisores();
                    //}

                    /*
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
                        */
                }
            }
        });
        return 1;
    }

//***************************************************************************************************
//***************************************************************************************************
//********************************** ADAPTACIÓN INVENTARIO FÍSICO ***********************************
//***************************************************************************************************
//***************************************************************************************************

    function cargarDeatllesInventarioFisico(id_plan, conteo = 0)
    {
        nro_inventario = id_plan;
        _tipo_inventario = 'fisico';
        console.log("cargarDeatllesInventarioFisico->id_plan = ", id_plan);
        console.log("cargarDeatllesInventarioFisico->conteo = ", conteo);
        if(!conteo || conteo == '--') conteo = 0;
        $.ajax({
            type: "GET",
            dataType: "JSON",
            url: '/inventario/detalles-inventario-fisico/'+id_plan+'-'+conteo,
            data: {
                id_plan : id_plan,
                conteo : conteo,
                action: "cargarDeatllesInventarioFisico"
            },
            success: function(data) 
            {
                console.log("cargarDeatllesInventarioFisico->success = ", data);
                if (data.status == 200) 
                {
                    var row = '', i = 0, readonly = '' , value = '', style = '', style_none = '', value_reg = '';
                    var lastValue = $('#conteo_fisico_select option:last-child').text();
                    console.log("lastValueSelect = ", lastValue);
                    $.each(data.data, function(index, item){
                        i++;
                    console.log("item.conteo = ", item.conteo);
                    console.log("item.Status = ", item.Status);
                        readonly = ''; value = ''; style = ''; style_none = ''; value_reg = '';
                        if(item.conteo == 0) {readonly = 'readonly'; value = item.stockTeorico; style_none = 'display: none;';}
                        //if(item.diferencia == 0) {style = ' style="display:none" ';}
                        if(item.Status == 'T' || item.conteo < lastValue || item.Cantidad_reg != 0 /*&& item.conteo > 0*/) {style_none = 'display: none;'; value_reg = item.Cantidad_reg; }

                        row += '<tr '+style+'>'+
                        '<td>'+item.clave+'</td>'+
                        '<td>'+item.descripcion+'</td>'+
                        //'<td>'+item.zona+'</td>'+
                        '<td>'+item.ubicacion+'</td>'+
                        '<td>'+item.lote+'</td>'+
                        '<td>'+item.caducidad+'</td>'+
                        '<td><span id="txt-stock-teorico-fisico-'+i+'">'+item.stockTeorico+'</span></td>'+
                        '<td><input id="stock-fisico'+i+'" class="stock-fisico" data-index="'+i+'" data-id="'+item.id+'"  type="text" value="'+value+'" autocomplete="off" '+readonly+' style="max-width: 100%;text-align: right;margin: 1px; '+style_none+'"/>'+value_reg+'</td>'+
                        '<td><span id="txt-diferencia-fisico-'+i+'">'+item.diferencia+'</td>'+
                        '<td>'+item.conteo+'</td>'+
                        '<td>'+item.usuario+'</td>'+
                        '<td>'+item.unidad_medida+'</td>'+
                        '</tr>';
                    });

                    $('#dt-inventario-fisico tbody').html(row);
                    addEventListenerConteosFisicos();


                }
                else 
                {
                    swal({title: "Error",text: "No se pudo planificar el inventario",type: "error"});
                }
            }, error: function(data){
                console.log("cargarDeatllesInventarioFisico->ERROR = ", data);
            }
        })
    }


    $("#conteo_fisico_select").change(function(){

        var id_plan_cont = $(this).val().split("-");
        var id_plan = id_plan_cont[0];
        var conteo = id_plan_cont[1];

        console.log("CHANGE id plan = ", id_plan, " conteo = ", conteo);

        cargarDeatllesInventarioFisico(id_plan, conteo);

        //console.log("val_select_conteo = ", );
        $("#conteo_fisico_select").val($(this).val());
    });

    /**
     * Undocumented function
     *
     * @return void
     */
    function addEventListenerConteosFisicos()
    {
        var stocksFisicoControls = $('.stock-fisico');
        $.each(stocksFisicoControls, function(index, control){
            $(control).keyup(function(e){
                actulizarValoresConteoCiclico(this);
                if(e.keyCode == 13) 
                {
                    textboxes = $("input.stock-fisico");
                    currentBoxNumber = textboxes.index(this);
                    if (textboxes[currentBoxNumber + 1] != null) 
                    {
                        nextBox = textboxes[currentBoxNumber + 1];
                        console.log("nextBox = ", nextBox);
                        nextBox.focus();
                        nextBox.select();
                        event.preventDefault();
                        return false;
                    } 
                    else 
                    {
                        swal("Exito", "Conteo realizado, proceda a guardar los cambios para finalizar", "info");
                    }
                }
            });
        });
    }

    /**
     */
    function actulizarValoresConteoFisico( control )
    {
        var txtStockFisico = $(control);
        console.log("txtStockFisico = ", txtStockFisico);
        var data_id = txtStockFisico.data('index');
        var stockTeorico = $('#txt-stock-teorico-fisico-'+data_id).text();
        var diferencia = txtStockFisico.val() - stockTeorico;
        $('#txt-diferencia-fisico-'+data_id).text(diferencia);
    }
        
    /**
     * Guardar el conteo del inventario Ciclico
     *
     * @return void
     */
    function guardarStockFisico(cerrar_modal = false)
    {
        var stocksFisicoControls = $('.stock-fisico'),
            _conteos = [];
        $.each(stocksFisicoControls, function(index, item){
            var control = $(item);
            //console.log("control = ", control);
            _conteos.push({
                id : control.data('id'),
                value : control.val()
            });
        });

        console.log("Conteos = ", _conteos);
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: '/api/v2/inventario/guardar-conteo-fisico',
            data: {
                conteos : _conteos,
                action: "guardarStockFisico"
            },
            success: function(data) {
                console.log("guardarStockFisico = ", data);
                if(data.status == 200)
                {
                    //if(cerrar_modal)
                    //{
                        $('#modal-inventario-fisico').modal('hide');
                        if(data.cerrado == true) showModalSupervisores();
                    //}

                    /*
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
                        */
                }
            }, error: function(data)
            {
                console.log("ERROR guardarStockFisico = ", data);
            }
        });
        return 1;
    }

//***************************************************************************************************
//***************************************************************************************************
//***************************************************************************************************
//***************************************************************************************************
/*
    function cargarDeatllesInventarioFisico(id_inventario) 
    {
        nro_inventario = id_inventario;
        _tipo_inventario = 'fisico';
        $("#contador").addClass('hidden');
        //$.jgrid.gridUnload("#grid-table3");
        $("#folionumero2").html(nro_inventario);
        $("#modal-inventario-fisico").modal('show');
        $("#btn-guardar-stock-fisico").attr("disabled", false);
          
        $.ajax({
            type : "GET",
            dataType : "JSON",
            url : '/inventario/detalles-inventario-fisico/' + id_inventario,
            success: function(data) 
            {
                if (data.status == 200) 
                {
                    var row = '', i = 0;
                    $.each(data.data, function(index, item){
                        i++;
                        row += '<tr>'+
                        '<td>'+item.clave+'</td>'+
                        '<td>'+item.descripcion+'</td>'+
                        '<td>'+item.ubicacion+'</td>'+
                        '<td>'+item.serie+'</td>'+
                        '<td>'+item.lote+'</td>'+
                        '<td>'+item.caducidad+'</td>'+
                        '<td><span id="txt-stock-teorico-fisico-'+i+'">'+item.stockTeorico+'</span></td>'+
                        '<td><input id="stock-fisico'+i+'" name="stock-fisico'+i+'" autocomplete="off" class="stock-fisico" data-index="'+i+'" data-id="'+item.id+'"  type="text" value="'+item.stockFisico+'" style="max-width: 100%;text-align: right;margin: 1px;"/></td>'+
                        '<td><span id="txt-diferencia-fisico-'+i+'">'+item.diferencia+'</td>'+
                        '<td>'+item.conteo+'</td>'+
                        '<td><select id="usuarios_lista'+i+'" class="usuarios_lista"></select></td>'+
                        '<td>'+item.unidad_medida+'</td>'+
                        '</tr>';                            
                    });
                    $('#dt-inventario-fisico tbody').html(row);
                    insertOption(data.users);
                    //$(".stock-fisico").keyup(change_color);
                    addEventListenerConteosFisico();
                } 
                else 
                {
                  swal({title: "Error",text: "No se pudo planificar el inventario",type: "error"});
                }
            }
        });
    }
*/

    function insertOption(data)
    {
        var data_pro = data;
        for (var i = 0; i < data_pro.length; i++) 
        {
            console.log(data_pro[i]);
            $(".usuarios_lista").append(new Option(data_pro[i].nombre, data_pro[i].clave));
        }
    }
      
    function change_color()
    {
        var j=0;
        var teorico = [];
        var real = [];
        var table = document.getElementById("dt-inventario-fisico");
        for(var i=1; i< table.rows.length; i++)
        {
            var campo_teorico = document.getElementById("dt-inventario-fisico").rows[i].cells.item(6).innerText;
            var campo_real2 = $("#stock-fisico"+i+"").val();
            teorico.push(campo_teorico); 
            real.push(campo_real2);
            var color = "#FFFFFF";
            if(campo_real2!="")
            {
                if(teorico[j] == real[j]){ color = "#45F842";}
                else{ color = "#FAF44C";}
            }
            for(var k = 0;k<=11;k++)
            {
                document.getElementById("dt-inventario-fisico").rows[i].cells.item(k).style.backgroundColor = color;
            }
            j++;
        }
    }
      
    function change_color_detalle()
    {
        var j=0;
        var teorico = [];
        var real = [];
        var table = document.getElementById("grid-table3");
        for(var i=1; i< table.rows.length; i++)
        {
            var campo_teorico = document.getElementById("grid-table3").rows[i].cells.item(7).innerText;
            var campo_real2 = document.getElementById("grid-table3").rows[i].cells.item(8).innerText;
            teorico.push(campo_teorico); 
            real.push(campo_real2);
            var color = "#FFFFFF";
            if(campo_real2!="")
            {
                if(teorico[j] == real[j]){ color = "#45F842";}
                else{ color = "#FAF44C";}
            }
            for(var k = 0;k<=12;k++)
            {
                document.getElementById("grid-table3").rows[i].cells.item(k).style.backgroundColor = color;
            }
            j++;
        }
    }
      
    $("#conteo").change(function()
    {
        var inventario = false;
        var estado_del_inventario = $("#status_inventario").text();
        if(estado_del_inventario == "Cerrado")
        {
            inventario = true;
        }
        if(inventario == true)
        {
            var conteo_cierre = $('#conteo option:last').val();
            if($("#conteo").val() == conteo_cierre)
            {
                $("#textoConteoCierre").show();
            }
            else 
            {
                $("#textoConteoCierre").hide();
            }
        }
    });

    /**
     * Undocumented function
     *
     * @return void
     */
    function addEventListenerConteosFisico()
    {
        var stocksFisicoControls = $('.stock-fisico');
        $.each(stocksFisicoControls, function(index, control){
            $(control).keyup(function(e){
                actulizarValoresConteoFisico(this);
                change_color();
                if(e.keyCode == 13) 
                {
                    textboxes = $("input.stock-fisico");
                    currentBoxNumber = textboxes.index(this);
                    if (textboxes[currentBoxNumber + 1] != null) 
                    {
                        nextBox = textboxes[currentBoxNumber + 1]
                        nextBox.focus();
                        nextBox.select();
                        e.preventDefault();
                        return false 
                    }
                    else 
                    {
                        swal("Exito", "Conteo realizado, proceda a guardar los cambios para finalizar", "info");
                        e.preventDefault();
                        return false 
                    }
                }
            });
        });
    }

    function actulizarValoresConteoFisico( control )
    {
        var txtStockFisico = $(control);
        var data_id = txtStockFisico.data('index');
        var stockTeorico = $('#txt-stock-teorico-fisico-'+data_id).text();
        var diferencia = txtStockFisico.val() - stockTeorico;
        $('#txt-diferencia-fisico-'+data_id).text(diferencia);
    }
        
    /**
     * Guardar el conteo del inventario Ciclico
     *
     * @return void
     */
    function XguardarStockFisicoX()
    {
        $("#table-usuarios").jqGrid("clearGridData", true).trigger("reloadGrid");
        var _conteosFisicoControls = $('.stock-fisico'),
            _conteos = [];
        $.each(_conteosFisicoControls, function(index, item){
            var control = $(item);
            _conteos.push({id : control.data('id'), value : control.val()});
        });
                    
        var usuarios = [];
        var cve_articulo = [];
        var datos = [];
        var table = document.getElementById("dt-inventario-fisico");
        for(var i=1; i< table.rows.length; i++)
        {
            var campo_usuarios = $("#usuarios_lista"+i+"").val();
            //usuarios.push(campo_usuarios);
            var campo_cve_articulo = document.getElementById("dt-inventario-fisico").rows[i].cells.item(1).innerText;
            //cve_articulo.push(campo_cve_articulo);
            datos.push({usuario:campo_usuarios,articulo:campo_cve_articulo});
        }
        var info = JSON.stringify({datos:datos,conteos:_conteos});
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: '/api/v2/inventario/guardar-conteo-fisico',
            data: {
                info:info,
                action: "guardarStockFisico"
            },
            success: function(data) 
            {
                if(data.status == 200)
                {
                    if(data.cerrado == false)
                    {
                        swal("Advertencia", "Inventario No Cerrado", "warning");
                    }
                    if(data.cerrado == true)
                    {
                        swal("Exito", "Inventario Cerrado", "success");
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
                        //Activar Conteo Cierre
                        $.ajax({
                            type: "POST",
                            dataType: "JSON",
                            url: '/api/v2/inventario/activar-conteo-cierre',
                            data: {
                                conteos : _conteos,
                                datos: datos,
                                action: "activarConteoCierre"
                            },
                            success: function(data) 
                            {
                                if(data.status == 200)
                                {
                                    //console.log(data.ultimoConteo);
                                    swal("Perfecto", "Conteo #"+data.conteo+ " de Cierre, Generado Correctamente", "success");
                                }
                            }
                        });
                    }
                    $('#modal-inventario-fisico').modal('hide');
                    $("#btn-guardar-stock").attr("disabled", false);
                    if(data.cerrado == true)
                    {
                        showModalSupervisores();
                    }
                
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
        });
    }

    /**
    * Undocumented function
    *
    * @return void
    */
    function showModalSupervisores() 
    {
        $.ajax({
            url: '/api/v2/usuarios/administradores',
            dataType: 'json',
            method: 'GET'
        }).done(function(data) {
            var html = '';
            $.each(data.data, function(index, item){
                html += '<option value="'+item.cve_usuario+'">('+item.cve_usuario+') - '+item.nombre_completo+'</option>';
            })                
            $("#txt-supervisor").html(html).trigger("chosen:updated");
            $("#modal-asignar-supervisor").modal('show');
        });
    }


    /**
    * 
    */
    function asignarSupervisor()
    {
        var stocksFisicoControls = $('.stock-fisico, .stock-ciclico'),
            _conteos = [];
        $.each(stocksFisicoControls, function(index, item){
            var control =item;
            console.log(control);
            _conteos.push({
                id : control.id,
                value : control.value
            });
        });
        console.log("conteos",_conteos);
        $.ajax({
            url: '/api/v2/inventario/asignar-supervisor',
            dataType: 'json',
            method: 'POST',
            data : {
                user : $("#txt-supervisor").val(),
                password : $("#txt-supervisor-pass").val(),
                inventario : nro_inventario,
                conteos : _conteos,
                tipo_inventario : _tipo_inventario
            }
        }).done(function(data) {
            if (data.status == 200) 
            {
                $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', 
                {
                    postData: 
                    {
                        criterio: ''
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                  current: true
                }]);
                $("#modal-asignar-supervisor").modal('hide');
                swal("Exito", ""+data.statusText+"", "success");
            }
            else 
            {
              swal("Error", ""+data.statusText+"", "error");
            }
        });
    }
        
    function ReloadGrid() 
    {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    status: $('#status').val(),
                    almacen: $('#almacen2').val() 
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

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
            url: '/inventario/paginate',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            mtype: 'GET',
            colNames: ['Acciones','Consecutivo', 'Almacén', 'Zona', 'Fecha Inicio', 'Fecha Fin', 'Superviso', 'Diferencia','Fiabilidad %', 'Status', 'Tipo', 'Nro. Conteo', 'Inventario Efectuado'],
            colModel: [
                {name: 'myac',index: '',width: 110,fixed: true,align: 'center',sortable: false,resize: false,formatter: imageFormat}, 
                {name: 'consecutivo',index: 'consecutivo',align: 'right',width: 90,sortable: false,editable: false}, 
                {name: 'almacen',index: 'almacen',width: 120,editable: false,sortable: false}, 
                {name: 'zona',index: 'zona',width: 160,editable: false,sortable: false}, 
                {name: 'fecha_inicio',index: 'fecha_inicio',width: 150,editable: false,sortable: false}, 
                {name: 'fecha_final',index: 'fecha_final',width: 150,editable: false,sortable: false}, 
                {name: 'supervisor',index: 'supervisor',width: 150,editable: false,sortable: false}, 
                {name: 'diferencia',index: 'diferencia',align: 'right',width: 100,editable: false,sortable: false}, 
                {name: 'porcentaje',index: 'porcentaje',align: 'right',width: 100,editable: false,sortable: false}, 
                {name: 'status',index: 'status',align: 'center',formatter: formatStatus,width: 80,editable: false,sortable: false}, 
                {name: 'tipo',index: 'tipo',align: 'center',width: 70,editable: false,sortable: false}, 
                {name: 'n_inventario',index: 'n_inventario',align: 'center',width: 100,editable: false,sortable: false}, 
                {name: 'efectuado',index: 'efectuado',align: 'center',width: 150,editable: false,sortable: false}
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'fecha_inicio',
            viewrecords: true,
            sortorder: "ASC"
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

        function formatStatus(cellvalue, options, rowObject) 
        {
            var html = '', estado = rowObject[9];
            if(estado == 'Abierto')
            {
                html = '<span class="red"><strong>'+estado+'</strong></span>';
            } 
            else 
            {
                html = '<span class="green"><strong>'+estado+'</strong></span>';
            }
            return html;
        }

        function imageFormat(cellvalue, options, rowObject) 
        {
            var serie = rowObject[1];
            var html = '';
            var estado = rowObject[9];
            var tipo = rowObject[10];
            var conteo = rowObject[11];

            if (tipo === "Físico") 
            {
                //console.log("tipo OK = ", tipo);
                //console.log("serie = ", serie);
                //console.log("estado = ", estado);

                //html = '<a href="#" onclick="detalleConteoFisico(\'' + serie + '\', \'' + estado + '\', \'' + tipo + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';

                html = '<a href="#" onclick="detalleFisico(\'' + serie + '\', \'' + conteo + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';

                if (estado === "Abierto") 
                {
                    //html += '<a href="#" onclick="asignarUsuarioFisico(\'' + serie + '\')"  title="Asignar Usuario y Contar"><i class="fa fa-user-plus"></i></a>&nbsp;&nbsp;&nbsp;';
                    html += '<a href="#" onclick="asignarUsuarioFisico(\'' + serie + '\', \'' + conteo + '\')"  title="Asignar Usuario y Contar"><i class="fa fa-user-plus"></i></a>&nbsp;&nbsp;&nbsp;';
                }
                html += `<a href="#" onclick="impimir_stocks_vacios('` + serie + `', '` + estado + `')" title="Imprimir Stock Teorico"><i class="fa fa-file-pdf-o"></i></a>&nbsp;&nbsp;&nbsp;`;
                html += `<a href="#" onclick="generarPDFFisico('` + serie + `', '` + estado + `')" title="Imprimir Reporte"><i class="fa fa-print"></i></a>`;
            }
            else if (tipo === 'Cíclico') 
            {
                html = '<a href="#" onclick="detalleCiclico(\'' + serie + '\', \'' + conteo + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';
                //html = '<a href="#" onclick="detalleConteoFisico(\'' + serie + '\', \'' + estado + '\', \'' + tipo + '\')"  title="Ver Detalle"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;';
                if (estado === "Abierto") 
                {                        
                    html += '<a href="#" onclick="asignarUsuarioCiclico(\'' + serie + '\', \'' + conteo + '\')"  title="Asignar Usuario y Contar"><i class="fa fa-user-plus"></i></a>&nbsp;&nbsp;&nbsp;';
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
        console.log("Creando inventario");
        var ubicaciones = [];
        var usuarios = [];
        $("#toU").each(function() {
            var localRels = [];
            $(this).find('li').each(function() {
                localRels.push($(this).attr('value') + " | " + $(this).attr('area'));
            });
            ubicaciones.push(localRels);
        });

        if ((ubicaciones.length <= 0) || ($('#zona').val() == "" && !document.getElementById("recepcion").checked) || ($('#almacen').val().split("|")[0] == "") || ($('#Fecha').val() == "")) 
        {
            swal({
                title: "Faltan campos",
                text: "Por favor llene todos los campos para planificar un inventario",
                //type: "success"
            });
        } 
        else 
        {
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
                    if (data.success) 
                    {
                        swal({
                            title: "Inventario Planificado",
                            text: "El inventario ha sido enviado al administrador de inventario",
                            type: "success"
                        });
                    } 
                    else 
                    {
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

    function detalleConteoFisico(_codigo, status, tipo) 
    {
        $("#numero_inventario").html(_codigo);
        $("#status_inventario").html(status);
        $("#tipo_inv").html(tipo);
        

        console.log("****************************");
        console.log("serie = ", _codigo);
        console.log("estado = ", status);
        $.ajax({
            url: '/api/inventariosfisicos/lista/index.php',
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'getConteos',
                id: _codigo
            }
        }).done(function(data){
            $("#hiddenInventario").val(_codigo)
            var conteos = data.conteos;
            var select = $("#conteo");
            select.empty();
            var option = document.createElement('option');
            option.value = '0';
            option.text = 'Conteo Teorico';
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
            $("#coModal").modal('show');
        });
    }
/*
    function asignarUsuarioFisico(_codigo) 
    {
        $.ajax({
            url: '/api/inventariosfisicos/lista/index.php',
            data: {
                action: 'getPendingCount',
                id: _codigo
            },
            dataType: 'json',
            method: 'GET'
        }).done(function(data) {
            if (data.total > -100) {
                swal({
                    title: "Advertencia",
                    text: "INVENTARIO No. "+_codigo+" Asigne los Usuarios correspondientes.",
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "No",
                    showConfirmButton: true,
                    confirmButtonText: "Sí",
                    allowOutsideClick: false
                }, function(confirm) {
                    if (confirm) {
                        loadModalUserFisico(_codigo);
                    }
                });
            } 
        });
    }
*/
    function asignarUsuarioFisico(_codigo, conteo) 
    {
        console.log("conteo usuario = ", conteo);
        $("#conteo_fisico").val(conteo);

//******************************************************************************************
        
            $.ajax({
                url: '/api/inventariosfisicos/update/index.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'existe_conteo_0',
                    id: _codigo
                }
            }).done(function(data){

                console.log("existe_conteo_0 = ", data);
                $("#conteo_fisico_select").empty();
                if(data.cuenta == 0)
                {
                    console.log("Entró guardarStockFisico() = '--'");
                    if(guardarStockFisico(true))
                       console.log("guardarStockFisico() = Realizado");
                   else
                       console.log("guardarStockFisico() = Falló");
                }

                $("#conteo_fisico_select").append(data.conteos_option);

                //if(select_conteo == -1)
                    $("#conteo_fisico_select").val(data.max_conteo);
                //console.log("conteo_ciclico_select _codigo = ", _codigo);
                //console.log("conteo_ciclico_select data.max_conteo = ", data.max_conteo);
                //console.log("conteo_ciclico_select = ", $("#conteo_ciclico_select").val());

            }).fail(function(data){
                console.log("ERROR existe_conteo_0 = ", data, "ID_PLAN = ", _codigo);
            });
//******************************************************************************************


        $.ajax({
            url: '/api/inventariosfisicos/lista/index.php',
            data: {
                action: 'getPendingCount',
                id: _codigo
            },
            dataType: 'json',
            method: 'GET'
        }).done(function(data) {
            if (data.total > 0) 
            {
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
                    if (confirm) 
                    {
                        detalleFisico(_codigo, conteo);
                    } 
                    else 
                    {
                        loadModalUserFisico(_codigo);
                    }
                });
            } 
            else 
            {
                loadModalUserFisico(_codigo);
            }
        });
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
        }).done(function(data) 
        {
          var usuarios = data.usuarios;
          var usuario = document.getElementById('usuario');
          var select = document.createElement('option');
          usuario.innerHTML = null;
          select.text = 'Seleccione Usuario';
          select.value = '';
          usuario.add(select)

          for (var i = 0; i < usuarios.length; i++) 
          {
              var options = document.createElement('option');
              options.value = usuarios[i].cve_usuario;
              options.text = usuarios[i].nombre_completo;
              usuario.add(options);
          }

          $("#cod1").val(_codigo);
          $("#txt-supervisor1").trigger("chosen:updated");

          $("#modal-asignar-supervisor1").modal('show');
        });
    }

    $("#guardarUsuarioSup1").on("click", function(e) {
        $.ajax({
            url: '/api/inventariosfisicos/update/index.php',
            data: {
                action: 'guardarUsuarioSup1',
                id: $("#cod1").val(),
                usuario: $("#txt-supervisor1").val()
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data) {
            $("#asignarUsuarioSuperviso1").modal('hide');
            if (data.success) 
            {
                swal("Exito", "Supervisor asignado correctamente", "success");
                swal({
                    title: "Exito",
                    text: "Supervisor asignado correctamente",
                    type: "success",
                    showCancelButton: false,
                    allowOutsideClick: false
                }, function(confirm) {
                    cargarDeatllesInventarioFisico($("#cod1").html());
                });
            } 
            else 
            {
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
      
    $(function($) {
        var grid_selector = "#table-usuarios";
        var pager_selector = "#table-usuarios-pager";
        //resize to fit page size
        $(window).on('resize.jqGrid', function() 
        {
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

        $("#table-usuarios").jqGrid({
            datatype: "local",
            shrinkToFit: true,
            height: 'auto',
            colNames:['id','Clave de Usuario', 'Nombre de Usuario'],
            colModel:[
                {name:'id',index:'id', width:10, hidden:true},
                {name:'cve',index:'cve', width:338},
                {name:'name',index:'name', width:350}
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            viewrecords: true,
            sortorder: "ASC"
        });
    });

    $("#guardarUsuario").on("click", function(e) 
    {
        var ids = $("#table-usuarios").jqGrid('getDataIDs');
        var length = ids.length;
        var nuevo = true;
        for (var i = 0; i < length; i++) 
        {
            var sel = document.getElementById('usuario');
            var opt = sel.options[sel.selectedIndex];
            var clave = ( opt.value );
            var rowId = ids[i];
            var rowData = $('#table-usuarios').jqGrid('getRowData', rowId);
            console.log(rowData);
            if (rowData.cve==clave) 
            {
                window.alert("Este Usuario ya fue incluido");
                return;
            } 
        }
        //aqui esta agregando los datos a la grid Tabla de Usuarios
        if (nuevo == true) 
        {
            var sel = document.getElementById('usuario');
            var opt = sel.options[sel.selectedIndex];
            var clave = ( opt.value );
            var nombre = ( opt.text.split("-")[1].trim() );
            emptyItem = [{
                id: opt.text,
                cve: clave,
                name: nombre
            }];
            $("#table-usuarios").jqGrid('addRowData', 0, emptyItem);
        }
    });
        
    $("#asignar_usuarios").on("click", function(e)
    {
        $("#asignar_usuarios").attr("disabled", true);
        var arrDetalle = [];
        var ids = $("#table-usuarios").jqGrid('getDataIDs');
        var length = ids.length;
        for (var i = 0; i < length; i++) 
        {
            var rowId = ids[i];
            var rowData = $('#table-usuarios').jqGrid('getRowData', rowId);
            //console.log(rowData);
            arrDetalle.push({
                cve_usuario: rowData.cve,
                name: rowData.name
            });
        }

        $.ajax({
            url: '/api/inventariosfisicos/update/index.php',
            data: {
                action: 'guardarUsuario',
                id: $("#folionumero").html(),
                usuario: arrDetalle
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data){
            $("#asignarUsuario").modal('hide');
            if (data.success) 
            {
                swal("Exito", "Usuarios asignados correctamente", "success");
                swal({
                title: "Exito",
                text: "Usuarios asignados correctamente",
                type: "success",
                showCancelButton: false,
                allowOutsideClick: false
                }, function(confirm) {
                    cargarDeatllesInventarioFisico($("#folionumero").html());
                    $("#asignar_usuarios").attr("disabled", false);
                });
            } 
            else 
            {
                swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
                $("#asignar_usuarios").attr("disabled", false);
            }
        });
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
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacen/update/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    var arr = $.map(data.ubicaciones, function(el) {
                        return el;
                    })
                    arr.pop();
                    for (var i = 0; i < data.ubicaciones.length; i++) 
                    {
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

    function XloadModalUserFisicoX(_codigo) 
    {
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

            for (var i = 0; i < usuarios.length; i++) 
            {
                var options = document.createElement('option');
                options.value = usuarios[i].cve_usuario;
                options.text = "("+ usuarios[i].cve_usuario + ") - " + usuarios[i].nombre_completo;
                usuario.add(options);
            }
            $("#usuario").trigger("chosen:updated");
            $("#folionumero").html(_codigo);
            $("#asignarUsuario").modal('show');
        });
    }

    function almacen() 
    {
        if ($("#almacen").val() !== '') 
        {
            $("#recepcion").iCheck('enable');
        }
        $('#zona').find('option').remove().end();
        var value = $('#almacen').val().split("|");
        var clave = value[0], id = value[1];
        $("#fromU .itemlist").remove();
        $("#toU .itemlist").remove();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                clave: clave,
                action: "traerZonasDeAlmacenP"
            },
            beforeSend: function(x) 
            {
                if (x && x.overrideMimeType) 
                {
                  x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenp/update/index.php',
            success: function(data) 
            {
                if (data.success == true) 
                {
                    var options = $("#zona");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i = 0; i < data.zonas.length; i++) 
                    {
                        options.append(new Option(data.zonas[i].nombre_zona, data.zonas[i].clave_zona));
                    }
                    $('.chosen-select').trigger("chosen:updated");
                }
            }
        });
    }

    $('#zona').on('change', function() 
    {
        if ($(this).val() != "")
        {
            $("#cargarUbicaciones").prop('disabled', false);
        }
        else
        {
          $("#cargarUbicaciones").prop('disabled', true);
        }
        $.ajax(
        {
            type: "POST",
            dataType: "json",
            data: {
                zona: $('#zona').val(),
                action: "traerRackDeZonas",
                conProducto: true
            },
            beforeSend: function(x) 
            {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacen/update/index.php',
            success: function(data) 
            {
                if (data.success == true) 
                {
                    var options = $("#rack");
                    options.empty();
                    options.append(new Option("Seleccione", ""));
                    for (var i = 0; i < data.racks.length; i++) 
                    {
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
            if (e.target.checked && e.target.id === 'recepcion') 
            {
                $("#cargarUbicaciones").removeAttr("disabled");
            } 
            else 
            {
                if ($("#zona").val() === '') 
                {
                    $("#cargarUbicaciones").attr("disabled", "disabled");
                }
            }
            if (e.target.checked && e.target.id === 'selectAll') 
            {
                $('#fromU li input[type="checkbox"].drag').each(function(i, e) {
                    e.checked = true;
                    e.parentElement.setAttribute('aria-grabbed', true);
                });
            } 
            else 
            {
                $('#fromU li input[type="checkbox"].drag').each(function(i, e) {
                    e.checked = false;
                    e.parentElement.setAttribute('aria-grabbed', false);
                });
            }
        });
    });

    function add(from, to) 
    {
        var elements = document.querySelectorAll(`${from} input.drag:checked`),
            li, newli;
        for (e of elements) 
        {
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

    function selectParent(e) 
    {
        if (e.checked) 
        {
            e.parentNode.setAttribute("aria-grabbed", "true");
        } 
        else 
        {
            e.parentNode.setAttribute("aria-grabbed", "false");
        }
    }

    function selectChild(e) 
    {
        if (e.getAttribute("aria-grabbed") == "true") 
        {
            e.firstChild.checked = true;
        } 
        else 
        {
            e.firstChild.checked = false;
        }
    }
</script>
<script type="text/javascript">
    var loadArticleDetails;
    (function() {
        loadArticleDetails = function(codigo) {
            return false;
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
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
                {
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
                    criterio:$("#criterio").val(),
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
                    if (stockFisico === 0 && parseInt(grid.diferencia) !== 0 && parseInt(grid.conteo) > 0) 
                    {
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

            console.log("loadDetailsConteo codigo = ", codigo);
            console.log("criterio = ", $("#criterio").val());
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
                        $(grid_selector).jqGrid('setGridWidth',  $(".page-content").width() - 60);
                    }, 0);
                }
            })
            //EDG
            $(grid_selector).jqGrid({
                url: '/api/inventariosfisicos/update/index.php',
                datatype: "json",
                postData: {
                    ID_Inventario: codigo,
                    criterio: $("#criterio").val(),
                    action: "loadDetalle",
                    conteo:0
                },
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['ID', 'Ubicacion', 'Clave', 'Descripción', 'Lote', 'Caducidad', 'Serie', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Conteo', 'Usuario', 'Unidad de Medida'],
                colModel: [
                    {name: 'ID_Inventario',index: 'ID_Inventario',width: 60,sorttype: "int",editable: false}, 
                    {name: 'ubicacion',index: 'ubicacion',width: 200,editable: false,sortable: false}, 
                    {name: 'clave',index: 'clave',width: 120,editable: false,sortable: false}, 
                    {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false}, 
                    {name: 'lote',index: 'lote',width: 150,editable: false,sortable: false}, 
                    {name: 'caducidad',index: 'caducidad',width: 150,editable: false,sortable: false}, 
                    {name: 'serie',index: 'serie',width: 150,editable: false,sortable: false}, 
                    {name: 'stockTeorico',index: 'stockTeorico',width: 150,editable: false,sortable: false, align:'right'}, 
                    {name: 'stockFisico',index: 'stockFisico',width: 150,editable: false,sortable: false, align:'right'}, 
                    {name: 'diferencia',index: 'diferencia',width: 150,editable: false,sortable: false, align:'right'}, 
                    {name: 'conteo',index: 'conteo',width: 100,editable: false,sortable: false, align:'right'}, 
                    {name: 'usuario',index: 'usuario',width: 100,editable: false,sortable: false}, 
                    {name: 'unidad_medida',index: 'unidad_medida',width: 180,editable: false,sortable: false}
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                viewrecords: true,
                loadComplete:function () {
                    console.log($("#grid-table3").getGridParam()["records"]);
                    $("#total_u").val($("#grid-table3").getGridParam()["records"]);
                    change_color_detalle();
                }
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
        reloadGridConteo($("#hiddenInventario").val(), e.target.value,$("#criterio").val());
    });

    function reloadGridConteo(_codigo, _conteo, _criterio) 
    {
        $('#grid-table3').jqGrid('clearGridData')
        .jqGrid('setGridParam', 
        {
            postData: 
            {
                action: 'loadDetalle',
                criterio: _criterio,
                ID_Inventario: _codigo,
                conteo: _conteo
            },
            datatype: 'json',
            page: 1
        }).trigger('reloadGrid');
    }
</script>
<script type="text/javascript">
    function impimir_stocks_vacios(consecutivo , status)
    {
        $("#consecutivo").val(consecutivo);
        $("#status_pdf").val(status);
        console.log("Consecutivo = ", consecutivo, " - Status = ", status);
        $("#modal-imprimir-inventario").modal('show');
    }
      
    function generarPDF_StockFisico_ParaLlenar() 
    {
        var form = document.createElement('form'),
            nobody = document.createElement('input'),
            folio = document.createElement('input'),
            estado = document.createElement('input'),
            action = document.createElement('input');
        var consecutivo = $("#consecutivo").val();
        var status = $("#status_pdf").val();

        console.log("Consecutivo = ", consecutivo, " - Status = ", status);

        form.setAttribute('target', '_blank');
        form.setAttribute('method', 'post');
        form.setAttribute('action', '/api/inventariosfisicos/update/index.php');
        nobody.setAttribute('type', 'hidden');
        nobody.setAttribute('name', 'nofooternoheader');
        nobody.setAttribute('value', 'true');
        folio.setAttribute('type', 'hidden');
        folio.setAttribute('name', 'id');
        folio.setAttribute('value', consecutivo);
        estado.setAttribute('type', 'hidden');
        estado.setAttribute('name', 'status');
        estado.setAttribute('value', status);
        action.setAttribute('type', 'hidden');
        action.setAttribute('name', 'action');
        action.setAttribute('value', 'printReport_fisico_ParaLlenado');
        form.appendChild(nobody);
        form.appendChild(folio);
        form.appendChild(estado);
        form.appendChild(action);
        document.getElementsByTagName('body')[0].appendChild(form);
        form.submit();
    }
      
    function generarPDF_StockTeorico() 
    {
        var form = document.createElement('form'),
            nobody = document.createElement('input'),
            folio = document.createElement('input'),
            estado = document.createElement('input'),
            action = document.createElement('input');
        var consecutivo = $("#consecutivo").val();
        var status = $("#status").val();

        form.setAttribute('target', '_blank');
        form.setAttribute('method', 'post');
        form.setAttribute('action', '/api/inventariosfisicos/update/index.php');
        nobody.setAttribute('type', 'hidden');
        nobody.setAttribute('name', 'nofooternoheader');
        nobody.setAttribute('value', 'true');
        folio.setAttribute('type', 'hidden');
        folio.setAttribute('name', 'id');
        folio.setAttribute('value', consecutivo);
        estado.setAttribute('type', 'hidden');
        estado.setAttribute('name', 'status');
        estado.setAttribute('value', status);
        action.setAttribute('type', 'hidden');
        action.setAttribute('name', 'action');
        action.setAttribute('value', 'printReport_teorico');
        form.appendChild(nobody);
        form.appendChild(folio);
        form.appendChild(estado);
        form.appendChild(action);
        document.getElementsByTagName('body')[0].appendChild(form);
        form.submit();
    }
      
    function generarPDFFisico(consecutivo, status) 
    {
        var form = document.createElement('form'),
            nobody = document.createElement('input'),
            folio = document.createElement('input'),
            estado = document.createElement('input'),
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
        estado.setAttribute('type', 'hidden');
        estado.setAttribute('name', 'status');
        estado.setAttribute('value', status);
        action.setAttribute('type', 'hidden');
        action.setAttribute('name', 'action');
        action.setAttribute('value', 'printReport');
        form.appendChild(nobody);
        form.appendChild(folio);
        form.appendChild(estado);
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
    function detalleCiclico(_codigo, conteo, select_conteo = -1) 
    {
            $.ajax({
                url: '/api/inventariosciclicos/update/index.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'existe_conteo_0',
                    id: _codigo
                }
            }).done(function(data){

                console.log("existe_conteo_0 = ", data);
                $("#conteo_ciclico_select").empty();
                //if(data.cuenta == 0)
                //{
                //    console.log("Entró guardarStockCiclico() = '--'");
                //    if(guardarStockCiclico(true))
                //       console.log("guardarStockCiclico() = Realizado");
                //   else
                //       console.log("guardarStockCiclico() = Falló");
                //}

                $("#conteo_ciclico_select").append(data.conteos_option);

                if(select_conteo == -1)
                    $("#conteo_ciclico_select").val(data.max_conteo);
                //console.log("conteo_ciclico_select _codigo = ", _codigo);
                //console.log("conteo_ciclico_select data.max_conteo = ", data.max_conteo);
                //console.log("conteo_ciclico_select = ", $("#conteo_ciclico_select").val());

            }).fail(function(data){
                console.log("ERROR existe_conteo_0 = ", data, "ID_PLAN = ", _codigo);
            });
            cargarDeatllesInventarioCiclico(_codigo, conteo);
            $("#modal-inventario-ciclico").modal('show');

    }

    function detalleFisico(_codigo, conteo, select_conteo = -1) 
    {
            $.ajax({
                url: '/api/inventariosfisicos/update/index.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'existe_conteo_0',
                    id: _codigo
                }
            }).done(function(data){

                console.log("existe_conteo_0 = ", data);
                $("#conteo_fisico_select").empty();
                //if(data.cuenta == 0)
                //{
                //    console.log("Entró guardarStockCiclico() = '--'");
                //    if(guardarStockCiclico(true))
                //       console.log("guardarStockCiclico() = Realizado");
                //   else
                //       console.log("guardarStockCiclico() = Falló");
                //}

                $("#conteo_fisico_select").append(data.conteos_option);

                if(select_conteo == -1)
                    $("#conteo_fisico_select").val(data.max_conteo);
                //console.log("conteo_fisico_select _codigo = ", _codigo);
                //console.log("conteo_fisico_select data.max_conteo = ", data.max_conteo);
                //console.log("conteo_fisico_select = ", $("#conteo_fisico_select").val());

            }).fail(function(data){
                console.log("ERROR existe_conteo_0 = ", data, "ID_Inventario = ", _codigo);
            });
            cargarDeatllesInventarioFisico(_codigo, conteo);
            $("#modal-inventario-fisico").modal('show');

    }

    function detallecCiclico(_codigo) 
    {
        loadArticleDetailscCiclico(_codigo);
        $("#modal-inventario-ciclico").modal('show');
    }

    function asignarUsuarioCiclico(_codigo, conteo) 
    {
        console.log("conteo usuario = ", conteo);
        $("#conteo_ciclico").val(conteo);

//******************************************************************************************
        
            $.ajax({
                url: '/api/inventariosciclicos/update/index.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'existe_conteo_0',
                    id: _codigo
                }
            }).done(function(data){

                console.log("existe_conteo_0 = ", data);
                $("#conteo_ciclico_select").empty();
                if(data.cuenta == 0)
                {
                    console.log("Entró guardarStockCiclico() = '--'");
                    if(guardarStockCiclico(true))
                       console.log("guardarStockCiclico() = Realizado");
                   else
                       console.log("guardarStockCiclico() = Falló");
                }

                $("#conteo_ciclico_select").append(data.conteos_option);

                //if(select_conteo == -1)
                    $("#conteo_ciclico_select").val(data.max_conteo);
                //console.log("conteo_ciclico_select _codigo = ", _codigo);
                //console.log("conteo_ciclico_select data.max_conteo = ", data.max_conteo);
                //console.log("conteo_ciclico_select = ", $("#conteo_ciclico_select").val());

            }).fail(function(data){
                console.log("ERROR existe_conteo_0 = ", data, "ID_PLAN = ", _codigo);
            });
//******************************************************************************************


        $.ajax({
            url: '/api/inventariosciclicos/lista/index.php',
            data: {
                action: 'getPendingCount',
                id: _codigo
            },
            dataType: 'json',
            method: 'GET'
        }).done(function(data) {
            if (data.total > 0) 
            {
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
                    if (confirm) 
                    {
                        detalleCiclico(_codigo, conteo);
                    } 
                    else 
                    {
                        loadModalUserCiclico(_codigo);
                    }
                });
            } 
            else 
            {
                loadModalUserCiclico(_codigo);
            }
        });
    }

    function loadModalUserCiclico(_codigo) 
    {
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

            for (var i = 0; i < usuarios.length; i++) 
            {
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

    function loadModalUserFisico(_codigo) 
    {
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
            var usuario = document.getElementById('usuario_fisico');
            var select = document.createElement('option');
            usuario.innerHTML = null;
            select.text = 'Seleccione Usuario';
            select.value = '';
            usuario.add(select)

            for (var i = 0; i < usuarios.length; i++) 
            {
                var options = document.createElement('option');
                options.value = usuarios[i].cve_usuario;
                options.text = usuarios[i].nombre_completo;
                usuario.add(options);
            }

            $("#usuario_fisico").trigger("chosen:updated");
            $("#folionumero_fisico").html(_codigo);
            $("#asignarUsuarioFisico").modal('show');
        });
    }


    $("#guardarUsuarioCiclico").on("click", function(e) {

        console.log("id:", $("#folionumero_ciclico").html());
        console.log("usuario:", $("#usuario_ciclico").val());
        console.log("#conteo_ciclico = ", $("#conteo_ciclico").val());
/*
        if($("#conteo_ciclico").val() == '--')
        {
            console.log("Entró guardarStockCiclico() = '--'");
            if(guardarStockCiclico())
               console.log("guardarStockCiclico() = Realizado");
           else
               console.log("guardarStockCiclico() = Falló");
        }
*/
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
            console.log("guardarUsuarioCiclico = ", data);
            $("#asignarUsuarioCiclico").modal('hide');
            if (data.success) 
            {
                swal("Exito", "Usuario asignado correctamente", "success");
                swal({
                    title: "Exito",
                    text: "Usuario asignado correctamente",
                    type: "success",
                    showCancelButton: false,
                    allowOutsideClick: false
                }, function(confirm) {
                    detalleCiclico($("#folionumero_ciclico").html(), $("#conteo_ciclico").val());
                });

            } 
            else 
            {
                swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
            }
        }).fail(function(data){
            console.log("ERROR guardarUsuarioCiclico = ", data);
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
        });
        asignarUsuarioCiclico($("#folionumero_ciclico").html(), $("#conteo_ciclico").val());
    });

    $("#guardarUsuarioFisico").on("click", function(e) {

        console.log("id:", $("#folionumero_fisico").html());
        console.log("usuario:", $("#usuario_fisico").val());
        console.log("#conteo_fisico = ", $("#conteo_fisico").val());
/*
        if($("#conteo_ciclico").val() == '--')
        {
            console.log("Entró guardarStockCiclico() = '--'");
            if(guardarStockCiclico())
               console.log("guardarStockCiclico() = Realizado");
           else
               console.log("guardarStockCiclico() = Falló");
        }
*/
        $.ajax({
            url: '/api/inventariosfisicos/update/index.php',
            data: {
                action: 'guardarUsuario',
                id: $("#folionumero_fisico").html(),
                usuario: $("#usuario_fisico").val()
            },
            dataType: 'json',
            method: 'POST'
        }).done(function(data) {
            console.log("guardarUsuarioFisico = ", data);
            $("#asignarUsuarioFisico").modal('hide');
            if (data.success) 
            {
                swal("Exito", "Usuario asignado correctamente", "success");
                swal({
                    title: "Exito",
                    text: "Usuario asignado correctamente",
                    type: "success",
                    showCancelButton: false,
                    allowOutsideClick: false
                }, function(confirm) {
                    //detalleFisico($("#folionumero_fisico").html(), $("#conteo_fisico").val());
                    window.location.reload();
                });

            } 
            else 
            {
                swal("Error", "El usuario que intenta asignar ya fue asignado durante los conteos", "error");
            }
        }).fail(function(data){
            console.log("ERROR guardarUsuarioFisico = ", data);
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
        });
        asignarUsuarioFisico($("#folionumero_fisico").html(), $("#conteo_fisico").val());
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
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
                {
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
                    criterio:$("#criterio").val(),
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
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
                {
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
                    criterio:$("#criterio").val(),
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

    function cargar_usuario(id_inventario)
    {
      $.ajax({
          type: "POST",
          dataType: "JSON",
          url: 'lista/index.php',
          data: {
            id_inventario : id_inventario,
            action: "cargar_usuario"
          },
          success: function(data) 
          {
              if (data.success == true) 
              {
                  var row = '', i = 0;
                  $.each(data.usuarios, function(index, item){
                    i++;
                    row += 
                      '<tr>'+
                      '<td>'+item.clave+'</td>'+
                      '<td>'+item.descripcion+'</td>'+
                      '</tr>';                            
                  });
                  $('#table-usuarios tbody').html(row);
              } 
              else 
              {
                  swal({title: "Error",text: "No se pudo planificar el inventario",type: "error"});
              }
          }
      });
  }
</script>
<style>
    @media (min-width: 768px){
        .modal-dialog {
            width: 750px;
            margin: 30px auto;
        }
    }
    @media (min-width: 992px){
        .modal-lg {
            width: 990px;
        }
    }
    @media (min-width: 1200px){
        .modal-lg {
            width: 1190px;
        }
    }

    .table>caption+thead>tr:first-child>td, 
    .table>caption+thead>tr:first-child>th, 
    .table>colgroup+thead>tr:first-child>td, 
    .table>colgroup+thead>tr:first-child>th, 
    .table>thead:first-child>tr:first-child>td, 
    .table>thead:first-child>tr:first-child>th {
        border-top: 0;
        font-weight: bold;
        color: #000000;
        border: 1px solid #ccc;
    }
    .table > thead > tr > th, 
    .table > tbody > tr > th, 
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #cccccc !important;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #e7eaec;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    
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
    .w100,.ui-jqgrid-view,.ui-jqgrid-hdiv,.ui-jqgrid-bdiv,.ui-jqgrid-pager
    {
      width:100% !important;
    }
    .center 
    {
      text-align: center;
    }
</style>