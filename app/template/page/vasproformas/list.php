<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaDiaO = new \Venta\Venta();

$listTransportes = new \Transporte\Transporte();
$vendedores = new \Usuarios\Usuarios();


$cve_almacen = $_SESSION['cve_almacen'];
$listTransportes = \db()->prepare("SELECT *  FROM t_transporte WHERE Activo = 1 and id_almac = (SELECT id FROM c_almacenp WHERE clave = '$cve_almacen')");
$listTransportes->execute();
$listTransportes = $listTransportes->fetchAll(PDO::FETCH_ASSOC);

$vere = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=45 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$vere->execute();
$ver = $vere->fetchAll(PDO::FETCH_ASSOC);

$agre = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=46 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$agre->execute();
$ag = $agre->fetchAll(PDO::FETCH_ASSOC);

$edita = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=47 and id_role='" . $_SESSION["perfil_usuario"] . "'");
$edita->execute();
$edit = $edita->fetchAll(PDO::FETCH_ASSOC);

$borra = \db()->prepare("SELECT * from t_profiles as a where id_menu=20 and id_submenu=48 and id_role='" . $_SESSION["perfil_usuario"] . "'");
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
<input type="hidden" name="cve_usuario" id="cve_usuario" value="<?php echo $_SESSION['cve_usuario']; ?>">
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
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1"
                                   placeholder="Buscar por Ruta...">
                            <div class="input-group-btn">
                                <a href="#" onclick="ReloadGrid1()">
                                    <button type="submit" id="buscarAx" class="btn btn-sm btn-primary">Buscar
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


<div class="modal fade" id="ModalProforma" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Generar Proforma</h4>
                </div>
                <div class="modal-body">
                        <div class="input-group">
                            <label for="nro_pedido">
                            Número de Pedido
                            <input type="text" class="form-control" name="nro_pedido" id="nro_pedido" placeholder="Número de Pedido">
                            </label>
                        </div>
                        <br><br>
                        <div class="input-group">
                            <label for="tipo_operacion">
                            Tipo Operación
                            <input type="text" class="form-control" name="tipo_operacion" id="tipo_operacion" placeholder="Tipo de Operación">
                            </label>
                        </div>
                        <br><br>
                        <div class="input-group">
                            <label for="observaciones">
                            Observaciones
                            <textarea class="form-control" name="observaciones" id="observaciones"></textarea></label>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="generar_proforma">Generar Proforma</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/assets/js/jquery.dataTables.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/datatables.min.js"></script>
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
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<link href="/css/datatables.min.css" rel="stylesheet"/>

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right {
        position: absolute;
        left: auto;
        right: 0;
    }
</style>

<input type="hidden" name="folios_grupo" id="folios_grupo" value="">

