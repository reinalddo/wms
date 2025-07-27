<?php

    $cliente_almacen_style = ""; $cve_cliente = ""; $almacen_cliente_prov = ""; $filtro_cliente = ""; $almacen_cliente = "";
    $cve_almacen_cliente = "";
    if($_SESSION['es_cliente'] == 1) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_cliente = $_SESSION['cve_cliente'];
        $id_user = $_SESSION['id_user'];
        $almacen_cliente_prov = "AND clave = (SELECT cve_almacen FROM c_usuario WHERE id_user = {$id_user})";
        $filtro_cliente = "AND Cve_Clte = '{$cve_cliente}'";

        $usuario = $_SESSION["cve_usuario"];
        $alm_cliente = \db()->prepare("SELECT a.id as id_almacen, a.clave as cve_almacen
                                       FROM c_usuario u 
                                       LEFT JOIN c_almacenp a ON a.clave = u.cve_almacen
                                       WHERE u.cve_usuario = '$usuario' AND u.cve_cliente = '$cve_cliente'");
        $alm_cliente->execute();
        $row_alm = $alm_cliente->fetch();
        $almacen_cliente = $row_alm['id_almacen'];
        $cve_almacen_cliente = $row_alm['cve_almacen'];

    }

    $cve_proveedor = ""; $filtro_proveedor = "";
    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
        $id_user = $_SESSION['id_user'];
        $almacen_cliente_prov = "AND clave = (SELECT cve_almacen FROM c_usuario WHERE id_user = {$id_user})";
        $filtro_proveedor = "AND ID_Proveedor = {$cve_proveedor}";
    }

    if(isset($_SESSION['id_proveedor']))
    {
        $cve_proveedor = $_SESSION['id_proveedor'];
    }

$listaEjecutivo = new \Ejecutivos\Ejecutivos();
$listaArtic = new \Articulos\Articulos();
$listaMotivosDevolucion = new \MotivoDevolucion\MotivoDevolucion();
$proxId = new \Incidencias\Incidencias();
$almacenes = new \AlmacenP\AlmacenP();
$almacenes = $almacenes->getAll($almacen_cliente_prov);
$clientes  = new \Clientes\Clientes();
$clientes = $clientes->getAll($filtro_cliente);
$usuarios = new \Usuarios\Usuarios();
$usuarios = $usuarios->getAll();

$proveedores  = new \Proveedores\Proveedores();
$proveedores = $proveedores->getAll($filtro_proveedor);


$mod=67;
$var1=203;
$var2=204;
$var3=205;
$var4=206;

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

//$fecha_actual = \db()->prepare("SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') as Fecha_Actual FROM DUAL");
//$fecha_actual->execute();
//$fecha_actual = $fecha_actual->fetchAll(PDO::FETCH_ASSOC);
//$fecha_actual = $fecha_actual['Fecha_Actual'];

?>

    <input type="hidden" id="cve_cliente" value="<?php echo $cve_cliente; ?>">
    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
    <input type="hidden" id="almacen_predeterminado" value="">
    <input type="hidden" id="almacen_cliente" value="<?php echo $almacen_cliente; ?>">
    <input type="hidden" id="cve_almacen_cliente" value="<?php echo $cve_almacen_cliente; ?>">

<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<style>
  #list {
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0px;
    z-index: 999;
  }
  #FORM {
    width: 100%;
    position: absolute;
    left: 0px;
    z-index: 999;
  }
  .ui-jqgrid-hdiv.ui-state-default.ui-corner-top, #gview_grid-table2, .ui-jqgrid.ui-widget.ui-widget-content.ui-corner-all, .ui-jqgrid-pager.ui-state-default.ui-corner-bottom, #grid-table2, .ui-jqgrid-bdiv{
    max-width: 100% !important;
  }
