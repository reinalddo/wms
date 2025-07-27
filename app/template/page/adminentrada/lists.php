<?php
$listaNCompa = new \AlmacenP\AlmacenP();
$listaProto = new \Protocolos\Protocolos();
$listaProyectos = new \Proyectos\Proyectos();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \AlmacenP\AlmacenP();
$listaArtic = new \Articulos\Articulos();
$listaPresupuestos = new \AdminEntrada\AdminEntrada();
$consecSql = \db()->prepare("SELECT consec_protocolo FROM th_aduana ORDER BY consec_protocolo desc limit 1 ");
$consecSql->execute();
$consec = $consecSql->fetch()['consec_protocolo'];

$confSql = \db()->prepare("SELECT IFNULL(Valor, '0') AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'SAP' LIMIT 1 ");
$confSql->execute();
$ValorSAP = $confSql->fetch()['Valor'];

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1 ");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

$model_usuarios = new \Usuarios\Usuarios();
$usuarios = $model_usuarios->getAll();

$fecha_actual = "";

if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com')
{
$confSql = \db()->prepare("SELECT DATE_FORMAT(MAX(Fec_Entrada), '%d-%m-%Y') AS fecha_actual FROM th_entalmacen");
$confSql->execute();
$fecha_actual = $confSql->fetch()['fecha_actual'];
}

$confSql = \db()->prepare("SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_semana FROM DUAL");
$confSql->execute();
$fecha_semana = $confSql->fetch()['fecha_semana'];

?>

<input type="hidden" name="instancia" id="instancia" value="<?php echo $instancia; ?>">
<input type="hidden" name="cve_usuario" id="cve_usuario" value="<?php echo $_SESSION['cve_usuario']; ?>">
<input type="hidden" name="folio_tipo" id="folio_tipo" value="">


<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">


<style type="text/css">
    .ui-jqgrid, .ui-jqgrid-view, .ui-jqgrid-hdiv, .ui-jqgrid-bdiv, .ui-jqgrid, .ui-jqgrid-htable, #grid-table, #grid-table2, #grid-table3, #grid-table4, #grid-pager, #grid-pager2, #grid-pager3, #grid-pager4{
        max-width: 100% !important;
    }
        .select2 /*, .select2-container, .select2-container--default, .select2-container--below*/
        {
            width: 100% !important;
        }

</style>

<div class="wrapper wrapper-content  animated" id="list">
 <h3>Administración de Entradas*</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
    <?php
    $cliente_almacen_style = ""; $cve_cliente = "";
    if($_SESSION['es_cliente'] == 1) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_cliente = $_SESSION['cve_cliente'];
    }

    $cve_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
    }

    if(isset($_SESSION['id_proveedor']))
    {
        $cve_proveedor = $_SESSION['id_proveedor'];
    }

    ?>

    <input type="hidden" id="cve_cliente" value="<?php echo $cve_cliente; ?>">
    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
    <input type="hidden" id="folio_entrada" value="">
    <input type="hidden" id="folio_oc" value="">



                    <div class="row">
                        <div class="form-group col-md-4" <?php echo $cliente_almacen_style; ?>>
                            <label>Seleccione un almacen</label>
                            <select class="form-control" id="almacen" >
                                <option value="">Almacen</option>
                                <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                <option value="<?php echo $a->clave; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <!--
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label> </label>
                                    <button  onclick="reloadGridConteo($('#hiddenInventario').val(),$('#conteo').val(),$('#criterio').val())" type="button" class="btn btn-m btn-primary btn-block">
                                         <i class="fa fa-search"></i> Buscar
                                    </button>
                            </div>
                        </div>
                    -->

                        <div class="form-group col-md-4">
                            <label>Fecha Inicio</label>
                            <div class="input-group date"  id="data_3">
                                <input id="fechai" type="text" class="form-control" value="<?php echo $fecha_semana; ?>"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>


                        <div class="form-group col-md-4">
                            <label>Fecha Fin</label>
                            <div class="input-group date"  id="data_4">
                                <input id="fechaf" type="text" class="form-control" value="<?php echo $fecha_actual; ?>"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row" class="col-md-12">
                        <div class="form-group col-md-4">
                            <label>Tipo Entrada </label>
                            <select id="tipo_entrada" name="tipo_entrada" class="form-control">
                                <option value="">Todos</option>
                                <option value="RL">Recepción Libre</option>
                                <option value="OC">Orden de Compra</option>
                                <option value="TR">Traslado</option>
                                <option value="DV">Devolución</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Buscar: </label>
                            <input id="txtCriterio" type="text" placeholder="" value="" class="form-control">
                        </div>

                        <?php 
                        if($instancia == "welldex")
                        {
                        ?>
                        <div class="form-group col-md-2">
                            <label>Referencia Well: </label>
                            <input id="refWell" type="text" placeholder="" value="" class="form-control">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Pedimento: </label>
                            <input id="pedimentoW" type="text" placeholder="" value="" class="form-control">
                        </div>
                        <?php 
                        }
                        ?>
                        <div class="form-group col-md-8">
                        <!--<div class="input-group-addon" style="padding: 0px;">-->
                            <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarA" style="margin-top: 22px;">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                        <!--</div>-->
                        </div>
                        <?php 
                        /*
                        ?>
                        <div class="form-group col-md-8">
                            <label for="email">En</label>
                            <div class="input-group">
                                
                                <select name="filtro" id="filtro" class="chosen-select form-control" style="display:none;">
                                    <option value="">Seleccione filtro</option>
                                    <option value="numero_oc">Folio|OC</option>
                                    <option value="erp">Numero de Orden (ERP)</option>
                                    <option value="proveedor">Proveedor</option>
                                    <option value="estado">Status</option>
                                </select>
                                <div class="input-group-addon" style="padding: 0px;">
                                    <button onclick="ReloadGrid()" type="submit" class="btn btn-primary" id="buscarA">
                                        <i class="fa fa-search"></i> Buscar
                                    </button>
                                </div>
                                
                                    <?php if($ag[0]['Activo']==1){?><div class="input-group-addon"  style="padding: 0px;">
                                        <button onclick="agregar()" class="btn btn-primary" type="button">
                                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo
                                        </button></div>
                                    <?php }?>
                                
                            </div>
                        </div>
                        <?php 
                        */
                        ?>
                        <!--div class="row col-md-12">
                            <div class="form-group col-md-6">
                                  <label for="email">Presupuesto</label>
                                  <div class="input-group">
                                      <select name="presupuestoSelect" id="presupuestoSelect" class="chosen-select form-control">
                                          <option value="">Seleccione Presupuesto</option>
                                          <?php /* foreach( $listaPresupuestos->getPresupuestos() AS $p ): ?>
                                          <option value="<?php echo $p['id']; ?>"><?php echo $p['nombreDePresupuesto']; ?></option>
                                          <?php endforeach;*/ ?>
                                      </select>
                                      <div class="input-group-addon" style="padding: 0px;">
                                          <button onclick="calcularPresupuesto()" type="submit" class="btn btn-primary" id="buscarA">
                                              <i class="fa fa-bar-chart"></i> Calcular
                                          </button>
                                      </div>
                                  </div>
                              </div>
                              <div class="form-group col-md-2">
                                  <label for="email">Presupuesto Asignado</label>
                                  <div class="input-group date"  id="data_3">
                                      <input id="valorDePresupuesto" type="text" class="form-control" placeholder="$ 0.00" style= "background-color: white" readonly>
                                  </div>
                              </div>
                              <div class="form-group col-md-2">
                                  <label for="email">Valor de Compras</label>
                                  <div class="input-group date"  id="data_3">
                                      <input id="importeTotalPresupuesto" type="text" class="form-control" placeholder="$ 0.00" style= "background-color: white" readonly>
                                  </div>
                              </div>
                              <div class="form-group col-md-2">
                                  <label for="email">Remanente</label>
                                  <div class="input-group date"  id="data_3">
                                      <input id="remanenteDePresupuesto" type="text" class="form-control" placeholder="$ 0.00" style= "background-color: white" readonly>
                                  </div>
                              </div>
                        </div-->
                    </div>
                </div>
                <div class="ibox-content">
                    <div style="width: 100%; text-align: center; margin-bottom: 50px;">
                        <button id="importRL" type="button"  class="pull-left btn btn-primary permiso_registrar">
                            <i class="fa fa-file-excel-o"></i>
                            Importar Entradas RL
                        </button>
                        <?php if($instancia != 'welldex'){ ?>
                        <button id="importExcelOC" type="button"  class="pull-left btn btn-primary" style="margin-left: 15px;">
                            <i class="fa fa-file-excel-o"></i>
                            Importar Entradas OC
                        </button>
                        <?php } ?>

                        <?php 
                        /*
                        ?>
                        <button type="button" id="exportExcel" class="btn btn-primary">
                            <i class="fa fa-file-excel-o"></i>
                            Excel
                        </button>
                        <button type="button" id="exportPDF" class="btn btn-danger">
                            <i class="fa fa-file-pdf-o"></i>
                            PDF
                        </button>
                        <?php 
                        */
                        ?>
                    </div>
                    <div class="jqGrid_wrapper">

            <div>
                <span>
                    <b style="font-size: 14px;">Las entradas que se muestran al iniciar de los últimos 7 días, use los filtros para buscar más entradas</b><br><br>
                </span>
            </div>

                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>

                    </div>
					          <label id="totales" class="pull-right">Total Pedido: 0 </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="enviarSAP" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 80%;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Envío de Datos SAP</h4>
                </div>
                <div class="modal-body" style="overflow-y: scroll;max-height: 400px;">

                            <div class="row">
                                <div class="col-md-4">
                                    <label style="white-space: nowrap;">Tipo de Cambio</label>
                                    <input type="number" name="tipo_cambio" id="tipo_cambio" class="form-control" placeholder="Tipo de Cambio">
                                </div>
                            </div>
                            <br><br>
                            <div class="row">
                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Artículo</label>
                                </div>
                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Lote/Serie</label>
                                </div>

                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Número Pedimento</label>
                                </div>
                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Fecha Pedimento</label>
                                </div>
                            </div>

                            <input type="hidden" id="datos_sap_array">
                            <div id="datos_sap_oc">
                        <!--
                                <div class="row">
                              <div class="col-md-3">
                              <input type="text" name="cve_articulo_oc" id="cve_articulo_oc" class="form-control" readonly>
                              </div>
                              <div class="col-md-3">
                              <input type="text" name="cve_lote_oc" id="cve_lote_oc" class="form-control" readonly>
                              </div>
                              <div class="col-md-3">
                              <input type="text" name="pedimento_oc" id="pedimento_oc" class="form-control" placeholder="Pedimento...">
                              </div>
                              <div class="col-md-3">
                              <input type="date" name="fecha_pedimento_oc" id="fecha_pedimento_oc" class="form-control" placeholder="Pedimento...">
                              </div>
                          </div>
                          <br>
                      -->
                            </div>

                </div>
                <div class="modal-footer">
                    <button id="btn-enviarSAP" type="button" class="btn btn-primary">Enviar</button><!--funcion de import-->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="DetalleTransporte" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 1000px;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="CerrarDetalle()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Detalle Transportes Entradas</h4>
                <br>
                <h3>Folio: <span id="folio_ent"></span></h3>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <table class="table table-striped">
                        <tr>
                            <td><b>Nombre Operador: </b></td>    <td><input type="text" class="form-control input_transporte" readonly id="nombre_operador" value="0" style="background-color: transparent;text-align: right;"></td> 
                            <td><b>ID Chofer: </b></td><td><input type="text" class="form-control input_transporte" readonly id="id_chofer" value="0" style="background-color: transparent;text-align: right;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Número de Unidad: </b></td><td><input type="text" class="form-control input_transporte" readonly id="num_unidad" value="0" style="background-color: transparent;text-align: right;"></td> 
                            <td><b>Clave Transportadora:</b></td>   <td>
                                <select class="chosen-select form-control input_transporte" id="cve_transportadora" name="cve_transportadora" disabled style="background-color: transparent;">
                                    <option value="">Seleccione una Transportadora</option>  
                                    <?php foreach( $listaProvee->getAll(" AND es_transportista = 1 ") AS $p ): //AND es_cliente = 1 ?>    
                                        <option value="<?php echo $p->cve_proveedor; ?>"><?php echo "( ".$p->cve_proveedor." ) - ".$p->Nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <?php /* ?><input type="text" class="form-control input_transporte" readonly id="cve_transportadora" value="0" style="background-color: transparent;text-align: right;"><?php */ ?>
                            </td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Placa: </b></td>   <td><input type="text" class="form-control input_transporte" readonly id="placa" value="0" style="background-color: transparent;text-align: right;"></td> 
                            <td><b>Sello/Precinto: </b></td> <td><input type="text" class="form-control input_transporte" readonly id="sello" value="0" style="background-color: transparent;text-align: right;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Fecha: </b></td>    <td>
                                <div class="form-group" id="data_1">
                                <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" class="form-control input_transporte" readonly id="fecha_transp" style="background-color: transparent;text-align: right;">
                                </div>
                                </div>
                            </td> 
                            <td><b>Hora: </b></td>     <td>

                                <div class="form-group" id="data_H">
                                <div class="input-group date">
                                <input type="time" class="form-control input_transporte" readonly id="hora_transp" value="0" style="background-color: transparent;text-align: right;">
                                </div>
                                </div>
                            </td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Observaciones: </b></td><td colspan="6"><textarea class="form-control input_transporte" readonly style="background-color: transparent;" id="observaciones_transp" rows='5'></textarea></td> 
                        </tr>
                    </table>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnGuardarTransporte" style="display: none;">Guardar <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-primary" id="btnEditarTransporte">Editar <i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>&nbsp;&nbsp;
                &nbsp;&nbsp;
                <a href="#" onclick="CerrarDetalle()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="DetalleEdicion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 500px;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="CerrarDetalleEdicion()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Editar Pedimento y Referencia</h4>
                <br>
                <h3>Folio Entrada: <span id="folio_ent2"></span> | Folio OC: <span id="folio_ent3"></span></h3>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <table class="table table-striped">
                        <tr>
                            <td><b>Pedimento: </b></td>
                            <td><input type="text" class="form-control input_edicion" readonly id="pedimentoW_edit" value="0" style="background-color: transparent;text-align: right;"></td> 
                        </tr>
                        <tr>
                            <td><b>Referencia: </b></td><td><input type="text" class="form-control input_edicion" readonly id="referenciaW_edit" value="0" style="background-color: transparent;text-align: right;"></td> 
                        </tr>
                    </table>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnGuardarEdicion" style="display: none;">Guardar <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-primary" id="btnEditarEdicion">Editar <i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>&nbsp;&nbsp;
                &nbsp;&nbsp;
                <a href="#" onclick="CerrarDetalleEdicion()"><button type="button" class="btn btn-white" id="btnCancelEdit">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="DetalleEdicionProyecto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 500px;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="CerrarDetalleEdicionProyecto()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Editar Pedimento y Referencia</h4>
                <br>
                <h3>Folio Entrada: <span id="folio_ent2Proyecto"></span> | Folio OC: <span id="folio_ent3Proyecto"></span></h3>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label>Proyecto</label> 
                    <select class="form-control input_edicionProyecto" name="claveproyectoEdicion" id="claveproyectoEdicion" disabled>
                        <option value="">Seleccione</option>
                        <?php foreach( $listaProyectos->getAllProyectos($_SESSION['id_almacen']) AS $a ): ?>
                            <option value="<?php echo $a->Cve_Proyecto; ?>"><?php echo " ( ".$a->Cve_Proyecto." ) ".$a->Des_Proyecto; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnGuardarEdicionProyecto" style="display: none;">Guardar <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-primary" id="btnEditarEdicionProyecto">Editar <i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>&nbsp;&nbsp;
                &nbsp;&nbsp;
                <a href="#" onclick="CerrarDetalleEdicionProyecto()"><button type="button" class="btn btn-white" id="btnCancelEditProyecto">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="coModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 80%;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalle de Entrada</h4>
                    <label id="numeroDetalle"></label>
                  <br>
                    <label id="proveedorDetalle"></label>
                   <br>
                  
                    <label id="ordenCompraDetalle" ></label>
                  
                  
                </div>
                <div class="modal-body">
                   	<div class="tabbable" id="tabs-131708">
                        <ul class="nav nav-tabs">
                            <!--
                            <li >
                                <a href="#panel-928563"  data-toggle="tab">Resumen</a>
                            </li>
                            -->
                            <li class="active">
                                <a href="#panel-594076" id="avanzada" data-toggle="tab">Detalle</a>
                            </li>
                            <!--
                            <li >
                                <a href="#panel-594077" id="bitacora" data-toggle="tab">Bitacora</a>
                            </li>
                            -->

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane" id="panel-928563">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table2"></table>
                                    <div id="grid-pager2"></div>
                                </div>
                            </div>
                            <div class="tab-pane active" id="panel-594076">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table3"></table>
                                    <div id="grid-pager3"></div>
                                </div>
                            </div>
                            <div class="tab-pane" id="panel-594077">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table4"></table>
                                    <div id="grid-pager4"></div>
                                </div>
                            </div>
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