<div class="modal inmodal" id="myModalClientes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizar()">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </a>
                <h4 class="modal-title">Asignar clientes a Ruta <span></span></h4>
            </div>
            <div class="modal-body">
                <table class="footable table table-stripped toggle-arrow-tiny" data-paging="true" data-filtering="true"
                       data-sorting="true" data-expand-first="true" data-paging-size="3">
                    <thead>
                    <tr>
                        <th> Clave</th>
                        <th>Cliente</th>
                        <th data-visible="false">Municipio</th>
                        <th data-breakpoints="all">Rutas</th>
                        <th data-filterable="false">Asignar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php /* foreach( $listaCliente->getCliente() AS $p ): ?>
                            <tr>
                                <td><?php echo $p->Cve_Clte; ?></td>
                                <td><?php echo $p->RazonSocial; ?></td>
                                <td> <?php echo $p->desc_municipio; ?> </td>
                                <td>
                                    <?php foreach( $listaCliente->traerRutas($p->id_cliente) AS $r ): ?>
                                        <?php echo $r['descripcion'].", "; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td><input type="checkbox" id="clientes" name="clientes[]" value="<?php echo $p->Cve_Clte; ?>"></td>
                            </tr>
                        <?php endforeach; */ ?>
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
            <div class="modal-footer">
                <a href="#" onclick="minimizar()">
                    <button type="button" class="btn btn-white" id="btnCancel">Cerrar</button>
                </a>&nbsp;&nbsp;
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" onclick="minimizar()"
                        style="width: auto;">Seleccionar Clientes
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="ruta_clave_select" value="">
<div class="modal inmodal" id="modalTransportes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarT()">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </a>
                <h4 class="modal-title">Asignar Transporte a Ruta <span id="ruta_clave"></span></h4>
            </div>
            <div class="modal-body">
                <select class="form-control" id="transportes">
                    <option value="">Seleccione Transporte</option>
                    <?php foreach ($listTransportes as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo $r['ID_Transporte'] . "-" . $r['Nombre'] . "-" . $r['Placas']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <a href="#" onclick="minimizarT()">
                    <button type="button" class="btn btn-white" id="btnCancel">Cerrar</button>
                </a>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger ladda-button" data-style="contract"
                        onclick="EliminarTransporte();" style="width: auto;">Eliminar
                </button>
                <button type="button" class="btn btn-primary ladda-button" data-style="contract"
                        onclick="AsignarTransporte();" style="width: auto;">Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="modalChofer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarChofer()">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </a>
                <h4 class="modal-title">Asignar Operador <span id="ruta_clave"></span></h4>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label for="agentes">Agente | Operador:</label>
                    <select class="form-control chosen-select" id="agentes" name="agentes">
                        <?php
                        /*
                        ?>
                        <option value="">Seleccione Agente | Operador</option>
                        <?php
                        foreach( $vendedores->getAllVendedor() AS $ch ):
                        ?>
                        <option value="<?php echo $ch->cve_usuario; ?>"><?php echo "( ".$ch->cve_usuario." ) - ".$ch->nombre_completo; ?></option>
                        <?php endforeach;
                        */
                        ?>

                    </select>
                </div>


            </div>
            <div class="modal-footer">
                <a href="#" onclick="minimizarChofer()">
                    <button type="button" class="btn btn-white" id="btnCancel">Cerrar</button>
                </a>&nbsp;&nbsp;
                &nbsp;&nbsp;
                <button type="button" class="btn btn-primary ladda-button" data-style="contract"
                        onclick="AsignarChofer();" style="width: auto;">Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ver_detalles" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title simple">Folio: <span id="num_folio"></span></h3>
                <br>

                <div class="jqGrid_wrapper" style="overflow-x: hidden; display: none;">
                    <table id="grid-table_detalles"></table>
                    <div id="grid-pager_detalles" style="width: auto;"></div>
                </div>

                <div hidden="hidden" class="jqGrid_wrapper" style="overflow-x: hidden;">
                    <table id="grid-table_detalles-cobranza"></table>
                    <div id="grid-pager_detalles-cobranza" style="width: auto;"></div>
                </div>

                <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                    <table class="table table-striped table-bordered table-hover dataTables-example"
                           id="grid-table-4" style="width: 100%">
                        <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Referencia</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

                <br><br>
                <label id="titulo_promo" style="display: none;">Promoción</label>
                <div class="row" id="tabla_promo" style="display: none;">
                    <div class="col-md-12">
                        <div class="jqGrid_wrapper" style="overflow-x: hidden;">
                            <table id="grid-table-promo"></table>
                            <div class="class_pager" id="grid-pager-promo"></div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="modalChoferEliminar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarChofer()">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </a>
                <h4 class="modal-title">Eliminar Operador <span id="ruta_claveEliminar"></span></h4>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label for="agentes">Eliminar Agente | Operador:</label>
                    <select class="form-control chosen-select" id="EliminarAgentes" name="EliminarAgentes">
                        <?php
                        /*
                        ?>
                        <option value="">Seleccione Agente | Operador</option>
                        <?php
                        foreach( $vendedores->getAllVendedor() AS $ch ):
                        ?>
                        <option value="<?php echo $ch->cve_usuario; ?>"><?php echo "( ".$ch->cve_usuario." ) - ".$ch->nombre_completo; ?></option>
                        <?php endforeach;
                        */
                        ?>

                    </select>
                </div>


            </div>
            <div class="modal-footer">
                <a href="#" onclick="minimizarChofer()">
                    <button type="button" class="btn btn-white" id="btnCancel">Cerrar</button>
                </a>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger ladda-button" data-style="contract"
                        onclick="EliminarChofer();" style="width: auto;">Eliminar
                </button>
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="modalClientes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarCliente()">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </a>
                <h4 class="modal-title">Eliminar Cliente de Ruta <span id="ruta_clave"></span></h4>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label for="agentes">Cliente:</label>
                    <select class="form-control chosen-select" id="clientes" name="clientes">
                    </select>
                </div>


            </div>
            <div class="modal-footer">
                <a href="#" onclick="minimizarCliente()">
                    <button type="button" class="btn btn-white" id="btnCancel">Cerrar</button>
                </a>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger ladda-button" data-style="contract"
                        onclick="EliminarCliente();" style="width: auto;">Eliminar
                </button>
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
                            <h3>Agregar Ruta</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <input type="hidden" id="hiddenIDProveedor">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">
                                <label>Almacen *</label>
                                <div class="form-group">
                                    <select class="form-control" id="cve_almacenp" required="true">
                                        <option value="">Almacen</option>
                                        <?php foreach ($listaNCompa->getAll() as $p): ?>
                                            <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <label>Clave de la Ruta *</label>
                                <input id="cve_ruta" type="text" placeholder="Clave de la Ruta" class="form-control"
                                       required="true">
                                <?php
                                /*
                                ?>
                                <div class="form-group"><a href="#" onclick="traeModal()"><button type="button" class="btn btn-primary" >Clientes</button></a></div>
                                <?php
                                */
                                ?>
                                <br>
                                <input type="hidden" id="hiddenAction">
                                <input type="hidden" id="hiddenRuta">
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()">
                                        <button type="button" class="btn btn-white" id="btnCancel">Cerrar</button>
                                    </a>&nbsp;&nbsp;
                                    <button type="submit" class="btn btn-primary ladda-button" data-style="contract"
                                            id="btnSave">Guardar
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label>Descripción</label>
                                <input id="descripcion" type="text" placeholder="Descripción" class="form-control"><br>
                                <div class="col-md-2 b-r">
                                    <label id="mostrar1">Activo</label>
                                    <label id="mostrar2" style="display:none;">Activo</label>
                                    <input type="checkbox" id="status" name="statuss" value="1" class="form-control">
                                    <input type="hidden" id="status_send" value="">
                                </div>
                                <div class="col-md-3">
                                    <label>Venta | Preventa</label>
                                    <input type="checkbox" id="venta_preventa" name="venta_preventa" value="1"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Barra de nuevo y busqueda -->
<div class="wrapper wrapper-content  animated fadeInRight" id="rut">
    <h3>Administración de Proformas</h3>
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
                                    <?php foreach ($almacenes->getAll() as $a): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo "(" . $a->clave . ") - " . $a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
<?php 
/*
?>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="rutas_list">Rutas:</label>
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
                                    <option value="todas">Todas las Rutas</option>
                                    <?php
                                    foreach ($listaRuta->getAll($cve_almacen) as $r):
                                        ?>
                                        <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( " . $r->cve_ruta . " ) - " . $r->descripcion; ?></option>
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
                                    foreach ($listaDiaO->getAllDiaO($cve_almacen) as $r):
                                        ?>
                                        <option value="<?php echo $r->DiaO; ?>"><?php echo $r->DiaO; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="operacion_list">Operación:</label>
                                <select class="form-control chosen-select" id="operacion_list" name="operacion_list">
                                    <option value="">Seleccione Operación</option>
                                    <option value="venta">Venta</option>
                                    <!--<option value="preventa">Pre Venta</option>-->
                                    <option value="entrega">Entrega</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="status_list">Status:</label>
                                <select class="form-control chosen-select" id="status_list" name="status_list">
                                    <option value="">Seleccione Status</option>
                                    <option value="1">Abierta</option>
                                    <option value="2">Pagada</option>
                                </select>
                            </div>
                        </div>
<?php 
*/
?>
                        <div class="col-sm-4" style="display: none;">
                            <div class="form-group">
                                <label for="agentes">Agente | Operador:</label>
                                <select class="form-control chosen-select" id="agentes_list" name="agentes_list">
                                    <option value="">Seleccione Agente | Operador</option>
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
                                <label>Fecha Inicial:</label>
                                <div class="input-group date" id="data_1">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="fechaini" type="text" class="form-control"
                                           value=""><?php // echo $fecha_semana; ?>
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

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="almacenes">Cliente: </label>
                                <select class="form-control chosen-select" id="lista_clientes" name="lista_clientes">
                                    <option value="">Seleccione el Cliente</option>
                                    <?php 
                                        $id_almacen = $_SESSION['id_almacen'];
                                        foreach ($listaCliente->getAllProformas(" AND s.Cve_Almac = $id_almacen ") as $a): ?>
                                        <option value="<?php echo $a->Cve_Clte; ?>"><?php echo "( " . $a->Cve_Clte . " ) - " . $a->RazonSocial; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status_servicios">Status: </label>
                                <select class="form-control chosen-select" id="status_servicios" name="status_servicios">
                                    <option value="">Todos</option>
                                    <option value="A">Abierto</option>
                                    <option value="T">Terminado</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3" style="margin-top:14px;">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 10px;"
                                           id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button onclick="ReloadGrid()" type="submit" id="buscarA"
                                                class="btn btn-primary" style="margin: 10px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                        <div class="row" id="loadgif" style="text-align: center;padding: 0; display: none;font-size: 16px;top: 2px;left: 120px;position: absolute;">
                            <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2" style="margin-top:14px;">
                            <button type="button" id="enviartransaccion" class="btn btn-primary" style="margin: 10px;">
                                <?php /* ?><span class="fa fa-file-pdf-o"></span><?php */?> Enviar Transacción
                            </button>
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

<?php 
/*
?>
                     <div class="row">
                      <div class="col-lg-2">
                        <div class="checkbox" id="chb_asignar">
                          <label for="btn-asignarTodo">
                            <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Seleccionar Todo
                          </label>
                        </div>
                      </div>
                    </div> 

<?php 
*/
/*
?>
                    <br>
                    <a href="#" id="generarExcelCobranza" class="btn btn-primary" style="margin: 10px;">
                        <span class="fa fa-file-excel-o"></span> Reporte de Cobranza
                    </a>
                    <a href="#" id="generarExcelCuentasPorPagar" class="btn btn-primary" style="margin: 10px;">
                        <span class="fa fa-file-excel-o"></span> Reporte de Cuentas por Cobrar
                    </a>
                    <br>
                    <div class="row" style="text-align: center;display: none;">
                        <br>
                        <b>Importe: </b><span id="timporte"></span><b> | </b><b>IVA: </b><span id="tiva"></span><b>
                            | </b>
                        <b>Descuento: </b><span id="tdescuento"></span><b> | </b><b>Total: </b><span id="ttotal"></span><b>
                            | </b>
                        <b>Total C: </b><span id="ttotalc"></span><b> | </b><b>Total P: </b><span
                                id="ttotalp"></span><b> | </b>
                        <b>Promo C: </b><span id="tpromoc"></span><b> | </b><b>Promo P: </b><span
                                id="tpromop"></span><b> | </b>
                        <b>Obseq C: </b><span id="tobseqc"></span><b> | </b><b>Obseq P: </b><span id="tobseqp"></span>
                    </div>
                    <div class="row" style="text-align: center;">
                        <br>
                        <b>Credito: </b><span id="tcredito">0.00</span><b> | </b>
                        <b>Cobranza: </b><span id="tcobranza">0.00</span><b> | </b>
                        <b>Adeudo: </b><span id="tadeudo">0.00</span>
                    </div>

                    <br>
<?php 
*/
?>

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

<!--Administración de Registro de Movimientos con Cargo-->













                        <div class="tab-content">
                            <div class="tab-pane active" id="panel-928563">
                                <div hidden="hidden" class="jqGrid_wrapper">
                                    <table id="grid-table"></table>
                                    <div id="grid-pager"></div>
                                </div>
                                <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-striped table-bordered table-hover dataTables-example"
                                           id="grid-table-3">
                                        <thead>
                                        <tr>
                                            <th style="text-align: center">Acciones</th>
                                            <th style="text-align: center">Fecha</th>
                                            <th style="text-align: center">Folio</th>
                                            <th style="text-align: center">Referencia </th>
                                            <th style="text-align: center">Clave Cliente</th>
                                            <th style="text-align: center">Nombre de Cliente</th>
                                            <th style="text-align: center">Tipo Operación</th>
                                            <th style="text-align: center">Observaciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
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
                        <?php /* ?><input type="button" class="btn btn-primary" name="aplicar-pago-grupo" id="aplicar-pago-grupo"
                               value="Aplicar Pagos de Cliente">
                               <?php */ ?>
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
                            <input type="file" name="file" id="file" class="form-control" required>
                        </div>
                    </form>
                    <div class="col-md-12">
                        <div class="progress" style="display:none">
                            <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                <div class="percent">0%</div>
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
    $('#avanzada').on('click', function () {
        $("#busqueda")[0].style.display = 'none';
    });
    $('#simple').on('click', function () {
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
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function (data) {
                if (data.success == true) {
                    document.getElementById('almacenes').value = data.codigo.id;
                    $('.chosen-select').chosen();
                    //almacen();
                }
            },
            error: function (res) {
                window.console.log(res);
            }
        });
    }

    almacenPrede();

    $(function ($) {

        //**********************************************************************************************
        var grid_selector = "#grid-table-promo";
        var pager_selector = "#grid-pager-promo";

        //resize to fit page size
        /*
        $(window).on('resize.jqGrid', function() {
            $("#grid-table-promo").jqGrid('setGridWidth', $("#coModal").width() - 50);
        })*/
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function () {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            //url:'/api/inventariosfisicos/lista/index_detalle.php',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            mtype: 'POST',
            colNames: ['Articulo', 'Descripcion', 'Ruta', 'Cliente', 'Cantidad', 'Unidad Medida'],
            colModel: [
                {
                    name: 'cve_articulo',
                    index: 'cve_articulo',
                    width: 180,
                    editable: false,
                    sortable: false,
                    hidden: false
                },
                {
                    name: 'descripcion',
                    index: 'descripcion',
                    width: 280,
                    editable: false,
                    sortable: false,
                    hidden: false
                },
                {name: 'cve_ruta', index: 'cve_ruta', width: 230, editable: false, sortable: false, hidden: true},
                {name: 'cliente', index: 'cliente', width: 280, editable: false, sortable: false, hidden: true},
                {
                    name: 'cantidad',
                    index: 'cantidad',
                    width: 100,
                    editable: false,
                    align: 'right',
                    sortable: false,
                    hidden: false
                },
                {
                    name: 'unidad_medida',
                    index: 'unidad_medida',
                    width: 100,
                    editable: false,
                    sortable: false,
                    hidden: false
                }
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            sortname: 'cve_articulo',
            viewrecords: true,
            sortorder: "desc"
        });

        //resize to fit page size

        $(window).on('resize.jqGrid', function () {
            $("#grid-table-promo").jqGrid('setGridWidth', $("#coModal").width() - 50);
        })

        // Setup buttons
        $("#grid-table-promo").jqGrid('navGrid', '#grid-pager-promo', {
            edit: false,
            add: false,
            del: false,
            search: false
        }, {
            height: 200,
            reloadAfterSubmit: true
        });
        //**********************************************************************************************
        var grid_selector = "#grid-table_detalles-cobranza";
        var pager_selector = "#grid-pager_detalles-cobranza";

        //resize to fit page size
        /*
        $(window).on('resize.jqGrid', function() {
            $("#grid-table_detalles-cobranza").jqGrid('setGridWidth', $("#coModal").width() - 50);
        })*/
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function () {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            //url:'/api/inventariosfisicos/lista/index_detalle.php',
            datatype: "json",
            shrinkToFit: false,
            //cache: false,
            height: 'auto',
            mtype: 'POST',
            colNames: ['Folio', 'Referencia'],
            colModel: [
                {name: 'Fol_Folio', index: 'Fol_Folio', width: 100, editable: false, sortable: false, align: 'left'},
                {name: 'Referencia', index: 'Referencia', width: 100, editable: false, sortable: false, align: 'left'},
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            sortname: 'Cve_Servicio',
            loadComplete: function (data) {
                console.log("SUCCESS Load: ", data);

                let dataGrid = $(this).jqGrid('getRowData');
                console.log("dataGrid = ", dataGrid);
                let dataReporte2 = [];
                    for (let i = 0; i < data.rows.length; i++) {
                        dataReporte2.push({
                            Fol_Folio: data.rows[i].cell['Fol_Folio'],
                            Referencia: data.rows[i].cell['Referencia']
                        });
                    }
                if ($.fn.DataTable.isDataTable('#grid-table-4')) {
                    $('#grid-table-4').DataTable().destroy();
                }
                $('#grid-table-4').DataTable(
                    {
                        responsive: true,
                        autoWidth: true,
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        searching: false,
                        data: dataReporte2, "lengthMenu": [[10, 20, 30], [10, 20, 30]],
                        columns: [
                            {data: 'Fol_Folio', className: 'text-left'},
                            {data: 'Referencia', className: 'text-left'}
                        ],
                    }
                ).columns.adjust();
                // $('#grid-table-4').DataTable(
                //
                // );
                console.log("dataReporte2 = ", dataReporte2);
                // if ($.fn.DataTable.isDataTable('#grid-table-4')) {
                //     $('#grid-table-4').DataTable().destroy();
                // }


            },
            loadError: function (data) {
                console.log("ERROR: ", data);
            },
            viewrecords: true,
            sortorder: "desc"
        });
        $('#grid-table-4').on('length.dt', function (e, settings, len) {
            $('#pg_grid-pager_detalles-cobranza .ui-pg-selbox').val(len).trigger('change');
        });
        //get event entries to jqgrid table grid-table input_grid-pager
        $('#pg_grid-pager_detalles-cobranza .ui-pg-selbox').on('change', function () {
            //selecciona el valor del select anterior
            var selected = $(this).val();
            console.log("selected: ", selected);
        });
        //resize to fit page size

        $(window).on('resize.jqGrid', function () {
            $("#grid-table_detalles-cobranza").jqGrid('setGridWidth', $("#coModal").width() - 50);
        })

        // Setup buttons
        $("#grid-table_detalles-cobranza").jqGrid('navGrid', '#grid-pager_detalles-cobranza', {
            edit: false,
            add: false,
            del: false,
            search: false
        }, {
            height: 200,
            reloadAfterSubmit: true
        });
        //**********************************************************************************************


        var grid_selector_detalles = "#grid-table_detalles";
        var pager_selector_detalles = "#grid-pager_detalles";


        /*//resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_detalles).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        })*/
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector_detalles).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function () {
                    $(grid_selector_detalles).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector_detalles).jqGrid({
            datatype: "local",
            mtype: 'GET',
            shrinkToFit: false,
            autowidth: true,
            height: 'auto',
            mtype: 'GET',
            colNames: ['Clave', 'Artículo', 'Cajas', 'Piezas', 'Importe', 'IVA', 'Descuento', 'Total', 'PromC', 'PromP'],
            colModel: [
                {name: 'clave', index: 'clave', width: 100, editable: false, sortable: false},
                {name: 'articulo', index: 'articulo', width: 300, editable: false, sortable: false},
                {name: 'cajas', index: 'cajas', width: 100, editable: false, sortable: false, align: 'right'},
                {name: 'piezas', index: 'piezas', width: 100, editable: false, sortable: false, align: 'right'},
                {name: 'importe', index: 'importe', width: 100, editable: false, sortable: false, align: 'right'},
                {name: 'iva', index: 'iva', width: 100, editable: false, sortable: false, align: 'right'},
                {name: 'descuento', index: 'descuento', width: 100, editable: false, sortable: false, align: 'right'},
                {name: 'total', index: 'total', width: 100, editable: false, sortable: false, align: 'right'},
                {
                    name: 'promc',
                    index: 'promc',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },
                {
                    name: 'promp',
                    index: 'promp',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector_detalles,
            loadError: function (data) {
                console.log("ERROR Det: ", data);
            },
            loadComplete: function (data) {
                console.log("SUCCESS Det: ", data);
            },
            viewrecords: true
        });

        // Setup buttons
        $(grid_selector_detalles).jqGrid('navGrid', '#grid-pager_detalles',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector_detalles).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })

        $(document).one('ajaxloadstart.page', function (e) {
            $(grid_selector_detalles).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });

        $(window).triggerHandler('resize.jqGrid');

    });

    //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
    $(function ($) {
        //almacenPrede();
        console.log("almacen:", $("#almacenes").val());
        console.log("criterio:",  $("#txtCriterio").val());
        console.log("ruta:",  $("#rutas_list").val());
        console.log("diao:",  $("#diao_list").val());
        console.log("operacion:",  $("#operacion_list").val());
        console.log("fechaini:",  $("#fechaini").val());
        console.log("fechafin:",  $("#fechafin").val());
        console.log("status:",  $("#status_list").val());
        console.log("credito:",  true);
        console.log("almacen:",  $("#almacenes").val());
        console.log("agente:",  $("#agentes_list").val());


        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function () {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            //url: '/api/vasproformas/lista/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                action: 'getCobranzaCxC',
                criterio: $("#txtCriterio").val(),
                //ruta: $("#rutas_list").val(),
                //diao: $("#diao_list").val(),
                //operacion: $("#operacion_list").val(),
                fechaini: $("#fechaini").val(),
                fechafin: $("#fechafin").val(),
                status_servicios: $("#status_servicios").val(),
                cliente: $("#lista_clientes").val()
                //credito: true,
                //almacen: $("#almacenes").val(),
                //agente: $("#agentes_list").val()

            },
            mtype: 'POST',
            colNames: ["Acciones", 'Fecha', 'Folio', 'Referencia ', 'Clave Cliente', 'Nombre de Cliente', 'Tipo Operación', 'Observaciones', 'Status'], 
            colModel: [
                {name: 'myac',index: 'myac',width: 100,fixed: true,sortable: false,resize: false,formatter: imageFormat},//0
                {name: 'fecha', index: 'fecha', width: 100, editable: false, sortable: false, align: 'right'},//1
                {name: 'folio', index: 'folio', width: 120, editable: false, sortable: false},//2
                {name: 'referencia ', index: 'referencia', width: 120, editable: false, sortable: false, align: 'center'},//3
                {name: 'clave_cliente', index: 'clave_cliente', width: 100, editable: false, sortable: false},//4
                {name: 'nombre_de_cliente', index: 'nombre_de_cliente', width: 150, editable: false, sortable: false, align: 'right'},//5
                {name: 'tipo_operacion', index: 'tipo_operacion', width: 150, editable: false, sortable: false},//6
                {name: 'observaciones', index: 'observaciones', width: 150, editable: false, sortable: false, hidden: true},//7
                {name: 'Status', index: 'Status', width: 100, editable: false, sortable: false, align: 'right', hidden: true},//8
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'id_pedido',
            viewrecords: true,
            //loadError: function(data){console.log("ERROR: ", data);},
            sortorder: "desc",
            loadComplete: function (data) {
                $("#loadgif").hide();
                console.log("Cobranza RES: ", data);
                console.log("Cobranza Rows = ", data.records);
                //if(data.records == 0)
                //    swal("Búsqueda sin datos", "No hay datos disponibles para esta búsqueda", "success");
                console.log("Cobranza SQL: ", data.sql);
                console.log($(this).jqGrid('getGridParam', 'colModel'));
                //imprime la data de las filas de la tabla grid-table

                //$('#tcredito').text('');
                //$('#tcobranza').text('');
                //$('#tadeudo').text('');

                let dataGrid = $(this).jqGrid('getRowData');
                let dataReporte = [];
                //console.log("Cobranza DATA: ", dataGrid);
                var total = 0;
                var abono = 0;
                var saldo = 0;
                if (data.total > 0) {
                    console.log("data.rows**",data.rows);
                    console.log("dataGrid[j].folio**",dataGrid);
                    for (let i = 0; i < data.rows.length; i++) {
                        let acciones = "";
                        for (let j = 0; j < dataGrid.length; j++) {
                            //console.log("dataGrid[j]: ", dataGrid[j]);
                            //console.log(dataGrid[j].cvecliente, data.rows[i].cell[9], dataGrid[j].nombre_comercial, data.rows[i].cell[10]);
                            //if (dataGrid[j].cvecliente === data.rows[i].cell[9]) 
                            //if (dataGrid[j].ID_Venta == data.rows[i].cell[1]) 



                            if (dataGrid[j].folio === data.rows[i].cell[2]) 
                            {
                                acciones = dataGrid[j].myac;
                                break;
                            }


                            //else if(dataGrid[j].folio == '23116079' || data.rows[i].cell[6] == '23116079')
                                //console.log("vacio = ",data.rows[i].cell[6], '=', dataGrid[j].folio);
                        }
                        //
                        ////////console.log('totalP: ', data.rows[i].cell[18]);
                        ////////data.rows[i].cell[18] = data.rows[i].cell[18].replace(/,/g, '');
                        ////////data.rows[i].cell[20] = data.rows[i].cell[20].replace(/,/g, '');
                        ////////console.log('totalParceado: ', parseFloat(data.rows[i].cell[18]));
                        //total = total + parseFloat(data.rows[i].cell[12]/*.replace(',', '')*/);
                        //abono = abono + parseFloat(data.rows[i].cell[13]/*.replace(',', '')*/);
                        //saldo = saldo + parseFloat(data.rows[i].cell[14]/*.replace(',', '')*/);
    //ver(id, folio, cliente, responsable, operacion, sucursal, dia_operativo, ruta, vendedor, tienepromo, saldo_restante, limite_credito, total)
                        dataReporte.push({
                            acciones: acciones,
                            fecha: data.rows[i].cell[1],
                            folio: data.rows[i].cell[2],
                            referencia: data.rows[i].cell[3],
                            clave_cliente: data.rows[i].cell[4],
                            nombre_de_cliente: data.rows[i].cell[5],
                            tipo_operacion: data.rows[i].cell[6],
                            observaciones: data.rows[i].cell[7],
                            Status: data.rows[i].cell[8]
                        });
                        //console.log("dataReporte: ", dataReporte);
                    }
                }
               ////// console.log('total: ', total);
               ////// console.log('abono: ', abono);
               ////// console.log('saldo: ', saldo);
                // $('#tcredito').text('');
                // $('#tcobranza').text('');
                // $('#tadeudo').text('');
                // cambiar el texto de los 3 span por los valores de las variables
                //$('#tcredito').text(total.toFixed(2));
                //$('#tcobranza').text(abono.toFixed(2));
                //$('#tadeudo').text(saldo.toFixed(2));

                //console.log("dataReporte: ", dataReporte);
                if ($.fn.DataTable.isDataTable('#grid-table-3')) {
                    $('#grid-table-3').DataTable().destroy();
                }
                $('#grid-table-3').DataTable(
                    {
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        searching: false,
                        responsive: true,
                        //aumenta el tamaño de la primera columna al 100%
                        "autoWidth": false,
                        "columnDefs": [
                            {"width": "10%", "targets": 0}
                        ],
                        //paginate
                        "paging": true,
                        data: dataReporte,
                        "lengthMenu": [[30, 40, 50], [30, 40, 50]],
                        columns: [
                            {
                                data: 'acciones',
                                orderable: false,
                                searchable: false,
                                className: 'text-center'
                            },
                            {data: 'fecha', className: 'text-center'},
                            {data: 'folio', className: 'text-left'},
                            {data: 'referencia', className: 'text-left'},
                            {data: 'clave_cliente', className: 'text-left'},
                            {data: 'nombre_de_cliente', className: 'text-left'},
                            {data: 'tipo_operacion', className: 'text-left'},
                            {data: 'observaciones', className: 'text-left'}
                        ],
                    }
                );
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
                // */
                // $("#tcredito").text(data.total_credito);
                // $("#tcobranza").text(data.total_cobranza);
                // $("#tadeudo").text(data.total_adeudo);
            },
            loadError: function (data) {
                console.log("txtCriterio = ", $("#txtCriterio").val(), " - rutas_list = ", $("#rutas_list").val(), " - fechaini = ", $("#fechaini").val(), " - fechafin = ", $("#fechafin").val());
                //if($("#txtCriterio").val() != '')
                //    swal("Error", "Error en Consulta", "error");
                if ($.fn.DataTable.isDataTable('#grid-table-3')) {
                    $('#grid-table-3').DataTable().destroy();
                }
                $('#grid-table-3').DataTable(
                    {
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        searching: false,
                        "autoWidth": false,
                        responsive: true,
                        //ajusta el tamanio de todas las columnas apartir del contenid oe la celda

                        "paging": true,
                        data: [],
                        "lengthMenu": [[30, 40, 50], [30, 40, 50]],
                        columns: [
                            {
                                data: 'acciones',
                                orderable: false,
                                searchable: false,
                                className: 'text-center'
                            },
                            {data: 'Fecha', className: 'text-center'},
                            {data: 'Folio', className: 'text-center'},
                            {data: 'Referencia ', className: 'text-center'},
                            {data: 'Clave Cliente', className: 'text-center'},
                            {data: 'Nombre de Cliente', className: 'text-center'},
                            {data: 'Tipo Operación', className: 'text-center'},
                            {data: 'Observaciones', className: 'text-center'},
                        ],
                    }
                );
                console.log("Cobranza ERROR a: ", data);
            }
        });
        $('#grid-table-3').on('length.dt', function (e, settings, len) {
            $('#pg_grid-pager .ui-pg-selbox').val(len).trigger('change');
        });
        //get event entries to jqgrid table grid-table input_grid-pager
        $('#pg_grid-pager .ui-pg-selbox').on('change', function () {
            var selected = $(this).val();
            console.log("selected: ", selected);
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

        function imageFormat(cellvalue, options, rowObject) {

            var clave_cia = <?php echo $_SESSION['cve_cia']; ?>;
            var folio = rowObject[2];
            var cliente = rowObject[4];
            var status = rowObject[8];

            //console.log("id_venta = ", id_venta, " - status = ", status);

            var html = '<div class="row" style="display:inline-block;">';

            html += '<a href="#" class="btn btn-info" onclick="ver(\'' + folio + '\')"><i class="fa fa-search" title="Ver Detalle"></i></a> &nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a class="btn btn-danger" href="/api/koolreport/export/reportes/pedidos/consolidado-servicios?cve_cia='+ clave_cia+ '&folio='+ folio +'&cliente='+ cliente +'" target="_blank"><i class="fa fa-file-pdf-o" title="Proforma"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '</div>';
            if(status == 'A')
            html += '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="check_lp permiso_consultar" title="Seleccionar Proforma" value="'+folio+'">&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;

        }

        function aceSwitch(cellvalue, options, cell) {
            setTimeout(function () {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function () {
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
                success: function (data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function () {
                }
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
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
        }

        $(document).one('ajaxloadstart.page', function (e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

    $("#diao_list, #operacion_list, #status_list").change(function () {

        ReloadGrid();

    });

/*
    $("#btn-asignarTodo").on("click", function(){
      $("input[type=checkbox].check_lp").each(function(i, e){
        if($("#btn-asignarTodo").prop("checked") == false)
        {
          $("input[type=checkbox].check_lp").prop("checked", true);
          //
          if($("input[type=checkbox].check_lp").prop("checked") == true){$("input[type=checkbox].check_lp").prop("checked", false);}
          else{$("input[type=checkbox].check_lp").prop("checked", true);}
        }
        else
        {
          $("input[type=checkbox].check_lp").prop("checked", false);
          //
          if($("input[type=checkbox].check_lp").prop("checked") == true){$("input[type=checkbox].check_lp").prop("checked", false);}
          else{$("input[type=checkbox].check_lp").prop("checked", true);}
        }
      });
    });
*/

    $("#enviartransaccion").click(function()
    {
        var folios = [], k = 0;
        $(".check_lp").each(function(i,j){
            if($(this).is(":checked"))
            {
                //console.log("i = ", i, " value = ", j.value);
                folios[k] = j.value;
                k++;
            }
        });
        console.log("Folios Enviar = ", folios);
/*
            var seleccionarTodo = 0;
            if($("#btn-asignarTodo").is(":checked"))
                seleccionarTodo = 1;
*/
        if(folios.length == 0)
        {
            swal("Debe Seleccionar al menos una Proforma", "", "error");
            return;
        }
        return;
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_usuario: $("#cve_usuario").val(),
                cve_cliente: $("#lista_clientes").val(),
                id_almacen: $("#almacenes").val(),
                nro_pedido: $("#nro_pedido").val(),
                tipo_operacion: $("#tipo_operacion").val(),
                observaciones: $("#observaciones").val(),
                folios: folios,
                //seleccionarTodo: seleccionarTodo,
                action: "generar_proforma"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/vasproformas/lista/index.php',
            success: function (data) {
                console.log("SUCCESS: generar_proforma = ", data);
                $("#ModalProforma").modal("hide");
                $("#tipo_operacion, #observaciones, #nro_pedido").val("");
                swal("Proforma "+data.folio_gen+" Generada", "", "success");
                ReloadGrid();
            },
            error: function (data) {
                console.log("ERROR: generar_proforma = ", data);
            }
        });


    });

    function ReloadGrid2() {
        $('#grid-table_detalles-cobranza').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    id_venta: '',
                    folio: $("#num_folio").text(),
                    ruta: $("#rutas_list").val(),
                    cobranza: true,
                    action: 'getDetallesFolio'
                }, datatype: 'json', page: 1, mtype: 'GET', url: '/api/vasproformas/lista/index.php'
            })
            .trigger('reloadGrid', [{current: true}]);
    }

    function ReloadGrid() {
        console.log("almacen INIT: ", $("#almacenes").val());
        if($("#rutas_list").val() == '') return;
        $("#loadgif").show();
        setTimeout(function () {

        console.log("almacen:", $("#almacenes").val());
        console.log("criterio:",  $("#txtCriterio").val());
        console.log("ruta:",  $("#rutas_list").val());
        console.log("diao:",  $("#diao_list").val());
        console.log("operacion:",  $("#operacion_list").val());
        console.log("fechaini:",  $("#fechaini").val());
        console.log("fechafin:",  $("#fechafin").val());
        console.log("status:",  $("#status_list").val());
        console.log("credito:",  true);
        console.log("almacen:",  $("#almacenes").val());
        console.log("agente:",  $("#agentes_list").val());

            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        action: 'getCobranzaCxC',
                        criterio: $("#txtCriterio").val(),
/*
                        ruta: $("#rutas_list").val(),
                        diao: $("#diao_list").val(),
                        operacion: $("#operacion_list").val(),
                        status: $("#status_list").val(),
                        credito: true,
                        agente: $("#agentes_list").val(),
*/
                        status_servicios: $("#status_servicios").val(),
                        fechaini: $("#fechaini").val(),
                        fechafin: $("#fechafin").val(),
                        cliente: $("#lista_clientes").val(),
                        almacen: $("#almacenes").val()
                    },
                    url: '/api/vasproformas/lista/index.php',
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }, 2000);
    }

    function almacen() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                    ruta: $("#rutas_list").val(),
                    diao: $("#diao_list").val(),
                    operacion: $("#operacion_list").val(),
                    fechaini: $("#fechaini").val(),
                    fechafin: $("#fechafin").val(),
                    status: $("#status_list").val(),
                    credito: true,
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

    function ReloadGrid1() {
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

    function downloadxml(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;


    function ver(folio) {
        //if (id)
        //    $("#ver_detalles #num_entrada").text(id);
        //else
        //    $("#ver_detalles #num_entrada").text(folio);
        $("#ver_detalles #num_folio").text(folio);
/*
        $("#ver_detalles #saldo_restante").val(saldo_restante);
        console.log("saldo_restante = ", saldo_restante);
        console.log("folio = ", folio, " | cliente = ", cliente, " | responsable = ", responsable, " | operacion = ", operacion, " | sucursal = ", sucursal, " | dia_operativo = ", dia_operativo, " | ruta = ", ruta, " | vendedor = ", vendedor, " | tienepromo = ", tienepromo, " | saldo_restante = ", saldo_restante, " | limite_credito = ", limite_credito, " | total , = ",total);

        
        $("#ver_detalles #num_cliente").text(cliente);
        $("#ver_detalles #cod_responsable").text(responsable);
        // $("#ver_detalles #id_dest_cliente").val(id_destinatario);

        $("#ver_detalles #val_operacion").text(operacion);
        $("#ver_detalles #val_sucursal").text(sucursal);
        $("#ver_detalles #dia_operativo").text(dia_operativo);
        $("#ver_detalles #val_ruta").text(ruta);
        $("#ver_detalles #val_vendedor").text(vendedor);

        
*/
        $("#ver_detalles").modal("show");
        loadDetalles(folio);
    }

    function loadDetalles(folio) {
        //$("#btn-aplicar-pago").show();
        //$("#btn-aplicar-pago-grupo").hide();

        //if($("#rutas_list").val() == '' || $("#rutas_list").val() == 'todas')
        //{
        //    $("#btn-aplicar-pago, #btn-aplicar-pago-grupo").hide();
        //}


        //$(".simple").show();
        //$(".grupo").hide();

        console.log("folio:", folio);

        click_from_grupos = false;
        $('#grid-table_detalles-cobranza').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    //id_venta: id,
                    //ruta: $("#rutas_list").val(),
                    folio: folio,
                    //limite_credito: limite_credito,
                    //total: total,
                    //cobranza: true,
                    action: 'getDetallesFolio'
                }, datatype: 'json', page: 1, mtype: 'GET', url: '/api/vasproformas/lista/index.php'
            })
            .trigger('reloadGrid', [{current: true}]);
        /*
                if(tienepromo)
                {
                    $("#titulo_promo").show();
                    $("#tabla_promo").show();
                    $('#grid-table-promo').jqGrid('clearGridData')
                        .jqGrid('setGridParam', {postData: {
                            folio: folio,
                            almacen: $("#almacenes").val(),
                            action: 'PromocionVenta'
                        }, datatype: 'json', page : 1, mtype: 'GET', url:'/api/vasproformas/lista/index.php'})
                        .trigger('reloadGrid',[{current:true}]);
                }
                else
                {
                    $("#titulo_promo").hide();
                    $("#tabla_promo").hide();
                    $("#grid-table-promo").jqGrid("clearGridData");
                }
        */
    }

    //, limite_credito, total

    $("#abono_cxc").keyup(function (e) {
        var cantidad = $(this).val();
        var cantidad_vec = cantidad.split("");

        if ((!$.isNumeric(e.key) && e.key != '.') || cantidad_vec[0] == '.') {
            //console.log("OKP", tecla.indexOf('.'));
            cantidad = cantidad.replace(e.key, '');
            $(this).val(cantidad);
            return;
        }

        if (e.key == '.') {
            var count_punto = 0, pos_point = 0;

            for (var i = 0; i < cantidad_vec.length; i++) {
                if (cantidad_vec[i] == '.') {
                    count_punto++;
                    if (count_punto == 2) {
                        pos_point = i;
                        break;
                    }
                }
            }

            if (count_punto > 1) {
                cantidad_vec[pos_point] = '';
                $(this).val('');
                var cantidad_peso = $(this).val();
                for (var i = 0; i < cantidad_vec.length; i++) {
                    if (i != pos_point) {
                        cantidad_peso += cantidad_vec[i];
                        $(this).val(cantidad_peso)
                    }
                }
            }

        }

    });

    $("#btn-aplicar-pago").click(function () {
        if ($("#abono_cxc").val() == '') {
            swal("Error", "Debe Ingresar un Abono", "error");
            return;
        }

        var saldo_restante = $("#saldo_restante").val();
        //console.log("saldo_restante: ", saldo_restante);

        $("#saldo_restante").val(saldo_restante.replace(",", ""));
        console.log("abono_cxc: ", parseFloat($("#abono_cxc").val()));
        console.log("saldo_restante: ", parseFloat($("#saldo_restante").val()));

        if (parseFloat($("#abono_cxc").val()) > parseFloat($("#saldo_restante").val())) {
            swal("Error", "El Abono no puede ser mayor al saldo restante", "error");
            return;
        }
        //return;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cobranza: true,
                cobranzadet: true,
                abono: $("#abono_cxc").val(),
                //cliente: $("#num_cliente").text(),
                //cliente: $("#ver_detalles #id_dest_cliente").val(),
                cliente: $("#ver_detalles #num_cliente").text(),
                ruta: $("#val_ruta").text(),
                diao: $("#dia_operativo").text(),
                folio: $("#num_folio").text(),
                saldo_restante: $("#saldo_restante").val(),
                forma_pago: $("#forma_pago").val(),
                almacen: $("#almacenes").val(),
                action: "realizar_abono"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/vasproformas/lista/index.php',
            success: function (data) {
                console.log("realizar_abono = ", data);
                $("#saldo_restante").val(data);
                $("#abono_cxc").val("");
                ReloadGrid();
                ReloadGrid2();
            },
            error: function (data) {
                console.log("ERROR: realizar_abono = ", data);
            }
        });

    });


    $("#btn-aplicar-pago-grupo").click(function () {
        console.log("folios grupo = ", $("#folios_grupo").val());
        //return;
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cobranzadet: true,
                //abono: $("#abono_cxc").val(),
                //cliente: $("#num_cliente").text(),
                //ruta: $("#val_ruta").text(),
                //diao: $("#dia_operativo").text(),
                folios: $("#folios_grupo").val(),
                //saldo_restante: $("#saldo_restante").val(),
                forma_pago: $("#forma_pago").val(),
                //almacen: $("#almacenes").val(),
                action: "realizar_abono_grupo"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/vasproformas/lista/index.php',
            success: function (data) {
                console.log("realizar_abono = ", data);
                $("#saldo_restante").val(data);
                $("#abono_cxc").val("");
                swal("Pagos Realizados Correctamente","", "success");
                $("#ver_detalles").modal("hide");
                ReloadGrid();
                ReloadGrid2();
            },
            error: function (data) {
                console.log("ERROR: realizar_abono = ", data);
            }
        });

    });

    $("#generarExcelCuentasPorPagar").click(function () {

        if ($("#rutas_list").val() == "")// || $("#diao_list").val() == ""
        {
            swal("Error", "Debe Ingresar una Ruta ", "error");//y Dia Operativo
            return;
        }
        var criterio = $("#txtCriterio").val(),
            ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            operacion = $("#operacion_list").val(),
            fechaini = $("#fechaini").val(),
            fechafin = $("#fechafin").val(),
            almacen = $("#almacenes").val();

        $(this).attr("href", "/api/koolreport/excel/ventas_cxc/export.php?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&operacion=" + operacion + "&fechaini=" + fechaini + "&fechafin=" + fechafin + "&criterio=" + criterio);

    });
    $("#generarExcelCobranza").click(function () {

        if ($("#rutas_list").val() == "")// || $("#diao_list").val() == ""
        {
            swal("Error", "Debe Ingresar una Ruta ", "error");//y Dia Operativo
            return;
        }
        var criterio = $("#txtCriterio").val(),
            ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            operacion = $("#operacion_list").val(),
            fechaini = $("#fechaini").val(),
            fechafin = $("#fechafin").val(),
            status_list = $("#status_list").val(),
            almacen = $("#almacenes").val();

        $(this).attr("href", "/api/koolreport/excel/ventas_cobranza/export.php?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&operacion=" + operacion + "&fechaini=" + fechaini + "&fechafin=" + fechafin + "&criterio=" + criterio+ "&status_list=" + status_list);

    });


