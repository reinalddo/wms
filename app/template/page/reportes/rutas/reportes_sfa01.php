<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
$listaCliente = new \Clientes\Clientes();
$listaArticulos = new \Articulos\Articulos();
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

$confSql = \db()->prepare("SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_semana FROM DUAL");
$confSql->execute();
$fecha_semana = $confSql->fetch()['fecha_semana'];

?>

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
                            <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1"
                                   placeholder="Buscar por Ruta...">
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

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/datatables.min.css" rel="stylesheet"/>
<!-- FooTable -->
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>

<script src="/js/bootstrap.min.js"></script>
<script src="/js/datatables.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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


<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right {
        position: absolute;
        left: auto;
        right: 0;
    }

    #loader {
        border: 16px solid #f3f3f3;
        border-top: 16px solid #3498db;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        animation: spin 2s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        position: relative;
    }

    .card::before {
        content: "";
        position: absolute;
        top: 0;
        left: -6px;
        height: 100%;
        width: 6px;
        background-color: rgba(26, 179, 148, 0.2);
        border-radius: 4px 0 0 4px;
    }

    .card .icon-container {
        background-color: rgba(26, 179, 148, 0.2); /* Color del borde */
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px; /* Ajusta el tamaño según tus necesidades */
        height: 100%; /* Ajusta el tamaño según tus necesidades */
        border-radius: 4px 0 0 4px;
    }

    .card .icon {
        font-size: 24px;
        color: #fff; /* Color blanco */
    }

    .card .title {
        font-size: 12px;
        font-weight: bold;
        color: #000; /* Color negro */
    }

    .card .amount {
        font-size: 24px;
        font-weight: bold;
        color: #000; /* Color negro */
    }
</style>

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