<div class="modal fade" id="modalImportEntradaRL" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Entradas RL</h4>
                    </div>
                    <div class="modal-body">

                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">

                            <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Almacén</label>
                                <select class="form-control" id="almacenes" name="almacenes">
                                    <option value="">Seleccione un Almacen</option>
                                    <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                        <option selected value="<?php echo $a->clave.'**WMS-WMS**'.$a->id; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Proyectos</label> 
                                <select class="form-control" name="claveproyecto" id="claveproyecto">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaProyectos->getAllProyectos($_SESSION['id_almacen']) AS $a ): ?>
                                        <option value="<?php echo $a->Cve_Proyecto; ?>"><?php echo $a->Des_Proyecto; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            </div>

                            <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Empresa</label> <!--lilo-->
                                <select class="chosen-select form-control" id="empresa" name="empresa">
                                    <option value="">Seleccione una Empresa</option>  
                                    <?php foreach( $listaProvee->getAll(" AND es_cliente = 1 AND es_transportista = 0 ") AS $p ): ?>    
                                        <option value="<?php echo $p->cve_proveedor.'-'.$p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Proveedor</label> <!--lilo-->
                                <select class="chosen-select form-control" id="proveedor" name="proveedor">
                                    <option value="">Seleccione un Proveedor</option>  
                                    <?php foreach( $listaProvee->getAll() AS $p ): ?>    
                                        <option value="<?php echo $p->cve_proveedor.'-'.$p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            </div>

                            <div class="row">
                            <div class="col-md-6 form-group">
                            <label>Usuarios</label>
                                <select name="txtUsuario" id="txtUsuario" class="chosen-select form-control">
                                <option value="">Usuario</option>
                                <?php foreach( $usuarios AS $usuario ): ?>
                                    <?php if($usuario->Activo == 1 && $usuario->cve_usuario == $_SESSION['cve_usuario']):?>
                                    <option selected value="<?php echo $usuario->cve_usuario; ?>"><?php echo "($usuario->cve_usuario) ".$usuario->nombre_completo; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            </div>

                            <div class="col-md-6 form-group" id="zonarecepcionif">
                                <label>Zona de Recepción</label>
                                <select class="form-control" name="zonarecepcioni" id="zonarecepcioni">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            </div>

                            <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Tipo de Protocolo*</label>
                                <select class="form-control" id="Protocol" name="Protocol">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaProto->getAll() AS $a ): ?>
                                    <option value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 form-group"><label>Consecutivo de Protocolo</label> 
                                <input id="Consecut" name="Consecut" type="text" placeholder="Consecutivo de Protocolo"  class="form-control" readonly="readonly">
                            </div>
                            </div>

                            <div class="row">
                            <div class="col-md-6 form-group" <?php if($instancia == 'welldex') echo "style='display:none;'"; ?> id="factura_remision_input"><label>Factura | Remisión</label> 
                                <input id="factura_remision" name="factura_remision" type="text" placeholder="Factura|Remisión"  class="form-control">
                            </div>

                            <div class="col-md-6 form-group" <?php if($instancia == 'welldex') echo "style='display:none;'"; ?> id="factura_remision_input"><label>Tipo de Cambio</label> 
                                <input id="tipo_cambio" name="tipo_cambio" type="number" min="0"  class="form-control" value="1">
                            </div>
                            </div>


                            <div class="checkbox">
                                <label for="palletizar_entrada" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="palletizar_entrada" id="palletizar_entrada" value="0">Palletizar Entrada</label>
                            </div>

                            <div class="checkbox">
                                <label for="sobreescribir_existencias" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="sobreescribir_existencias" id="sobreescribir_existencias" value="0">Reemplazar Existencias</label>
                            </div>

                            <div class="checkbox"><?php //style="display: none;" ?>
                                <label for="convertir_a_oc" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="convertir_a_oc" id="convertir_a_oc" checked value="0">Registrar Orden de Compra</label>
                            </div>


                            <div class="checkbox" <?php if($instancia == 'welldex') echo "style='display:none;'"; ?>>
                                <label for="mandar_a_rtm" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="mandar_a_rtm" id="mandar_a_rtm" value="0">Registrar en RTM (Sin Acomodo)</label>
                            </div>

                            <div class="form-group" id="factura_oc_input"><label>Número de Orden | Cross Docking (ERP)</label> 
                                <input id="factura_oc" name="factura_oc" type="text" placeholder="Número de Orden"  class="form-control">
                            </div>


                            <?php 
                            if($instancia == 'welldex')
                            {
                            ?>
                            <div class="row">
                            <div class="col-md-6 form-group" id="referencia_well_input"><label>Referencia Well</label> 
                                <input id="referencia_well" name="referencia_well" type="text" placeholder="Referencia Well"  class="form-control">
                            </div>

                            <div class="col-md-6 form-group" id="pedimento_well_input"><label>Pedimento</label> 
                                <input id="pedimento_well" name="pedimento_well" type="text" placeholder="Pedimento"  class="form-control">
                            </div>
                            </div>
                            <?php 
                            }
                            ?>

                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".xlsx" required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>

                        <div class="row" id="loadgif" style="text-align: center;padding: 15px; display: none;font-size: 16px;top: 15px;position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layoutRL" type="button" class="btn btn-primary">Descargar Layout</button><!--cambiar layout-->
                        </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-importRL" type="button" class="btn btn-primary">Importar</button><!--funcion de import-->
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>

