<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$almacenes = new \AlmacenP\AlmacenP();
//$listaCliente = new \Clientes\Clientes();
$listaRuta = new \Ruta\Ruta();

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

<!-- Mainly scripts -->
<style type="text/css">
    ul.dropdown-menu.dropdown-menu-right 
    {
        position: absolute;
        left: auto;
        right: 0;
    }
</style>

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
                <select class="form-control" id="transportes">
                    <option value="">Seleccione Transporte</option>
               <?php foreach( $listTransportes AS $r ): ?>
                   <option value="<?php echo $r['id']; ?>"><?php echo $r['ID_Transporte']."-".$r['Nombre']."-".$r['Placas']; ?></option>
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
                                    <button type="submit" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
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
                                    <input type="checkbox" id="venta_preventa" name="venta_preventa" value="1" class="form-control">
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
                        <div class="col-sm-4" style="display: none;">
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
                        <div class="col-md-8" style="display: none;">
                            <?php if($ag[0]['Activo']==1){ ?>
                                <a href="#" onclick="agregar()"><button class="btn btn-primary" type="button" style="margin: 10px;"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>
                            <?php } ?>
                            <a href="/api/v2/rutas/exportar" target="_blank" class="btn btn-primary pull-right" style="margin-left:15px"><span class="fa fa-upload"></span> Exportar</a>
                            <button class="btn btn-primary pull-right" style="margin-left:15px" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
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
            url: '/api//reportesRutas/lista/index.php',
            datatype: "json",
            height: 250,
            shrinkToFit: false,
            height: 'auto',
            postData: {
                criterio: $("#txtCriterio").val(),
                ruta: $("#rutas_list").val(),
                agente: $("#agentes_list").val()
            },
            mtype: 'POST',
            colNames: ["Acciones", 'Id Venta', 'Fecha', 'DO', 'Ruta', 'Cliente', 'Responsable', 'Nombre Comercial', 'Folio', 'Articulo', 'Tipo', 'Método de Pago', 'Importe', 'IVA', 'Descuento', 'Total', 'Comisiones', 'Utilidad', 'Cajas', 'Piezas', 'Cancelada', 'Vendedor', 'Ayudante 1', 'Ayudante 2', 'Promociones'],
            colModel: [
                {name: 'myac',index: '',width: 80,fixed: true,sortable: false,resize: false,formatter: imageFormat},
                {name: 'ID_Venta',index: 'ID_Venta',width: 100,editable: false,sortable: false,hidden: false, align: 'right'},
                {name: 'fecha',index: 'fecha',width: 150,editable: false,sortable: false, align: 'center'},
                {name: 'dia_o',index: 'dia_o',width: 50,editable: false,sortable: false, align: 'right'},
                {name: 'ruta',index: 'ruta',width: 150,editable: false,sortable: false},
                {name: 'cliente',index: 'cliente',width: 100,editable: false,sortable: false, align: 'right'},
                {name: 'responsable',index: 'responsable',width: 150,editable: false,sortable: false},
                {name: 'nombre_comercial',index: 'nombre_comercial',width: 150,editable: false,sortable: false},
                {name: 'folio',index: 'folio',width: 100,editable: false,sortable: false, align: 'right'},
                {name: 'aticulo',index: 'aticulo',width: 200,editable: false,sortable: false},
                {name: 'tipo',index: 'tipo',width: 150,editable: false,sortable: false},
                {name: 'metodo_pago',index: 'metodo_pago',width: 150,editable: false,sortable: false, align: 'right'},
                {name: 'importe',index: 'importe',width: 100,editable: false,sortable: false, align: 'right'},
                {name: 'iva',index: 'iva',width: 100,editable: false,sortable: false, align: 'right'},
                {name: 'descuento',index: 'descuento',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'total',index: 'total',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'comisiones',index: 'comisiones',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'utilidad',index: 'utilidad',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'cajas',index: 'cajas',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'piezas',index: 'piezas',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'cancelada',index: 'cancelada',width: 120,editable: false,sortable: false, align: 'right'},
                {name: 'vendedor',index: 'vendedor',width: 120,editable: false,sortable: false},
                {name: 'ayudante1',index: 'ayudante1',width: 120,editable: false,sortable: false},
                {name: 'ayudante2',index: 'ayudante2',width: 120,editable: false,sortable: false},
                {name: 'promociones',index: 'promociones',width: 120,editable: false,sortable: false, align: 'right'}
            ],
            rowNum: 30,
            rowList: [30, 40, 50],
            pager: pager_selector,
            sortname: 'ID_Venta',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: almacenPrede()
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
            var clave_cia = <?php echo $_SESSION['cve_cia']; ?>;

            var html = '';
            html += '<a href="/api/koolreport/export/reportes/ventas/ventas_sfa/?id_venta='+id_venta+'&cve_cia='+clave_cia+'" target="_blank"><i class="fa fa-file-pdf-o" title="Reporte de Venta"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
        function ReloadGrid() 
        {
            $('#grid-table').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        criterio: $("#txtCriterio").val(),
                        ruta: $("#rutas_list").val(),
                        agente: $("#agentes_list").val(),
                        almacen: $("#almacenes").val()
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

            select_operadores($(this).val(), 1);
            ReloadGrid();

        });

        $("#agentes_list").change(function(){

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

        function select_transporte(clave_ruta)
        {
            $("#ruta_clave_select").val(clave_ruta);
            $("#ruta_clave").text(clave_ruta);
            $('#modalTransportes').show();
        }

        function select_operadores(clave_ruta, asignados_si_no)
        {
            console.log("clave_ruta = ", clave_ruta);
            console.log("asignados_si_no = ", asignados_si_no);
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    Cve_Ruta: clave_ruta,
                    asignados_si_no: asignados_si_no,
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
                    if(asignados_si_no == 0)
                    {
                        $('#EliminarAgentes').empty();
                        $('#EliminarAgentes').append(data.operadores);
                        $('#EliminarAgentes').trigger("chosen:updated");
                    }
                    else 
                    {
                        $('#agentes, #agentes_list').empty();
                        $('#agentes, #agentes_list').append(data.operadores);
                        $('#agentes, #agentes_list').trigger("chosen:updated");
                    }
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