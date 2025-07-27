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

$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

?>

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/js/datatables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<link href="/css/datatables.min.css" rel="stylesheet"/>
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
<link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">


<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right {
        position: absolute;
        left: auto;
        right: 0;
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

<div class="modal fade" id="ver_detalles" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 1200px">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <?php
                /*
                ?>
                <h3 class="modal-title">Reporte de Ventas #<span id="num_entrada"></span></h3>
                <h3 class="modal-title">Folio: <span id="num_folio"></span></h3>
                <h3 class="modal-title">Cliente: (<span id="num_cliente"></span>) - <span id="cod_responsable"></span></h3>
                <?php 
                */
                ?>
                <br>
                <h3> <?php /* ?><b>Operación:</b> <span id="val_operacion"></span> |<?php */ ?> <b>Sucursal:</b> <span
                            id="val_sucursal"></span> | <b>Día Operativo:</b> <span id="dia_operativo"></span> | <b>Ruta:</b>
                    <span id="val_ruta"></span> <?php /* ?> | <b>Vendedor:</b> <span id="val_vendedor"></span><?php */ ?>
                </h3>

                <br><br>

                <div class="jqGrid_wrapper" style="overflow-x: hidden;">
                    <table id="grid-table_detalles"></table>
                    <div id="grid-pager_detalles" style="width: auto;"></div>
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
    <h3>Reporte de Liquidación</h3>
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
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="rutas_list">Rutas:</label>
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
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

                        <div class="col-sm-2" style="display:none;">
                            <div class="form-group">
                                <label for="operacion_list">Operación:</label>
                                <select class="form-control chosen-select" id="operacion_list" name="operacion_list">
                                    <option value="">Seleccione Operación</option>
                                    <option value="venta">Venta</option>
                                    <option value="preventa">Pre Venta</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <a href="#" id="generarExcel" class="btn btn-danger" style="margin: 10px;">
                                <span class="fa fa-file-pdf-o"> </span> Generar Reporte de Liquidación
                            </a>
                        </div>
                        <div class="col-sm-2">
                            <a href="#" id="excelExportButton" class="btn btn-primary" style="margin: 10px;">
                                <span class="fa fa-file-excel-o"> </span> Exportar Reporte de Liquidación XLS
                            </a>
                        </div>


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
                    <div hidden="hidden" class="row">
                        <div class="col-md-4">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 10px;"
                                           id="txtCriterio" placeholder="Id Venta, Folio, Cliente, Vendedor...">
                                    <div class="input-group-btn">
                                        <button onclick="ReloadGrid()" type="submit" id="buscarA"
                                                class="btn btn-primary" style="margin: 10px;">
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
                    <div hidden="hidden" class="row" style="text-align: center;">
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
                    <br>

                </div>
                <div class="ibox-content">
                    <div hidden="hidden" class="tabbable" id="tabs-131708">
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
                    </div>
                    <!-- Crea 4 secciones donde en cada una va a haber un datatable jquery con bootstrap (no jqgrid), la primer seccion debe de tener el titulo resumen financiero, 2da Credito y Cobranza, 3ro Devoluciones y 4ta Analisis de Ventas -->
                    <div style="width: 100%; overflow-y: auto; overflow-x: auto;">
                        <section>
                            <h2>Resumen Financiero</h2>
                            <table id="tabla-resumen"
                                   class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                <tr>
                                    <th>Venta Total</th>
                                    <th>Preventa</th>
                                    <th>Ventas Contado</th>
                                    <th>Ventas Crédito</th>
                                    <th>Devoluciones</th>
                                    <th>Cobranza</th>
                                    <th>Descuentos</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7" id="totalResumen" style="text-align: end">Total a liquidar:$0</td>
                                </tr>
                                </tfoot>
                            </table>
                        </section>

                        <section>
                            <h2>Crédito y Cobranza</h2>
                            <table id="tabla-credito-cobranza"
                                   class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Cliente</th>
                                    <th>Crédito</th>
                                    <th>Cobranza</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                <tr>
                                    <td style="text-align: end" colspan="2">Total:</td>
                                    <td style="text-align: end" id="totalCreditos"></td>
                                    <td style="text-align: end" id="totalCobranza"></td>
                                </tr>
                                </tfoot>
                            </table>
                        </section>

                        <section>
                            <h2>Devoluciones</h2>
                            <table id="tabla-devoluciones"
                                   class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Articulo</th>
                                    <th>Cajas</th>
                                    <th>Piezas</th>
                                    <th>Importe</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td style="text-align: end" colspan="2">Total:</td>
                                    <td style="text-align: end" id="totalDevolucionesCaja"></td>
                                    <td style="text-align: end" id="totalDevolucionesPieza"></td>
                                    <td style="text-align: end" id="totalDevoluciones"></td>
                                </tr>
                                </tfoot>
                            </table>
                        </section>
                        <section>
                            <h2>Análisis de Ventas</h2>
                            <table id="analisis-ventas"
                                   class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                <tr>
                                    <th rowspan="2">Clave</th>
                                    <th colspan="2">Stock Inicial</th>
                                    <th colspan="2">Ventas</th>
                                    <th colspan="2">Preventa</th>
                                    <th colspan="2">Entrega</th>
                                    <th colspan="2">Rec</th>
                                    <th colspan="2">Dev</th>
                                    <th colspan="2">Prom</th>
                                    <th colspan="2">Prom Prev</th>
                                    <th rowspan="2">Total $</th>
                                    <th colspan="2">Stock Final</th>
                                    <th colspan="2">Total Pedido</th>
                                </tr>
                                <tr>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                    <th>Cj</th>
                                    <th>Pz</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!--                                Datos de ejemplo-->

                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="1">Total</th>
                                    <th id="totalStockInicialCaja"></th>
                                    <th id="totalStockInicialPieza"></th>
                                    <th id="totalVentasCaja"></th>
                                    <th id="totalVentasPieza"></th>
                                    <th id="totalPreventaCaja"></th>
                                    <th id="totalPreventaPieza"></th>
                                    <th id="totalEntregaCaja"></th>
                                    <th id="totalEntregaPieza"></th>
                                    <th id="totalRecCaja"></th>
                                    <th id="totalRecPieza"></th>
                                    <th id="totalDevCaja"></th>
                                    <th id="totalDevPieza"></th>
                                    <th id="totalPromCaja"></th>
                                    <th id="totalPromPieza"></th>
                                    <th id="totalPromPrevCaja"></th>
                                    <th id="totalPromPrevPieza"></th>
                                    <th id="totalVenta"></th>
                                    <th id="totalStockFinalCaja"></th>
                                    <th id="totalStockFinalPieza"></th>
                                    <th id="totalPedidoCaja"></th>
                                    <th id="totalPedidoPieza"></th>

                                </tr>
                                </tfoot>
                            </table>
                        </section>
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
    $('#tabla-resumen').DataTable({
        language: {
            url: '/assets/plugins/DataTable/spanish.json',
            searching: false,
            paging: false,
            lengthChange: false // Desactivar el combo de registros a mostrar
        }
    });
    $('#tabla-credito-cobranza').DataTable({
        language: {
            url: '/assets/plugins/DataTable/spanish.json'
        }
    });
    $('#tabla-devoluciones').DataTable({
        language: {
            url: '/assets/plugins/DataTable/spanish.json'
        }
    });
    $('#analisis-ventas').DataTable({
        language: {
            url: '/assets/plugins/DataTable/spanish.json'
        }
    });
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
                    almacen();
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
            colNames: ['Clave', 'Artículo', 'Inv. Inicial', 'Cajas', 'Piezas', 'Importe', 'IVA', 'Descuento', 'Total', 'PromC', 'PromP'],
            colModel: [
                {name: 'clave', index: 'clave', width: 100, editable: false, sortable: false},
                {name: 'articulo', index: 'articulo', width: 300, editable: false, sortable: false},
                {name: 'InvInicial', index: 'InvInicial', width: 100, editable: false, sortable: false, align: 'right'},
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
                liquidacion: true,
                agente: $("#agentes_list").val()

            },
            mtype: 'POST',
            colNames: ["Acciones", 'Id Venta', 'DO', 'Ruta', 'Operación', 'Fecha', 'F. Compromiso', 'Folio', 'Cliente', 'Cliente', 'Responsable', 'Nombre Comercial', 'Status', 'Tipo', 'Método de Pago', 'Importe', 'IVA', 'Descuento', 'Total', 'Abono', 'Saldo Final', 'Total C', 'Total P', 'PromoC', 'PromoP', 'Obseq C', 'Obseq P', 'Vendedor', 'Ayudante 1', 'Ayudante 2', 'Promociones', 'Tiene Promoción'],
            colModel: [
                {
                    name: 'myac',
                    index: '',
                    width: 80,
                    fixed: true,
                    sortable: false,
                    resize: false,
                    formatter: imageFormat,
                    hidden: true
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
                {
                    name: 'fecha',
                    index: 'fecha',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'center',
                    hidden: true
                },//5
                {
                    name: 'fechac',
                    index: 'fechac',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'center',
                    hidden: true
                },//6
                {name: 'folio', index: 'folio', width: 100, editable: false, sortable: false, hidden: true},//7
                {
                    name: 'cliente',
                    index: 'cliente',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//8
                {name: 'cliente', index: 'cliente', width: 100, editable: false, sortable: false, align: 'right'},//8
                {name: 'responsable', index: 'responsable', width: 150, editable: false, sortable: false, hidden: true},//9
                {
                    name: 'nombre_comercial',
                    index: 'nombre_comercial',
                    width: 150,
                    editable: false,
                    sortable: false,
                    hidden: true
                },//10
                {name: 'status', index: 'status', width: 80, editable: false, sortable: false, hidden: true},//11
                {name: 'tipo', index: 'tipo', width: 80, editable: false, sortable: false, hidden: true},//12
                {name: 'metodo_pago', index: 'metodo_pago', width: 110, editable: false, sortable: false, hidden: true},//13
                {name: 'importe', index: 'importe', width: 100, editable: false, sortable: false, align: 'right'},//14
                {name: 'iva', index: 'iva', width: 100, editable: false, sortable: false, align: 'right'},//15
                {name: 'descuento', index: 'descuento', width: 100, editable: false, sortable: false, align: 'right'},//16
                {name: 'total', index: 'total', width: 100, editable: false, sortable: false, align: 'right'},//17
                {
                    name: 'abono',
                    index: 'abono',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//18
                {
                    name: 'saldofinal',
                    index: 'saldofinal',
                    width: 100,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//19
                {
                    name: 'cajas_total',
                    index: 'cajas_total',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right'
                },//20
                {
                    name: 'piezas_total',
                    index: 'piezas_total',
                    width: 80,
                    editable: false,
                    sortable: false,
                    align: 'right'
                },//21
                {name: 'PromoC', index: 'PromoC', width: 80, editable: false, sortable: false, align: 'right'},//22
                {name: 'PromoP', index: 'PromoP', width: 80, editable: false, sortable: false, align: 'right'},//23
                {name: 'ObseqC', index: 'ObseqC', width: 80, editable: false, sortable: false, align: 'right'},//24
                {name: 'ObseqP', index: 'ObseqP', width: 80, editable: false, sortable: false, align: 'right'},//25
                {name: 'vendedor', index: 'vendedor', width: 120, editable: false, sortable: false, hidden: true},//26
                {name: 'ayudante1', index: 'ayudante1', width: 120, editable: false, sortable: false, hidden: true},//27
                {name: 'ayudante2', index: 'ayudante2', width: 120, editable: false, sortable: false, hidden: true},//28
                {
                    name: 'promociones',
                    index: 'promociones',
                    width: 120,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                },//29
                {
                    name: 'tienepromo',
                    index: 'tienepromo',
                    width: 120,
                    editable: false,
                    sortable: false,
                    align: 'right',
                    hidden: true
                }//30
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'ID_Venta',
            viewrecords: true,
            loadError: function (data) {
                console.log("ERROR: ", data);
            },
            sortorder: "desc",
            loadComplete: function (data) {
                console.log("Ventas: ", data);

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

        function imageFormat(cellvalue, options, rowObject) {
            var id_venta = rowObject[1];
            var clave_cia = <?php echo $_SESSION['cve_cia']; ?>;
            var folio = rowObject[7],
                cliente = rowObject[8],
                responsable = rowObject[9],
                tipo = rowObject[12],
                operacion = rowObject[4],
                sucursal = $("#almacenes option:selected").text(),
                dia_operativo = rowObject[2],
                ruta = rowObject[3],
                tienepromo = rowObject[30],
                vendedor = rowObject[26];

            var html = '';
            //html = '<a href="#" onclick="ver(\''+id_venta+'\', \''+folio+'\', \''+cliente+'\', \''+responsable+'\', \''+operacion+'\', \''+sucursal+'\', \''+dia_operativo+'\', \''+ruta+'\', \''+vendedor+'\', \''+tienepromo+'\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //html += '<a href="/api/koolreport/export/reportes/ventas/ventas_sfa/?id_venta='+id_venta+'&cve_cia='+clave_cia+'&folio='+folio+'" target="_blank"><i class="fa fa-file-pdf-o" title="Reporte de Venta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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

    $("#diao_list, #operacion_list").change(function () {
        console.log('cambio de dia operativo o tipo de operacion');
        Swal.fire(
            'Espere un momento',
            'Estamos analizando la información',
            'info'
        );
        Swal.showLoading();
        console.log('llenarTablasResumen()');
        llenarTablasResumen();

    });

    var datosReporteXLS = [];

    function llenarTablasResumen() {
        datosReporteXLS = [];
        var ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            almacen = $("#almacenes").val()
        cve_cia = <?php echo $_SESSION['cve_cia']; ?>;
        var dataResumen = [];
        console.log('GO llenarTablasResumen()');
        $.ajax({
            url: "/api/reportesRutas/lista/liquidacion.php",
            type: "POST",
            dataType: "json",
            async: true,
            data: {
                cve_cia: cve_cia,
                ruta: ruta,
                diao: diao,
                almacen: almacen,
                accion: "obtener_reporte_liquidacion"
            },
            always: function (data) {
                console.log('DATA ALWAYS = ', data);
            },
            success: function (data) {
                console.log('DATA SUCCESS = ', data);
                if (data.status == "success") {
                    console.log(data.data);
                    dataResumen = data.data.resumen;
                    var totalResumen = 0;
                    var datosResumen = {
                        'preventa': dataResumen['preventa'] ? dataResumen['preventa'] : 0,
                        'venta_contado': dataResumen['venta_contado'] ? dataResumen['venta_contado'] : 0,
                        'venta_credito': dataResumen['venta_credito'] ? dataResumen['venta_credito'] : 0,
                        'devoluciones': dataResumen['devoluciones'] ? dataResumen['devoluciones'] : 0,
                        'cobranza': dataResumen['cobranza'] ? dataResumen['cobranza'] : 0,
                        'descuentos_vp': dataResumen['descuentos_vp'] ? dataResumen['descuentos_vp'] : 0,
                        'descuentos_credito': dataResumen['descuentos_credito'] ? dataResumen['descuentos_credito'] : 0
                    };
                    for (var i in datosResumen) {
                        if (typeof datosResumen[i] === 'string' && datosResumen[i].includes(',')) {
                            datosResumen[i] = datosResumen[i].replace(/,/g, '');
                        }
                    }

                    console.log('resumen', datosResumen);
                    totalResumen = parseFloat(dataResumen['preventa']) + parseFloat(dataResumen['venta_contado']) + parseFloat(dataResumen['venta_credito']) + parseFloat(dataResumen['devoluciones']) + parseFloat(dataResumen['cobranza']) + parseFloat(dataResumen['descuentos_vp']) - parseFloat(dataResumen['descuentos_credito']);

                    console.log('TotalResumen', totalResumen);

                    if ($.fn.DataTable.isDataTable('#tabla-resumen')) {
                        $('#tabla-resumen').DataTable().destroy();
                    }

                    dataDevoluciones = data.data.devoluciones;
                    var totalDevolucionesCajas = 0;
                    var totalDevolucionesPiezas = 0;
                    var totalDevolucionesImporte = 0;
                    //quitamos las comas de los numeros
                    for (var i = 0; i < dataDevoluciones.length; i++) {
                        if (typeof dataDevoluciones[i]['Cajas'] === 'string' && dataDevoluciones[i]['Cajas'].includes(',')) {
                            dataDevoluciones[i]['Cajas'] = dataDevoluciones[i]['Cajas'].replace(/,/g, '');
                        }
                        if (typeof dataDevoluciones[i]['Piezas'] === 'string' && dataDevoluciones[i]['Piezas'].includes(',')) {
                            dataDevoluciones[i]['Piezas'] = dataDevoluciones[i]['Piezas'].replace(/,/g, '');
                        }
                        if (typeof dataDevoluciones[i]['Importe'] === 'string' && dataDevoluciones[i]['Importe'].includes(',')) {
                            dataDevoluciones[i]['Importe'] = dataDevoluciones[i]['Importe'].replace(/,/g, '');
                        }
                    }
                    for (var i = 0; i < dataDevoluciones.length; i++) {
                        totalDevolucionesCajas += parseFloat(dataDevoluciones[i]['Cajas']);
                        totalDevolucionesPiezas += parseFloat(dataDevoluciones[i]['Piezas']);
                        totalDevolucionesImporte += parseFloat(dataDevoluciones[i]['Importe']);
                    }
                    if ($.fn.DataTable.isDataTable('#tabla-devoluciones')) {
                        $('#tabla-devoluciones').DataTable().destroy();
                    }
                    $('#tabla-devoluciones').DataTable({
                        responsive: true,
                        autoWidth: true,
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        data: dataDevoluciones, // Envuelve el objeto en un array
                        columns: [
                            {data: 'cve_articulo'},
                            {data: 'Articulo'},
                            {data: 'Cajas'},
                            {data: 'Piezas'},
                            {data: 'Importe'}
                        ],
                        columnDefs: [
                            {
                                targets: 4,
                                className: 'dt-body-right',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            },
                            {
                                targets: [2, 3],
                                className: 'dt-body-center',
                            },
                        ],
                    });
                    $('#totalDevoluciones').html('$' + totalDevolucionesImporte);
                    $('#totalDevolucionesCaja').html(totalDevolucionesCajas);
                    $('#totalDevolucionesPieza').html(totalDevolucionesPiezas);
                    dataCobranza = data.data.creditos;
                    var totalCredito = 0;
                    var totalSaldo = 0;
                    //quitamos las comas de los numeros
                    for (var i = 0; i < dataCobranza.length; i++) {
                        if (typeof dataCobranza[i]['limite_credito'] === 'string' && dataCobranza[i]['limite_credito'].includes(',')) {
                            dataCobranza[i]['limite_credito'] = dataCobranza[i]['limite_credito'].replace(/,/g, '');
                        }
                        if (typeof dataCobranza[i]['saldo'] === 'string' && dataCobranza[i]['saldo'].includes(',')) {
                            dataCobranza[i]['saldo'] = dataCobranza[i]['saldo'].replace(/,/g, '');
                        }
                    }
                    for (var i = 0; i < dataCobranza.length; i++) {
                        totalCredito += parseFloat(dataCobranza[i]['limite_credito']);
                        totalSaldo += parseFloat(dataCobranza[i]['saldo']);
                    }
                    if ($.fn.DataTable.isDataTable('#tabla-credito-cobranza')) {
                        $('#tabla-credito-cobranza').DataTable().destroy();
                    }
                    $('#tabla-credito-cobranza').DataTable({
                        responsive: true,
                        autoWidth: true,
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        data: dataCobranza, // Envuelve el objeto en un array
                        columns: [
                            {data: 'Documento'},
                            {data: 'Cliente'},
                            {data: 'limite_credito'},
                            {data: 'saldo'}
                        ],
                        columnDefs: [
                            {
                                targets: [2, 3],
                                className: 'dt-body-right',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            },
                        ],
                    });
                    $('#totalCreditos').html('$' + totalCredito.toFixed(2));
                    $('#totalCobranza').html('$' + totalSaldo.toFixed(2));
                    dataAnalisis = data.data.analisis;
                    var totalCajaStockInicial = 0;
                    var totalPiezaStockInicial = 0;
                    var totalVentasCaja = 0;
                    var totalVentasPieza = 0;
                    var totalPreventaCaja = 0;
                    var totalPreventaPieza = 0;
                    var totalEntregaCaja = 0;
                    var totalEntregaPieza = 0;
                    var totalRecargaCaja = 0;
                    var totalRecargaPieza = 0;
                    var totalDevolucionCaja = 0;
                    var totalDevolucionPieza = 0;
                    var totalPromocionesCaja = 0;
                    var totalPromocionesPieza = 0;
                    var totalPedidoCajaPreventa = 0;
                    var totalPedidoPiezaPreventa = 0;
                    var totalCajaStockFinal = 0;
                    var totalPiezaStockFinal = 0;
                    var totalFinal = 0;
                    var total_descuento = 0;

                    for (var i = 0; i < dataAnalisis.length; i++) {
                        removeCommas(dataAnalisis[i]);
                        totalFinal += parseFloat(dataAnalisis[i]['total_articulo']);
                        totalVentasCaja += parseFloat(dataAnalisis[i]['total_venta_caja']);
                        totalVentasPieza += parseFloat(dataAnalisis[i]['total_venta_pieza']);
                        totalPreventaCaja += parseFloat(dataAnalisis[i]['total_preventa_caja']);
                        totalPreventaPieza += parseFloat(dataAnalisis[i]['total_preventa_pieza']);
                        totalEntregaCaja += parseFloat(dataAnalisis[i]['total_entrega_caja']);
                        totalEntregaPieza += parseFloat(dataAnalisis[i]['total_entrega_pieza']);
                        totalRecargaCaja += parseFloat(dataAnalisis[i]['total_recarga_caja']);
                        totalRecargaPieza += parseFloat(dataAnalisis[i]['total_recarga_pieza']);
                        totalDevolucionCaja += parseFloat(dataAnalisis[i]['total_devolucion_caja']);
                        totalDevolucionPieza += parseFloat(dataAnalisis[i]['total_devolucion_pieza']);
                        totalPromocionesCaja += parseFloat(dataAnalisis[i]['total_promociones_caja']);
                        totalPromocionesPieza += parseFloat(dataAnalisis[i]['total_promociones_pieza']);
                        totalCajaStockInicial += parseFloat(dataAnalisis[i]['stock_inicial_caja']);
                        totalPiezaStockInicial += parseFloat(dataAnalisis[i]['stock_inicial_pieza']);
                        totalCajaStockFinal += parseFloat(dataAnalisis[i]['stock_final_caja']);
                        totalPiezaStockFinal += parseFloat(dataAnalisis[i]['stock_final_pieza']);
                        totalPedidoCajaPreventa += parseFloat(dataAnalisis[i]['total_pedido_caja_preventa']);
                        totalPedidoPiezaPreventa += parseFloat(dataAnalisis[i]['total_pedido_pieza_preventa']);
                        total_descuento += parseFloat(dataAnalisis[i]['total_descuento']);
                        if (parseFloat(dataAnalisis[i]['total_entrega_caja']) > 0) {
                            dataAnalisis[i]['total_entrega_caja'] = parseFloat(dataAnalisis[i]['total_entrega_caja']) - parseFloat(dataAnalisis[i]['total_promociones_caja']);
                        }
                        if (parseFloat(dataAnalisis[i]['total_entrega_pieza']) > 0) {
                            dataAnalisis[i]['total_entrega_pieza'] = parseFloat(dataAnalisis[i]['total_entrega_pieza']) - parseFloat(dataAnalisis[i]['total_promociones_pieza']);
                        }
                        parseFloat(dataAnalisis[i]['stock_final_caja']) > 0 ? dataAnalisis[i]['stock_final_caja'] = parseFloat(dataAnalisis[i]['stock_final_caja']) - parseFloat(dataAnalisis[i]['total_promociones_caja']) - parseFloat(dataAnalisis[i]['total_devolucion_caja']) - parseFloat(dataAnalisis[i]['total_entrega_caja']) : dataAnalisis[i]['stock_final_caja'] = 0;
                        parseFloat(dataAnalisis[i]['stock_final_pieza']) > 0 ? dataAnalisis[i]['stock_final_pieza'] = parseFloat(dataAnalisis[i]['stock_final_pieza']) - parseFloat(dataAnalisis[i]['total_promociones_pieza']) - parseFloat(dataAnalisis[i]['total_devolucion_pieza']) - parseFloat(dataAnalisis[i]['total_entrega_pieza']) : dataAnalisis[i]['stock_final_pieza'] = 0;
                    }
                    if ($.fn.DataTable.isDataTable('#analisis-ventas')) {
                        $('#analisis-ventas').DataTable().destroy();
                    }
                    var articulos = [];
                    $('#analisis-ventas').DataTable({
                        responsive: true,
                        autoWidth: true,
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        data: dataAnalisis,
                        columns: [
                            {
                                data: function (row) {
                                    return row.cve_articulo + ' - ' + row.Articulo;
                                },
                                width: '20%'

                            },
                            {data: 'stock_inicial_caja'},
                            {data: 'stock_inicial_pieza'},
                            {data: 'total_venta_caja'},
                            {data: 'total_venta_pieza'},
                            {data: 'total_preventa_caja'},
                            {data: 'total_preventa_pieza'},
                            {data: 'total_entrega_caja'},
                            {data: 'total_entrega_pieza'},
                            {data: 'total_recarga_caja'},
                            {data: 'total_recarga_pieza'},
                            {data: 'total_devolucion_caja'},
                            {data: 'total_devolucion_pieza'},
                            {data: 'total_promociones_caja'},
                            {data: 'total_promociones_pieza'},
                            {data: 'total_pedido_caja_preventa'},
                            {data: 'total_pedido_pieza_preventa'},
                            {data: 'total_articulo'},
                            {data: 'stock_final_caja'},
                            {data: 'stock_final_pieza'},
                            {data: 'total_pedido_caja_preventa'},
                            {data: 'total_pedido_pieza_preventa'},
                        ]
                    });
                    var totalArticulos = 0;
                    for (var i = 0; i < dataAnalisis.length; i++) {
                        articulos.push({
                            articulo: dataAnalisis[i]['Articulo'],
                            cve_articulo: dataAnalisis[i]['cve_articulo'],
                            total_articulo: dataAnalisis[i]['total_articulo'],
                            stock_inicial_caja: dataAnalisis[i]['stock_inicial_caja'],
                            stock_inicial_pieza: dataAnalisis[i]['stock_inicial_pieza'],
                            total_venta_caja: dataAnalisis[i]['total_venta_caja'],
                            total_venta_pieza: dataAnalisis[i]['total_venta_pieza'],
                            total_promociones_caja: dataAnalisis[i]['total_promociones_caja'],
                            total_promociones_pieza: dataAnalisis[i]['total_promociones_pieza'],
                            total_cajas_final: dataAnalisis[i]['stock_final_caja'],
                            total_piezas_final: dataAnalisis[i]['stock_final_pieza'],
                            total_descuento: dataAnalisis[i]['total_descuento'],
                            total_cajas_entrega: dataAnalisis[i]['total_entrega_caja'],
                            total_piezas_entrega: dataAnalisis[i]['total_entrega_pieza'],
                            total_cajas_devolucion: dataAnalisis[i]['total_devolucion_caja'],
                            total_piezas_devolucion: dataAnalisis[i]['total_devolucion_pieza'],
                            total_cajas_recarga: dataAnalisis[i]['total_recarga_caja'],
                            total_piezas_recarga: dataAnalisis[i]['total_recarga_pieza'],
                            total_cajas_preventa: dataAnalisis[i]['total_preventa_caja'],
                            total_piezas_preventa: dataAnalisis[i]['total_preventa_pieza'],
                            total_cajas_pedido: dataAnalisis[i]['total_pedido_caja_preventa'],
                            total_piezas_pedido: dataAnalisis[i]['total_pedido_pieza_preventa'],
                        });
                        totalArticulos += parseFloat(dataAnalisis[i]['total_articulo']);
                    }
                    datosReporteXLS['articulos'] = articulos;
                    datosResumenSimplificado = {
                        'Total': totalArticulos > 0 ? (dataResumen['venta_contado'] + dataResumen['preventa'] + dataResumen['venta_credito'] + dataResumen['devoluciones'] + dataResumen['cobranza'] - total_descuento): 0,
                        'preventa': dataResumen['preventa'] ? dataResumen['preventa'] : 0,
                        'venta_credito': dataResumen['venta_credito'] ? dataResumen['venta_credito'] : 0,
                        'descuentos_vp': total_descuento ? total_descuento : 0,
                        'venta_contado': dataResumen['venta_contado'] ? dataResumen['venta_contado'] : 0,
                        'devoluciones': dataResumen['devoluciones'] ? dataResumen['devoluciones'] : 0,
                        'cobranza': dataResumen['cobranza'] ? dataResumen['cobranza'] : 0,
                        'total_a_liquidar': totalArticulos > 0 ? (dataResumen['venta_contado'] + dataResumen['preventa'] /*+ dataResumen['venta_credito']*/ + dataResumen['devoluciones'] + dataResumen['cobranza'] - total_descuento + dataResumen['descuentos_credito']) : 0,
                    };

                    $('#tabla-resumen').DataTable({
                        responsive: true,
                        autoWidth: true,
                        language: {
                            url: '/assets/plugins/DataTable/spanish.json'
                        },
                        searching: false,
                        data: [datosResumenSimplificado], // Envuelve el objeto en un array
                        columns: [
                            {data: 'Total'},
                            {data: 'preventa'},
                            {data: 'venta_contado'},
                            {data: 'venta_credito'},
                            {data: 'devoluciones'},
                            {data: 'cobranza'},
                            {data: 'descuentos_vp'}
                        ],
                        columnDefs: [
                            {
                                targets: '_all',
                                className: 'dt-body-right',
                                render: $.fn.dataTable.render.number(',', '.', 2, '$')
                            },
                        ],
                    });
                    $('#totalResumen').html('');
                    $('#totalResumen').html('Total a liquidar: $' + datosResumenSimplificado['total_a_liquidar'].toFixed(2));
                    datosReporteXLS['resumen'] = datosResumenSimplificado;
                    $('#totalStockInicialCaja').text(totalCajaStockInicial.toFixed(2));
                    $('#totalStockInicialPieza').text(totalPiezaStockInicial.toFixed(2));
                    $('#totalVentasCaja').text(totalVentasCaja.toFixed(2));
                    $('#totalVentasPieza').text(totalVentasPieza.toFixed(2));
                    $('#totalPreventaCaja').text(totalPreventaCaja.toFixed(2));
                    $('#totalPreventaPieza').text(totalPreventaPieza.toFixed(2));
                    $('#totalEntregaCaja').text(totalEntregaCaja.toFixed(2));
                    $('#totalEntregaPieza').text(totalEntregaPieza.toFixed(2));
                    $('#totalRecCaja').text(totalRecargaCaja.toFixed(2));
                    $('#totalRecPieza').text(totalRecargaPieza.toFixed(2));
                    $('#totalDevCaja').text(totalDevolucionCaja.toFixed(2));
                    $('#totalDevPieza').text(totalDevolucionPieza.toFixed(2));
                    $('#totalPromCaja').text(totalPromocionesCaja.toFixed(2));
                    $('#totalPromPieza').text(totalPromocionesPieza.toFixed(2));
                    $('#totalPromPrevCaja').text(totalPedidoCajaPreventa.toFixed(2));
                    $('#totalPromPrevPieza').text(totalPedidoPiezaPreventa.toFixed(2));
                    $('#totalVenta').text('$' + totalFinal.toFixed(2));
                    $('#totalStockFinalCaja').text(totalCajaStockFinal.toFixed(2));
                    $('#totalStockFinalPieza').text(totalPiezaStockFinal.toFixed(2));
                    $('#totalPedidoCaja').text(totalPedidoCajaPreventa.toFixed(2));
                    $('#totalPedidoPieza').text(totalPedidoPiezaPreventa.toFixed(2));
                }
                setTimeout(function () {
                    Swal.close();
                }, 500);
            },
            error: function (data) {
                console.log('DATA ERROR = ', data);
                setTimeout(function () {
                    Swal.close();
                }, 500);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar la tabla',
                    text: 'No se pudo cargar la tabla correctamente, contacte al administrador del sistema'
                });
            }
        });
    }

    $('#excelExportButton').on('click', function () {
        if (datosReporteXLS.hasOwnProperty('articulos') && datosReporteXLS.hasOwnProperty('resumen')) {
            console.log(datosReporteXLS);
            $.ajax({
                url: '/api/koolreport/export/reportes/ventas/ventas_sfa/ExportExcelLiquidacionService.php',
                type: 'POST',
                data: {
                    datosReporteArticulo: datosReporteXLS['articulos'],
                    datosReporteResumen: datosReporteXLS['resumen']
                },
                success: function (data) {
                    var link = document.createElement('a');
                    //href excel
                    link.href = "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," + data;
                    link.target = '_blank';
                    link.download = 'reporte_liquidacion.xlsx';
                    link.dispatchEvent(new MouseEvent('click'));

                },
                error: function (res) {
                    swal("Error", 'Error al generar el reporte', "error");
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al exportar',
                text: 'No se pudo exportar el reporte, no hay datos para exportar'
            });
        }
    });

    function removeCommas(obj) {
        for (var prop in obj) {
            console.log('obj[prop] = ', obj[prop]);
            if (typeof obj[prop] === 'string' && obj[prop].includes(',')) {
                obj[prop] = obj[prop].replace(/,/g, '');
            } else {
                obj[prop] = obj[prop];
            }
        }
    }

    $("#rutas_list").change(function () {
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
        //setTimeout(function()}, 2000);
    });

    // function ReloadGrid() {
    //     console.log("almacen INIT: ", $("#almacenes").val());
    //     console.log("criterio: ", $("#txtCriterio").val());
    //     console.log("ruta: ", $("#rutas_list").val());
    //     console.log("diao: ", $("#diao_list").val());
    //     console.log("operacion: ", $("#operacion_list").val());
    //
    //     setTimeout(function () {
    //         $('#grid-table').jqGrid('clearGridData')
    //             .jqGrid('setGridParam', {
    //                 postData: {
    //                     criterio: $("#txtCriterio").val(),
    //                     ruta: $("#rutas_list").val(),
    //                     diao: $("#diao_list").val(),
    //                     operacion: $("#operacion_list").val(),
    //                     agente: $("#agentes_list").val(),
    //                     liquidacion: true,
    //                     almacen: $("#almacenes").val()
    //                 },
    //                 datatype: 'json',
    //                 page: 1
    //             })
    //             .trigger('reloadGrid', [{
    //                 current: true
    //             }]);
    //     }, 2000);
    // }

    function almacen() {
        $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    criterio: $("#txtCriterio").val(),
                    ruta: $("#rutas_list").val(),
                    diao: $("#diao_list").val(),
                    operacion: $("#operacion_list").val(),
                    liquidacion: true,
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

    // function ReloadGrid1() {
    //     $('#grid-table2').jqGrid('clearGridData')
    //         .jqGrid('setGridParam', {
    //             postData: {
    //                 criterio: $("#txtCriterio1").val(),
    //             },
    //             datatype: 'json',
    //             page: 1
    //         })
    //         .trigger('reloadGrid', [{
    //             current: true
    //         }]);
    // }

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

        $("#ver_detalles").modal("show");
        loadDetalles(id, tienepromo, folio, dia_operativo, ruta, operacion);
    }

    function loadDetalles(id, tienepromo, folio, dia_operativo, ruta, operacion) {
        console.log("id_venta = ", id);
        console.log("tienepromo = ", tienepromo);
        console.log("folio = ", folio);
        console.log("dia_operativo = ", dia_operativo);
        console.log("ruta = ", ruta);
        console.log("operacion = ", operacion);
        $('#grid-table_detalles').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    id_venta: id,
                    folio: folio,
                    ruta: ruta,
                    diao: dia_operativo,
                    operacion: operacion,
                    liquidacion: true,
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
                        liquidacion: true,
                        ruta: ruta,
                        diao: dia_operativo,
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

    $("#generarExcel").click(function () {

        if ($("#diao_list").val() == "" || $("#rutas_list").val() == "") {
            swal("Error", "Debe Seleccionar una Ruta y un Día Operativo", "error");
            return;
        }

        var ruta = $("#rutas_list").val(),
            diao = $("#diao_list").val(),
            almacen = $("#almacenes").val()
        cve_cia = <?php echo $_SESSION['cve_cia']; ?>;
        //operacion = $("#operacion_list").val(),
        //clientes  = $("#clientes_list").val(),
        //fechaini  = $("#fechaini").val(),
        //fechafin  = $("#fechafin").val(),
        //articulos = $("#articulos_list").val(),
        //articulos_obsq = $("#articulos_obsq_list").val(),
        //tipoV = $("#tipo_list").val();

        console.log("ruta =", $("#rutas_list").val());
        console.log("diao =", $("#diao_list").val());
        console.log("almacen =", $("#almacenes").val());
        console.log("/api/koolreport/export/reportes/ventas/liquidacion/?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&cve_cia=" + cve_cia);

        $(this).attr("href", "/api/koolreport/export/reportes/ventas/liquidacion/?almacen=" + almacen + "&ruta=" + ruta + "&diao=" + diao + "&cve_cia=" + cve_cia);
        $(this).attr("target", "_blank");

    });

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

    $("#txtCriterio").keyup(function (event) {
        if (event.keyCode == 13) {
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