<div class="modal fade" id="modalImportOC" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Ordenes de Compra</h4>
                    </div>
                    <div class="modal-body">

                        <form id="form-importOC" action="import" method="post" enctype="multipart/form-data">

                            <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Almacén</label>
                                <select class="form-control" id="almacenesOC" name="almacenesOC">
                                    <?php foreach( $listaAlma->getAll() AS $a ): ?>
                                        <option selected value="<?php echo $a->clave.'-'.$a->id; ?>"><?php echo "($a->clave) $a->nombre"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="almacen_clave_oc" id="almacen_clave_oc" value="">
                            <div class="col-md-6 form-group">
                                <label>Empresa</label> <!--lilo-->
                                <select class="chosen-select form-control" id="empresaOC" name="empresaOC">
                                    <option value="">Seleccione una Empresa</option>  
                                    <?php foreach( $listaProvee->getAll(" AND es_cliente = 1 AND es_transportista = 0 ") AS $p ): ?>    
                                        <option value="<?php echo $p->ID_Proveedor; ?>"><?php echo $p->Nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            </div>

                            <div class="row">
                            <div class="col-md-6 form-group">
                            <label>Usuarios</label>
                                <select name="txtUsuarioOC" id="txtUsuarioOC" class="chosen-select form-control">
                                <option value="">Usuario</option>
                                <?php foreach( $usuarios AS $usuario ): ?>
                                    <?php if($usuario->Activo == 1 && $usuario->cve_usuario == $_SESSION['cve_usuario']):?>
                                    <option selected value="<?php echo $usuario->cve_usuario; ?>"><?php echo "($usuario->cve_usuario) ".$usuario->nombre_completo; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            </div>

                            <div class="col-md-6 form-group" id="zonarecepcionifOC">
                                <label>Zona de Recepción</label>
                                <select class="form-control" name="zonarecepcioniOC" id="zonarecepcioniOC">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            </div>

                            <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Tipo de Protocolo*</label>
                                <select class="form-control" id="ProtocolOC" name="ProtocolOC">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaProto->getAll() AS $a ): ?>
                                    <option value="<?php echo $a->ID_Protocolo; ?>"><?php echo $a->descripcion; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 form-group"><label>Consecutivo de Protocolo</label> 
                                <input id="ConsecutOC" name="ConsecutOC" type="text" placeholder="Consecutivo de Protocolo"  class="form-control" readonly="readonly">
                            </div>
                            </div>

                            <div class="row">

                            <div class="col-md-6 form-group" <?php if($instancia == 'welldex') echo "style='display:none;'"; ?> id="factura_remision_inputOC"><label>Tipo de Cambio</label> 
                                <input id="tipo_cambioOC" name="tipo_cambioOC" type="number" min="0"  class="form-control" value="1">
                            </div>
                            </div>


                            <div class="checkbox">
                                <label for="palletizar_entradaOC" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="palletizar_entradaOC" id="palletizar_entradaOC" value="0">Palletizar Entrada</label>
                            </div>

                            <div class="checkbox">
                                <label for="sobreescribir_existenciasOC" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="sobreescribir_existenciasOC" id="sobreescribir_existenciasOC" value="0">Reemplazar Existencias</label>
                            </div>
                            <div class="checkbox">
                                <label for="registrar_soloOC" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="registrar_soloOC" id="registrar_soloOC" value="0">Registrar solo OC</label>
                            </div>

                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="fileOC" id="fileOC" class="form-control" accept=".xlsx" required>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>

                        <div class="row" id="loadgifOC" style="text-align: center;padding: 15px; display: none;font-size: 16px;top: 15px;position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layoutOC" type="button" class="btn btn-primary">Descargar Layout</button><!--cambiar layout-->
                        </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-importOC" type="button" class="btn btn-primary">Importar</button><!--funcion de import-->
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>

<div class="modal fade" id="fotos_oc_th" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Fotos del Folio <span id="n_folio"></span></h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-subir-fotos-th" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccione las fotos a documentar</label>
                                <input type="hidden" name="folio_foto_th" id="folio_foto_th" value="">
                                <input type="file" name="image_file_th" id="file_th" class="form-control" accept=".png, .PNG, .jpg, .JPG, .JPEG, .jpeg" required>
                                <br>
                                <input type="text" class="form-control" name="descripcion_foto" id="descripcion_foto" placeholder="Descripción de la foto" required>
                                <br><br>
                                <button id="btn-fotos-th" type="button" class="btn btn-primary">Subir Foto(s)</button>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="fotos_th">
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>


<div class="modal fade" id="fotos_oc_td" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Fotos de <span id="desc_id_td"></span></h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-subir-fotos-td" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccione las fotos a documentar</label>
                                <input type="hidden" name="id_foto_td" id="id_foto_td" value="">
                                <input type="file" name="image_file_td" id="file_td" class="form-control" accept=".png, .PNG, .jpg, .JPG, .JPEG, .jpeg" required>
                                <br><br>
                                <input class="form-control" name="descripcion_foto_td" id="descripcion_foto_td" placeholder="Descripción del Daño">
                                <br><br>
                                <button id="btn-fotos-td" type="button" class="btn btn-primary permiso_registrar">Subir Foto | Descripción del Daño</button>
                            </div>
                        </form>
                        <div class="col-md-12">
                            <div class="progress" style="display:none">
                                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                    <div class="percent">0%</div >
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="fotos_td">
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="text-align: right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>


<div class="modal fade" id="lista_lp_entrada" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Lista de LP en Reporte Entradas</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-lp" action="import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Seleccione los LP que desea que muestre el Reporte</label>
                                <select id="lista_lp" name="lista_lp[]" multiple size="10px" data-folio="" data-proveedor="" data-oc="" style="width: 100%;" placeholder="Todos">
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6"></div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-lp" type="button" class="btn btn-primary" onclick="exportarPDFCodigoBarras()">Imprimir Reporte</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
          </div>
    </div>
</div>

<script src="/js/pdfmake.min.js"></script>
<script src="/js/vfs_fonts.js"></script>
<script src="/js/jszip.min.js"></script>
<!-- Mainly scripts -->
<script src="/js/jquery-2.1.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/utils.js"></script>