<div class="modal fade" id="ver_detalles" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title">Reporte de <span id="tipo_venta"></span> #<span id="num_entrada"></span></h3>
                <h3 class="modal-title">Folio: <span id="num_folio"></span></h3>
                <h3 class="modal-title">Cliente: (<span id="num_cliente"></span>) - <span id="cod_responsable"></span>
                </h3>
                <br>
                <h3><b>Operación:</b> <span id="val_operacion"></span> | <b>Sucursal:</b> <span
                            id="val_sucursal"></span> | <b>Día Operativo:</b> <span id="dia_operativo"></span> | <b>Ruta:</b>
                    <span id="val_ruta"></span> | <b>Vendedor:</b> <span id="val_vendedor"></span></h3>

                <br><br>

                <div hidden="hidden" class="jqGrid_wrapper" style="overflow-x: hidden;">
                    <table id="grid-table_detalles"></table>
                    <div id="grid-pager_detalles" style="width: auto;"></div>
                </div>

                <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                    <table class="table table-striped table-bordered table-hover dataTables-example"
                           id="grid-table-3" style="width: 100%">
                        <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Articulo</th>
                            <th>Cajas</th>
                            <th>Piezas</th>
                            <th>Importe</th>
                            <th>IVA</th>
                            <th>Descuento</th>
                            <th>Total</th>
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
                        <div hidden="hidden" class="jqGrid_wrapper" style="overflow-x: hidden;">
                            <table id="grid-table-promo"></table>
                            <div class="class_pager" id="grid-pager-promo"></div>
                        </div>

                        <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                            <table class="table table-striped table-bordered table-hover dataTables-example"
                                   id="grid-table-4" style="width: 100%">
                                <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Unidad de Medida</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
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
    <h3>Reporte de Ventas</h3>
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

                        ?>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="rutas_list">Rutas:</label>
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
                                    <option value="todas">Todas las Rutas</option>
                                    <?php
                                    foreach ($listaRuta->getAll($cve_almacen, 0) as $r):
                                        ?>
                                        <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( " . $r->cve_ruta . " ) - " . $r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                        <?php

                        ?>
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
                                    <option value="preventa">Pre Venta</option>
                                    <option value="F">Entrega</option>
                                    <option value="Devoluciones">Devoluciones</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="agentes">Agente | Operador:</label>
                                <select class="form-control chosen-select" id="agentes_list" name="agentes_list">
                                    <option value="">Seleccione Agente | Operador</option>
                                    <?php
                                    foreach ($vendedores->getAllVendedor($cve_almacen) as $ch):
                                        ?>
                                        <option value="<?php echo $ch->Id_Vendedor; ?>"><?php echo "( " . $ch->Cve_Vendedor . " ) - " . $ch->Nombre; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">

                        <div class="col-md-2">
                            <?php
                            /*
                            ?>
                                                        <div id="fechaini_input">
                                                            <label for="fechaini">Fecha Inicial:</label>
                                                                <div class="input-group date" id="data_1">
                                                                    <input id="fechaini" type="text" class="form-control">
                                                                </div>
                                                        </div>
                            <?php
                            */
                            ?>
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
                            <?php
                            /*
                            ?>
                                                        <div id="fechafin_input">
                                                            <label for="fechafin">Fecha Final:</label>
                                                        <div class="input-group date" id="data_2">
                                                            <input id="fechafin" type="text" class="form-control" value="<?php //if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com') echo $fecha_actual; ?>">
                                                        </div>
                                                        </div>
                            <?php
                            */
                            ?>
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
                                <label for="clientes_list">Tipo:</label>
                                <select class="form-control chosen-select" id="tipo_list" name="tipo_list">
                                    <option value="">Seleccione Tipo</option>
                                    <option value="Credito">Credito</option>
                                    <option value="Contado">Contado</option>
                                    <option value="Obsequio">Obsequio</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="clientes_list">Clientes:</label>
                                <select class="form-control chosen-select" id="clientes_list" name="clientes_list">
                                    <option value="">Seleccione Cliente</option>
                                    <?php
                                    $id_almacen = $_SESSION['id_almacen'];
                                    foreach ($listaCliente->getClientesAnalisisVentas($id_almacen) as $ch):
                                        //foreach( $listaCliente->getAll(" AND Cve_Almacenp = $id_almacen ") AS $ch ):
                                        ?>
                                        <option value="<?php echo $ch->CodCliente; ?>"><?php echo "( " . $ch->Cve_Clte . " ) - " . $ch->Nombre; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="articulos_list">Artículos:</label>
                                <select class="form-control chosen-select" id="articulos_list" name="articulos_list">
                                    <option value="">Seleccione Artículo</option>
                                    <?php
                                    //foreach( $listaArticulos->getArticulosVenta($cve_almacen) AS $ch ): 
                                    foreach ($listaArticulos->getAllArticulosQ($_SESSION['id_almacen']) as $ch):
                                        ?>
                                        <option value="<?php echo $ch->cve_articulo; ?>"><?php echo "( " . $ch->cve_articulo . " ) - " . $ch->des_articulo; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="articulos_obsq_list">Artículos Obsequio:</label>
                                <select class="form-control chosen-select" id="articulos_obsq_list"
                                        name="articulos_obsq_list">
                                    <option value="">Seleccione Artículo</option>
                                    <?php
                                    foreach ($listaArticulos->getArticulosVentaObseq($cve_almacen) as $ch):
                                        ?>
                                        <option value="<?php echo $ch->cve_articulo; ?>"><?php echo "( " . $ch->cve_articulo . " ) - " . $ch->des_articulo; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio"
                                           style="margin: 10px 10px  10px 0;" id="txtCriterio"
                                           placeholder="Id Venta, Folio, Cliente, Vendedor...">
                                    <div class="input-group-btn">
                                        <button onclick="ReloadGrid()" type="submit" id="buscarA"
                                                class="btn btn-primary" style="margin: 10px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php
                            /*
                            ?>
                            <button type="submit" id="generarExcel" class="btn btn-primary" style="margin: 10px;" disabled>
                                <span class="fa fa-file-excel-o"></span> Generar Reporte de Ventas
                            </button>
                            <a href="#" id="generarExcelLink" target="_blank" style="display:none;">excel</a>
                            <?php
                            */
                            ?>
                            <a href="#" id="generarExcel" class="btn btn-primary" style="margin: 10px;" disabled>
                                <span class="fa fa-file-excel-o"></span> Generar Reporte de Ventas
                            </a>

                            <a href="#" id="generarExcelClientes" class="btn btn-primary" style="margin: 10px;">
                                <span class="fa fa-file-excel-o"></span> Reporte de Clientes
                            </a>

                            <button type="button" id="LimpiarFiltros" class="btn btn-primary" style="margin: 10px;">
                                Limpiar Filtros
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
                    <br>
                    <div class="row justify-content-center">
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total Venta:</div>
                                    <div class="amount"><span id="tventa"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-money"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total Efectivo:</div>
                                    <div class="amount"><span id="tefectivo"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-google-wallet"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total Otros Depósitos:</div>
                                    <div class="amount"><span id="totrosdepositos"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-money"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total Contado:</div>
                                    <div class="amount"><span id="tcontado"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-credit-card"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total Crédito:</div>
                                    <div class="amount"><span id="tcredito"></div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="row justify-content-center">
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon">
                                        <i class="fa fa-money"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="title">Importe:</div>
                                    <div class="amount"><span id="timporte"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-money"></i></div>
                                </div>
                                <div>
                                    <div class="title">IVA:</div>
                                    <div class="amount"><span id="tiva"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon">
                                        <i class="fa fa-percent"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="title">Descuentos:</div>
                                    <div class="amount"><span id="tdescuento"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-money"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total:</div>
                                    <div class="amount"><span id="ttotal"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-archive"></i></div>
                                </div>
                                <div>
                                    <div class="title">Total C:</div>
                                    <div class="amount"><span id="ttotalc"></span></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row justify-content-center">
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon">
                                        <i class="fa fa-glass"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="title">Total P:</div>
                                    <div class="amount"><span id="ttotalp"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-archive"></i></div>
                                </div>
                                <div>
                                    <div class="title">Promo C:</div>
                                    <div class="amount"><span id="tpromoc"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon">
                                        <i class="fa fa-glass"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="title">Promo P:</div>
                                    <div class="amount"><span
                                                id="tpromop"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-archive"></i></div>
                                </div>
                                <div>
                                    <div class="title">Obseq C:</div>
                                    <div class="amount"><span id="tobseqc"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="card">
                                <div class="icon-container">
                                    <div class="icon"><i class="fa fa-glass"></i></div>
                                </div>
                                <div>
                                    <div class="title">Obseq P:</div>
                                    <div class="amount"><span id="tobseqp"></span></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div hidden="hidden" class="row" style="text-align: center;">

                        <!--                        <b>Total Efectivo: </b><span id="tefectivo"></span> |-->
                        <!--                        <b>Total Otros Depósitos: </b><span id="totrosdepositos"></span> |-->
                        <!--                        <b>Total Contado: </b><span id="tcontado"></span> |-->
                        <!--                        <b>Total Crédito: </b><span id="tcredito"></span> |-->
                        <!--                        <b>Total Venta: </b><span id="tventa"></span>-->

                        <!--                        <br><br>-->
                        <!--                        <b>Importe: </b><span id="timporte"></span><b> | </b><b>IVA: </b><span id="tiva"></span><b>-->
                        <!--                            | </b>-->
                        <!--                        <b>Descuento: </b><span id="tdescuento"></span><b> | </b><b>Total: </b><span id="ttotal"></span><b>-->
                        <!--                            | </b>-->
                        <!--                        <b>Total C: </b><span id="ttotalc"></span><b> | </b><b>Total P: </b><span-->
                        <!--                                id="ttotalp"></span><b> | </b>-->
                        <!--                        <b>Promo C: </b><span id="tpromoc"></span><b> | </b><b>Promo P: </b><span-->
                        <!--                                id="tpromop"></span><b> | </b>-->
                        <!--                        <b>Obseq C: </b><span id="tobseqc"></span><b> | </b><b>Obseq P: </b><span id="tobseqp"></span>-->
                        <br>
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
                                <div hidden="hidden" class="jqGrid_wrapper">
                                    <table id="grid-table"></table>
                                    <div id="grid-pager"></div>
                                </div>
                                <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                                    <!--                                    centra los botones-->
                                    <!--                                    <div hidden="hidden" class="btn-group"-->
                                    <!--                                         style="text-align: center; margin: auto;">-->
                                    <!--                                        <a style="margin: 10px" class="btn btn-danger" id="imprimir"-->
                                    <!--                                           onclick="printGrid()"><i class="fa fa-print"></i> Reporte Completo PDF-->
                                    <!--                                        </a>-->
                                    <!--                                        <a style="margin: 10px" class="btn btn-primary" onclick="printExcel()"-->
                                    <!--                                           id="excel"><i-->
                                    <!--                                                    class="fa fa-file-excel-o"></i> Reporte Completo Excel-->
                                    <!--                                        </a>-->
                                    <!--                                    </div>-->

                                    <table class="table table-striped table-bordered table-hover dataTables-example"
                                           id="grid-table-2">
                                        <thead>
                                        <tr>
                                            <th style="text-align: center">Acciones</th>
                                            <th style="text-align: center">DO</th>
                                            <th style="text-align: center">Ruta</th>
                                            <th style="text-align: center">Operación</th>
                                            <th style="text-align: center">Fecha</th>
                                            <th style="text-align: center">F. Compromiso</th>
                                            <th style="text-align: center">Folio</th>
                                            <th style="text-align: center">Cliente</th>
                                            <th style="text-align: center">Nombre Comercial</th>
                                            <th style="text-align: center">Tipo</th>
                                            <th style="text-align: center">M&eacute;todo de Pago</th>
                                            <th style="text-align: center">Importe</th>
                                            <th style="text-align: center">IVA</th>
                                            <th style="text-align: center">Descuento</th>
                                            <th style="text-align: center">Total</th>
                                            <th style="text-align: center">Vendedor</th>
                                            <th style="text-align: center">Status</th>
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