</style>
<!-- AGREGAR INCIDENCIA -->
<div class="wrapper wrapper-content  animated fadeInRight" id="FORM" style="display: none">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title">
          <div class="row">
            <div class="col-lg-4" id="_title">
              <h3>Agregar Incidencia</h3>
            </div>
          </div>
        </div>
        <form id="myform">
          <div class="ibox-content">
            <div class="row">
              <div class="col-lg-2">
                <label>N° de Incidencia</label>
                <input id="ID_Incidencia" name="ID_Incidencia" type="text" class="form-control" maxlengh="20" required readonly>
                <label id="CodeMessage" style="color:red;"></label><br>

                <br>
                <div class="form-group">
                  <label>Responsable de recibir la PQRS*</label>
                  <select class="form-control" name="responsable_recibo" id="responsable_recibo">
                    <?php /*if($cve_cliente != "" && $cve_proveedor != ""): ?>
                      <option value="">Seleccione</option>
                    <?php endif;*/ ?>

                    <?php if(!empty($usuarios)): ?>
                    <?php foreach($usuarios as $usuario): ?>
                      <?php if($usuario->cve_usuario == $_SESSION["cve_usuario"]): ?>
                    <option value="<?php echo $usuario->cve_usuario?>"><?php echo $usuario->nombre_completo?></option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>

              </div>

              <div class="col-lg-2">
                <div class="form-group">
                  <label>Fecha Reporte*</label>
                  <div class="input-group date" id="data_1">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="fecha_recibo" id="Fecha" type="text" class="form-control"  required>
                  </div>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group titulo">
                  <label>Almacén | CEDIS*</label>
                  <select class="form-control" name="centro_distribucion" id="centro_distribucion" <?php if($cve_cliente != '' || $cve_proveedor != '') echo "readonly='true'"; ?>>
                    <?php if($cve_cliente != "" && $cve_proveedor != ""): ?>
                    <option value="">Seleccione</option>
                    <?php endif; ?>
                    <?php if(!empty($almacenes)): ?>
                    <?php foreach($almacenes as $almacen): ?>
                    <option value="<?php echo $almacen->clave; ?>"><?php echo "( ".$almacen->clave." ) - ".$almacen->nombre?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-5">
                <div class="form-group titulo">
                  <?php $label_cp = "Cliente"; ?>
                  <?php if(!empty($cve_proveedor) && empty($cve_cliente)){$label_cp = "Proveedor";} ?>
                  <label><?php echo $label_cp; ?>*</label>
                  <input type="hidden" id="label_cp" value="<?php echo $label_cp; ?>">
                  <?php 
                  if(!empty($cve_proveedor) || !empty($cve_cliente))
                  {
                  ?>
                  <select class="form-control" name="cliente" id="cliente">
                    <?php if($cve_cliente != "" && $cve_proveedor != ""): ?>
                    <option value="">Seleccion</option>
                    <?php endif; ?>

                    <?php if(!empty($cve_proveedor) && empty($cve_cliente)): ?>
                    <?php foreach($proveedores as $proveedor): ?>
                    <option value="<?php echo $proveedor->ID_Proveedor; ?>"><?php echo "( ".$proveedor->ID_Proveedor." ) - ".$proveedor->Nombre; ?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if(empty($cve_proveedor) && !empty($cve_cliente)): ?>
                    <?php foreach($clientes as $cliente): ?>
                    <option value="<?php echo $cliente->Cve_Clte; ?>"><?php echo "( ".$cliente->Cve_Clte." ) - ".$cliente->RazonSocial?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                  <?php 
                  }
                  ?>

                    <?php if(empty($cve_proveedor) && empty($cve_cliente) && !empty($clientes)): ?>
                    <?php /*foreach($clientes as $cliente): ?>
                    <option value="<?php echo $cliente->Cve_Clte; ?>"><?php echo "( ".$cliente->Cve_Clte." ) - ".$cliente->RazonSocial?></option>
                    <?php endforeach;*/ ?>
                    <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código del Cliente">
                    <input class="form-control" name="cliente_b" id="cliente_b" placeholder="Código del Cliente" style="display: none;">
                    <br><br>
                  <label>Clave y Nombre Cliente</label>
                       <select id="desc_cliente" name="desc_cliente" class="form-control">
                       </select>

                    <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-3">
                <div class="form-group">
                  <label>Tipo de Reporte*</label>
                  <select class="form-control" name="tipo_reporte" id="tipo_reporte">
                    <option value="">Seleccion</option>
                    <option value="P">Petición</option>
                    <option value="Q">Queja</option>
                    <option value="R">Reclamo</option>
                    <option value="S">Sugerencia</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-4">
                <label>Número de Folio o Factura*</label>
                <div class="input-group">
                  <input type="text" class="form-control input-sm" name="Fol_folio" id="Fol_folio" placeholder="Número de Folio" readonly required>
                  <div class="input-group-btn">
                    <button type="button" class="btn btn-sm btn-primary" id="pedidos">
                      Buscar <?php if($cve_proveedor) echo "OC"; else echo "Pedido"; ?>
                    </button>
                  </div>
                </div>
                <input type="hidden" id="action" name="action">
              </div>

              <div class="col-lg-4" id="motivo_apertura">
                <div class="form-group">
                  <label>Motivo de Registro de Incidencia*</label>
                  <select class="form-control chosen-select" name="mot_apertura" id="mot_apertura">
                  </select>
                </div>
              </div>

              <div class="col-lg-3" style="display: none;">
                <div class="form-group">
                  <label>Nombre (Quien Reporta)*</label>
                  <input type="text" name="reportador" id="reportador" class="form-control">
                </div>
              </div>
              <div class="col-lg-3" style="display: none;">
                <div class="form-group">
                  <label>Cargo</label>
                  <input name="cargo_reportador" type="text" maxlengh="100" placeholder="Cargo" class="form-control">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12" id="descripcion_apertura">
                <label>Descripcion de Registro de Incidencia*</label>
                <textarea name="desc_apertura" id="desc_apertura" class="form-control" style="resize: none;" rows="7" placeholder="Descripción de la Incidencia"></textarea>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Responsable del plan de acción</label>
                  <select class="form-control" name="responsable_plan">
                    <option value="">Seleccione</option>
                    <?php if(!empty($usuarios)): ?>
                    <?php foreach($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario->cve_usuario?>"><?php echo $usuario->nombre_completo?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-lg-12">
                <div class="form-group">
                  <label>Acciones | Solución*</label>
                  <textarea style="resize:none" name="descripcion" id="descripcion" rows="5" type="text" placeholder="Descripción de la PQRS" class="form-control"></textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-4" style="display: none;">
                <div class="form-group">
                  <label>Responsable del caso reportado*</label>
                  <select class="form-control" name="responsable_caso" id="responsable_caso">
                    <option value="">Seleccione</option>
                    <option value="Empresa">Empresa</option>
                    <option value="Transportador">Transportador</option>
                    <option value="Cliente">Cliente</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-4" style="display:none;">
                <div class="form-group">
                  <label for="otroI" class="hidden">Nombre del Responsable</label>
                  <input id="otroI" type="text" maxlengh="50" placeholder="¿Cual?" class="form-control" style="margin-top: 22px" disabled>
                </div>
              </div>
            </div>
            <div class="row">
<!--               <div class="col-lg-4">
                <div class="form-group">
                  <label>Plan de acción</label>
                  <input name="plan_accion" type="text" placeholder="Plan de acción" maxlengh="100" class="form-control">
                </div>
                
              </div> -->
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Fecha acción</label>
                  <div class="input-group date" id="data_2">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="fecha_accion" id="Fecha_accion" type="text" class="form-control">
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
            <div class="row">
              <div class="col-lg-12" id="subtitle">
                <h3>Verificacion de Acciones Tomadas</h3>
              </div>
            </div>
              <div class="col-lg-4">
                <div class="form-group">
                  <label>Responsable verificación</label>
                  <select class="form-control" name="responsable_verificacion">
                    <option value="">Seleccione</option>
                    <?php if(!empty($usuarios)): ?>
                    <?php foreach($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario->cve_usuario?>"><?php echo $usuario->nombre_completo?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-lg-12">
                <div class="form-group">
                  <label>Validación | Resultados</label>
                  <textarea style="resize:none" name="plan_accion" rows="5" type="text" maxlengh="100" placeholder="Plan de acción" class="form-control"></textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-4" id="status_pqrs">
                <div class="form-group">
                  <label>Status de la PQRS*</label>
                  <select class="form-control" name="status" id="status">
                    <option value="A">Abierto</option>
                    <option value="C">Cerrado</option>
                  </select>
                </div>
              </div>
              <?php 
              /*
              ?>
              <div class="col-lg-4" id="motivo_apertura">
                <div class="form-group">
                  <label>Motivo de Registro de Incidencia*</label>
                  <select class="form-control chosen-select" name="mot_apertura" id="mot_apertura">
                  </select>
                </div>
              </div>
              <?php 
              */
              ?>
              <div class="col-lg-4" id="motivo_cierre">
                <div class="form-group">
                  <label>Motivo de Cierre de Incidencia*</label>
                  <select class="form-control chosen-select" name="mot_cierre" id="mot_cierre">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6" id="descripcion_cierre">
                <label>Descripcion de Cierre de Incidencia*</label>
                <textarea name="desc_cierre" id="desc_cierre" class="form-control" style="resize: none;" rows="7"></textarea>
              </div>
              <div class="col-lg-6">
                 <div class="imageupload panel panel-default" id="upload">
                    <div class="panel-heading clearfix">
                        <h3 class="panel-title">Subir Imagen</h3>
                    </div>
                    <div class="file-tab panel-body">
                        <div class="row" style="margin-bottom: 30px;">
                            <div class="col-md-4 text-center">
                                <div class="label label-default">
                                    Tamaño Máximo: 512kb
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="label label-default">
                                    Alto Máximo: 150px
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="label label-default">
                                    Ancho Máximo: 150px
                                </div>
                            </div>
                        </div>
                        <label class="btn btn-primary btn-file fileContainer ">        <!-- The file is stored here. -->
                          <b>Examinar</b>
                          <input id="imagen" type="file" name="image-file" accept="image/x-png,image/gif,image/jpeg">

                        </label>

                        <button type="button" class="btn btn-default">Remover</button>
                    </div>
                </div>
              </div>
              <?php 
              /*
              ?>
              <div class="col-lg-6" id="descripcion_apertura">
                <label>Descripcion de Registro de Incidencia*</label>
                <textarea name="desc_apertura" id="desc_apertura" class="form-control" style="resize: none;" rows="7"></textarea>
              </div>
              <?php 
              */
              ?>

            </div>
            <div class="row">
              <div class="col-lg-12">
                <div class="pull-right">
                  <button type="button" class="btn btn-white" id="btnCancel" onclick="cancelar()">Cerrar</button>
                  <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="btnSave">Guardar</button>
                </div>
                <div style="height:250px;">
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
<div class="wrapper wrapper-content  animated " id="list">
  <h3>Control de Incidencias (PQRS)</h3>
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox ">
        <div class="ibox-title">
          <div class="row">
            <div class="col-lg-1">


              <style>

                <?php if($edit[0]['Activo']==0){?>

                .fa-edit{

                  display: none;

                }

                <?php }?>


                <?php if($borrar[0]['Activo']==0){?>

                .fa-eraser{

                  display: none;

                }

                <?php }?>

              </style>

              <a href="#" class="permiso_registrar" onclick="agregar()"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Nuevo</button></a>


            </div>
              <div class="col-lg-3">
                <div class="form-group">
                  <label>Tipo de Reporte</label>
                  <select class="form-control" name="tipo_reporte_busq" id="tipo_reporte_busq">
                    <option value="">Seleccion</option>
                    <option value="P">Petición</option>
                    <option value="Q">Queja</option>
                    <option value="R">Reclamo</option>
                    <option value="S">Sugerencia</option>
                  </select>
                </div>
              </div>
            <div class="col-lg-4">
              <div class="input-group">
                  <label>Búsqueda</label>
                <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio" placeholder="Buscar...">
                <div class="input-group-btn">
                  <a href="#" onclick="ReloadGrid()">
                    <button type="submit" class="btn btn-sm btn-primary" id="buscarI" style="margin-top: 22px;">
                      Buscar
                    </button>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-lg-4" style="text-align: right">
              <button type="button" id="exportExcel1" class="btn btn-primary">
                  <i class="fa fa-file-excel-o"></i>
                  Excel
              </button>
              <button type="button" id="exportPDF1" class="btn btn-danger">
                  <i class="fa fa-file-pdf-o"></i>
                  PDF
              </button>
            </div>
          </div>
        </div>
        <div class="ibox-content">
          <div class="jqGrid_wrapper">
            <table id="grid-table"></table>
            <div id="grid-pager"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Menu de pedidos -->
<div class="modal fade" id="coModal" role="dialog">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog vertical-align-center modal-lg" style="width: 1200px!important;">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Seleccionar <?php if($cve_proveedor) echo "OC"; else echo "Pedido"; ?></h4>
        </div>
        <div class="modal-body">
          <div class="col-lg-4">
            <div class="input-group">
              <input type="text" class="form-control input-sm" name="txtCriterio" id="txtCriterio1" placeholder="Buscar <?php if($cve_proveedor) echo "OC"; else "pedido"; ?>...">
              <div class="input-group-btn">
                <a href="#" onclick="ReloadGrid1()">
                  <button type="submit" class="btn btn-sm btn-primary" id="buscarP">
                    Buscar
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
          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary ladda-button" data-style="contract" id="seleccionarpedido">Seleccionar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="detalleModal" role="dialog">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog vertical-align-center modal-lg">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Detalles de la incidencia</h4>
          <div style="text-align: right">
            <button type="button" id="exportExcel2" class="btn btn-primary">
                <i class="fa fa-file-excel-o"></i>
                Excel
            </button>
            <button type="button" id="exportPDF2" class="btn btn-danger">
                <i class="fa fa-file-pdf-o"></i>
                PDF
            </button>
          </div>
        </div>
        <div class="modal-body">
          <div class="ibox-content">
            <table class="table table-bordered" id="tableDetalle">
              <input type="hidden" id="numero">
              <thead>
                <tr>
                  <th>Centro de distribución</th>
                  <th>Cliente</th>
                  <th>Tipo de reporte</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="centro"></td>
                  <td id="cliente"></td>
                  <td id="tipo_reporte"></td>
                </tr>
              </tbody>
              <thead>
                <tr>
                  <th>Nombre (Quien Reporta)</th>
                  <th>Cargo</th>
                  <th>Fecha Reporte</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="reportador"></td>
                  <td id="cargo_reportador"></td>
                  <td id="fecha_reporte"></td>
                </tr>
              </tbody>
              <thead>
                <tr>
                  <th>Descripción de la PQRS</th>
                  <th>Responsable de recibir la PQRS</th>
                  <th>Responsable del caso reportado</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="descripcion"></td>
                  <td id="responsable_pqrs"></td>
                  <td id="responsable_caso"></td>
                </tr>
              </tbody>
              <thead>
                <tr>
                  <th>Plan de Acción</th>
                  <th>Responsable del Plan de Acción</th>
                  <th>Fecha de Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="plan_accion"></td>
                  <td id="responsable_plan"></td>
                  <td id="fecha_accion"></td>
                </tr>
              </tbody>
              <thead>
                <tr>
                  <th>Responsable de Verificación</th>
                  <th>Status de la PQRS</th>
                  <th>Número de Folio o Factura</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="responsable_verificacion"></td>
                  <td id="cierre_pqrs"></td>
                  <td id="folio"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
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
<!-- Select -->
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<!-- Clock picker -->
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/utils.js"></script>
<!-- Grid de pedidos -->
<script>
  
  

// //edg
            
  
  

    var exportDataGrid = new ExportDataGrid();
  $(function($) {
    var grid_selector = "#grid-table2";
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
    });
    
    
    $(grid_selector).jqGrid({
      url: '/api/incidencias/lista/index_pedidos.php',
      datatype: "json",
      shrinkToFit: false,
      height: 'auto',
      postData: {
        criterio: $("#txtCriterio1").val(),
        cve_proveedor: $("#cve_proveedor").val(),
        cve_cliente: $("#cve_cliente").val(),
        usuario: $("#cliente").val()
      },
      mtype: 'POST',
      colNames: ['id_pedido', 'Fecha de Pedido', 'Número de Folio', 'OC Cliente', 'Fecha de Entrega', $("#label_cp").val(), 'cve_cliente'],
      colModel: [
        {
          name: 'id_pedido',
          index: 'id_pedido',
          width: 0,
          editable: false,
          sortable: false,
          hidden: true
        },
        {
          name: 'Fec_Pedido',
          index: 'Fec_Pedido',
          width: 150,
          editable: false,
          sortable: false,
          align: 'center'
        },
        {
          name: 'Fol_folio',
          index: 'Fol_folio',
          width: 150,
          editable: false,
          sortable: false
        },
        {
          name: 'OC_Cliente',
          index: 'OC_Cliente',
          width: 150,
          editable: false,
          sortable: false,
          align: 'center'
        },
        {
          name: 'Fec_Entrega',
          index: 'Fec_Entrega',
          width: 150,
          editable: false,
          sortable: false,
          align: 'center'
        },
        {
          name: 'RazonSocial',
          index: 'RazonSocial',
          width: 350,
          editable: false,
          sortable: false
        },
        {
          name: 'Cve_Clte',
          index: 'Cve_Clte',
          width: 250,
          editable: false,
          sortable: false,
          hidden: true
        },
      ],
      rowNum: 30,
      rowList: [30, 40, 50],
      pager: pager_selector,
      sortname: 'id_pedido',
      pgbuttons: false,
      viewrecords: true,
      userDataOnFooter: true,
      footerrow: true,
      loadonce: true,
      sortorder: "desc",
      onCellSelect: function(rowId, iCol, content, event) {
        
        var nombre = jQuery('#grid-table2').jqGrid('getCell', rowId, 5);
        var nombre_string = nombre.toString();
        $("#cliente").val(nombre_string);
        $("#cliente").trigger("chosen:updated");
        
        $("#hiddenpedido").val(jQuery('#grid-table2').jqGrid('getCell', rowId, 1));
        $("#Fol_folio").val(jQuery('#grid-table2').jqGrid('getCell', rowId, 1));
        $modal0 = $("#coModal");
        $modal0.modal('hide');
      }
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
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    $(document).one('ajaxloadstart.page', function(e) {
      $(grid_selector).jqGrid('GridUnload');
      $('.ui-jqdialog').remove();
    });


  });
</script>
<script type="text/javascript">
  $(document).ready(function(){
    $("div.ui-jqgrid-bdiv").css("max-height",$("#list").height()-80);
    $(function() {
      $('.chosen-select').chosen();
      $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
    });
  });
  
  $("#cliente").on("change", function(){
    ReloadGrid1();
    //$("#Fol_folio").val("");
  });
  
  var cuenta_normal = false;
  $("#pedidos").on("click", function(){

    if($("#cve_cliente").val() == '' || $("#cve_cliente").val() == null || cuenta_normal == true) {$("#cve_cliente").val($("#desc_cliente").val()); cuenta_normal = true;}

    console.log("Pedidos = ",$("#desc_cliente").val());
      if($("#desc_cliente").val() == '' || $("#desc_cliente").val() == null) 
      {
        swal("Error", "Debe Seleccionar un Cliente", "error");
        return;
      }
      ReloadGrid1();
      $modal0 = $("#coModal");
      $modal0.modal('show');
  });


//******************************************************************************************************
//******************************************************************************************************
        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            //console.log("cliente.length = ", cliente.length);
            $("#Fol_folio").val("");
            if(cliente.length > 2)
                BuscarCliente(cliente);
            else
                BuscarCliente("Borrar_La_Lista_de_Clientes");
        });

        function BuscarCliente(cliente)
        {
            console.log("Cliente = ", cliente);
            //document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getClientesSelect',
                    id_almacen: <?php if($_SESSION['id_almacen']) echo $_SESSION['id_almacen']; else echo $almacen_cliente; ?>,
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS: ", data);
                    //var combo = data.combo;
                    //if(combo.length > 36 || data.direccion.length > 2){
                        var desc_cliente = document.getElementById("desc_cliente");
                        if(data.combo !== '' && data.find == true){
                            desc_cliente.innerHTML = data.combo;
                            var text = desc_cliente.options[desc_cliente.selectedIndex].text;
                            //document.getElementById("txt-direc").value = text;
                            desc_cliente.removeAttribute('disabled');
                            //$("#cliente_b").val(data.clave_cliente);
                            //$("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            $("#cliente_b").val(data.firsTValue);
                            //BuscarDestinatario(data.firsTValue);
                            console.log("#cliente_b = ", $("#cliente_b").val());
                        }
                        else
                        {
                            //$("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente_b").val("");
                            $("#desc_cliente").val("");
                            console.log("#cliente_b = ", $("#cliente_b").val());
                        }
                        $('#desc_cliente').trigger("chosen:updated");
                        $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR: ", data);
                }

            });
        }