</script>

<script>
    $(document).ready(function () {
        $("#inactivos").on("click", function () {
            $modal0 = $("#coModal");
            $modal0.modal('show');
            ReloadGrid1();
        });
        // $("#cve_almacenp").select2();
        $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);
    });
</script>


<!-- Grid de recuperar -->
<script>
    $(function ($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function () {
            var $grid = $("#grid-table2"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function (ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function () {
                    $(grid_selector).jqGrid('setGridWidth', parent_column.width());
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/ruta/lista/index_i.php',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio1").val()
            },
            mtype: 'POST',
            colNames: ['ID', 'Clave', 'Nombre de la Ruta', 'Status', 'Recuperar'],
            colModel: [
                {name: 'ID_Ruta', index: 'ID_Ruta', width: 0, editable: false, sortable: false, hidden: true},
                {name: 'cve_ruta', index: 'cve_ruta', width: 210, editable: false, sortable: false},
                {name: 'descripcion', index: 'descripcion', width: 510, editable: false, sortable: false},
                {name: 'status', index: 'status', width: 180, editable: false, sortable: false},
                {
                    name: 'myac',
                    index: '',
                    width: 80,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    formatter: imageFormat
                },
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            sortname: 'ID_Ruta',
            viewrecords: true,
            sortorder: "desc"
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2', {
            edit: false,
            add: false,
            del: false,
            search: false
        }, {
            height: 200,
            reloadAfterSubmit: true
        });

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        function imageFormat(cellvalue, options, rowObject) {
            var serie = rowObject[0];
            var correl = rowObject[4];
            var url = "x/?serie=" + serie + "&correl=" + correl;
            var url2 = "v/?serie=" + serie + "&correl=" + correl;

            var ID_Ruta = rowObject[0];
            $("#hiddenRuta").val(serie);
            var html = '';
            html += '<a href="#" onclick="recovery(\'' + ID_Ruta + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        function aceSwitch(cellvalue, options, cell) {
            setTimeout(function () {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function () {
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
                success: function (data) {
                    grid.trigger("reloadGrid", [{
                        current: true
                    }]);
                },
                error: function () {
                }
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
            $('.navtable .ui-pg-button').tooltip({
                container: 'body'
            });
            $(table).find('.ui-pg-div').tooltip({
                container: 'body'
            });
        }

        $(document).one('ajaxloadstart.page', function (e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
</script>

<script>
    function recovery(_codigo) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Ruta: _codigo,
                action: "recovery"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/ruta/update/index.php',
            success: function (data) {
                if (data.success == true) {
                    ReloadGrid();
                    ReloadGrid1();
                }
            }
        });
    }
</script>

<script>
    $("#cve_ruta").keyup(function (e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");

        if (claveCodeRegexp.test(claveCode)) {
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
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function (data) {
                    if (data.success == false) {
                        $("#CodeMessage").html("");
                        $("#btnSave").prop('disabled', false);
                    } else {
                        $("#CodeMessage").html(" Clave de ruta ya existe");
                        $("#btnSave").prop('disabled', true);
                    }
                }
            });
        } else {
            $("#CodeMessage").html("Por favor, ingresar una Clave de ruta válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $("#cve_ruta").keyup(function (e) {
        var claveCode = $(this).val();
        var claveCodeRegexp = new RegExp("^[a-zA-Z0-9]{1,20}$");
        if (claveCodeRegexp.test(claveCode)) {
            $("#CodeMessage").html("");
            $("#btnSave").prop('disabled', false);

        } else {
            $("#CodeMessage").html("Por favor, ingresar una Clave de ruta válida");
            $("#btnSave").prop('disabled', true);
        }
    });

    $(document).ready(function () {
        setTimeout(function () {
            ReloadGrid();
        }, 1000);
    });


</script>

<script type="text/javascript">
    // IE9 fix
    if (!window.console) {
        var console = {
            log: function () {
            },
            warn: function () {
            },
            error: function () {
            },
            time: function () {
            },
            timeEnd: function () {
            }
        }
    }

    jQuery(function ($) {
        $('.footable').footable();
    });


    $("#lista_clientes, #status_servicios").change(function () {
        //console.log("Clientes Change");
        $("#buscarA").click();
    });

    $("#txtCriterio").keyup(function (event) {
        if (event.keyCode == 13) {
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