<script>

    $('#btn-import').on('click', function () {
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
            beforeSend: function () {
                $('.progress').show();
                var percentVal = '0%';
                bar.width(percentVal);
                percent.html(percentVal);
            },
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete + '%');
                            if (percentComplete === 100) {
                                setTimeout(function () {
                                    $('.progress').hide();
                                }, 2000);
                            }
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                setTimeout(
                    function () {
                        if (data.status == 200) {
                            swal("Exito", data.statusText, "success");
                            $('#importar').modal('hide');
                            ReloadGrid();
                        } else {
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
    $('#avanzada').on('click', function () {
        $("#busqueda")[0].style.display = 'none';
    });
    $('#simple').on('click', function () {
        $("#busqueda")[0].style.display = 'block';
    });

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
            sortorder: "desc",
            loadComplete: function (data) {
                console.log("SUCCESS Promo: ", data);
                let dataGrid = $(this).jqGrid('getRowData');
                let dataReporte = [];
                if (data.total > 0) {
                    for (let i = 0; i < dataGrid.length; i++) {
                        dataReporte.push({
                            cve_articulo: dataGrid[i].cve_articulo,
                            descripcion: dataGrid[i].descripcion,
                            cve_ruta: dataGrid[i].cve_ruta,
                            cliente: dataGrid[i].cliente,
                            cantidad: dataGrid[i].cantidad,
                            unidad_medida: dataGrid[i].unidad_medida
                        });
                    }
                }
                if ($.fn.DataTable.isDataTable('#grid-table-4')) {
                    $('#grid-table-4').DataTable().destroy();
                }
                $('#grid-table-4').DataTable(
                    {
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        searching: false,
                        //paginate
                        "paging": true,
                        data: dataReporte,
                        "lengthMenu": [[30, 40, 50], [30, 40, 50]],
                        columns: [
                            {data: 'cve_articulo'},
                            {data: 'descripcion'},
                            {data: 'cantidad'},
                            {data: 'unidad_medida'},
                        ],
                    }
                ).columns.adjust();
            }
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
                let dataGrid = $(this).jqGrid('getRowData');
                let dataReporte = [];
                if (data.total > 0) {
                    console.log(data.rows);
                    for (let i = 0; i < data.rows.length; i++) {
                        //quita las comas de los miles a los numeros de la columna importe, iva, descuento para que se sumen correctamente y den el total
                        let importe = data.rows[i].cell['importe'] != null ? data.rows[i].cell['importe'].replace(/,/g, '') : 0;
                        let iva = data.rows[i].cell['iva'] != null ? data.rows[i].cell['iva'].replace(/,/g, '') : 0;
                        let descuento = data.rows[i].cell['descuento'] != null ? data.rows[i].cell['descuento'].replace(/,/g, '') : 0;
                        let total = parseFloat(importe) + parseFloat(iva) - parseFloat(descuento);
                        total = total.toFixed(2);
                        dataReporte.push({
                            clave: data.rows[i].cell['clave'],
                            articulo: data.rows[i].cell['articulo'],
                            cajas: data.rows[i].cell['cajas'],
                            piezas: data.rows[i].cell['piezas'],
                            importe: importe,
                            iva: iva,
                            descuento: descuento,
                            total: total,
                        });
                    }
                }
                console.log("dataReporte: ", dataReporte);
                if ($.fn.DataTable.isDataTable('#grid-table-3')) {
                    $('#grid-table-3').DataTable().destroy();
                }
                $('#grid-table-3').DataTable(
                    {
                        responsive: true,
                        autoWidth: true,
                        columnDefs: [
                            {responsivePriority: 1, targets: 0},
                            {responsivePriority: 2, targets: 1},
                            {responsivePriority: 3, targets: 2},
                            {responsivePriority: 4, targets: 3},
                            {responsivePriority: 5, targets: 4},
                            {responsivePriority: 6, targets: 5}
                        ],
                        "lengthMenu": [[10, 20, 30], [10, 20, 30]],
                        "lengthMenu": [[10, 20, 30], [10, 20, 30]],
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        searching: false,
                        data: dataReporte,
                        columns: [
                            {data: 'clave'},
                            {data: 'articulo'},
                            {data: 'cajas'},
                            {data: 'piezas'},
                            //agrega un simbolo de pesos a la columna importe, iva, descuento y total, asi tambien como el formato de miles con comas
                            {
                                data: 'importe',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            },
                            {
                                data: 'iva',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            },
                            {
                                data: 'descuento',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            },

                            {
                                data: 'total',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            }
                        ],
                    }
                ).columns.adjust();
            },
            viewrecords: true
        });
// Setup buttons
        $(grid_selector_detalles).jqGrid('navGrid', '#grid-pager_detalles',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        //resize to fit page size
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
        almacenPrede();
        console.log("almacen:", $("#almacenes").val());

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
            url: '/api/reportesRutas/lista/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                ruta: $("#rutas_list").val(),
                diao: $("#diao_list").val(),
                operacion: $("#operacion_list").val(),
                almacen: $("#almacenes").val(),
                agente: $("#agentes_list").val(),
                tipoV: $("#tipo_list").val(),
                clientes: $("#clientes_list").val(),
                articulos: $("#articulos_list").val(),
                articulos_obsq: $("#articulos_obsq_list").val(),
                fechaini: $("#fechaini").val(),
                fechafin: $("#fechafin").val()
            },
            mtype: 'POST',
            colNames: ["Acciones", 'Id Venta', 'DO', 'Ruta', 'Operación', 'Fecha', 'F. Compromiso', 'Folio', 'ClienteD', 'Cliente', 'Responsable', 'Nombre Comercial', 'Status', 'Tipo', 'Método de Pago', 'Importe', 'IVA', 'Descuento', 'Total', 'Abono', 'Saldo Final', 'Total C', 'Total P', 'PromoC', 'PromoP', 'Obseq C', 'Obseq P', 'Vendedor', 'Ayudante 1', 'Ayudante 2', 'Promociones', 'Tiene Promoción'],
            colModel: [
                {
                    name: 'myac',
                    index: '',
                    width: 80,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    formatter: imageFormat
                },//0
                {
                    name: 'ID_Venta',
                    index: 'ID_Venta',
                    width: 70,
                    editable: false,
                    sortable: false,
                    hidden: true,
                    align: 'right'
                },//1
                {name: 'dia_o', index: 'dia_o', width: 50, editable: false, sortable: false, align: 'right'},//2
                {name: 'ruta', index: 'ruta', width: 70, editable: false, sortable: false},//3
                {name: 'Operacion', index: 'Operacion', width: 100, editable: false, sortable: false},//4
                {name: 'fecha', index: 'fecha', width: 100, editable: false, sortable: false, align: 'center'},//5
                {name: 'fechac', index: 'fechac', width: 100, editable: false, sortable: false, align: 'center'},//6
                {name: 'folio', index: 'folio', width: 100, editable: false, sortable: false},//7
                {
                    name: 'clienteD',
                    index: 'clienteD',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//8
                {name: 'cliente', index: 'cliente', width: 100, editable: false, sortable: false, align: 'right'},//9
                {name: 'responsable', index: 'responsable', width: 150, editable: false, sortable: false, hidden: true},//10
                {name: 'nombre_comercial', index: 'nombre_comercial', width: 150, editable: false, sortable: false},//11
                {name: 'status', index: 'status', width: 80, editable: false, sortable: false},//12
                {name: 'tipo', index: 'tipo', width: 80, editable: false, sortable: false},//13
                {name: 'metodo_pago', index: 'metodo_pago', width: 110, editable: false, sortable: false},//14
                {name: 'importe', index: 'importe', width: 100, editable: false, sortable: false, align: 'right'},//15
                {name: 'iva', index: 'iva', width: 100, editable: false, sortable: false, align: 'right'},//16
                {name: 'descuento', index: 'descuento', width: 100, editable: false, sortable: false, align: 'right'},//17
                {name: 'total', index: 'total', width: 100, editable: false, sortable: false, align: 'right'},//18
                {
                    name: 'abono',
                    index: 'abono',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//19
                {
                    name: 'saldofinal',
                    index: 'saldofinal',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//20
                {
                    name: 'cajas_total',
                    index: 'cajas_total',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },
                {
                    name: 'piezas_total',
                    index: 'piezas_total',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//22
                {
                    name: 'PromoC',
                    index: 'PromoC',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//23
                {
                    name: 'PromoP',
                    index: 'PromoP',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//24
                {
                    name: 'ObseqC',
                    index: 'ObseqC',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//25
                {
                    name: 'ObseqP',
                    index: 'ObseqP',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//26
                {name: 'vendedor', index: 'vendedor', width: 120, editable: false, sortable: false},//27
                {name: 'ayudante1', index: 'ayudante1', width: 120, editable: false, sortable: false, hidden: true},//28
                {name: 'ayudante2', index: 'ayudante2', width: 120, editable: false, sortable: false, hidden: true},//29
                {
                    name: 'promociones',
                    index: 'promociones',
                    width: 120,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//30
                {
                    name: 'tienepromo',
                    index: 'tienepromo',
                    width: 120,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                }//31
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'ID_Venta',
            viewrecords: true,
            loadError: function (data) {
                console.log("ERROR: ", data);
                setTimeout(function () {
                    Swal.close();
                }, 500);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar la tabla',
                    text: 'No se pudo cargar la tabla correctamente, contacte al administrador del sistema'
                });

            },
            sortorder: "desc",
            loadComplete: function (data) {
                console.log("SUCCESS Reload = ", data);
                console.log("SUCCESS SQL = ", data.sql);
                console.log("SUCCESS SQL_CONTEOS = ", data.sql_conteos);
                let dataGrid = $(this).jqGrid('getRowData');
                let dataReporte = [];
                let importeTotal = data.timporte;
                let ivaTotal = data.tiva;
                let descuentoTotal = data.tdescuento;
                let totalTotal = data.ttotal;
                let totalCajas = data.ttotalc;
                let totalPiezas = data.ttotalp;
                let totalPromoC = data.tpromoc;
                let totalPromoP = data.tpromop;
                let totalObseqC = data.tobseqc;
                let totalObseqP = data.tobseqp;
                let totalEfectivo = data.tefectivo;
                let totalOtrosDepositos = data.totrosdepositos;
                let totalVentas = data.tventa;
                let totalCredito = data.tcredito;
                let totalContado = data.tcontado;
                let idsVenta = [];
                let idsVentaTipo = [];
                let idsFolio = [];
                if (data.total > 0) {
                    dataTablaGeneral = [];

                    for (let i = 0; i < data.rows.length; i++) {
                        let acciones = "";
                        for (let j = 0; j < dataGrid.length; j++) {
                            if (dataGrid[j].cliente === data.rows[i].cell[9] && dataGrid[j].folio === data.rows[i].cell[7]) {
                                acciones = dataGrid[j].myac;
                                break;
                            }
                        }
                        //
                        // data.rows[i].cell[15] = data.rows[i].cell[15] != null ? data.rows[i].cell[15].replace(/,/g, "") : 0;
                        // data.rows[i].cell[16] = data.rows[i].cell[16] != null ? data.rows[i].cell[16].replace(/,/g, "") : 0;
                        // data.rows[i].cell[17] = data.rows[i].cell[17] != null ? data.rows[i].cell[17].replace(/,/g, "") : 0;
                        // data.rows[i].cell[18] = data.rows[i].cell[18] != null ? data.rows[i].cell[18].replace(/,/g, "") : 0;
                        // //data.rows[i].cell[12] viene del modo "<b style='color:green;'>Abierta</b>", se quita el html en una variable
                        // let tipoVenta = data.rows[i].cell[12].replace(/<[^>]+>/g, '');
                        // console.log("tipoVenta = ", tipoVenta);
                        // // Total efectivo= sumatoria de  método pago=Efectivo
                        // if (data.rows[i].cell[14] == "Efectivo" && data.rows[i].cell[13] != "Credito" && tipoVenta != "Cancelada") {
                        //     totalEfectivo += parseFloat(data.rows[i].cell[15]);
                        // }
                        // //total  otros depósitos= sumatoria de método pago  diferente a  efectivo
                        // if (data.rows[i].cell[14] != "Efectivo" && tipoVenta != "Cancelada") {
                        //     totalOtrosDepositos += parseFloat(data.rows[i].cell[18]);
                        // }
                        // //total ventas =  Sumatoria de la columna  Total
                        // if (tipoVenta != "Cancelada") {
                        //     totalVentas += parseFloat(data.rows[i].cell[18]);
                        // }
                        // //Total credito= Sumatoria de  tipo= credito
                        // if (data.rows[i].cell[13] == "Credito" && tipoVenta != "Cancelada") {
                        //     totalCredito += parseFloat(data.rows[i].cell[18]);
                        // }
                        // //Total contado= Sumatoria de  tipo= Contado
                        // if (data.rows[i].cell[13] == "Contado" && tipoVenta != "Cancelada") {
                        //     console.log("entro");
                        //     totalContado += parseFloat(data.rows[i].cell[18]);
                        //     console.log('totalContado', totalContado);
                        // }
                        // console.log('data.rows[i]', data.rows[i]);
                        dataReporte.push({
                            acciones: acciones,
                            DO: data.rows[i].cell[2],
                            Ruta: data.rows[i].cell[3],
                            Venta: data.rows[i].cell[4],
                            Fecha: data.rows[i].cell[5],
                            FechaCompromiso: data.rows[i].cell[6],
                            Folio: data.rows[i].cell[7],
                            Cliente: data.rows[i].cell[9],
                            NombreComercial: data.rows[i].cell[10],
                            Status: data.rows[i].cell[12],
                            Tipo: data.rows[i].cell[13],
                            MetodoPago: data.rows[i].cell[14],
                            Importe: data.rows[i].cell[15],
                            IVA: data.rows[i].cell[16],
                            Descuento: data.rows[i].cell[17],
                            Total: data.rows[i].cell[18],
                            Vendedor: data.rows[i].cell[27],
                        });
                        // if (tipoVenta != "Cancelada") {
                        //     // importeTotal += parseFloat(data.rows[i].cell[15]);
                        //     // ivaTotal += parseFloat(data.rows[i].cell[16]);
                        //     // descuentoTotal += parseFloat(data.rows[i].cell[17]);
                        //     // totalTotal += parseFloat(data.rows[i].cell[18]);
                        //     idsVenta.push(data.rows[i].cell[1]);
                        //     idsVentaTipo.push({
                        //         id: data.rows[i].cell[1],
                        //         tipo: data.rows[i].cell[13],
                        //         folio: data.rows[i].cell[7]
                        //     });
                        //     idsFolio.push("'" + data.rows[i].cell[7] + "'");
                        // }
                    }
                }
                // if (idsVenta.length > 0 || idsFolio.length > 0) {
                //     $.ajax({
                //         url: '/api/reportesRutas/lista/index.php',
                //         type: 'GET',
                //         async: false,
                //         data: {
                //             action: 'getDetallesFolio2',
                //             folio: idsFolio.length > 0 ? idsFolio : '',
                //             id_venta: idsVenta.length > 0 ? idsVenta : '',
                //             page: 1,
                //             rows: 1000
                //         },
                //         beforeSend: function () {
                //
                //         },
                //         success: function (response) {
                //             response = JSON.parse(response);
                //             console.log('response folios o ventas', response);
                //             if (response.total > 0) {
                //                 for (let j = 0; j < response.rows.length; j++) {
                //                     response.rows[j].cell['cajas'] = response.rows[j].cell['cajas'].replace(/,/g, "");
                //                     response.rows[j].cell['piezas'] = response.rows[j].cell['piezas'].replace(/,/g, "");
                //                     for (let k = 0; k < idsVentaTipo.length; k++) {
                //                         if (idsVentaTipo[k].id === response.rows[j]['idVenta'] || idsVentaTipo[k].folio === response.rows[j]['idVenta']) {
                //                             console.log("entro");
                //                             if (idsVentaTipo[k].tipo === "Obsequio") {
                //                                 totalObseqC += parseFloat(response.rows[j].cell['cajas']);
                //                                 totalObseqP += parseFloat(response.rows[j].cell['piezas']);
                //                             } else {
                //                                 totalCajas += parseFloat(response.rows[j].cell['cajas']);
                //                                 totalPiezas += parseFloat(response.rows[j].cell['piezas']);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }
                //         },
                //     });
                //     $.ajax({
                //         url: '/api/reportesRutas/lista/index.php',
                //         type: 'GET',
                //         async: false,
                //         data: {
                //             action: 'PromocionVenta2',
                //             folio: idsFolio.length > 0 ? idsFolio : '',
                //             almacen: $('#almacenes').val(),
                //             page: 1,
                //             rows: 1000
                //         },
                //         success: function (response1) {
                //             response1 = JSON.parse(response1);
                //             if (response1.total > 0) {
                //                 for (let k = 0; k < response1.rows.length; k++) {
                //                     if (response1.rows[k].cell['unidad_medida'] === "Caja" || response1.rows[k].cell['unidad_medida'] === "caja") {
                //                         totalPromoC += parseFloat(response1.rows[k].cell['cantidad']);
                //                     } else {
                //                         totalPromoP += parseFloat(response1.rows[k].cell['cantidad']);
                //                     }
                //                 }
                //             }
                //         },
                //     });
                // }
                dataTablaGeneral = dataReporte;
                if ($.fn.DataTable.isDataTable('#grid-table-2')) {
                    $('#grid-table-2').DataTable().destroy();
                }
                $('#grid-table-2').DataTable(
                    {
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
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
                            {data: 'DO', className: 'text-center'},
                            {data: 'Ruta', className: 'text-center'},
                            {data: 'Venta', className: 'text-center'},
                            {data: 'Fecha', className: 'text-center'},
                            {data: 'FechaCompromiso', className: 'text-center'},
                            {data: 'Folio', className: 'text-center'},
                            {data: 'Cliente', className: 'text-center'},
                            {data: 'NombreComercial', className: 'text-center'},
                            {data: 'Tipo', className: 'text-center'},
                            {data: 'MetodoPago', className: 'text-center'},
                            {data: 'Importe', className: 'text-right'},
                            {data: 'IVA', className: 'text-right'},
                            {data: 'Descuento', className: 'text-right'},
                            {data: 'Total', className: 'text-right'},
                            {data: 'Vendedor', className: 'text-center'},
                            {data: 'Status', className: 'text-center'},
                        ],
                    }
                );
                var rowNum = $(this).jqGrid('getGridParam', 'rowNum');

                $("#tventa").text(totalVentas);
                $("#totrosdepositos").text(totalOtrosDepositos);
                $("#tefectivo").text(totalEfectivo);
                $("#tcontado").text(totalContado);
                $("#tcredito").text(totalCredito);
                $("#timporte").text(importeTotal);
                $("#tiva").text(ivaTotal);
                $("#tdescuento").text(descuentoTotal);
                $("#ttotal").text(totalTotal);
                $("#ttotalc").text(totalCajas);
                $("#ttotalp").text(totalPiezas);
                $("#tpromoc").text(totalPromoC);
                $("#tpromop").text(totalPromoP);
                $("#tobseqc").text(totalObseqC);
                $("#tobseqp").text(totalObseqP);
                //hide Swal
                setTimeout(function () {
                    Swal.close();
                }, 500);
            }
        });

        $('#grid-table-2').on('length.dt', function (e, settings, len) {
            $('#pg_grid-pager .ui-pg-selbox').val(len).trigger('change');
        });
        //get event entries to jqgrid table grid-table input_grid-pager
        $('#pg_grid-pager .ui-pg-selbox').on('change', function () {
            var selected = $(this).val();
            console.log("selected: ", selected);
        });
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
            var id_venta = rowObject[1];
            var clave_cia = <?php echo $_SESSION['cve_cia']; ?>;
            var folio = rowObject[7],
                cliente = rowObject[8],
                responsable = rowObject[10],
                tipo = rowObject[13],
                operacion = rowObject[4],
                sucursal = $("#almacenes option:selected").text(),
                dia_operativo = rowObject[2],
                ruta = rowObject[3],
                tienepromo = rowObject[31],
                vendedor = rowObject[27];

            var html = '';
            html = '<a class="btn btn-info" href="#" onclick="ver(\'' + id_venta + '\', \'' + folio + '\', \'' + cliente + '\', \'' + responsable + '\', \'' + operacion + '\', \'' + sucursal + '\', \'' + dia_operativo + '\', \'' + ruta + '\', \'' + vendedor + '\', \'' + tienepromo + '\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a class="btn btn-danger" href="/api/koolreport/export/reportes/ventas/ventas_sfa/?id_venta=' + id_venta + '&cve_cia=' + clave_cia + '&folio=' + folio + '" target="_blank"><i class="fa fa-file-pdf-o" title="Reporte de Venta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            /*
                        html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                        html += '<a href="#" onclick="select_clientes(\'' + clave_ruta + '\')"><i class="fa fa-times" alt="Eliminar Clientes Ruta" title="Eliminar Clientes Ruta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                        html += '<a href="#" onclick="select_transporte(\'' + clave_ruta + '\')"><i class="fa fa-truck" alt="Catálogo de Transportes" title="Catálogo de Transportes"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                        html += '<a href="#" onclick="select_chofer(\'' + clave_ruta + '\', 1)"><i class="fa fa-male" alt="Asignar Chofer" title="Asignar Chofer"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                        html += '<a href="#" onclick="select_choferEliminar(\'' + clave_ruta + '\', 0)"><i class="fa fa-user-times" alt="Eliminar Chofer" title="Eliminar Chofer"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            */
            return html;
        }

        function aceSwitch(cellvalue, options, cell) {
            setTimeout(function () {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }

        //inicializa el datatable grid-table-2 con data vacia
        $('#grid-table-2').DataTable(
            {
                language: {
                    url: '/assets/plugins/DataTable/spanish.json'
                },
                searching: false,

            }
        );

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
    })
    ;

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

    $("#operacion_list").change(function () {

        //setTimeout(function(){ReloadGrid();}, 2000);
        ReloadGrid();

    });

    $("#LimpiarFiltros").click(function () {

        //setTimeout(function(){ReloadGrid();}, 2000);

        $("#rutas_list").val("");
        $("#rutas_list").trigger("chosen:updated");
        $("#diao_list").val("");
        $("#diao_list").trigger("chosen:updated");
        $("#agentes_list").val("");
        $("#agentes_list").trigger("chosen:updated");
        $("#operacion_list").val("");
        $("#operacion_list").trigger("chosen:updated");
        $("#clientes_list").val("");
        $("#clientes_list").trigger("chosen:updated");
        $("#fechaini").val("");
        $("#fechaini").trigger("chosen:updated");
        $("#fechafin").val("");
        $("#fechafin").trigger("chosen:updated");
        $("#articulos_list").val("");
        $("#articulos_list").trigger("chosen:updated");
        $("#articulos_obsq_list").val("");
        $("#articulos_obsq_list").trigger("chosen:updated");
        $("#tipo_list").val("");
        $("#tipo_list").trigger("chosen:updated");

        ReloadGrid(1);

    });


    $("#generarExcelClientes").click(function () {

        if($("#diao_list").val() == "" && $("#fechaini").val() == "" && $("#fechafin").val() == "")
        {
            swal("Aviso", "Debe Seleccionar un Dia Operativo o un Rango de Fechas", "warning");
            return;
        }

        var ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            almacen = $("#almacenes").val(),
            operacion = $("#operacion_list").val(),
            clientes = $("#clientes_list").val(),
            fechaini = $("#fechaini").val(),
            fechafin = $("#fechafin").val(),
            articulos = $("#articulos_list").val(),
            articulos_obsq = $("#articulos_obsq_list").val(),
            tipoV = $("#tipo_list").val();

        console.log("ruta =", $("#rutas_list").val());
        console.log("diao =", $("#diao_list").val());
        console.log("almacen =", $("#almacenes").val());
        console.log("/api/koolreport/excel/ventas_sfa_clientes/export.php?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&operacion=" + operacion + "&fechaini=" + fechaini + "&fechafin=" + fechafin + "&clientes=" + clientes + "&articulos=" + articulos + "&articulos_obsq=" + articulos_obsq + "&tipoV=" + tipoV);

        $(this).attr("href", "/api/koolreport/excel/ventas_sfa_clientes/export.php?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&operacion=" + operacion + "&fechaini=" + fechaini + "&fechafin=" + fechafin + "&clientes=" + clientes + "&articulos=" + articulos + "&articulos_obsq=" + articulos_obsq + "&tipoV=" + tipoV);

    });

    $("#generarExcel").click(function () {

        if($("#diao_list").val() == "" && $("#fechaini").val() == "" && $("#fechafin").val() == "")
        {
            swal("Aviso", "Debe Seleccionar un Dia Operativo o un Rango de Fechas", "warning");
            return;
        }

        var ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            almacen = $("#almacenes").val(),
            operacion = $("#operacion_list").val(),
            clientes = $("#clientes_list").val(),
            fechaini = $("#fechaini").val(),
            fechafin = $("#fechafin").val(),
            articulos = $("#articulos_list").val(),
            articulos_obsq = $("#articulos_obsq_list").val(),
            criterio = $("#txtCriterio").val(),
            tipoV = $("#tipo_list").val();

        console.log("ruta =", $("#rutas_list").val());
        console.log("diao =", $("#diao_list").val());
        console.log("almacen =", $("#almacenes").val());
        console.log("/api/koolreport/excel/ventas_sfa/export.php?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&operacion=" + operacion + "&fechaini=" + fechaini + "&fechafin=" + fechafin + "&clientes=" + clientes + "&articulos=" + articulos + "&articulos_obsq=" + articulos_obsq + "&tipoV=" + tipoV + "&criterio=" + criterio);

        $(this).attr("href", "/api/koolreport/excel/ventas_sfa/export.php?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&operacion=" + operacion + "&fechaini=" + fechaini + "&fechafin=" + fechafin + "&clientes=" + clientes + "&articulos=" + articulos + "&articulos_obsq=" + articulos_obsq + "&tipoV=" + tipoV + "&criterio=" + criterio);

    });

    $("#diao_list").change(function () {

        //setTimeout(function(){ReloadGrid();}, 2000);
        if($("#diao_list").val() == "" && $("#fechaini").val() == "" && $("#fechafin").val() == "")
        {
            swal("Aviso", "Debe Seleccionar un Dia Operativo o un Rango de Fechas", "warning");
            return;
        }

        $("#generarExcel").removeAttr("disabled");
        ReloadGrid();

    });

    $("#rutas_list").change(function () {
        //$("#generarExcel").attr("href", "#");
        //$("#generarExcel").attr("disabled", "");
        $("#generarExcel").removeAttr("disabled");


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_ruta: $(this).val(),
                action: "extraer_diaso"
            },
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/reportesRutas/lista/filtros_select.php',
            success: function (data) {
                //console.log("SUCCESS DIAS_O = ", data);
                $("#diao_list").empty();
                $("#diao_list").append(data);
                $("#diao_list").trigger("chosen:updated");
                //$('.chosen-select').chosen();
            },
            error: function (data) {
                console.log("ERROR = ", data);
            }
        });
        //setTimeout(function(){ReloadGrid();}, 2000);
        //ReloadGrid();
    });

    function ReloadGrid(limpiar = 0) {


        if($("#diao_list").val() == "" && $("#fechaini").val() == "" && $("#fechafin").val() == "")
        {
            swal("Aviso", "Debe Seleccionar un Dia Operativo o un Rango de Fechas", "warning");
            return;
        }

        Swal.fire(
            'Espere un momento',
            'Estamos analizando la información',
            'info'
        );

        Swal.showLoading();

        console.log("almacen INIT: ", $("#almacenes").val());
        console.log("criterio: ", $("#txtCriterio").val());
        console.log("ruta: ", $("#rutas_list").val());
        console.log("diao: ", $("#diao_list").val());
        console.log("operacion: ", $("#operacion_list").val());
        console.log("fechaini: ", $("#fechaini").val());
        console.log("fechafin: ", $("#fechafin").val());
        console.log("tipoV: ", $("#tipo_list").val());

        if ($("#rutas_list").val() == '' && limpiar == 0) {
            swal("Error", "Debe Seleccionar una ruta para realizar la búsqueda", "error");
            setTimeout(function () {
                Swal.close();
            }, 500);
            return;
        }

        //setTimeout(function(){
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                //url: '/api/reportesRutas/lista/index.php',
                postData: {
                    criterio: $("#txtCriterio").val(),
                    ruta: $("#rutas_list").val(),
                    diao: $("#diao_list").val(),
                    operacion: $("#operacion_list").val(),
                    agente: $("#agentes_list").val(),
                    tipoV: $("#tipo_list").val(),
                    clientes: $("#clientes_list").val(),
                    articulos: $("#articulos_list").val(),
                    articulos_obsq: $("#articulos_obsq_list").val(),
                    fechaini: $("#fechaini").val(),
                    fechafin: $("#fechafin").val(),
                    almacen: $("#almacenes").val()
                },
                datatype: 'json',
                page: 1
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
        //}, 2000);
    }

    function almacen() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                    ruta: $("#rutas_list").val(),
                    diao: $("#diao_list").val(),
                    operacion: $("#operacion_list").val(),
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


    function ver(id, folio, cliente, responsable, operacion, sucursal, dia_operativo, ruta, vendedor, tienepromo) {
        if (id)
            $("#ver_detalles #num_entrada").text(id);
        else
            $("#ver_detalles #num_entrada").text(folio);
        $("#ver_detalles #num_folio").text(folio);
        $("#ver_detalles #num_cliente").text(cliente);
        $("#ver_detalles #cod_responsable").text(responsable);

        $("#ver_detalles #val_operacion").text(operacion);
        $("#ver_detalles #val_sucursal").text(sucursal);
        $("#ver_detalles #dia_operativo").text(dia_operativo);
        $("#ver_detalles #val_ruta").text(ruta);
        $("#ver_detalles #val_vendedor").text(vendedor);

        $("#tipo_venta").text(operacion);

        $("#ver_detalles").modal("show");
        loadDetalles(id, tienepromo, folio);
    }

    function loadDetalles(id, tienepromo, folio) {
        $('#grid-table_detalles').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    id_venta: id,
                    folio: folio,
                    cve_articulo: $("#articulos_list").val(),
                    action: 'getDetallesFolio'
                }, datatype: 'json', page: 1, mtype: 'GET', url: '/api/reportesRutas/lista/index.php'
            })
            .trigger('reloadGrid', [{current: true}]);

        if (tienepromo) {
            $("#titulo_promo").show();
            $("#tabla_promo").show();
            $('#grid-table-promo').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        folio: folio,
                        ruta: $("#rutas_list").val(),
                        almacen: $("#almacenes").val(),
                        action: 'PromocionVenta'
                    }, datatype: 'json', page: 1, mtype: 'GET', url: '/api/reportesRutas/lista/index.php'
                })
                .trigger('reloadGrid', [{current: true}]);
        } else {
            $("#titulo_promo").hide();
            $("#tabla_promo").hide();
            $("#grid-table-promo").jqGrid("clearGridData");
        }
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

    $("#venta_preventa").change(function () {
        if ($("#venta_preventa").is(":checked"))
            console.log("ON");
        else
            console.log("OFF");
    });

    function agregar() {

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

    function traeModal() {
        $("#myModalClientes .modal-title span").text($("#cve_ruta").val());
        $('#myModalClientes').show();
    }

    function minimizar() {
        $('#myModalClientes').hide();
    }

    function EliminarTransporte() {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Agente = ", $('#agentes').val());
        //$('#transportes').val("");

        if ($('#transportes').val()) {
            swal({
                title: "¿Está seguro de Eliminar este Transporte?",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Borrar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: false
            }, function () {

                //************************************************************************************************
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_ruta: $("#ruta_clave_select").val(),
                        id_transporte: $("#transportes").val(),
                        action: "EliminarTransporte"
                    },
                    beforeSend: function (x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/ruta/update/index.php',
                    success: function (data) {
                        console.log("SUCCESS = ", data);
                        if (data.success == true) {
                            swal("Éxito", 'El Transporte ha sido eliminado', "success");
                            location.reload();
                            //$("#btnCancel").show();
                        } else {
                            //alert(data.err);
                            swal("Error", 'El Transporte no Existe en esta ruta', "error");
                            l.ladda('stop');
                            $("#btnCancel").show();
                        }
                    },
                    error: function (data) {
                        console.log("ERROR = ", data);
                    }
                });
                minimizarT();
                //**********************************************************************************************

            });
        }

    }

    function AsignarTransporte() {
        //console.log("Ruta = ", $("#ruta_clave_select").val(), "Transpote = ", $('#transportes').val());
        //$('#transportes').val("");

        if ($('#transportes').val()) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ruta: $("#ruta_clave_select").val(),
                    id_transporte: $("#transportes").val(),
                    action: "AsignarTransporte"
                },
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function (data) {
                    console.log("SUCCESS = ", data);
                    if (data.success == true) {
                        location.reload();
                        //$("#btnCancel").show();
                    } else {
                        //alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function (data) {
                    console.log("ERROR = ", data);
                }
            });
            minimizarT();
        }

    }

    function minimizarT() {
        $('#modalTransportes').hide();
    }


    function AsignarChofer() {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Agente = ", $('#agentes').val());
        //$('#transportes').val("");

        //return;

        if ($('#agentes').val()) {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ruta: $("#ruta_clave_select").val(),
                    id_agente: $("#agentes").val(),
                    action: "AsignarChofer"
                },
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function (data) {
                    console.log("SUCCESS = ", data);
                    if (data.success == true) {
                        location.reload();
                        //$("#btnCancel").show();
                    } else {
                        //alert(data.err);
                        swal("Error", 'El Agente ya fué asignado a esta ruta', "error");
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function (data) {
                    console.log("ERROR = ", data);
                }
            });
            minimizarChofer();
        }

    }

    function EliminarCliente() {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Cliente = ", $('#clientes').val());
        //$('#transportes').val("");
//return;
        if ($('#clientes').val()) {
            swal({
                title: "¿Está seguro de Eliminar este Cliente?",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Borrar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: false
            }, function () {

                //************************************************************************************************
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_ruta: $("#ruta_clave_select").val(),
                        id_cliente: $("#clientes").val(),
                        action: "EliminarCliente"
                    },
                    beforeSend: function (x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/ruta/update/index.php',
                    success: function (data) {
                        console.log("SUCCESS = ", data);
                        if (data.success == true) {
                            swal("Éxito", 'Cliente Eliminado de la Ruta', "success");
                            location.reload();
                            //$("#btnCancel").show();
                        } else {
                            //alert(data.err);
                            swal("Error", 'El Cliente no está asignado a esta ruta', "error");
                            l.ladda('stop');
                            $("#btnCancel").show();
                        }
                    },
                    error: function (data) {
                        console.log("ERROR = ", data);
                    }
                });
                minimizarChofer();
                //**********************************************************************************************

            });
        }

    }

    function EliminarChofer() {
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Agente = ", $('#EliminarAgentes').val());
        //$('#transportes').val("");

        if ($('#EliminarAgentes').val()) {
            swal({
                title: "¿Está seguro de Eliminar este Agente?",
                text: "Al eliminar al Agente se eliminará toda asignación de Visitas.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Borrar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: false
            }, function () {

                //************************************************************************************************
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_ruta: $("#ruta_clave_select").val(),
                        id_agente: $("#EliminarAgentes").val(),
                        action: "EliminarChofer"
                    },
                    beforeSend: function (x) {
                        if (x && x.overrideMimeType) {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/ruta/update/index.php',
                    success: function (data) {
                        console.log("SUCCESS = ", data);
                        if (data.success == true) {
                            location.reload();
                            //$("#btnCancel").show();
                        } else {
                            //alert(data.err);
                            swal("Error", 'El Agente no está asignado a esta ruta', "error");
                            l.ladda('stop');
                            $("#btnCancel").show();
                        }
                    },
                    error: function (data) {
                        console.log("ERROR = ", data);
                    }
                });
                minimizarChofer();
                //**********************************************************************************************

            });
        }

    }

    function minimizarChofer() {
        $('#modalChofer').hide();
        $('#modalChoferEliminar').hide();
    }

    function minimizarCliente() {
        $('#modalClientes').hide();
    }

    var l = $('#btnSave').ladda();
    l.click(function () {

        if ($("#cve_almacenp").val() == "") return;
        if ($("#cve_ruta").val() == "") return;
        if ($("#descripcion").val() == "") return;

        var venta_preventa = 0;
        if ($("#venta_preventa").is(":checked"))
            venta_preventa = 1;

        $("#btnCancel").hide();
        l.ladda('start');
        if ($("#hiddenAction").val() == "add") {
            var rels = $("input[id='clientes']")
                .map(function () {
                    if ($(this).is(":checked"))
                        return $(this).val();
                }).get();

            if ($("#status").is('checked'))
                $("#status").val("A");
            else
                $("#status").val("B");

            if ($("#status_send").val() == "")
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
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function (data) {
                    console.log("SUCCESS = ", data);
                    if (data.success == true) {
                        location.reload();
                        $("#btnCancel").show();
                    } else {
                        alert(data.err);
                        l.ladda('stop');
                        $("#btnCancel").show();
                    }
                },
                error: function (data) {
                    console.log("ERROR = ", data);
                }
            });
        } else {
            var rels = $("input[id='clientes']")
                .map(function () {
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
                beforeSend: function (x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function (data) {
                    if (data.success == true) {
                        location.reload();
                        $("#btnCancel").show();
                    } else {
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

    /*
    $(document).ready(function() {
      setTimeout(function(){
        ReloadGrid();
      }, 1000);
    });
    */

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

    /*
            $('#data_1').datetimepicker({
                locale: 'es',
                format: 'YYYY-MM-DD',
                useCurrent: false
            });

            $('#data_2').datetimepicker({
                locale: 'es',
                format: 'YYYY-MM-DD',
                useCurrent: false
            });
    */
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
        //$("#fechaini").val($("#fecha_semanal").val());
    });

    $("#txtCriterio").keyup(function (event) {
        if (event.keyCode == 13) {
            $("#buscarA").click();
        }
    });

</script>
<style>
    <?php /* if($edit[0]['Activo']==0) { ?>
        .fa-edit {
            display: none;
        }
    <?php } ?>
    <?php if($borrar[0]['Activo']==0) { ?>
        .fa-eraser {
            display: none;
        }
    <?php } */?>

</style>