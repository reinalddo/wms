<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();
$listaProv = new \Proveedores\Proveedores();

$listTransportes = new \Transporte\Transporte();
$vendedores = new \Usuarios\Usuarios();


$cve_almacen = $_SESSION['cve_almacen'];
$listTransportes = \db()->prepare("SELECT *  FROM t_transporte WHERE Activo = 1 and id_almac = (SELECT id FROM c_almacenp WHERE clave = '$cve_almacen')  AND id NOT IN (SELECT id_transporte FROM Rel_Ruta_Transporte)");
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



$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}

$cve_usuario_logueado = $_SESSION['cve_usuario'];
?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
<input type="hidden" id="cve_usuario_logueado" value="<?php echo $cve_usuario_logueado; ?>">
<input type="hidden" id="ruta_agente" value="">


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


<div class="modal fade" id="modal_detalle" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Número de Clientes | Visitas | Ruta: (<span id="ruta_detalle"></span>)</h4>
                </div>
                <div class="modal-body">
                    <div class="ibox-content">
                        <div class="jqGrid_wrapper">
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
<script src="/js/dragdrop.js"></script>
   <script src="/js/plugins/iCheck/icheck.min.js"></script>

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
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right 
    {
        position: absolute;
        left: auto;
        right: 0;
    }

#clientesEditar li{
        cursor: pointer;
}

.wi{
    width: 90% !important;
}
.relative{
    position: relative;
}
.floating-button{
    position: absolute;
    right: 0;
    top: 40%;
    transform: translateY(-40%);
}
[aria-grabbed="true"]
{
    background: #1ab394 !important;
    color: #fff !important;
}
.input-group-addon {
    padding: 0px;
}

</style>

<input type="hidden" id="clave_almacen" value="<?php echo $_SESSION['cve_almacen']; ?>">
<div class="modal inmodal" id="asignar_rutas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 style="font-size: 18px;" class="modal-title">Asignar Rutas</h4>
            </div>
            <div class="modal-body" style="height: 370px;">
                <div class="ibox-content dragdrop">
                    
                    <div class="col-sm-12" style="margin-bottom: 20px">
                        <input type="checkbox" id="selectAll" >
                        <label for="selectAll">Seleccionar Todo</label>
                    </div>                                   
                    <div class="row" id="clientesEditar">
                        <div class="col-md-6" relative>
                            <label for="email">Rutas Disponibles</label>
                            <ol data-draggable="target" id="from" class="wi"></ol>
                            <button class="btn btn-primary floating-button" onclick="add('#from', '#to')">>></button>
                            <button class="btn btn-primary floating-button" onclick="remove('#to', '#from')" style="margin-top: 40px"><<</button>
                        </div>
                        <div class="col-md-6">
                            <label for="email">Rutas Asignadas</label>
                            <ol data-draggable="target" id="to" class="wi"></ol>
                        </div>
                    </div>
                
                 </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="myModalClientes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizar()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Asignar clientes a Ruta <span></span></h4>
            </div>
            <div class="modal-body">
                <table class="footable table table-stripped toggle-arrow-tiny" data-paging="true" data-filtering="true" data-sorting="true" data-expand-first="true" data-paging-size="3">
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
                <a href="#" onclick="minimizar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" onclick="minimizar()" style="width: auto;">Seleccionar Clientes</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="ruta_clave_select" value="">