//******************************************************************************************************
//******************************************************************************************************

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
      url: '/api/incidencias/lista/index.php',
      datatype: "json",
      shrinkToFit: false,
      height: 'auto',
      postData: {
        cve_proveedor: $("#cve_proveedor").val(),
        cve_cliente: $("#cve_cliente").val(),
        criterio: $("#txtCriterio").val()
      },
      mtype: 'POST',
      colNames: ['Acciones','Incidencia Nº', 'Clave', 'Empresa | Proveedor', 'Razón Social', 'Folio Pedido/Factura', 'Tipo Reporte', 'Fecha/Hora Inicio', 'Usuario Registro', 'Fecha/Hora Fin', 'Usuario Cierre', 'Status', 'Almacén'],
      colModel: [
        {
          name: 'myac',
          index: '',
          width: 80,
          fixed: false,
          sortable: false,
          resize: false,
          formatter: imageFormat
        },
        {name: 'numero', index: 'numero', width: 90, editable: false, sortable: false, hidden: false, align: 'right'},
        {name: 'clave', index: 'clave', width: 60, editable: false, sortable: false, hidden: false},
        {name: 'proveedor', index: 'proveedor', width: 250, editable: false, sortable: false, hidden: false},
        {name: 'razon_social', index: 'razon_social', width: 250, editable: false, sortable: false, hidden: false},
        {name: 'folio', index: 'folio', width: 140, editable: false, sortable: false, hidden: false},
        {name: 'tipo_reporte', index: 'tipo_reporte', width: 100, editable: false, sortable: false, hidden: false},
        {name: 'fecha_inicio', index: 'fecha_inicio', width: 120, editable: false, sortable: false, hidden: false, align: 'center'},
        {name: 'usuario_registro', index: 'usuario_registro', width: 130, editable: false, sortable: false, hidden: false},
        {name: 'fecha_fin', index: 'fecha_fin', width: 120, editable: false, sortable: false, hidden: false, align: 'center'},
        {name: 'usuario_cierre', index: 'usuario_cierre', width: 130, editable: false, sortable: false, hidden: false},
        {name: 'status', index: 'status', width: 100, editable: false, sortable: false, hidden: false},
        {name: 'almacen', index: 'almacen', width: 120, editable: false, sortable: false, hidden: false}
      ],
      rowNum: 30,
      rowList: [10, 20, 30],
      pager: pager_selector,
      sortname: 'ID_Incidencia',
      viewrecords: true,
      sortorder: "desc",
      pgbuttons: false,
      userDataOnFooter: true,
      footerrow: true,
      loadonce: true
    });

    jQuery(grid_selector).jqGrid('setGroupHeaders', {
      useColSpanStyle: true,
      groupHeaders:[
        {startColumnName: 'clave', numberOfColumns: 3, titleText: '<div style="text-align:center">Cliente</div>'},
      ]
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
      var serie = rowObject[1];
      var cve_cliente = rowObject[2];
      var cliente     = rowObject[4];
      var correl = rowObject[4];
      var url = "x/?serie=" + serie + "&correl=" + correl;
      var url2 = "v/?serie=" + serie + "&correl=" + correl;
      $("#hiddenID_Aduana").val(serie);
      var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;

      var html = '';
      //html += '<a href="#" onclick="detalle(\'' + serie + '\')"><i class="fa fa-search" alt="Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
      html += '<a href="/api/koolreport/export/reportes/incidencias/reporte-incidencia?folio='+serie+'&cve_cia='+cve_cia+'" target="_blank"><i class="fa fa-file-pdf-o" title="Reporte Incidencia"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
      if($("#permiso_editar").val() == 1)
      html += '<a href="#" onclick="editar(\'' + serie + '\', \'' + cve_cliente + '\', \'' + cliente + '\')"><i class="fa fa-edit" title="Editar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';

      if($("#cve_proveedor").val() == '' && $("#cve_cliente").val() == '' && $("#permiso_eliminar").val() == 1)
        html += '<a href="#" onclick="borrar(\'' + serie + '\')"><i class="fa fa-eraser" title="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
      return html;
    }

    $(document).one('ajaxloadstart.page', function(e) {
      $(grid_selector).jqGrid('GridUnload');
      $('.ui-jqdialog').remove();
    });
  });

  //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

  $("#tipo_reporte_busq").change(function(){
      ReloadGrid();
  });

  $("#desc_cliente").change(function(){
      $("#Fol_folio").val("");
  });

  function ReloadGrid() {
    $('#grid-table').jqGrid('clearGridData')
      .jqGrid('setGridParam', {postData: {
        almacen: $("#almacen_predeterminado").val(),
        criterio: $("#txtCriterio").val(),
        tipo_reporte_busq: $("#tipo_reporte_busq").val()
      }, datatype: 'json', page : 1})
      .trigger('reloadGrid',[{current:true}]);
  }

  function ReloadGrid1() {
    console.log("ReloadGrid1() = ",$("#cve_cliente").val());
    $('#grid-table2').jqGrid('clearGridData')
      .jqGrid('setGridParam', {postData: {
        criterio: $("#txtCriterio1").val(),
        cve_cliente: $("#cve_cliente").val(),
        usuario: $("#cliente").val(),
      }, datatype: 'json', page : 1})
      .trigger('reloadGrid',[{current:true}]);
  }

  $modal0 = null;

  function borrar(_codigo) {
    $.ajax({
      type: "POST",
      dataType: "json",
      data: {
        ID_Incidencia : _codigo,
        action : "delete"
      },
      beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                              },
      url: '/api/incidencias/update/index.php',
      success: function(data) {
        if (data.success == true) {
          ReloadGrid();
        }
      }
    });
  }
  $('#data_1').datetimepicker({
    locale: 'es',
    format: 'DD-MM-YYYY',
    useCurrent: false
  });
  $('#data_2').datetimepicker({
    locale: 'es',
    format: 'DD-MM-YYYY',
    useCurrent: true
  });
  $('#data_2').data("DateTimePicker").minDate(new Date());

  $("#Fecha").attr('readonly', 'readonly');


  function cancelar() {
    resetForm();

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

/*
  $("#status").change(function(){
    if($(this).val() == 'A')
    {
        $("#motivo_apertura").show();
        $("#motivo_cierre").hide();
        $("#descripcion_apertura").show();
        $("#descripcion_cierre").hide();
    }
    else if($(this).val() == 'C')
    {
        $("#motivo_apertura").hide();
        $("#motivo_cierre").show();
        $("#descripcion_apertura").hide();
        $("#descripcion_cierre").show();
        //apertura_cierre_change();
    }

  });
*/
  function editar(_codigo, cve_cliente, cliente) {
    resetForm();
    //apertura_cierre_change();
    $("#ID_Incidencia").val(_codigo);
    $("#_title").html('<h3>Editar Incidencia</h3>');
    $("#Fecha").prop('disabled', true);
    //$("#status_pqrs").show();
    $.ajax({
      type: "POST",
      dataType: "json",
      data: {
        ID_Incidencia : _codigo,
        action : "load"
      },
      beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
      url: '/api/incidencias/update/index.php',
      success: function(data) {
        console.log("SUCESS Load: ",data);
        if (data.success == true) {
          $("input[name='Fol_folio']").val(data.Fol_folio);
          $("select[name='centro_distribucion']").val(data.centro_distribucion);
          $("select[name='tipo_reporte']").val(data.tipo_reporte);
          $("input[name='reportador']").val(data.reportador);
          $("input[name='cargo_reportador']").val(data.cargo_reportador);
          $('#data_1').data("DateTimePicker").date(data.Fecha);
          $("select[name='cliente']").val(data.cliente);
          $("textarea[name='descripcion']").val(data.Descripcion);
          $("select[name='responsable_recibo']").val(data.responsable_recibo);
          $("#cliente_buscar").val(cve_cliente);
          $("#desc_cliente").append("<option selected value='"+cve_cliente+"'>"+'['+cve_cliente+'] - '+cliente+"</option>");
          
          if(data.responsable_caso !== 'Transportador' && data.responsable_caso !== 'Empresa' && data.responsable_caso !== 'Cliente'){
            $("select[name='responsable_caso']").val('Otro');
            $("#otroI").val(data.responsable_caso);
            $("#otroI").css({'margin-top': 0});
            $("label[for='otroI']").removeClass('hidden');
          }else{
            $("select[name='responsable_caso']").val(data.responsable_caso);
            $("#otroI").css({'margin-top': '22px'});
            $("label[for='otroI']").addClass('hidden');
          }

          $("select[name='status']").val(data.status);

          //apertura_cierre_change();
          if(data.status == 'A')
          {
              $("#motivo_apertura").show();
              $("#motivo_cierre").hide();
              $("#descripcion_apertura").show();
              //$("#descripcion_cierre").hide();
          }
          else if(data.status == 'C')
          {
              $("#motivo_apertura").hide();
              $("#motivo_cierre").show();
              $("#descripcion_apertura").hide();
              $("#descripcion_cierre").show();
          }

          $("#mot_apertura").val(data.id_motivo_registro);
          $("#desc_apertura").val(data.desc_motivo_registro);
          $("#mot_cierre").val(data.id_motivo_cierre);
          $("#desc_cierre").val(data.desc_motivo_cierre);

          $("textarea[name='plan_accion']").val(data.plan_accion);
          $("select[name='responsable_plan']").val(data.responsable_plan);
          $('#data_2').data("DateTimePicker").date(data.Fecha_accion);
          $("select[name='responsable_verificacion']").val(data.responsable_verificacion);
          $("select.form-control").trigger("chosen:updated");
          l.ladda('stop');
          $("#btnCancel").show();
          $('#list').removeAttr('class').attr('class', '');
          $('#list').addClass('animated');
          $('#list').addClass("fadeOutRight");
          $('#list').hide();
          $('#FORM').show();
          $('#FORM').removeAttr('class').attr('class', '');
          $('#FORM').addClass('animated');
          $('#FORM').addClass("fadeInRight");
          $("#action").val("edit");
        }
      }
    });
  }


  function resetForm(){
    var form = document.getElementById('myform');
    form.reset();
    document.getElementById("Fecha").removeAttribute('disabled');
    $("select.form-control").trigger("chosen:updated");
  }


  function agregar() 
  {
    resetForm();
    $("#_title").html('<h3>Agregar Incidencia</h3>');
    $('#data_1').data("DateTimePicker").date(new Date());
    $("#ID_Incidencia").prop('disabled', true);
    $("#Fol_folio").prop('disabled', false);
    $.ajax({
      type: "POST",
      dataType: "json",
      data: {
        action: "lastid"
      },
      beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
      url: '/api/incidencias/update/index.php',
      success: function(data) {
        if (data.success == true) {
          $("#ID_Incidencia").val(data.id);
        }
      }
    });

    $('#list').removeAttr('class').attr('class', '');
    $('#list').addClass('animated');
    $('#list').addClass("fadeOutRight");
    $('#list').hide();
    $('#FORM').show();
    $('#FORM').removeAttr('class').attr('class', '');
    $('#FORM').addClass('animated');
    $('#FORM').addClass("fadeInRight");
    $("#action").val("add");
    l.ladda('stop');
    $("#btnCancel").show();
    
    almacenPrede();


    //apertura_cierre_change();
  }

  var l = $( '.ladda-button' ).ladda();

  l.click(function() {
    if ($("#centro_distribucion").val()=="") {
      swal(
        'Error',
        'Por favor seleccione un centro de distribucion',
        'error'
      );
      return;
    }
    if (($("#cve_cliente").val()=="" && $("#cve_proveedor").val()=="" && $("#desc_cliente").val()=="") || 
       (($("#cve_cliente").val() != "" || $("#cve_proveedor").val() != "") && $("#desc_cliente").val()=="")) {
      swal(
        'Error',
        'Por favor seleccione un cliente',
        'error'
      );
      return;
    }
    if ($("#tipo_reporte").val()=="") {
      swal(
        'Error',
        'Por favor seleccione un tipo de reporte',
        'error'
      );
      return;
    }

    if ($("#status").val()=="A" && ($("#mot_apertura").val()=="" || $("#desc_apertura").val()=="")) {
      swal(
        'Error',
        'Debe Seleccionar un Motivo e Ingresar una descripción para el Registro de la Indicencia',
        'error'
      );
      return;
    }

    if ($("#status").val()=="C" && ($("#mot_cierre").val()=="" || $("#desc_cierre").val()=="")) {
      swal(
        'Error',
        'Debe Seleccionar un Motivo e Ingresar una descripción para el Registro de la Indicencia',
        'error'
      );
      return;
    }

    /*
    if ($("#reportador").val()=="") {
      swal(
        'Error',
        'Por favor seleccione un usuario de reporte',
        'error'
      );
      return;
    }*/
    if ($("#descripcion").val()=="") {
      swal(
        'Error',
        'Por favor indique una descripcion de la PQRS',
        'error'
      );
      return;
    }
    if ($("#Fecha").val()=="") {
      swal(
        'Error',
        'Por favor seleccione una fecha de reporte',
        'error'
      );
      return;
    }
    if ($("#responsable_recibo").val()=="") {
      swal(
        'Error',
        'Por favor indique un responsable recibir la PQRS',
        'error'
      );
      return;
    }
    /*
    if ($("#responsable_caso").val()=="") {
      swal(
        'Error',
        'Por favor indique un responsable del caso reportado',
        'error'
      );
      return;
    }*/
    /*if ($("#status").val()=="") {
      swal(
        'Error',
        'Por favor indique un status de la PQRS',
        'error'
      );
      return;
    }*/



    l.ladda( 'start' );
    var formData = $('#myform').serializeArray().reduce(function(a, x) { a[x.name] = x.value; return a; }, {});
    if($("select[name='responsable_caso']").val() === 'Otro'){
      formData.responsable_caso = $("#otroI").val();
    }
    formData.fecha_recibo = $('#Fecha').val();
    $("#btnCancel").hide();
    //EDG
     if($('#imagen').val() != '') {
        var path = $('#imagen').val();
        var filename = path.replace(/^.*\\/, "");
        uploadFile();
    } else if ($('#image').attr('src') != "" && !$('#imagen').val()) {
      if($('#imagen').val() != ''){
        var path = $('#image').attr("src");
        var filename = path.replace(/^.*\\/, "");
        uploadFile();
      }
      else {
        filename = "noimage.jpg";
      }
    } else {
        filename = "noimage.jpg";
    }
    formData.foto = "img/compania/"+filename;

    console.log("action = ", $("#action").val());
    console.log("formData = ", formData);
    formData.reportador = <?php echo "'".$_SESSION['cve_usuario']."'"; ?>;
    //return;

    if ($("#action").val() == "add") {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: formData,
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/incidencias/update/index.php',
        success: function(data) {
          if (data.success == true) {
            cancelar();
            ReloadGrid();
            l.ladda('stop')
            $("#btnCancel").show();
          } else {
            alert(data.err);
            l.ladda('stop');
            $("#btnCancel").show();
          }
          window.location.reload();
        },
        error: function(data)
        {
            console.log("ERROR", data);
            l.ladda('stop');
        }
      });


    } else {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: formData,
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/incidencias/update/index.php',
        success: function(data) {
          if (data.success == true) {
            cancelar();
            ReloadGrid();
            l.ladda('stop');
            $("#btnCancel").show();
            window.location.reload();
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
  $(document).ready(function(){
    $("select.form-control").chosen();
    $("select[name='responsable_caso']").change(function(e){
      var responsable = e.target.value;
      var otro = document.getElementById('otroI');
      var label = document.querySelector('label[for="otroI"]');
      if(responsable === 'Otro'){
        otro.removeAttribute('disabled');
        otro.style.marginTop = 0;
        label.classList.remove('hidden');
      }else{
        otro.setAttribute('disabled', 'true');
        otro.value = '';
        otro.style.marginTop = "22px";
        label.classList.add('hidden');
      }
    })
    $("#txtCriterio").keyup(function(event){
      if(event.keyCode == 13){
        $("#buscarI").click();
      }
    });

    $("#txtCriterio1").keyup(function(event){
      if(event.keyCode == 13){
        $("#buscarP").click();
      }
    });
  });


  function detalle(id){
    $("#tableDetalle #numero").val(id);
    $.ajax({
      url: '/api/incidencias/lista/index.php',
      dataType: 'json',
      data: {
        id: id
      },
      type: 'GET'
    }).done(function(data){
      if(data.data){
        if(data.data.tipo_reporte === 'Petici&oacute;n'){
          data.data.tipo_reporte = 'Petición';
        }
        $("#tableDetalle #centro").text(data.data.centro)
        $("#tableDetalle #cliente").text(data.data.cliente)
        $("#tableDetalle #tipo_reporte").text(data.data.tipo_reporte)
        $("#tableDetalle #reportador").text(data.data.reportador)
        $("#tableDetalle #cargo_reportador").text(data.data.cargo_reportador)
        $("#tableDetalle #fecha_reporte").text(data.data.fecha_reporte)
        $("#tableDetalle #descripcion").text(data.data.descripcion)
        $("#tableDetalle #responsable_pqrs").text(data.data.responsable_recibo)
        $("#tableDetalle #responsable_caso").text(data.data.responsable_caso)
        $("#tableDetalle #plan_accion").text(data.data.plan_accion)
        $("#tableDetalle #responsable_plan").text(data.data.responsable_plan)
        $("#tableDetalle #fecha_accion").text(data.data.fecha_plan)
        $("#tableDetalle #responsable_verificacion").text(data.data.responsable_verificacion)
        $("#tableDetalle #cierre_pqrs").text(data.data.status)
        $("#tableDetalle #folio").text(data.data.folio)
      }
      $("#detalleModal").modal('toggle');
    });
  }

    $("#exportExcel1").on("click", function(){
        window.exportDataGrid.exportExcel("grid-table","Control_Incidencias.xls");
    });
    $("#exportPDF1").on("click", function(){
        window.exportDataGrid.exportPDF("grid-table","Control_Incidencias.pdf","A3");
    });
    $("#exportExcel2").on("click", function(){
        window.exportDataGrid.exportExcel("grid-table2","Control_Incidencias.xls");
    });
    $("#exportPDF2").on("click", function(){
        window.exportDataGrid.exportPDF("grid-table2","Control_Incidencias.pdf","A3");
    });
  
    $(document).ready(function() {
      almacenPrede();
      apertura_cierre_change();
    });
  
    function almacenPrede(swict)
    {
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
            if (data.success == true) 
            {
              console.log(data);
                if(swict){
                    //document.getElementById('centro_distribucion').value = data.codigo.clave;
                    if($("#almacen_cliente").val() == '')
                      $('#centro_distribucion').val(data.codigo.clave);
                    else
                      $('#centro_distribucion').val($("#cve_almacen_cliente").val());
                      
                    $('#centro_distribucion').trigger("chosen:updated");
                    fillSelectArti();
                }
                else{
                    //document.getElementById('centro_distribucion').value = data.codigo.clave;
                    if($("#almacen_cliente").val() == '')
                      $('#centro_distribucion').val(data.codigo.clave);
                    else
                      $('#centro_distribucion').val($("#cve_almacen_cliente").val());

                    $('#centro_distribucion').trigger("chosen:updated");
                    //console.log("almacen = ", data.codigo.clave);
                    if($("#almacen_cliente").val() == '')
                      $('#almacen_predeterminado').val(data.codigo.clave);
                    else
                      $('#almacen_predeterminado').val($("#cve_almacen_cliente").val());

                    setTimeout(function() {
                        ReloadGrid();
                    }, 1000);
                }
            }
        },
        error: function(res){
          window.console.log(res);
        }
      });

      if($("#cve_cliente").val() != "" || $("#cve_proveedor").val() != "")
      {
          //$("#centro_distribucion").prop("disabled", true);
          //$("#cliente").prop("disabled", true);
          //$("#centro_distribucion").prop("readonly", true);
          //$("#cliente").prop("readonly", true);

          if($("#cve_cliente").val() != "")
             $("#cliente").val($("#cve_cliente").val());
          else
             $("#cliente").val($("#cve_proveedor").val());

          $('#cliente').trigger("chosen:updated");
          $('#centro_distribucion').trigger("chosen:updated");

          //$("#centro_distribucion_chosen, #cliente_chosen, .titulo").hide();
          //$("#cliente_chosen").hide();

      }
    }
  
  function apertura_cierre_change()
  {
      console.log("Status Incidencia = ", $("#status").val());
      $.ajax({
        type: "POST",
        dataType: "json",
        async: false,
        data: {
          status: 'A',
          action: "apertura_cierre_change"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/incidencias/lista/index.php',
        success: function(data) 
        {
            //console.log("options = ", data);
            $("#mot_apertura").empty();
            $("#mot_apertura").append(data);
            $(".chosen-select").trigger("chosen:updated");
        },
        error: function(data) 
        {
            console.log("ERROR options = ", data);
        }
      });


      $.ajax({
        type: "POST",
        dataType: "json",
        async: false,
        data: {
          status: 'C',
          action: "apertura_cierre_change"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/incidencias/lista/index.php',
        success: function(data) 
        {
            //console.log("options = ", data);
            $("#mot_cierre").empty();
            $("#mot_cierre").append(data);
            $(".chosen-select").trigger("chosen:updated");
        },
        error: function(data) 
        {
            console.log("ERROR options = ", data);
        }
      });

  }

  /*
  var $imageupload = $('.imageupload');
            $imageupload.imageupload({
                maxFileSizeKb: 512,
                maxWidth: 150,
                maxHeight: 150,
            });

            $('#imageupload-disable').on('click', function() {
                $imageupload.imageupload('disable');
                $(this).blur();
            })

            $('#imageupload-enable').on('click', function() {
                $imageupload.imageupload('enable');
                $(this).blur();
            })

            $('#imageupload-reset').on('click', function() {
                $imageupload.imageupload('reset');
                $(this).blur();
            });

  */
  function uploadFile() {
    
                var input = document.getElementById("imagen");
                file = input.files[0];

                if (file != undefined) {
                    formData = new FormData();
                    if (!!file.type.match(/image.*/)) {
                        formData.append("image", file);
                        $.ajax({
                            url: "/app/template/page/incidencias/upload.php",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                //alert(data);
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                //console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                                alert(thrownError);
                            }
                        });
                    } else {
                        alert('Not a valid image!');
                    }
                } else {
                    //alert('Input something!');
                }
            }
  
</script>