<script src="/js/select2.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<script type="text/javascript">


    var exportDataGrid = new ExportDataGrid();
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    function almacenPrede(){ 
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) {
                    document.getElementById('almacen').value = data.codigo.clave;
                    ReloadGrid();
                    //fillSelectZona(data.codigo.id);
                }
            },
            error: function(res){
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
            url:'/api/adminentrada/lista/index.php',
            datatype: "local",
            //datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                search: $("#txtCriterio").val(),
                refWell: $("#refWell").val(),
                pedimentoW: $("#pedimentoW").val(),
                filtro: $("#filtro").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                almacen: $("#almacen").val(),
                tipo_ent: $("#tipo_entrada").val(),
                status: $("#status").val(),
                presupuesto: $("#presupuestoSelect").val(),
                tipo_entrada: <?php if(isset($_GET['m'])){ echo "'".$_GET['m']."'";} else echo '0'; ?>,
                cve_cliente: $("#cve_cliente").val(),
                cve_proveedor: $("#cve_proveedor").val(),
                criterio: $("#txtCriterio").val()
            },
            mtype: 'POST',
            colModel:[
                {label: 'Acciones' ,name:'myac',index:'',fixed:true, sortable:false, resize:false, formatter:imageFormat, width:400},
                {label: 'Tipo' ,name:'tipo',index:'tipo', editable:false, sortable:false, width:80, align:'center'},
                {label: 'Numero OC' ,name:'numero_oc',index:'numero_oc', width:120,editable:false, hidden:false, sortable:false, align:'center'},
                {label: 'Numero de Orden (ERP)' ,name:'erp',index:'erp', width:200, align:'left'},
                {label: 'Folio Entradas' ,name:'folio_entradas',index:'folio_entradas', width:120, hidden:false, align:'center'},
                {label: 'Factura | Remisión' ,name:'facprov',index:'facprov', editable:false, hidden:false, sortable:false, width:200},
                {label: 'Proyecto' ,name:'proyecto',index:'proyecto', editable:false, hidden:false, sortable:false, width:200},
                {label: 'Referencia Well' ,name:'ref_well',index:'ref_well', editable:false, sortable:false, align:'center', width:200, hidden: <?php if($instancia == 'welldex' || $instancia == 'dev') echo "false";else echo "true"; ?>},
                {label: 'Pedimento' ,name:'pedim_well',index:'pedim_well', editable:false, sortable:false, align:'center', width:200, hidden: <?php if($instancia == 'welldex' || $instancia == 'dev') echo "false";else echo "true"; ?>},
                {label: 'Fecha Recepcion' ,name:'fecha_recepcion',index:'fecha_recepcion', editable:false, sortable:false, align:'center', width:200},
                {label: 'Total Pedido (Pzas)' ,name:'total_pedido',index:'total_pedido',editable:false, sortable:false, width:150, align:'right'},
                {label: 'Cantidad Recibida (Pzas)' ,name:'cantidad_recibida',index:'cantidad_recibida',editable:false, sortable:false, width:155, align:'right'},
                {label: 'Peso Est. (Kgs)' ,name:'peso_estimado',index:'peso_estimado',editable:false, sortable:false, width:150, align:'right'},
                {label: 'Fecha E. Solicitada' ,name:'fecha_entrega',index:'fecha_entrega', editable:false, sortable:false, align:'center', width:200},
                {label: 'Fecha Fin Recepcion' ,name:'fecha_fin_recepcion',index:'fecha_recepcion', editable:false, align:'center', sortable:false, width:200},
                {label: 'Status' ,name:'estado',index:'estado', editable:false, sortable:false, hidden:false, width:140, align:'center'},
                {label: 'Usuario Activo' ,name:'usuario_activo',index:'usuario_activo', editable:false, sortable:false, hidden:false, width:200},
                {label: '% de Recepcion' ,name:'porcentaje_recibido',index:'porcentaje_recibido', editable:false, hidden:false, sortable:false, width:200, align:'center'},
                {label: 'Tipo de Entrada' ,name:'tipo_entrada',index:'tipo_entrada', editable:false, hidden:false, sortable:false, width:200, align:'center'},
                {label: 'Tipo de Recurso', name:'recurso',index:'recurso', editable:false, hidden:true, sortable:false, width:200, align:'center'},
                {label: 'Tipo de Procedimiento' ,name:'procedimiento',index:'procedimiento', editable:false, hidden:true, sortable:false, width:200, align:'center'},
                {label: 'Dictamen' ,name:'dictamen',index:'dictamen', editable:false, hidden:true, sortable:false, width:200},
                {label: 'Presupuesto' ,name:'presupuesto',index:'presupuesto', editable:false, hidden:true, sortable:false, width:250},
                {label: 'Condiciones de Pago' ,name:'condicionesDePago',index:'condicionesDePago', editable:false, hidden:true, sortable:false, width:200},
                {label: 'Lugar de Entrega' ,name:'lugarDeEntrega',index:'lugarDeEntrega', editable:false, hidden:true, sortable:false, width:200},
                {label: 'Fecha de Fallo' ,name:'fechaDeFallo',index:'fechaDeFallo', editable:false, hidden:true, sortable:false, width:150, align: "center"},
                {label: 'Plazo de Entrega' ,name:'plazoDeEntrega',index:'plazoDeEntrega', editable:false, hidden:true, sortable:false, width:130, align: "center"},
                {label: 'No. de Expediente' ,name:'numeroDeExpediente',index:'numeroDeExpediente', editable:false, hidden:true, sortable:false, width:150},
                {label: 'Importe de la Entrada' ,name:'importe',index:'importe', editable:false, hidden:false, sortable:false, width:150, align:'right'},
              {label: 'Area Solicitante' ,name:'areaSolicitante',index:'areaSolicitante',width:150, editable:false, sortable:false, hidden:true},
              {label: 'Numero de Suficiencia' ,name:'numSuficiencia',index:'numSuficiencia',width:150, editable:false, sortable:false, hidden:true},
              {label: 'Fecha de Suficiencia' ,name:'fechaSuficiencia',index:'fechaSuficiencia',width:150, editable:false, sortable:false, hidden:true, align:'center'},
              {label: 'Fecha de Contrato' ,name:'fechaContrato',index:'fechaContrato',width:150, editable:false, sortable:false, hidden:true, align:'center'},
              {label: 'Monto de Suficiencia' ,name:'montoSuficiencia',index:'montoSuficiencia',width:150, editable:false, sortable:false, hidden:true, align:'right'},
              {label: 'Numero de Contrato' ,name:'numeroContrato',index:'numeroContrato',width:150, editable:false, sortable:false, hidden:true},
               {label: 'Empresa' ,name:'empresa_proveedor',index:'empresa_proveedor', width:220, editable:false, sortable:false, align:'left'},
               {label: 'envioSAP_disponible' ,name:'envioSAP_disponible',index:'envioSAP_disponible', width:220, editable:false, sortable:false, align:'left', hidden:true},
               {label: 'Proveedor' ,name:'proveedor',index:'proveedor', width:220, editable:false, sortable:false, align:'left'},
               {label: 'ID_Protocolo' ,name:'ID_Protocolo',index:'ID_Protocolo', width:220, editable:false, sortable:false, align:'left', hidden: true},
            ],
            
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'numero_oc',
            pgbuttons: true,
            viewrecords: true,
            userDataOnFooter: true,
            footerrow: true,
            loadonce: false,
            sortorder: "desc",
            loadComplete: function(res){console.log("LOAD SUCESS RES", res); total_records();},//[almacenPrede() , total_records()],//
            loadError: function(data){console.log("ERROR Load", data);}

            
          
        });
        
        
        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
            {edit: false, add: false, del: false, search: false},
            {height: 200, reloadAfterSubmit: true}
        );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
        function imageFormat( cellvalue, options, rowObject ){
            //return '<img src="'+cellvalue+'" />&nbsp;&nbsp;&nbsp;&nbsp;<img src="'+cellvalue+'" />';
            var serie = rowObject[4];
            var proveedor = rowObject[37];
            var envioSAP_disponible = rowObject[36];
            var ID_Protocolo = rowObject[38];
            //var oc = rowObject[3];
            var oc = rowObject[2];
            var erp = rowObject[3];
			var tipo = rowObject[1];
            var proyecto = rowObject[6];
            var correl = rowObject[12];
            var porc_recepcion = rowObject[17];
	          var estatus = rowObject[16];
              //var oc_pdf = oc;
              //console.log('Almacen = ',$("#almacen").val(), 'Tipo = ', tipo, 'Serie = ', serie, 'OC = ', oc);
              if(tipo == 'DV' || tipo == 'DVL' || tipo == "DVP" || tipo == "DPL" || tipo == "TR")
                oc = rowObject[4];

            if(tipo == 'RL')
                oc = serie;

            var url = "x/?serie="+serie+"&correl="+correl;
            var url2 = "v/?serie="+serie+"&correl="+correl;
            $("#hiddenID_Aduana").val(serie);


            // la funcion downloadxml ejecuta el index.php que esta en la carpeta x pasandole la serie y el correlativo via get
            // la funcion viewPdf ejecuta el index.php que esta en la carpeta v pasandole la serie y el correlativo via get

            var html = '';
            //html += '<a href="#" onclick="ver(\''+serie+'\')"><i class="fa fa-eye" alt="Ver"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            html += '<a href="#" onclick="editar(\''+serie+'\',\''+oc+'\')"><i class="fa fa-search" title="Ver detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            html += '<a href="#" onclick="verDetalle(\'' + serie + '\')"><i class="fa fa-truck" alt="Ver Detalle" title="Inspección de Transporte"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            if(($("#instancia").val() == 'welldex' || $("#instancia").val() == 'dev'))// && tipo == 'OC'
            {
                html += '<a href="#" onclick="verDetalleEdicion(\''+serie+'\', \'' + oc + '\', \'' + tipo + '\')"><i class="fa fa-pencil-square-o" alt="Editar Pedimento y Referencia" title="Editar Pedimento y Referencia"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if(($("#instancia").val() == 'repremundo' || $("#instancia").val() == 'dev'))// && tipo == 'OC'
            {
                html += '<a href="#" onclick="verDetalleEdicionProyecto(\''+serie+'\', \'' + oc + '\', \'' + proyecto + '\')"><i class="fa fa-pencil-square-o" alt="Editar Proyecto" title="Editar Proyecto"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }


            html += '<a href="#" onclick="exportarPDF(\''+oc+'\',\''+tipo+'\')"><i class="fa fa-file-pdf-o" title="PDF OC"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="exportarPDFEntradas(\''+serie+'\',\''+erp+'\', \''+tipo+'\')"><i class="fa fa-file-pdf-o" title="PDF Recepción"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="#" onclick="printExcel(\''+serie+'\',\''+tipo+'\')" title="Imprimir Excel"><i class="fa fa-file-excel-o"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //if(tipo == 'OC')
            //{
                //html += '<a href="/api/koolreport/excel/reporte_de_entradas/export.php?folio='+serie+'" target="_blank" title="Reporte de Entradas"><i class="fa fa-file-excel-o"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<a href="#" onclick="printExcelReporteEntradas(\''+serie+'\')" title="Reporte de Entradas"><i class="fa fa-file-excel-o"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //}

            <?php 
            if($_SERVER['HTTP_HOST'] == 'avavex.assistpro-adl.com')
            {
            ?>
            html += '<a href="/api/koolreport/export/reportes/entradas/reporte-entradas?folio='+serie+'&oc='+oc+'&unidad='+0+'&proveedor='+proveedor+'" target="_blank"><i class="fa fa-barcode" title="Código de barras"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="/api/koolreport/export/reportes/entradas/reporte-entradas?folio='+serie+'&oc='+oc+'&unidad='+1+'&proveedor='+proveedor+'" target="_blank"><i class="fa fa-barcode" title="Etiqueta por embalaje"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            <?php 
            }
            else
            {
            ?>
            var cve_cia = '<?php echo $_SESSION['cve_cia']; ?>';
            html += '<a href="#" onclick="exportarPDFCodigoBarras(\''+serie+'\',\''+proveedor+'\',\''+oc+'\')" ><i class="fa fa-barcode" title="Código de barras"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="/api/koolreport/export/reportes/entradas/reporte-entradas4x6?folio='+serie+'&oc='+oc+'&proveedor='+proveedor+'&cve_cia='+cve_cia+'" target="_blank"><i class="fa fa-barcode" title="Código de barras"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="/api/koolreport/export/reportes/entradas/reporte-entradas-cajas?folio='+serie+'&oc='+oc+'&proveedor='+proveedor+'&cve_cia='+cve_cia+'" target="_blank"><i class="fa fa-barcode" title="Código de barras de Cajas"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            html += '<a href="/api/koolreport/export/reportes/entradas/reporte-entradas-contenedores?folio='+serie+'&oc='+oc+'&proveedor='+proveedor+'&cve_cia='+cve_cia+'" target="_blank"><i class="fa fa-barcode" title="Código de barras de Pallet/Contenedor"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //$_SERVER['HTTP_HOST']

            var usuario_destinatario_email = $("#cve_usuario").val();
            var enlace = '/api/koolreport/export/reportes/entradas/reporte-entradas4x6?folio='+serie+'&oc='+oc+'&proveedor='+proveedor+'&cve_cia='+cve_cia;
            html += '<a href="#" onclick="enviar_email(\''+serie+'\',\''+enlace+'\', \''+usuario_destinatario_email+'\')"><i class="fa fa-envelope" title="Enviar por Mail"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;'

            <?php 
            }
            ?>
            if($("#permiso_editar").val() == 1 && $("#permiso_registrar").val() == 1)
            html += '<a href="#" onclick="ver_fotos(\''+serie+'\')"><i class="fa fa-camera" title="Fotos"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            <?php 
            if($ValorSAP == 1)
            {
            ?>
            if(tipo == 'OC'  && $("#permiso_registrar").val() == 1)
            html += '<a href="#" onclick="AbrirModalSAP(\''+serie+'\', \''+oc+'\')"><i class="fa fa-server" title="Enviar OC a SAP"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            <?php 
            }
            ?>
            if($("#permiso_eliminar").val() == 1)//$("#cve_proveedor").val() == '' && 
                html += '<a href="#" onclick="borrarEntrada(\''+serie+'\')"><i class="fa fa-eraser" title="Borrar Entrada"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

            //return '<a href="#" onclick="downloadxml(\''+url+'\')"><i class="ace-icon fa fa-download" alt="Download"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="viewPdf(\''+url2+'\')"><i class="ace-icon fa fa-file-pdf-o" alt="Ver PDF"></i></a>';
            return html;
        }

		function imageFormat2( cellvalue, options, rowObject ){
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\''+correl+'\')"><i class="fa fa-eraser no-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    function fillSelectZonaOC(id)
    {
        var almacen= id;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_almacen : almacen,
                action : "loadPorAlmacen2",
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/cortinaentrada/update/index.php',
            success: function(data) 
            {
                //console.log("SUCCESS ZONA = ", data);
                var options = $("#zonarecepcioniOC");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zonas.length; i++)
                {
                    options.append(new Option(data.zonas[i].cve_ubicacion+" - "+data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                }
            }, error: function(data)
            {
                //console.log("ERROR ZONA = ", data);
            }
        });
    }   

    function fillSelectZona(id)
    {
        var almacen= id;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_almacen : almacen,
                action : "loadPorAlmacen2",
                excludeInventario: 0
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
            url: '/api/cortinaentrada/update/index.php',
            success: function(data) 
            {
                var options = $("#zonarecepcioni");
                options.empty();
                options.append(new Option("Seleccione", ""));
                for (var i=0; i<data.zonas.length; i++)
                {
                    options.append(new Option(data.zonas[i].cve_ubicacion+" - "+data.zonas[i].desc_ubicacion, data.zonas[i].cve_ubicacion));
                }
            }
        });
    }   

    //////////////////////////////////////////////////////////Aqui se contruye el Grid 2 //////////////////////////////////////////////////////////
	/// Aqui se construye el grid de nueva orden /////
	  $(function($) {
        var grid_selector = "#grid-table2";
        var pager_selector = "#grid-pager2";

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
            url:'/api/adminentrada/update/index.php',
            datatype: "local",
            shrinkToFit: false,
	          height:'auto',
            mtype: 'POST',
            colNames:['Linea','Clave','Descripcion','Pendiente por Recibir','Cantidad Recibida','Diferencia'],
            colModel:[
                {name:'linea',index:'linea',width:150, editable:false, sortable:false},
	            {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:300, editable:false, sortable:false},
                {name:'cantidad_pedida',index:'cantidad_pedida',width:150, sortable:false},
                {name:'cantidad_recibida',index:'cantidad_recibida',width:150, editable:false, sortable:false},
                {name:'cantidad_faltante',index:'cantidad_faltante',width:150, editable:false, hidden:false, sortable:false},

            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'linea',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
        });

        // Setup buttons
        $("#grid-table2").jqGrid('navGrid', '#grid-pager2',
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

    // Grid Avanzado
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
            url:'/api/adminentrada/update/index.php',
            datatype: "local",
            shrinkToFit: false,
      height:'auto',
            mtype: 'POST',
            colNames:['Fotos', 'Linea', 'Pallet/Contenedor', 'LP', 'CJ','Clave', 'Lote', 'Caducidad', 'Serie', 'Descripcion', 'Pallet', 'Cajas', 'Piezas','Pendiente por Acomodar','Cantidad Ubicada','Diferencia','Fecha de Recepcion','Fecha/ Hora Inicio','Fecha/ Hora Fin','Pendiente | Dañado', "Factura Articulo","Usuario"],
            colModel:[
                {name:'myac',index:'myac',width:50, editable:false, sortable:false, align:'center'},
                {name:'linea',index:'linea',width:50, editable:false, sortable:false, align:'center'},
                {name:'contenedor',index:'contenedor',width:150, editable:false, sortable:false},
                {name:'LP',index:'LP',width:150, editable:false, sortable:false},
                {name:'CJ',index:'CJ',width:150, editable:false, sortable:false},
                {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'lote',index:'lote',width:150, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:150, editable:false, sortable:false, align:'center'},
                {name:'serie',index:'serie',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},

                {name:'pallet',index:'pallet',width:150, editable:false, sortable:false, align:'right'},
                {name:'cajas',index:'cajas',width:150, editable:false, sortable:false, align:'right'},
                {name:'piezas',index:'piezas',width:150, editable:false, sortable:false, align:'right'},

                {name:'cantidad_pedida',index:'cantidad_pedida',width:150, sortable:false, align:'right'},
                {name:'cantidad_recibida',index:'cantidad_recibida',widsth:150, editable:false, sortable:false, align:'right'},
                //{name:'status',index:'status',width:100, editable:false, sortable:false, hidden:true},
                {name:'fecha_recepcion',index:'fecha_recepcion',width:150, editable:false, sortable:false, align:'right'},
                
                {name:'hora_inicio',index:'hora_inicio',width:170, editable:false, sortable:false, align:'center'},
                {name:'hora_fin',index:'hora_fin',width:170, editable:false, sortable:false},
                {name:'cantidad_faltante',index:'cantidad_faltante',width:150, editable:false, sortable:false, align:'right'},
                {name:'cantidad_danada',index:'cantidad_danada', align:'right', width:150, editable:false, sortable:false},
                {name:'factura_articulo',index:'factura_articulo', width:150, editable:false, sortable:false, align:'left'},

                {name:'usuario_activo',index:'usuario_activo',width:150, editable:false, sortable:false}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            },
            loadComplete: function(data){console.log("SUCCESS DETALLE = ", data);},
            loadError: function(data){console.log("SUCCESS DETALLE = ", data);}
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

    //grid de bitacora

    $(function($) {
          var grid_selector = "#grid-table4";
          var pager_selector = "#grid-pager4";

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
              url:'/api/adminentrada/update/index.php',
              datatype: "local",
              shrinkToFit: false,
              height:'auto',
              mtype: 'POST',
              colNames:['Fecha Inicio', 'Fecha Fin', 'Usuario', 'Que hizo'],
              colModel:[
                  {name:'fecha_inicio',index:'fecha_inicio',width:150, editable:false, sortable:false},
                   {name:'fecha_fin',index:'fecha_fin',width:150, editable:false, sortable:false},
                  {name:'usuario',index:'usuario',width:200, editable:false, sortable:false},
                  {name:'quehizo',index:'quehizo',width:200, editable:false, sortable:false}

              ],
              rowNum:10,
              rowList:[10,20,30],
              pager: pager_selector,
              sortname: 'linea',
              viewrecords: true,
              sortorder: "desc",
              ondblClickRow: function (rowid, iRow,iCol) {
                  var row_id = $(grid_selector).getGridParam('selrow');
                  $(grid_selector).editRow(row_id, true);
              }
          });

          // Setup buttons
          $("#grid-table4").jqGrid('navGrid', '#grid-pager2',
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

    //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
    function ReloadGrid() {
      console.log("1");
      console.log("fechai = ", $("#fechai").val());
      console.log("fechaf = ", $("#fechaf").val());
      $('#grid-table').jqGrid('clearGridData')
            .jqGrid('setGridParam', {postData: {
                search: $("#txtCriterio").val(),
                refWell: $("#refWell").val(),
                pedimentoW: $("#pedimentoW").val(),
                filtro: $("#filtro").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                almacen: $("#almacen").val(),
                status: $("#status").val(),
                tipo_ent: $("#tipo_entrada").val(),
                cve_cliente: $("#cve_cliente").val(),
                presupuesto: $("#presupuestoSelect").val()
            }, datatype: 'json', page : 1})
            .trigger('reloadGrid',[{current:true}]);
        
      
            total_records();

    }

    function loadResumen(codigo, oc) 
    {
			console.log("1",codigo);
			console.log("2",oc);
      $('#grid-table2').jqGrid('clearGridData').jqGrid('setGridParam', {postData: 
      {
        action: 'loadResumen',
        codigo: codigo,
				oc: oc
      }, datatype: 'json'})
      .trigger('reloadGrid',[{current:true}]);
      
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/adminentrada/update/index.php',
        data: 
        {
          action: 'datosResumen',
          codigo: codigo,
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
          console.log(data);
          if (data.success == true)
          {
            $('#numeroDetalle').text("Folio de Entrada:"+" "+codigo);
            $('#proveedorDetalle').text("Proveedor:"+" "+data.nombre_proveedor);
            if((data.id_ocompra !="0") && (data.id_ocompra !=null))
            {
              console.log(data.id_ocompra);
              $('#ordenCompraDetalle').toggle(true); 
              $('#ordenCompraDetalle').text("Orden de Compra:"+" "+data.id_ocompra);
            }
            else
            {
							console.log("");
              $('#ordenCompraDetalle').toggle(false); 
              $('#ordenCompraDetalle').text("");
            }
          }
        }
      });
    }



        $('#almacenes').change(function(e) {
            
            var val_almacenes = $(this).val();
            var id_almacenes = val_almacenes.split("**WMS-WMS**")[1];

            fillSelectZona(id_almacenes);
        });

        $('#almacenesOC').change(function(e) {
            
            var val_almacenes = $(this).val();
            var id_almacenes = val_almacenes.split("-")[1];
            $("#almacen_clave_oc").val(val_almacenes.split("-")[0])

            fillSelectZonaOC(id_almacenes);
        });

      $('#Protocol').change(function(e) {
        var ID_Protocolo= $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo : ID_Protocolo,
                action : "getConsecutivo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                console.log(data);
                $('#Consecut').val(data.consecutivo);
            }, error: function(data) {
                console.log("ERROR", data);
            }

        });

    });

      $('#ProtocolOC').change(function(e) {
        var ID_Protocolo= $(this).val();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                ID_Protocolo : ID_Protocolo,
                action : "getConsecutivo"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/ordendecompra/update/index.php',
            success: function(data) {
                console.log(data);
                $('#ConsecutOC').val(data.consecutivo);
            }, error: function(data) {
                console.log("ERROR", data);
            }

        });

    });

    function loadDetalle(codigo, ordenCompra) 
    {
      $('#grid-table3').jqGrid('clearGridData').jqGrid('setGridParam', {postData:
      {
        action: 'loadDetalle',
        codigo: codigo
      }, datatype: 'json'}).trigger('reloadGrid',[{current:true}]);
    }
  
    function loadBitacora(codigo) 
    {
      $('#grid-table4').jqGrid('clearGridData').jqGrid('setGridParam', {postData: 
      {
        action: 'loadBitacora',
        codigo: codigo
      }, datatype: 'json'}).trigger('reloadGrid',[{current:true}]);
    }

    function filtralo()
    {
      console.log("fechai2 = ", $("#fechai").val());
      console.log("fechaf2 = ", $("#fechaf").val());
      $('#grid-table').jqGrid('clearGridData')
          .jqGrid('setGridParam', {postData: {
               criterio: $("#txtCriterio").val(),
               refWell: $("#refWell").val(),
               pedimentoW: $("#pedimentoW").val(),
               search: $("#txtCriterio").val(),
      fechaInicio: $("#fechai").val(),
      fechaFin: $("#fechaf").val(),
      almacen: $("#almacen").val(),
      tipo_ent: $("#tipo_entrada").val(),
      cve_cliente: $("#cve_cliente").val(),
      status: $("#status").val()
          }, datatype: 'json', page : 1, fromServer: true})
      .trigger('reloadGrid',[{current:true}]);
    }

    function downloadxml( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function viewPdf( url ) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    $modal0 = null;

    function editar(_codigo, ordenCompra) 
    {
      //loadResumen(_codigo, ordenCompra);
      $("#numeroDetalle").text("Folio de Entrada "+_codigo);
      loadDetalle(_codigo, ordenCompra);
      //loadBitacora(_codigo, ordenCompra);
      $modal0 = $("#coModal");
      $modal0.modal('show');
	}

    function ver_fotos(_codigo)
    {
        $("#folio_foto_th").val(_codigo);
        $("#n_folio").text(_codigo);
        $modal0 = $("#fotos_oc_th");
        $modal0.modal('show');
        ImprimirFotos_th(_codigo);
    }

    function ver_fotos_td(id, descripcion)
    {
        console.log("id", id);
        console.log("descripcion", descripcion);

        $("#id_foto_td").val(id);
        $("#desc_id_td").text(descripcion);

        $modal0 = $("#fotos_oc_td");
        $modal0.modal('show');
        ImprimirFotos_td(id);

    }

    function borrarEntrada(folio)
    {

            swal({
                title: "Borrar Entrada #"+folio+"?",
                text: "¿Está Seguro de Proceder a Borrar la Entrada? Esta operación no se puede deshacer",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {

                borrarEntradaOK(folio);

            });

    }

    function borrarEntradaOK(folio)
    {
          $.ajax({
              type: "POST",
              dataType: "json",
              data: 
              {
                  folio: folio,
                  action : "borrar_entrada"
              },
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },
              url: '/api/adminentrada/update/index.php',
              success: function(data) 
              {
                    console.log("SUCCESS ELIM= ", data);

                    swal("Éxito", "Entrada Eliminada Con éxito"+data.mensaje, "success");
                    ReloadGrid();
              }, error: function(data) 
              {
                  console.log("ERROR = ", data);
              }
          });
    }

    function ImprimirFotos_th(folio)
    {
        console.log("ImprimirFotos_th", folio);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  folio: folio,
                  action : "cargarFotosTH"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/adminentrada/update/index.php',
              success: function(data) 
              {

                $("#fotos_th").html(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function ImprimirFotos_td(id)
    {
        console.log("ImprimirFotos_td", id);
        DescripcionDefecto_td(id);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  id: id,
                  action : "cargarFotosTD"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/adminentrada/update/index.php',
              success: function(data) 
              {

                $("#fotos_td").html(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function DescripcionDefecto_td(id)
    {
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  id: id,
                  action : "DescripcionDefectoTD"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/adminentrada/update/index.php',
              success: function(data) 
              {

                $("#descripcion_foto_td").val(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function eliminar_foto_th(id)
    {
        console.log("eliminar_foto_th", id);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  id: id,
                  action : "eliminarFotosTH"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/adminentrada/update/index.php',
              success: function(data) 
              {

                ImprimirFotos_th($("#folio_foto_th").val());

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function eliminar_foto_td(id)
    {
        console.log("eliminar_foto_td", id);
          $.ajax({
              type: "POST",
              //dataType: "json",
              data: 
              {
                  id: id,
                  action : "eliminarFotosTD"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/adminentrada/update/index.php',
              success: function(data) 
              {

                ImprimirFotos_td($("#id_foto_td").val());

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function CerrarDetalle()
    {
        $('#DetalleTransporte').hide();

        $("#btnGuardarTransporte").hide();
        $(".input_transporte").css("background-color", "transparent");
        $(".input_transporte").attr("readonly", "readonly");
        $("#cve_transportadora").prop("disabled", true);
        $("#btnEditarTransporte").show();
        $('.chosen-select').chosen();
        $('#cve_transportadora').trigger("chosen:updated");

    }

    function CerrarDetalleEdicionProyecto()
    {
        $('#DetalleEdicionProyecto').hide();

        $("#btnGuardarEdicionProyecto").hide();
        $(".input_edicionProyecto").css("background-color", "transparent");
        $(".input_edicionProyecto").attr("readonly", "readonly");
        $("#claveproyectoEdicion").prop("disabled", true);
        $("#btnEditarEdicionProyecto").show();
        $('.chosen-select').chosen();
        $('#claveproyectoEdicion').trigger("chosen:updated");

    }

    function CerrarDetalleEdicion()
    {
        $('#DetalleEdicion').hide();

        $("#btnGuardarEdicion").hide();
        $(".input_edicion").css("background-color", "transparent");
        $(".input_edicion").attr("readonly", "readonly");
        $("#cve_transportadora").prop("disabled", true);
        $("#btnEditarEdicion").show();
        $('.chosen-select').chosen();
        $('#cve_transportadora').trigger("chosen:updated");

    }

      function verDetalleEdicion(folio, oc, tipo)
    {

        console.log("DetalleEdicion");
        console.log("folio = ", folio);
        console.log("oc = ", oc);
        console.log("tipo = ", tipo);
        //$('#DetalleTransporte').show();

    //return;

       $("#folio_ent3").text(oc);
       $("#folio_ent2").text(folio);
       $("#folio_tipo").val(tipo);

       $.ajax({
            url: "/api/adminentrada/lista/index.php",
            type: "POST",
            dataType: "json",
            data: {
                folio : folio,
                oc : oc,
                tipo : tipo,
                action:"DetalleEdicion"
            },
            success: function(data) {
                console.log("DetalleEdicion = ", data);

                //$("#folio_ent2").text(folio);
                $("#pedimentoW_edit").val(data.pedimento);
                $("#referenciaW_edit").val(data.referencia);
                $('#DetalleEdicion').show();

            }, error: function(data){
                console.log("DetalleEdicion ERROR = ", data);
            }
        });

    }

      function verDetalleEdicionProyecto(folio, oc, proyecto)
    {

        console.log("DetalleEdicion");
        console.log("folio = ", folio);
        console.log("oc = ", oc);
        console.log("proyecto = ", proyecto);
        //$('#DetalleTransporte').show();
        $('#claveproyectoEdicion').val(proyecto);
        $('#claveproyectoEdicion').trigger("chosen:updated");

    //return;

       $("#folio_ent3Proyecto").text(oc);
       $("#folio_ent2Proyecto").text(folio);
       //$("#folio_tipo").val(tipo);

      $('#DetalleEdicionProyecto').show();
  /*
       $.ajax({
            url: "/api/adminentrada/lista/index.php",
            type: "POST",
            dataType: "json",
            data: {
                folio : folio,
                oc : oc,
                proyecto : proyecto,
                action:"DetalleEdicionProyecto"
            },
            success: function(data) {
                console.log("DetalleEdicionProyecto = ", data);

                //$("#folio_ent2").text(folio);
                //$("#pedimentoW_edit").val(data.pedimento);
                //$("#referenciaW_edit").val(data.referencia);
                $('#DetalleEdicionProyecto').show();

            }, error: function(data){
                console.log("DetalleEdicionProyecto ERROR = ", data);
            }
        });
*/
    }

      function verDetalle(folio)
    {

        console.log("DetalleTransporte");
        //$('#DetalleTransporte').show();

    //return;

       $.ajax({
            url: "/api/adminentrada/lista/index.php",
            type: "POST",
            dataType: "json",
            data: {
                folio : folio,
                action:"DetalleTransporte"
            },
            success: function(data) {
                console.log("DetalleTransporte = ", data);

                $("#folio_ent").text(folio);
                $("#nombre_operador").val(data.Operador);
                $("#id_chofer").val(data.Id_Operador);
                $("#num_unidad").val(data.No_Unidad);
                $("#cve_transportadora").val(data.Linea_Transportista);
                $("#placa").val(data.Placas);
                $("#sello").val(data.Sello);
                $("#fecha_transp").val(data.Fec_Ingreso);
                $("#hora_transp").val(data.Hora_Ingreso);
                $("#observaciones_transp").val(data.Observaciones);
                $('.chosen-select').chosen();
                $('#cve_transportadora').trigger("chosen:updated");

        $('#DetalleTransporte').show();

            }, error: function(data){
                console.log("DetalleExistencia ERROR = ", data);
            }
        });

    }

    function cancelar() {
        $(':input','#myform')
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
            .val('');
        $('#FORM').removeAttr('class').attr('class', '');
        $('#FORM').addClass('animated');
        $('#FORM').addClass("fadeOutRight");
        $('#FORM').hide();

        $('#list').show();
        $('#list').removeAttr('class').attr('class', '');
        $('#list').addClass('animated');
        $('#list').addClass("fadeInRight");
        $('#list').addClass("wrapper");
        $('#list').addClass("wrapper-content");
    }

    $('#data_3').datetimepicker({
        locale: 'es',
       format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_4').datetimepicker({
        locale: 'es',
       format: 'DD-MM-YYYY',
        useCurrent: false
    });

    $('#data_2 .input-group.date').datepicker({
          todayBtn: "linked",
          keyboardNavigation: false,
          forceParse: false,
          calendarWeeks: true,
          autoclose: true,
      format: 'dd-mm-yyyy'
      });

    $('#data_2 .input-group.date').datepicker('update', new Date());

    $(document).ready(function(){

        console.log("session_cve_cliente", $("#cve_cliente").val());
        console.log("session_cve_proveedor", $("#cve_proveedor").val());



    $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
        $("#basic").select2();

        $("#multi").select2({
            placeholder: "Select a country"
        });


        $("#minimum").select2({
            minimumInputLength: 2
        });
        $('.chosen-select').chosen();
        $('.chosen-select, #proveedor').trigger("chosen:updated");


    //        $.ajax({
        //     type: "POST",
        //     dataType: "json",
        //     data: {
        //          criterio: $("#txtCriterio").val(),
    // 		fechaInicio: $("#fechai").val(),
    // 		fechaFin: $("#fechaf").val(),
    // 		almacen: $("#almacen").val(),
    // 		status: $("#status").val(),
        //         action : "totalesPedido"
        //     },
        //     beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        //     },
        //     url: '/api/adminentrada/update/index.php',
        //     success: function(data) {
        //         if (data.success == true) {
    // 			if (data.total_pedido){
        //
    // 		$("#totales").empty();
    // 		$("#totales").append("Total Pedido: "+data.total_pedido);
    // 			} else{
    // 		$("#totales").empty();
    // 		$("#totales").append("Total Pedido: 0");
        //
    // 			}
        //
        //         }
        //     }
        // });

    });

    $("#btnEditarEdicion").click(function()
    {
        $(".input_edicion").css("background-color", "#fff");
        $(".input_edicion").removeAttr("readonly");
        $("#btnGuardarEdicion").show();
        $(this).hide();
        $('.chosen-select').chosen();
    });


    $("#btnEditarEdicionProyecto").click(function()
    {
        //$(".input_edicionProyecto").css("background-color", "#fff");
        //$(".input_edicionProyecto").removeAttr("readonly");
        $("#claveproyectoEdicion").prop("disabled", false);
        $("#btnGuardarEdicionProyecto").show();
        $(this).hide();
        $('.chosen-select').chosen();
    });

    $("#btnEditarTransporte").click(function()
    {
        $(".input_transporte").css("background-color", "#fff");
        $(".input_transporte").removeAttr("readonly");
        $("#cve_transportadora").prop("disabled", false);
        $("#btnGuardarTransporte").show();
        $(this).hide();
        $('.chosen-select').chosen();
        $('#cve_transportadora').trigger("chosen:updated");
    });

    $("#btnGuardarTransporte").click(function()
    {
        $(".input_transporte").css("background-color", "transparent");
        $(".input_transporte").attr("readonly", "readonly");
        $("#cve_transportadora").prop("disabled", true);
        $("#btnEditarTransporte").show();
        $(this).hide();
        $('.chosen-select').chosen();
        $('#cve_transportadora').trigger("chosen:updated");


          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/adminentrada/update/index.php',
            data: 
            {
                folio_ent: $("#folio_ent").text(),
                nombre_operador: $("#nombre_operador").val(),
                id_chofer: $("#id_chofer").val(),
                num_unidad: $("#num_unidad").val(),
                cve_transportadora: $("#cve_transportadora").val(),
                placa: $("#placa").val(),
                sello: $("#sello").val(),
                fecha_transp: $("#fecha_transp").val(),
                hora_transp: $("#hora_transp").val(),
                observaciones_transp: $("#observaciones_transp").val(),
                action: 'EditarTransporte'
            },
            beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            success: function(data) 
            {

              console.log("SUCCESS EditarTransporte", data);
                 if(data == 1)
                 {
                    swal("Éxito", "Edición Realizada Correctamente", "success");
                    $('.chosen-select').chosen();
                    $('#cve_transportadora').trigger("chosen:updated");
                    $('#DetalleTransporte').hide();
                 }
                 else 
                 {
                    swal("Formato de Fecha Incorrecto", "La fecha debe tener formato DD-MM-YYYY y la hora 24:00:00", "warning");
                    $("#fecha_transp").val("");
                    $("#hora_transp").val("");
                 }
            }, error: function(data) 
            {
              console.log("ERROR EjecutarOCSAP", data);
            }
          });

    });

    $("#btnGuardarEdicion").click(function()
    {
        $(".input_edicion").css("background-color", "transparent");
        $(".input_edicion").attr("readonly", "readonly");
        $("#btnEditarEdicion").show();
        $(this).hide();
        $('.chosen-select').chosen();

          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/adminentrada/update/index.php',
            data: 
            {
                folio_oc: $("#folio_ent3").text(),
                folio_ent: $("#folio_ent2").text(),
                tipo: $("#folio_tipo").val(),
                pedimentoW_edit: $("#pedimentoW_edit").val(),
                referenciaW_edit: $("#referenciaW_edit").val(),
                action: 'EditarDatos'
            },
            beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            success: function(data) 
            {

              console.log("SUCCESS Editar", data);
                 if(data == 1)
                 {
                    swal("Éxito", "Edición Realizada Correctamente", "success");
                    $('.chosen-select').chosen();
                    $('#DetalleEdicion').hide();
                    ReloadGrid();
                 }
            }, error: function(data) 
            {
              console.log("ERROR 23", data);
            }
          });

    });

    $("#btnGuardarEdicionProyecto").click(function()
    {
        //$(".input_edicionProyecto").css("background-color", "transparent");
        //$(".input_edicionProyecto").attr("readonly", "readonly");
        $("#claveproyectoEdicion").prop("disabled", true);
        $("#btnEditarEdicionProyecto").show();
        $(this).hide();
        $('.chosen-select').chosen();

        console.log("folio_oc:", $("#folio_ent3Proyecto").text());
        console.log("folio_ent:", $("#folio_ent2Proyecto").text());
        console.log("proyecto:", $("#claveproyectoEdicion").val());
        //return;

          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/adminentrada/update/index.php',
            data: 
            {
                folio_oc: $("#folio_ent3Proyecto").text(),
                folio_ent: $("#folio_ent2Proyecto").text(),
                proyecto: $("#claveproyectoEdicion").val(),
                action: 'EditarDatosProyecto'
            },
            beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            success: function(data) 
            {

              console.log("SUCCESS Editar", data);
                 if(data == 1)
                 {
                    swal("Éxito", "Edición Realizada Correctamente", "success");
                    $('.chosen-select').chosen();
                    $('#DetalleEdicionProyecto').hide();
                    ReloadGrid();
                 }
            }, error: function(data) 
            {
              console.log("ERROR 23", data);
            }
          });

    });

    $("#txtCriterio").keyup(function(event){
        if(event.keyCode == 13){
            $("#buscarA").click();
        }
    });

    $("#exportExcel").on("click", function(){
        window.exportDataGrid.exportExcel("grid-table","administracion_entradas.xls");
    });
  
    $("#exportPDF").on("click", function(){
        window.exportDataGrid.exportPDF("grid-table","administracion_entradas.pdf","A3");
    });
    
/////////////////////////////////////////Mostrar Modal Importar OC/////////////////////////////////////////////////////
    $("#importExcelOC").on("click", function(){
        console.log("mostrar modal de importador");
        $moda200 = $("#modalImportOC");
        $moda200.modal('show');
        $("#almacenesOC").change();

    });
///////////////////////////////////////////////Funcion de Importar///////////////////////////////////////////////
/////////////////////////////////////////Mostrar Modal Importar OC/////////////////////////////////////////////////////
    $("#importRL").on("click", function(){
        //console.log("mostrar modal de importador");
        $moda200 = $("#modalImportEntradaRL");
        $moda200.modal('show');

        $("#almacenes").change();
    });

    $("#btn-enviarSAP").click(function(){

        OCSap($('#folio_entrada').val(), $('#folio_oc').val());

    });

    function OCSap(folio_entrada, folio_oc){
        console.log("folio_oc = ", folio_oc);
        console.log("folio_entrada = ", folio_entrada);

        var data = {};
        var arr1 = [], arr2 = [], arr3 = [], arr4 = [], arr5 = [], arrDetalle = [];
        $(".id_entrada").each(function(i,j){
            console.log("id_entrada = ", i, j.value);
            arr1[i] = j.value;
        });
        data.id_entrada = arr1;

        $(".cve_articulo_oc").each(function(i,j){
            console.log("cve_articulo_oc = ", i, j.value);
            arr2[i] = j.value;
        });
        data.cve_articulo_oc = arr2;

        $(".cve_lote_oc").each(function(i,j){
            console.log("cve_lote_oc = ", i, j.value);
            arr3[i] = j.value;
        });
        data.cve_lote_oc = arr3;

        $(".pedimento_oc").each(function(i,j){
            console.log("pedimento_oc = ", i, j.value);
            arr4[i] = j.value;
        });
        data.pedimento_oc = arr4;

        $(".fecha_pedimento_oc").each(function(i,j){
            console.log("fecha_pedimento_oc = ", i, j.value);
            arr5[i] = j.value;
        });
        data.fecha_pedimento_oc = arr5;


        //arrDetalle.push(data);
        console.log("id_entrada = ", arr1);
        console.log("pedimento_oc = ", arr4);
        console.log("fecha_pedimento_oc = ", arr5);

        //return;
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/adminentrada/update/index.php',
        data: 
        {
          action: 'ConectarSAP',
          funcion: 'Login',
          metodo: 'POST',
          folio_oc: folio_oc
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS CONEX", data);
            //return;
            if(data.SessionId)
            {
                console.log("SessionId OK:", data.SessionId);
                    //return;
                  $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/adminentrada/update/index.php',
                    data: 
                    {
                      action: 'EjecutarOCSAP',
                      id_entrada: arr1,
                      pedimento_oc: arr4,
                      fecha_pedimento_oc: arr5,
                      tipo_cambio: $("#tipo_cambio").val(),
                      folio_entrada: folio_entrada,
                      funcion: 'PurchaseDeliveryNotes',
                      //funcion: 'Drafts',
                      metodo: 'POST',
                      folio_oc: folio_oc,

                      sesion_id: data.SessionId
                    },
                    beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    success: function(data) 
                    {
                      console.log("SUCCESS EjecutarOCSAP", data);
                      if(data.error)
                      {
                         swal("Error", data.error.message.value, "error");
                      }
                      else
                      {
                         swal("Envío Correcto", "Actualización a ERP Correcta", "success");
                         /*RETURN PARA SOLO DEVOLVER JSON*/ 
                         //return;
                         $("#enviarSAP").modal('hide');
                          $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: '/api/adminentrada/update/index.php',
                            data: 
                            {
                              action: 'CambiarAEnviadoOCSAP',
                              folio_entrada: folio_entrada,
                              folio_oc: folio_oc,
                              sesion_id: data.SessionId
                            },
                            beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                            },
                            success: function(data) 
                            {
                                    ReloadGrid();
                            }, error: function(data) 
                            {
                              console.log("ERROR EjecutarOCSAP", data);
                            }
                          });

                      }
                    }, error: function(data) 
                    {
                      console.log("ERROR EjecutarOCSAP", data);
                    }
                  });
            }
            else
            {
                console.log("SessionId ERROR:", data);
                swal("Error", "No hay Conexión a SAP", "error");
            }


        }, error: function(data) 
        {
          console.log("ERROR CONEXION", data);
        }
      });

    }

    function AbrirModalSAP(folio_entrada, folio_oc)
    {

      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/adminentrada/update/index.php',
        data: 
        {
          action: 'getDetalleEntrada',
          folio_entrada: folio_entrada
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS", data);
            $("#datos_sap_oc").empty();
            $("#datos_sap_oc").append(data.datos);
            $("#tipo_cambio").val(data.TipoCambioSAP);
            if(data.datos == "")
            {
                $("#btn-enviarSAP").prop("disabled", true);
            }
            else
                $("#btn-enviarSAP").prop("disabled", false);
            //return;

        }, error: function(data) 
        {
          console.log("ERROR", data);
        }
      });

        $('#folio_entrada').val(folio_entrada);
        $('#folio_oc').val(folio_oc);
        $("#enviarSAP").modal('show');
    }

    $("#convertir_a_oc").click(function(){

        var convertir_a_oc = 0;
        if($("#convertir_a_oc").is(':checked'))
            convertir_a_oc = 1;


        if(convertir_a_oc == 0)
            $("#factura_oc_input").hide(300);
        else
            $("#factura_oc_input").show(300);
    });


///////////////////////////////////////////////Funcion de Importar///////////////////////////////////////////////
    $('#btn-importRL').on('click', function() {//Modo 1

        if($("#almacenes").val() == '')
        {
            swal("Error", "Seleccione un almacén", "error");
            return;
        }
        if($("#proveedor").val() == '')
        {
            swal("Error", "Seleccione un proveedor", "error");
            return;
        }
        if($("#txtUsuario").val() == '')
        {
            swal("Error", "Seleccione un usuario", "error");
            return;
        }

        if($("#zonarecepcioni").val() == '')
        {
            swal("Error", "selecciona una zona de recepción", "error");
            return;
        }

        if($("#factura_oc").val() == '' && $("#convertir_a_oc").is(':checked'))
        {
            swal("Error", "Debe Registrar un Número de Orden | Cross Docking (ERP) para la OC", "error");
            return;
        }

        if($("#Protocol").val() == '')
        {
            swal("Error", "Seleccione un Protocolo", "error");
            return;
        }
        if($("#file").val() == '' || $("#file").val() == null)
        {
            swal("Error", "Debe seleccionar un archivo excel para importar", "error");
            return;
        }

        var palletizar = 0;
        if($("#palletizar_entrada").is(':checked'))
            palletizar = 1;

        $("#palletizar_entrada").val(palletizar);


        var sobreescribir_existencias = 0;
        if($("#sobreescribir_existencias").is(':checked'))
            sobreescribir_existencias = 1;

        $("#sobreescribir_existencias").val(sobreescribir_existencias);

        var convertir_a_oc = 0;
        if($("#convertir_a_oc").is(':checked'))
            convertir_a_oc = 1;

        $("#convertir_a_oc").val(convertir_a_oc);


        var mandar_a_rtm = 0;
        if($("#mandar_a_rtm").is(':checked'))
            mandar_a_rtm = 1;

        $("#mandar_a_rtm").val(mandar_a_rtm);

        $('#btn-importRL').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        console.log("instancia = ", $("#instancia").val());
        //return;

        $("#loadgif").show();

        var url_main = '/entradasrl/importar';

        //if($("#instancia").val() == 'dev')
        if($("#instancia").val() == 'welldex')
            url_main = '/entradasrl/importarW';

        var formData = new FormData();
        formData.append("clave", "valor");
        console.log("formData = ", formData);
        $.ajax({
            // Your server script to process the upload
            url: url_main,
            type: 'POST',

            // Form data
            data: new FormData($('#form-import')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
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
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log("SUCESS", data);
                setTimeout(
                    function(){if (data.status == 200) {
                        if(data.success == 1)
                        {
                            swal(
                                {
                                    html: true,
                                    title: "Éxito", 
                                    text: data.statusText, 
                                    type: "success"
                                });
                            $('#modalImportEntradaRL').modal('hide');
                            ReloadGrid();
                        }
                        else
                        swal(
                            {
                                html: true,
                                title: "La importación no se puede realizar", 
                                text: data.statusText, 
                                type: "error"
                            });


                        $("#loadgif").hide();
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000);
            }, error: function(data){
                console.log("ERROR", data);
                
            }
        });
    });

///////////////////////////////////////////////Funcion de Importar///////////////////////////////////////////////

    $('#btn-importOC').on('click', function() {

        if($("#almacenesOC").val() == '')
        {
            swal("Error", "Seleccione un almacén", "error");
            return;
        }

        if($("#empresaOC").val() == '')
        {
            swal("Error", "Seleccione una Empresa", "error");
            return;
        }

        if($("#txtUsuarioOC").val() == '')
        {
            swal("Error", "Seleccione un usuario", "error");
            return;
        }
        if($("#zonarecepcioniOC").val() == '' && !$("#registrar_soloOC").is(':checked'))
        {
            swal("Error", "selecciona una zona de recepción", "error");
            return;
        }

        //if($("#ProtocolOC").val() == '')
        //{
        //    swal("Error", "Seleccione un Protocolo", "error");
        //    return;
        //}

        if($("#fileOC").val() == '' || $("#fileOC").val() == null)
        {
            swal("Error", "Debe seleccionar un archivo excel para importar", "error");
            return;
        }

        var palletizar = 0;
        if($("#palletizar_entradaOC").is(':checked'))
            palletizar = 1;

        $("#palletizar_entradaOC").val(palletizar);


        var sobreescribir_existencias = 0;
        if($("#sobreescribir_existenciasOC").is(':checked'))
            sobreescribir_existencias = 1;

        $("#sobreescribir_existenciasOC").val(sobreescribir_existencias);

        var registrar_soloOC = 0;
        if($("#registrar_soloOC").is(':checked'))
            registrar_soloOC = 1;

        $("#registrar_soloOC").val(registrar_soloOC);


        if($("#zonarecepcioniOC").val() != '' && $("#registrar_soloOC").is(':checked')) $("#zonarecepcioniOC").val("");




        $("#loadgifOC").show();

        $('#btn-importOC').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/ordenescompra/importar',
            type: 'POST',

            // Form data
            data: new FormData($('#form-importOC')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
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
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log("SUCCESS IMPORT OC = ", data);
                setTimeout(
                    function(){if (data.status == 200) {
                        

                        if(data.success == 1)
                        {
                            swal(
                                {
                                    html: true,
                                    title: "Éxito", 
                                    text: data.statusText, 
                                    type: "success"
                                });
                            $('#modalImportOC').modal('hide');
                            ReloadGrid();
                        }
                        else
                        swal(
                            {
                                html: true,
                                title: "La importación no se puede realizar", 
                                text: data.statusText, 
                                type: "error"
                            });

                        //swal("Exito", data.statusText, "success");
                        //$('#modalImportOC').modal('hide');
                        //ReloadGrid();
                    }
                    else {
                        swal("Error", data.statusText, "error");
                    }
                },1000);
                $("#loadgifOC").hide();

            },error: function(data)
            {
                console.log("ERROR IMPORT OC = ", data);
            }
        });
    });
  
  ///////////////////////////////////////////////SUBIR FOTOS TH///////////////////////////////////////////////
    $('#btn-fotos-th').on('click', function() {

        $('#btn-fotos-th').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');
        var formData = new FormData();
        formData.append("clave", "valor");

        if(!$("#descripcion_foto").val() || !$("#file_th").val())
        {
            swal("Advertencia!", "Todos los datos son requeridos", "warning");
            return false;
        }

        $.ajax({
            // Your server script to process the upload
            url: '/ordenescompra/importarfotosth',
            type: 'POST',

            // Form data
            data: new FormData($('#form-subir-fotos-th')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
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
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log('sucess', data);
                setTimeout(
                    function(){if (data.status == 200 && data.statusType == 1) {
                        swal("Exito", data.statusText, "success");
                        //$('#fotos_oc_th').modal('hide');
                        //ReloadGrid();
                        $("#file_th").val("");
                        $("#descripcion_foto").val("");
                        ImprimirFotos_th($("#folio_foto_th").val());
                    }
                    else {
                        $('#fotos_oc_th').modal('hide');
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            },error: function(data) {
                console.log('error', data);
            }
        });
    });

    $('#btn-fotos-td').on('click', function() {

        $('#btn-fotos-td').prop('disable', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');
        var formData = new FormData();
        formData.append("clave", "valor");

        /*!$("#file_td").val() || */
        if(!$("#descripcion_foto_td").val())
        {
            swal("Advertencia!", "Descripción del daño requerida", "warning");
            return false;
        }

        $.ajax({
            // Your server script to process the upload
            url: '/ordenescompra/importarfotostd',
            type: 'POST',

            // Form data
            data: new FormData($('#form-subir-fotos-td')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
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
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = e.loaded / e.total;
                            percentComplete = parseInt(percentComplete * 100);
                            bar.css("width", percentComplete + "%");
                            percent.html(percentComplete+'%');
                            if (percentComplete === 100) {
                                setTimeout(function(){$('.progress').hide();}, 2000);
                            }
                        }
                    } , false);
                }
                return myXhr;
            },
            success: function(data) {
                console.log('sucess', data);
                setTimeout(
                    function(){if (data.status == 200 && data.statusType == 1) {
                        swal("Exito", data.statusText, "success");
                        //$('#fotos_oc_th').modal('hide');
                        //ReloadGrid();
                        $("#file_td").val("");
                        //$("#descripcion_foto").val("");
                        ImprimirFotos_td($("#id_foto_td").val());
                    }
                    else {
                        $('#fotos_oc_td').modal('hide');
                        swal("Error", data.statusText, "error");
                    }
                },1000)
            },error: function(data) {
                console.log('error', data);
            }
        });
    });

    $('#btn-layoutOC').on('click', function(e) {
        window.location.href = '/Layout/Layout_EntradasOC.xlsx';
    }); 
  
    $('#btn-layoutRL').on('click', function(e) {
      //e.preventDefault();  //stop the browser from following
      //window.location.href = 'http://www.tinegocios.com/proyectos/wms/LAYOUT_OC_01.xlsx';//cambiar href por nuevo layout
      //if($("#instancia").val() == 'dev')
      if($("#instancia").val() == 'welldex')
      {
        console.log("Layout_EntradasRL", window.location.href, '/Layout/Layout_EntradasRL_W.xlsx');
        window.location.href = '/Layout/Layout_EntradasRL_W.xlsx';
      }
      else 
      {
        console.log("Layout_EntradasRL", window.location.href, '/Layout/Layout_EntradasRL.xlsx');
        window.location.href = '/Layout/Layout_EntradasRL.xlsx';
      }

    }); 

    function calcularPresupuesto(){
      $.ajax({
          type: "POST",
          dataType: "json",
          data: 
          {
              presupuesto: $("#presupuestoSelect").val(),
              action : "cargarMonto"
          },
          beforeSend: function(x)
          { 
              if(x && x.overrideMimeType) 
              { 
                x.overrideMimeType("application/json;charset=UTF-8"); 
              }
          },
          url: '/api/adminentrada/update/index.php',
          success: function(data) 
          {
              //console.log(data.monto);
              if (data.success == true)
              {
                  if($('#presupuestoSelect').val()!="")
                  {
                      $('#valorDePresupuesto').val("$"+" "+(data.monto)); 
                      $('#importeTotalPresupuesto').val("$"+" "+(data.importeTotal));
                      $('#remanenteDePresupuesto').val("$"+" "+((data.monto)-(data.importeTotal)).toFixed(2));
                  }
                  else
                  {
                      swal("Error", "Selecione un Presupuesto", "error");
                  }
              }
          }
      });

      ReloadGrid();
      /*
      var valor=2;
      var valor2=1;
      if($('#presupuestoSelect').val()!="")
      {
          //$('#valorDePresupuesto').val(valor) 
          //$('#importeTotalPresupuesto').val(valor2)  
          //$('#remanenteDePresupuesto').val(valor-valor2)
      }*/
    }
  
    function exportarPDF(folio,tipo)
    {
            console.log(folio,tipo);
      
                var folio = folio,
                        form = document.createElement('form'),
                        input_nofooter = document.createElement('input'),
                        input_folio = document.createElement('input'),
                        input_tipo = document.createElement('input');
                //var tipo = (tipo == "OC")?1:0;

                form.setAttribute('method', 'post');
                form.setAttribute('action', '/entradas/pdf/exportar');//Class: ReportePDF/EntradasPDF.php->generarReporteEntradas($folio);
                form.setAttribute('target', '_blank');

                input_nofooter.setAttribute('name', 'nofooternoheader');
                input_nofooter.setAttribute('value', '1');

                input_folio.setAttribute('name', 'folio');
                input_folio.setAttribute('value', folio);
                input_tipo.setAttribute('name', 'tipo');
                input_tipo.setAttribute('value', tipo);

                form.appendChild(input_nofooter);
                form.appendChild(input_folio);
                form.appendChild(input_tipo);

                document.getElementsByTagName('body')[0].appendChild(form);
                form.submit();
              
    }

    function exportarPDFEntradas(folio, erp, tipo)
    {
            console.log(folio, erp);
      
                var folio = folio,
                        form = document.createElement('form'),
                        input_nofooter = document.createElement('input'),
                        input_folio = document.createElement('input'),
                        input_tipo = document.createElement('input'),
                        input_oc = document.createElement('input');
                //var tipo = (tipo == "OC")?1:0;

                form.setAttribute('method', 'post');
                form.setAttribute('action', '/entradas/pdf/exportarentradasoc');//Class: ReportePDF/EntradasPDF.php->generarReporteEntradas($folio);
                form.setAttribute('target', '_blank');

                input_nofooter.setAttribute('name', 'nofooternoheader');
                input_nofooter.setAttribute('value', '1');

                input_folio.setAttribute('name', 'folio');
                input_folio.setAttribute('value', folio);
                input_tipo.setAttribute('name', 'tipo');
                input_tipo.setAttribute('value', tipo);
                input_oc.setAttribute('name', 'erp');
                input_oc.setAttribute('value', erp);

                form.appendChild(input_nofooter);
                form.appendChild(input_folio);
                form.appendChild(input_tipo);
                form.appendChild(input_oc);

                document.getElementsByTagName('body')[0].appendChild(form);
                form.submit();
              
    }

    function SeleccionarLP(folio,proveedor, oc)
    {

      $.ajax({
          type: "POST",
          dataType: "json",
          data: 
          {
              folio: oc,
              action : "getLP"
          },
          beforeSend: function(x)
          { 
              if(x && x.overrideMimeType) 
              { 
                x.overrideMimeType("application/json;charset=UTF-8"); 
              }
          },
          url: '/api/adminentrada/update/index.php',
          success: function(data) 
          {
              console.log(data.lp);

              $("#lista_lp").empty();
              $("#lista_lp").append(data.lp);

              $modal0 = $("#lista_lp_entrada");
              $modal0.modal('show');

          }, error: function(data) 
          {
              console.log("ERROR", data);
          }
      });

      $("#lista_lp").data("folio", folio);
      $("#lista_lp").data("proveedor", proveedor);
      $("#lista_lp").data("oc", oc);

      $("#lista_lp").select2();

    }

    function enviar_email(folio, enlace, usuario_destinatario_email){

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                enlace: enlace,
                folio: folio,
                usuario: usuario_destinatario_email,
                action: 'email_entrada'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/emails/enviar.php',
            success: function(data) {
                    console.log("Email Enviado", data);
                    //fillSelectZona(data.codigo.id);
            },
            error: function(data){
                console.log("Email NO Enviado", data);
                swal("Reporte Enviado", "Correo Enviado Correctamente", "success");
            }
        });

    }

    function exportarPDFCodigoBarras(folio,proveedor, oc)
    //function exportarPDFCodigoBarras()
    {
            //console.log("folio = ", $("#lista_lp").data("folio"));
            //console.log("proveedor = ", $("#lista_lp").data("proveedor"));
            //console.log("oc = ", $("#lista_lp").data("oc"));


            //var lista_lp = $("#lista_lp").val();
            //if(lista_lp === null) lista_lp = "";
            //console.log("lista_lp = ", lista_lp);
            console.log("folio = ", folio);
            console.log("proveedor = ", proveedor);
            console.log("oc = ", oc);



                var folio = folio, //$("#lista_lp").data("folio"),
                        form = document.createElement('form'),
                        input_nofooter = document.createElement('input'),
                        input_folio = document.createElement('input'),
                        input_oc = document.createElement('input'),
                        input_proveedor = document.createElement('input'),
                        input_lp =  document.createElement('input');

                form.setAttribute('method', 'post');
                form.setAttribute('action', '/entradas/pdf/codigobarras');//Class: ReportePDF/EntradasPDF.php->generarReporteEntradas($folio);
                form.setAttribute('target', '_blank');

                input_nofooter.setAttribute('name', 'nofooternoheader');
                input_nofooter.setAttribute('value', '1');

                input_folio.setAttribute('name', 'folio');
                input_folio.setAttribute('value', folio);
                input_oc.setAttribute('name', 'oc');
                input_oc.setAttribute('value', oc);//$("#lista_lp").data("oc")
                input_proveedor.setAttribute('name', 'proveedor');
                input_proveedor.setAttribute('value', proveedor);//$("#lista_lp").data("proveedor")
                input_lp.setAttribute('name', 'lp');
                input_lp.setAttribute('value', '');

                form.appendChild(input_nofooter);
                form.appendChild(input_folio);
                form.appendChild(input_oc);
                form.appendChild(input_proveedor);
                form.appendChild(input_lp);

                document.getElementsByTagName('body')[0].appendChild(form);
                form.submit();
              
    }

	function printExcel(folio, tipo)
    {
        var folio = folio,
						form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input"),
						input4 = document.createElement("input");
				//var tipo = (tipo == "OC")?1:0;
			
        input1.setAttribute('name', 'nofooternoheader');
        input1.setAttribute('value', 'true');
        input2.setAttribute('name', 'folio');
        input2.setAttribute('value', folio);
				input3.setAttribute('name', 'tipo');
        input3.setAttribute('value', tipo);
        input4.setAttribute('name', 'action');
        input4.setAttribute('value', 'exportExcelEntrada');
        form.setAttribute('action', '/api/adminentrada/update/index.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input1);
        form.appendChild(input2);
        form.appendChild(input3);
				form.appendChild(input4);										
        document.body.appendChild(form);
        form.submit();
    }
  
    function printExcelReporteEntradas(folio)
    {
        var folio = folio,
                        form = document.createElement("form"),
            input1 = document.createElement("input"),
            input2 = document.createElement("input"),
            input3 = document.createElement("input"),
            input4 = document.createElement("input");
                //var tipo = (tipo == "OC")?1:0;
            
        input2.setAttribute('name', 'folio');
        input2.setAttribute('value', folio);
        input4.setAttribute('name', 'action');
        input4.setAttribute('value', 'exportExcelReporteEntradas');
        form.setAttribute('action', '/api/adminentrada/update/index.php');
        form.setAttribute('method', 'post');
        form.setAttribute('target', '_blank');
        form.appendChild(input2);
        form.appendChild(input4);
        document.body.appendChild(form);
        form.submit();
    }

    function total_records()
    {
        $(document).ready(function() {
              setTimeout(function() {
                   var lost = $("#grid-table").getGridParam("reccount");
                    console.log(lost);
                    $("#totales").text("Total Pedido: "+lost);

              }, 1000);
        });
    }
    $('.chosen-select').chosen();
    $('.chosen-select, #proveedor').trigger("chosen:updated");

    $('#data_1 .input-group.date').datetimepicker({
       locale: 'es',
       format: 'DD-MM-YYYY',
       useCurrent: false
    });
/*
    $('#data_H .input-group.date').datetimepicker({
        format: 'HH:mm',
        pickDate: false,
        pickSeconds: true,
        showMeridian:false,
        use24hours: true,
        pick12HourFormat: false            
    });
*/
</script>


<style>

<?php if($edit[0]['Activo']==0){?>

.fa-edit{

    display: none;

}

<?php }?>


<?php if($borrar[0]['Activo']==0){?>

.no-eraser{

    display: none;

}

<?php }?>

</style>