<div class="modal inmodal" id="modalTransportes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarT()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Asignar Transporte a Ruta <span id="ruta_clave"></span></h4>
            </div>
            <div class="modal-body">
                <select class="form-control chosen-select" id="transportes">
                    <option value="">Seleccione Transporte</option>
               <?php foreach( $listTransportes AS $r ): ?>
                   <option value="<?php echo $r['id']; ?>"><?php echo "( ".$r['ID_Transporte']." ) "." - ".$r['Nombre']."-".$r['Placas']; ?></option>
                <?php endforeach;  ?>
                </select>
            </div>
            <div class="modal-footer">
                <a href="#" onclick="minimizarT()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger ladda-button" data-style="contract" onclick="EliminarTransporte();" style="width: auto;">Eliminar</button>
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" onclick="AsignarTransporte();" style="width: auto;">Asignar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="modalChofer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarChofer()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
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
                <a href="#" onclick="minimizarChofer()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
                <button type="button" class="btn btn-primary ladda-button" data-style="contract" onclick="AsignarChofer();" style="width: auto;">Asignar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="modalChoferEliminar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarChofer()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Eliminar Operador <span id="ruta_claveEliminar"></span></h4>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label for="EliminarAgentes">Eliminar Agente | Operador:</label> 
                    <select class="form-control" id="EliminarAgentes" name="EliminarAgentes">
                        <?php 
                        /*
                        chosen-select
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
                <a href="#" onclick="minimizarChofer()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger ladda-button" data-style="contract" onclick="EliminarChofer();" style="width: auto;">Eliminar</button>
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="modalClientes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="minimizarCliente()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
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
                <a href="#" onclick="minimizarCliente()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                <button type="button" class="btn btn-danger ladda-button" data-style="contract" onclick="EliminarCliente();" style="width: auto;">Eliminar</button>
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
                                        <option value="">Almacen </option>
                                        <?php foreach( $listaNCompa->getAll() AS $p ): ?>
                                            <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <label>Clave de la Ruta *</label>
                                <input id="cve_ruta" type="text" placeholder="Clave de la Ruta" class="form-control" required="true">
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
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
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
                                <div class="col-md-6 b-r">
                                    <label>Control Contenedores | Pallets</label>
                                    <input type="checkbox" id="envases_ruta" name="envases_ruta" value="1" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                    <label class="form-check-label" for="venta_preventa">
                                    <input type="radio" id="venta_preventa" name="venta_preventa" value="1" class="form-check-input">
                                    Venta | Preventa</label>
                                    </div>
                                    <div class="form-check">
                                    <label class="form-check-label" for="ruta_entrega">
                                    <input type="radio" id="ruta_entrega" name="venta_preventa" value="2" class="form-check-input">
                                    Ruta Entrega</label>
                                    </div>
                                    <div class="form-check">
                                    <label class="form-check-label" for="ninguno">
                                    <input type="radio" id="ninguno" name="venta_preventa" value="0" class="form-check-input" checked>
                                    Ninguno</label>
                                    </div>

                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Empresa | Proveedor </label>
                                        <select class="form-control" id="cboProveedor">
                                        <option value="">Seleccione</option>
                                        <?php 
                                        foreach( $listaProv->getAll("AND es_cliente = 1") AS $p ): 
                                        ?>
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    </div>
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
    <h3>Rutas</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-sm-3">
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
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="agentes">Rutas:</label> 
                                <select class="form-control chosen-select" id="rutas_list" name="rutas_list">
                                    <option value="">Seleccione Ruta</option>
                                    <?php 
                                    foreach( $listaRuta->getAll($cve_almacen) AS $r ): 
                                    ?>
                                    <option value="<?php echo $r->cve_ruta; ?>"><?php echo "( ".$r->cve_ruta." ) - ".$r->descripcion; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="agentes">Agente | Operador:</label> 
                                <select class="form-control chosen-select" id="agentes_list" name="agentes_list">
                                    <option value="">Seleccione Agente | Operador</option>
                                    <?php 
                                        foreach( $vendedores->getAllVendedorRutas($_SESSION['cve_almacen']) AS $ch ): 
                                        ?>
                                        <option value="<?php echo $ch->cve_Vendedor; ?>"><?php echo "( ".$ch->cve_Vendedor." ) - ".$ch->Nombre; ?></option>
                                    <?php endforeach; ?>

                                </select>
                                <select class="form-control" id="agentes_list2" name="agentes_list2" style="display: none;">
                                    <option value="">Seleccione Agente | Operador</option>
                                    <?php 
                                        foreach( $vendedores->getAllVendedorRutas($_SESSION['cve_almacen']) AS $ch ): 
                                        ?>
                                        <option value="<?php echo $ch->cve_Vendedor; ?>"><?php echo "( ".$ch->cve_Vendedor." ) - ".$ch->Nombre; ?></option>
                                    <?php endforeach; ?>

                                </select>

                            </div>
                        </div>
                        <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Empresa | Proveedor </label>
                                    <select class="form-control" id="cboProveedor_busq">
                                    <?php if($cve_proveedor == ""){ ?>
                                    <option value="">Seleccione</option>
                                    <?php } ?>
                                    <?php 
                                        foreach( $listaProv->getAll("AND es_cliente = 1") AS $p ): 
                                            if($p->ID_Proveedor == $cve_proveedor && $cve_proveedor != '')
                                            {
                                    ?>
                                    <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                    <?php 
                                            }
                                            else if($cve_proveedor == "")
                                            {
                                    ?>
                                    <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                    <?php 
                                            }
                                    ?>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div id="busqueda">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txtCriterio" style="margin: 10px;" id="txtCriterio" placeholder="Buscar...">
                                    <div class="input-group-btn">
                                        <button  onclick="ReloadGrid()" type="submit" id="buscarA" class="btn btn-primary" style="margin: 10px;">
                                            <span class="fa fa-search"></span> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                                <a href="#" onclick="agregar()"><button class="btn btn-primary permiso_registrar" type="button" style="margin: 10px;"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <a href="/api/v2/rutas/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right permiso_registrar" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                            <button class="btn btn-primary pull-right" type="button" id="inactivos" style="margin: 0px;"><i class="fa fa-search"></i>&nbsp;&nbsp;Rutas inactivas</button>
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
                                                      <a href="#" class="permiso_editar"> onclick="editar('<?php echo $p->ID_Ruta; ?>')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                                      <a href="#" class="permiso_eliminar"> onclick="borrar('<?php echo $p->ID_Ruta; ?>')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
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
                    document.getElementById('almacenes').value = data.codigo.id;
                    document.getElementById('cve_almacenp').value = data.codigo.id;

                    $('.chosen-select').chosen();
                    almacen();
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }

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
            url: '/api/ruta/lista/index.php',
            datatype: "json",
            height: 250,
            cache: false,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                ruta: $("#rutas_list").val(),
                agente: $("#agentes_list").val()
            },
            mtype: 'POST',
            colNames: ["Acciones", 'ID', 'Clave', 'Nombre de la Ruta', 'Agente', 'Vehículo', 'No. Clientes', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom', 'Venta | Preventa', 'Ruta Entrega', 'Rutas Asignadas', 'Empresa|Proveedor', 'Control Pallet|Cont', 'Status', 'Cve_Vendedor'],
            colModel: [
                {name: 'myac',index: '',width: 200,fixed: true,sortable: false,resize: false,formatter: imageFormat},
                {name: 'ID_Ruta',index: 'ID_Ruta',width: 0,editable: false,sortable: false,hidden: true},
                {name: 'cve_ruta',index: 'cve_ruta',width: 100,editable: false,sortable: false},
                {name: 'descripcion',index: 'descripcion',width: 200,editable: false,sortable: false, hidden: true},
                {name: 'agente',index: 'agente',width: 150,editable: false,sortable: false},
                {name: 'vehiculo',index: 'vehiculo',width: 80,editable: false,sortable: false},
                {name: 'num_clientes',index: 'num_clientes', align: 'right',width: 100,editable: false,sortable: false, hidden: true},
                {name: 'Lun',index: 'Lun',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'Mar',index: 'Mar',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'Mie',index: 'Mie',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'Jue',index: 'Jue',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'Vie',index: 'Vie',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'Sab',index: 'Sab',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'Dom',index: 'Dom',width: 40,editable: false,sortable: false, align: 'right', hidden: true},
                {name: 'venta_preventa',index: 'venta_preventa',width: 120,editable: false,sortable: false, align: 'center'},
                {name: 'ruta_entrega',index: 'ruta_entrega',width: 120,editable: false,sortable: false, align: 'center'},
                {name: 'cve_ruta_entrega',index: 'cve_ruta_entrega',width: 120,editable: false,sortable: false},
                {name: 'empresa_proveedor',index: 'empresa_proveedor',width: 170,editable: false,sortable: false},
                {name: 'control_pallets_cont',index: 'control_pallets_cont',width: 130,editable: false,sortable: false, align: 'center'},
                {name: 'status',index: 'status',width: 80,editable: false,sortable: false},
                {name: 'cve_vendedor',index: 'cve_vendedor',width: 80,editable: false,sortable: false, hidden: true}
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'ID_Ruta',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: almacenPrede(),
            loadError: function(data){
                console.log("ERROR", data);
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
            var serie = rowObject[1];
            var clave_ruta = rowObject[2];
            var cve_vendedor = rowObject[20];
            //setTimeout(function(){
            if($("#agentes_list").val()!="") $("#ruta_agente").val(clave_ruta);
            //}, 1000);
            $("#hiddenRuta").val(serie);
            var html = '';
            html += '<a href="#" onclick="ver(\''+serie+'\',\''+clave_ruta+'\')"><i class="fa fa-search" title="Ver detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            if($("#permiso_editar").val() == 1)
            {
            html += '<a href="#" onclick="editar(\'' + serie + '\')"><i class="fa fa-edit" alt="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if($("#permiso_eliminar").val() == 1)
            {
            html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" alt="Borrar" title="Eliminar Ruta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="select_clientes(\'' + clave_ruta + '\')"><i class="fa fa-times" alt="Eliminar Clientes Ruta" title="Eliminar Clientes Ruta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if($("#permiso_registrar").val() == 1)
            {
            html += '<a href="#" onclick="select_transporte(\'' + clave_ruta + '\')"><i class="fa fa-truck" alt="Catálogo de Transportes" title="Catálogo de Transportes"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="select_chofer(\'' + clave_ruta + '\', 0, \'' + cve_vendedor + '\')"><i class="fa fa-male" alt="Asignar Chofer" title="Asignar Chofer"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if($("#permiso_eliminar").val() == 1)
            {
            html += '<a href="#" onclick="select_choferEliminar(\'' + clave_ruta + '\', 1, \'' + cve_vendedor + '\')"><i class="fa fa-user-times" alt="Eliminar Chofer" title="Eliminar Chofer"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
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

        function ver(id_ruta, cve_ruta) 
        {

            $('#ruta_detalle').text(cve_ruta);
            $('#modal_detalle').modal('show');

          $('#grid-table3').jqGrid('clearGridData').jqGrid('setGridParam', {postData:
          {
            action: 'loadDetalle',
            id_ruta: id_ruta,
            cve_ruta: cve_ruta

          }, datatype: 'json'}).trigger('reloadGrid',[{current:true}]);
        }

        function ReloadGrid() 
        {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        ruta: $("#rutas_list").val(),
                        agente: $("#agentes_list").val()
                    },
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
                        criterio: $("#txtCriterio").val(),
                        ruta: $("#rutas_list").val(),
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

        $("#rutas_list").change(function(){

            $("#agentes_list").val("");
            $('#agentes_list').trigger("chosen:updated");

            agentes_list($(this).val(), 1);
            ReloadGrid();

            setTimeout(function()
            {
                if($("#rutas_list").val() == "")
                {
                    //console.log($("#agentes_list2").html());
                    $("#agentes_list").empty();
                    $("#agentes_list").append($("#agentes_list2").html());
                    $('#agentes_list').trigger("chosen:updated");
                }
            }
            , 1000
            );

        });

        $("#agentes_list").change(function(){

            $("#rutas_list").val("");
            $('#rutas_list').trigger("chosen:updated");
            ReloadGrid();

            setTimeout(function()
            {
                console.log("ruta_agente = ", $("#ruta_agente").val());
                $("#rutas_list").val($("#ruta_agente").val());
                $('#rutas_list').trigger("chosen:updated");

                if($("#agentes_list").val() == "")
                {
                    //console.log($("#agentes_list2").html());
                    $("#ruta_agente").val("");
                }
            }
            , 1000
            );

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

        function select_transporte(clave_ruta)
        {
            console.log("clave_ruta = ", clave_ruta);
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_clave").text(clave_ruta);
            $('#modalTransportes').show();
        }

        function select_operadores(clave_ruta, asignados_si_no, cve_vendedor)
        {
            console.log("clave_ruta = ", clave_ruta);
            console.log("asignados_si_no = ", asignados_si_no);
            console.log("cve_vendedor = ", cve_vendedor);
            $('#EliminarAgentes').empty();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    Cve_Ruta: clave_ruta,
                    asignados_si_no: asignados_si_no,
                    cve_almac: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                    action: "getOperadoresAsignadosRuta"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    console.log("SUCCESS SELECT", data);
                    if(asignados_si_no == 1)
                    {
                        $('#EliminarAgentes').append(data.operadores);
                        //$('#EliminarAgentes').val(cve_vendedor);
                        //$('#EliminarAgentes').trigger("chosen:updated");
                    }
                    else 
                    {
                        $('#agentes, #agentes_list').empty();
                        $('#agentes, #agentes_list').append(data.operadores);
                        $('#agentes, #agentes_list').trigger("chosen:updated");
                    }
                }, error: function(data){
                    console.log("ERROR SELECT", data);
                }
            });
        }

        function agentes_list(clave_ruta, asignados_si_no)
        {
            console.log("clave_ruta = ", clave_ruta);
            console.log("asignados_si_no = ", asignados_si_no);

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    Cve_Ruta: clave_ruta,
                    asignados_si_no: asignados_si_no,
                    cve_almac: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                    action: "getOperadoresAsignadosRuta"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) 
                    {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/ruta/update/index.php',
                success: function(data) {
                    console.log("SUCCESS SELECT", data);
                    $('#agentes_list').empty();
                    //$('#agentes_list').append('<option value="">Seleccione Agente</option>');
                    $('#agentes_list').append(data.operadores);
                    $('#agentes_list').trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR SELECT", data);
                }
            });
        }

        function select_chofer(clave_ruta, asignados_si_no, cve_vendedor)
        {

            select_operadores(clave_ruta, asignados_si_no, cve_vendedor);
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_claveElimin").text(clave_ruta);
            $('#modalChofer').show();
        }

        function select_choferEliminar(clave_ruta, asignados_si_no, cve_vendedor)
        {
            select_operadores(clave_ruta, asignados_si_no, cve_vendedor);
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_claveEliminar").text(clave_ruta);
            $('#modalChoferEliminar').show();
            //$("#EliminarAgentes").val(cve_vendedor);
            //$('#EliminarAgentes').trigger("chosen:updated");
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

            //if($("#cve_usuario_logueado").val() != 'wmsmaster')
            //    $('input:radio[name=venta_preventa]').prop('disabled', true);
            if($("#permiso_editar").val() != 1)
                $('input:radio[name=venta_preventa]').prop('disabled', true);
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
                            $("#ninguno").prop('checked', true);
                        else if(data.venta_preventa == 1)
                            $("#venta_preventa").prop('checked', true);
                        else 
                            $("#ruta_entrega").prop('checked', true);

                        if(data.control_pallets_cont == 'N')
                            $("#envases_ruta").prop('checked', false);
                        else 
                            $("#envases_ruta").prop('checked', true);


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
                        $("#cboProveedor").val(data.ID_Proveedor);
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
                }, error: function(data)
                {
                    console.log("ERROR Edit: ", data);
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
        $('input:radio[name=venta_preventa]').prop('disabled', false);
        $(':input', '#FORM')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');

        $('#ninguno').prop('checked', true);

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
        //$("#cve_almacenp").val("");
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
        console.log("Ruta = ", $("#ruta_clave_select").val(), "Transpote = ", $('#transportes').val());
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
                cache: false,
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
  /*
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
*/
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

                        //});
        }
        
    }

    function minimizarChofer() 
    {
        $('#modalChofer').hide();
        $('#modalChoferEliminar').hide();
    }

    function minimizarCliente() 
    {
        $('#modalClientes').hide();
    }


    function GuardarRuta()
    {
        if ($("#cve_almacenp").val() == "") return;
        if ($("#cve_ruta").val() == "") return;
        if ($("#descripcion").val() == "") return;

        if($("#cboProveedor").val() == "")
        {
            swal("Error", "Debe Seleccionar un proveedor para asignar a la ruta", "error");
            return;
        }

        var venta_preventa = $('input:radio[name=venta_preventa]:checked').val();
        //if($("#venta_preventa").is(":checked"))
        //    venta_preventa = 1;

        var envases_ruta = 'N';
        if($("#envases_ruta").is(":checked"))
            envases_ruta = 'S';

        var rels_rutas = [];
        if(venta_preventa == 2)
        {
            var i = 0;
            $("#to").each(function() {
                var localRels = [];

                $(this).find('li').each(function(){
                    localRels.push( $(this).attr('value') );
                    i++;
                });

                rels_rutas.push(localRels);
            });
            console.log("rels_rutas = ",rels_rutas);
            console.log("rels_rutas length = ",rels_rutas.length);
            console.log("#hiddenAction = ", $("#hiddenAction").val());

            if(i == 0 && $("#hiddenAction").val() == "add"){
                swal("Error", "Debe Seleccionar al menos 1 ruta", "error");
                return;
            }
        }

            console.log("#hiddenAction = ", $("#hiddenAction").val());


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

            console.log("#hiddenRuta = ", $("#hiddenRuta").val());
            console.log("#cve_ruta = ", $("#cve_ruta").val());
            console.log("#descripcion = ", $("#descripcion").val());
            console.log("#status_send = ", $("#status_send").val());
            console.log("#cve_almacenp = ", $("#cve_almacenp").val());
            console.log("clientes = ", rels);



            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    ID_Ruta: $("#hiddenRuta").val(),
                    cve_ruta: $("#cve_ruta").val(),
                    descripcion: $("#descripcion").val(),
                    status: $("#status_send").val(),
                    cve_almacenp: $("#cve_almacenp").val(),
                    cve_almac: <?php echo "'".$_SESSION['cve_almacen']."'"; ?>,
                    venta_preventa: venta_preventa,
                    envases_ruta: envases_ruta,
                    clientes: rels,
                    rels_rutas: rels_rutas,
                    id_proveedor: $("#cboProveedor").val(),
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
                    clave_almacen: $("#clave_almacen").val(),
                    venta_preventa: venta_preventa,
                    envases_ruta: envases_ruta,
                    clientes: rels,
                    rels_rutas: rels_rutas,
                    id_proveedor: $("#cboProveedor").val(),
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
                }, error: function(data){
                    console.log("ERROR rels_rutas = ", data);
                }
            });
        } 
    }

    var l = $('#btnSave').ladda();
    l.click(function() 
    {
        if ($("#cve_almacenp").val() == "") return;
        if ($("#cve_ruta").val() == "") return;
        if ($("#descripcion").val() == "") return;

        var venta_preventa = $('input:radio[name=venta_preventa]:checked').val();
        //if($("#venta_preventa").is(":checked"))
        //    venta_preventa = 1;
        console.log("venta_preventa = ", venta_preventa);

        if(venta_preventa == 2)
        {
            //si es tipo ruta entrega
            console.log("OK venta_preventa", $("#hiddenRuta").val());

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Ruta: $("#hiddenRuta").val(),
                id_almacen: $("#cve_almacenp").val(),
                action : "traerRutasAsignadasDisponibles"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ruta/update/index.php',
            success: function(data) {
                console.log("data Rutas = ", data, " length = ",data.from.length, " success = ",data.success, " data.from = ", data.from);
                 if (data.success == true) {
                    
                    var arr = $.map(data.from, function(el) { return el; })
                    arr.pop();
                    $("#from .itemlist").remove();
                    $("#to .itemlist").remove();
                    for (var i=0; i<data.from.length; i++)
                    {
                        var ul = document.getElementById("from");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data.from[i].cve_ruta));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data.from[i].ID_Ruta);
                        ul.appendChild(li);

                    }

                    var arr1 = $.map(data.to, function(el) { return el; })
                    arr1.pop();
                    for (var i=0; i<data.to.length; i++)
                    {

                        var ul = document.getElementById("to");
                        var li = document.createElement("li");
                        var checkbox = document.createElement("input");
                        checkbox.style.marginRight = "10px";
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("class", "drag");
                        checkbox.setAttribute("onclick", "selectParent(this)");
                        li.appendChild(checkbox);
                        li.appendChild(document.createTextNode(data.to[i].cve_ruta));
                        li.setAttribute("dayta-draggable", "item");
                        li.setAttribute("draggable", "false");
                        li.setAttribute("aria-draggable", "false");
                        li.setAttribute("aria-grabbed","false");
                        li.setAttribute("tabindex","0");
                        li.setAttribute("class","itemlist");
                        li.setAttribute("onclick","selectChild(this)");
                        li.setAttribute("value",data.to[i].ID_Ruta);
                        ul.appendChild(li);

                    }
                }
            }, error: function(data){
                console.log("ERROR Ruta = ", data);
            }
        });


            $("#asignar_rutas").modal("show");
            return;
        }
        else
        {
            console.log("NO venta_preventa");
            GuardarRuta();
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
</script>


<!-- Grid de recuperar -->
<script>
    $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on("resize", function() {
            var $grid = $("#grid-table2"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
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
                {name: 'ID_Ruta',index: 'ID_Ruta',width: 0,editable: false,sortable: false,hidden: true},
                {name: 'cve_ruta',index: 'cve_ruta',width: 210,editable: false,sortable: false},
                {name: 'descripcion',index: 'descripcion',width: 510,editable: false,sortable: false},
                {name: 'status',index: 'status',width: 180,editable: false,sortable: false},
                {name: 'myac',index: '',width: 80,fixed: true,sortable: false,resize: false,formatter: imageFormat},
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

        function imageFormat(cellvalue, options, rowObject) 
        {
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

        function aceSwitch(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=checkbox]')
                    .addClass('ace ace-switch ace-switch-5')
                    .after('<span class="lbl"></span>');
            }, 0);
        }
        //enable datepicker
        function pickDate(cellvalue, options, cell) 
        {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
            }, 0);
        }

        function beforeDeleteCallback(e) 
        {
            var form = $(e[0]);
            if (form.data('styled')) return false;
            form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner('<div class="widget-header" />')
            style_delete_form(form);
            form.data('styled', true);
        }

        function reloadPage() 
        {
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

        function enableTooltips(table) 
        {
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
</script>

<script>
    $(function($) {
        var grid_selector = "#grid-table3";
        var pager_selector = "#grid-pager3";

        //resize to fit page size
        $(window).on("resize", function() {
            var $grid = $("#grid-table3"),
                newWidth = $grid.closest(".ui-jqgrid").parent().width();
            $grid.jqGrid("setGridWidth", newWidth, true);
        });
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
            url: '/api/ruta/lista/index.php',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            /*
            postData: {
                criterio: $("#txtCriterio1").val()
            },*/
            mtype: 'POST',
            colNames: ['No. Clientes', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
            colModel: [
                {name: 'num_clientes',index: 'num_clientes', align: 'right',width: 100,editable: false,sortable: false},
                {name: 'Lun',index: 'Lun',width: 40,editable: false,sortable: false, align: 'right'},
                {name: 'Mar',index: 'Mar',width: 40,editable: false,sortable: false, align: 'right'},
                {name: 'Mie',index: 'Mie',width: 40,editable: false,sortable: false, align: 'right'},
                {name: 'Jue',index: 'Jue',width: 40,editable: false,sortable: false, align: 'right'},
                {name: 'Vie',index: 'Vie',width: 40,editable: false,sortable: false, align: 'right'},
                {name: 'Sab',index: 'Sab',width: 40,editable: false,sortable: false, align: 'right'},
                {name: 'Dom',index: 'Dom',width: 40,editable: false,sortable: false, align: 'right'}
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            sortname: 'ID_Ruta',
            viewrecords: true,
            loadComplete: function(data){console.log("SUCCESS Det: ", data);},
            loadError: function(data){console.log("ERROR Det: ", data);},
            sortorder: "desc"
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

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size


        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
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

    $("#btn-guardar").click(function()
    {
        GuardarRuta();
/*
        var rels = [];

        $("#to").each(function() {
            var localRels = [];

            $(this).find('li').each(function(){
                localRels.push( $(this).attr('value') );
            });

            rels.push(localRels);
        });
        console.log(rels);

          $.post('/api/ruta/update/index.php',
            {//$("#hiddenAction")
                 ID_Ruta: $("#hiddenRuta").val(),
                 action : 'guardarAsignacionRutas',
                 usuarios: rels
            },
            function(response){
                console.log(response);
            }, "json")
            .always(function() {
                $("#conModal").modal();
                setTimeout(function(){
                    $("#conModal").modal("hide");
                }, 3000);
                $("#selectAll").iCheck('uncheck')
                $("#from .itemlist").remove();
                $("#to .itemlist").remove();
                $('.chosen-select').trigger("chosen:updated");
            });
*/
    });

    function add(from, to){
        console.log("Entro");
        var elements = document.querySelectorAll(`${from} input.drag:checked`),
            li, newli;
        for(e of elements){
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${from}`).removeChild(li);
            document.querySelector(`${to}`).appendChild(newli);
        }
    }
    function remove(to, from){
        var elements = document.querySelectorAll(`${to} input.drag:checked`),
            li, newli;
        for(e of elements){
            e.checked = false;
            li = e.parentElement;
            newli = li.cloneNode(true);
            newli.setAttribute("aria-grabbed", "false");
            document.querySelector(`${to}`).removeChild(li);
            document.querySelector(`${from}`).insertBefore(newli, document.querySelector(`${from}`).firstChild);
        }
    }
        function selectParent(e){
        if(e.checked){
            e.parentNode.setAttribute("aria-grabbed", "true");
        }else{
            e.parentNode.setAttribute("aria-grabbed", "false");
        }
    }
    function selectChild(e){
        if(e.getAttribute("aria-grabbed") == "true"){
            e.firstChild.checked = true;
        }else{
            e.firstChild.checked = false;
        }
    }



    $(document).ready(function() {
      setTimeout(function(){
        ReloadGrid();
      }, 1000);

       $('#selectAll').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    $("body").on("ifToggled", function(e){
          
            if(e.target.checked && e.target.id === 'selectAll'){
                $('#from li input[type="checkbox"].drag').each(function(i, e){
                    e.checked = true;
                    e.parentElement.setAttribute('aria-grabbed', true);
                });
            }else{
                $('#from li input[type="checkbox"].drag').each(function(i, e){
                    e.checked = false;
                    e.parentElement.setAttribute('aria-grabbed', false);
                });
            }
        });

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
    <?php /* if($edit[0]['Activo']==0) { ?>
        .fa-edit {
            display: none;
        }
    <?php } ?>
    <?php if($borrar[0]['Activo']==0) { ?>
        .fa-eraser {
            display: none;
        }
    <?php } */ ?>
</style>