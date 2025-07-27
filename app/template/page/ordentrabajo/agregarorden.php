<?php

session_start();
set_time_limit(12000);
$listaAP = new \AlmacenP\AlmacenP();
$listaProvee = new \Proveedores\Proveedores();
//$listaUnidadMedida = new \UnidadesMedida\UnidadesMedida();
$sql = "SELECT id_umed, cve_umed, des_umed FROM c_unimed WHERE Activo = 1;";
$rs_umed = mysqli_query(\db2(), $sql);

$comboUnidadesMedida = '<select id="cboUnidadesMedida" class="chosen-select form-control"><option value="">Seleccione Unidad de Medida</option>';

while( $a = mysqli_fetch_object($rs_umed)) {
    $comboUnidadesMedida .= '<option value="' . $a->id_umed . '"> (' . $a->cve_umed . ") " . utf8_encode($a->des_umed) . '</option>';
}/*
$comboUnidadesMedida .= '<option value="1">Pieza</option>';
$comboUnidadesMedida .= '<option value="2">Caja</option>';
$comboUnidadesMedida .= '<option value="3">Pallet</option>';
*/
$comboUnidadesMedida .= '</select>';

$sql = "SELECT DISTINCT c_articulo.cve_articulo, CONVERT(CAST(c_articulo.des_articulo as BINARY) USING utf8) as des_articulo 
        FROM c_articulo 
        INNER JOIN t_artcompuesto ON  t_artcompuesto.Cve_ArtComponente = c_articulo.cve_articulo
        WHERE `Compuesto` = 'S' ;";
$rs = mysqli_query(\db2(), $sql);

$comboArticuloParte = '<select id="txtArticuloParte" class="chosen-select form-control"><option value="">Seleccione C&oacute;digo del Producto</option>';
while( $a = mysqli_fetch_object($rs)) {
    $comboArticuloParte .= '<option value="' . $a->cve_articulo . '">' . $a->cve_articulo . " " . utf8_encode($a->des_articulo) . '</option>';
}
$comboArticuloParte .= '</select>';

$id_almacen = $_SESSION['id_almacen'];


$sql = "SELECT  DISTINCT a.des_almac AS nombre_zona, a.cve_almac AS clave_zona, u.CodigoCSD, u.idy_ubica
FROM c_almacen a
LEFT JOIN c_ubicacion u ON u.cve_almac = a.cve_almac 
WHERE a.Activo = 1 AND u.AreaProduccion = 'S' #AND u.picking = 'S'
AND a.cve_almac IN (SELECT cve_almac FROM c_ubicacion WHERE AreaProduccion = 'S' #AND picking = 'S' 
    AND Activo = 1) AND a.cve_almacenp = {$id_almacen}";
$rs = mysqli_query(\db2(), $sql);

$comboAlmacen = '<select id="cboZonaAlmacen" class="chosen-select form-control"><option value="">Seleccione Zona de Almac&eacute;n</option>';
while( $a = mysqli_fetch_object($rs)) {
    $comboAlmacen .= '<option value="' . $a->clave_zona . '" data-idy_ubica="'.$a->idy_ubica.'">' . $a->clave_zona . " " . ($a->nombre_zona) . " | " . $a->CodigoCSD . '</option>';
}
$comboAlmacen .= '</select>';


$sql = "SELECT  DISTINCT a.des_almac AS nombre_zona, a.cve_almac AS clave_zona, u.CodigoCSD, u.idy_ubica
FROM c_almacen a
LEFT JOIN c_ubicacion u ON u.cve_almac = a.cve_almac 
WHERE a.Activo = 1 AND u.Activo = 1 #AND u.picking = 'S'
AND a.cve_almacenp = {$id_almacen}";
$rs = mysqli_query(\db2(), $sql);

$comboAlmacenDest = '<select id="cboZonaAlmacenDest" class="chosen-select form-control"><option value="">Seleccione Ubicaci&oacute;n</option>';
while( $a = mysqli_fetch_object($rs)) {
    $comboAlmacenDest .= '<option value="' . $a->clave_zona . '" data-idy_ubica="'.$a->idy_ubica.'">' . $a->clave_zona . " " . ($a->nombre_zona) . " | " . $a->CodigoCSD . '</option>';
}
$comboAlmacenDest .= '</select>';

$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}


$sql = "SELECT  DISTINCT a.des_almac AS nombre_zona, a.cve_almac AS clave_zona, u.CodigoCSD, u.idy_ubica
FROM c_almacen a
LEFT JOIN c_ubicacion u ON u.cve_almac = a.cve_almac 
WHERE a.Activo = 1 AND u.AreaProduccion = 'S'
AND a.cve_almac IN (SELECT cve_almac FROM c_ubicacion WHERE AreaProduccion = 'S' AND Activo = 1) AND a.cve_almacenp = {$id_almacen}";
$rs = mysqli_query(\db2(), $sql);

$comboAlmacen2 = '<select id="cboZonaAlmacen-import" name="cboZonaAlmacenImport" class="chosen-select form-control">';
while( $a = mysqli_fetch_object($rs)) {
    $comboAlmacen2 .= '<option value="' . $a->clave_zona . '" data-idy_ubica="'.$a->idy_ubica.'">' . $a->clave_zona . " | " . $a->CodigoCSD . " " . ($a->nombre_zona) . '</option>';
}
$comboAlmacen2 .= '</select>';

$confSql = \db()->prepare("SELECT CURDATE() AS fecha_actual FROM th_pedido");
$confSql->execute();
$fecha_actual = $confSql->fetch()['fecha_actual'];


$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

/**************************************************************************
$sql = "SELECT  Fol_folio
        FROM th_pedido
        WHERE Activo = 1 AND 
        ";
$rs = mysqli_query(\db2(), $sql);

$comboPedidos = '<select id="cboZonaAlmacen" class="chosen-select form-control"><option value="">Seleccione Zona de Almac&eacute;n</option>';
while( $a = mysqli_fetch_object($rs)) {
    $comboPedidos .= '<option value="' . $a->clave_zona . '">' . $a->clave_zona . " " . utf8_encode($a->nombre_zona) . '</option>';
}
$comboPedidos .= '</select>';
//**************************************************************************/

$sql = "Select MAX(Folio_Pro) as Folio_Pro, Status  from t_ordenprod WHERE Cve_Usuario = '" . $_SESSION["id_user"] . "' and Status = 'I' Order by Folio_Pro Desc";
$rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
$row = mysqli_fetch_array($rs);

//if ($rs->num_rows > 0 && !empty($row['Folio_Pro'])) {
    //if ($row["Status"] == "I") {
    //    $newFolio = $row["Folio_Pro"];
    //    //$newFolio = $row["Folio_Pro"];
    //    $_SESSION["NroOrdenProduccion"] = $newFolio;
    //} else {
        $sql = "Select `fct_consecutivo_documentos`('t_ordenprod', 6);";
        $rs = mysqli_query(\db2(), $sql);
        $row = mysqli_fetch_array($rs);

        $newFolio = "OT".$row[0];
        //$newFolio = $row[0];

        $_SESSION["NroOrdenProduccion"] = $newFolio;
/*
        $sql = "Call `SPAD_AddUpdateOrdenProd`(
                        '$newFolio',
                        '',
                        '0',
                        '0',
                        '" . $_SESSION['id_user'] . "',
                        now(),
                        'I');";

        $rs = mysqli_query(\db2(), $sql);
*/
    //}
//} else {
//    $sql = "Select `fct_consecutivo_documentos`('t_ordenprod', 6);";
//    $rs = mysqli_query(\db2(), $sql);
//    $row = mysqli_fetch_array($rs);
//
//    $newFolio = "OT".$row[0];
//    //$newFolio = $row[0];
//
//    $_SESSION["NroOrdenProduccion"] = $newFolio;
///*
//    $sql = "Call `SPAD_AddUpdateOrdenProd`(
//                        '$newFolio',
//                        '',
//                        '0',
//                        '0',
//                        '".$_SESSION['id_user']."',
//                        now(),
//                        'I');";
//
//    $rs = mysqli_query(\db2(), $sql);
//*/
//}

?>

<!--<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">-->
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.css">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">
<!--<link href="/css/jquery.auto-complete.css" rel="stylesheet"/>-->

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
        /*height: 100%;*/
        position: absolute;
        left: 0px;
        z-index: 999;
    }
</style>

    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
<input type="hidden" id="instancia" value="<?php echo $instancia; ?>">

<div class="modal" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModal">
                <h2>Cargando...</h2>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="pleaseWaitSaving" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="headModal">
                <h2>Guardando...</h2>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="conModal" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1024px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">B&uacute;squeda de Art&iacute;culos</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="input-group">
                            <input name="txtBusqueda" id="txtBusqueda" type="text" class="form-control input-sm"/>
                            <input name="hiddenuid" id="hiddenuid" type="hidden"/>
                            <div class="input-group-btn">
                                <button class="btn btn-sm btn-primary" type="button" onclick="doSearch()"><i class="fa fa-search"></i>&nbsp;&nbsp;Buscar</button>
                            </div>
                        </div>
                    </div>
                    <table style="width: 100%" class="table table-striped table-bordered table-hover" id="dataTableProductos">
                        <thead>
                        <tr>
                            <th>C&oacute;digo</th>
                            <th>Descripci&oacute;n</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSaved" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 1024px!important;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header" id="modalHeader">
                    <button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Orden de Producci&oacute;n Guardada</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="input-group" id="modalContent">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="cancelar();nOrden()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="num_multiplo" value="1">
<input type="hidden" id="control_peso" value="">
<div class="wrapper wrapper-content  animated fadeInRight" id="FORM">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h4>Crear Orden de Trabajo</h4>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary permiso_registrar" data-toggle="modal" data-target="#importar"><span class="fa fa-download"></span> Importar</button>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6 b-r">
                            <div class="form-group">
                                <label>Almacen</label>
                                <select class="chosen-select form-control" id="almacen">
                                    <option value="">Seleccione un almacen</option>
                                    <?php foreach( $listaAP->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                                <div class="form-group">
                                    <label>Empresa | Proveedor</label>
                                    <select class="chosen-select form-control" id="Proveedr" name="Proveedr">
                                        <!--<option value="">Seleccione</option>-->
                                        <?php foreach( $listaProvee->getAll("AND es_cliente = 1") AS $a ): ?>
                                        <option value="<?php echo $a->ID_Proveedor; ?>">[<?php echo $a->cve_proveedor; ?>] - <?php echo $a->Nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>No. de Orden de Producción</label>
                                    <input name="txtNroOrdenProduccion" id="txtNroOrdenProduccion" type="text" class="form-control" maxlength="20" value="<?php echo $newFolio; ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label>No. de Orden de Trabajo</label>
                                    <input name="new_OT" id="new_OT" type="text" class="form-control" maxlength="20" value="">
                                </div>

                                <div class="form-group">
                                    <label>Seleccione Pedido</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <select class="chosen-select form-control" id="folios_pedidos"></select>
                                </div>

                                <div class="form-group">
                                    <label>Seleccione Área de Producción*</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <?php echo $comboAlmacen; ?>
                                    <input type="hidden" name="idy_ubica_ot" id="idy_ubica_ot" value="">
                                </div>
                                <div class="form-group" style="display:none;">
                                    <label>Ubicación Destino</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <?php echo $comboAlmacenDest; ?>
                                    <input type="hidden" name="idy_ubica_ot_dest" id="idy_ubica_ot_dest" value="">
                                </div>
                                <div class="form-group">
                                    <label>Seleccione Artículo a Producir*</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <?php echo $comboArticuloParte ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Cantidad a Producir*</label>
                                    <input name="txtCantidadLP" id="txtCantidadLP" type="number" step="1" class="form-control" maxlength="20" style="width: 50%">
                                </div>
                                <div class="form-group">
                                    <label>Unidad de Medida*</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <?php echo $comboUnidadesMedida ?>
                                </div>
                                <div class="form-group">
                                    <label>Total Kg | Pzas</label>
                                    <input name="txtNroLPTarima" id="txtNroLPTarima" type="number" step="1" class="form-control" maxlength="20" style="width: 50%" disabled>
                                </div>

                                <div class="form-group">
                                    <label>Fecha Compromiso</label>
                                    <div class="input-group" id="data_1">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaot" type="date" class="form-control" required min="<?php echo $fecha_actual; ?>" value="<?php echo $fecha_actual; ?>">
                                    </div>
                                </div>


                                <div class="form-group" id="_divFechaCaducidad" style="display: none">
                                    <label>Fecha Caducidad</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaCaducidad" type="text" class="form-control" value="<?php echo date("d-m-Y"); ?>"/>
                                    </div><input name="hiddenLote" id="hiddenLote" type="hidden">
                                </div>
                            </div>
                            <div class="col-md-8" style="margin-top: 5px;">
                                </br>
                                <button id ="editar" class="btn btn-primary" type="button"><i class="fa fa-check"></i>&nbsp;&nbsp;Calcular</button>
                            </div>
                        </div>

                        <br>

                        <div class="row" id="_rowPaso3" style="display: none">
                            <div class="row">
                                <div class="col-lg-4" id="_title">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>Agregar / Editar Componentes</label>
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <br>
                                <div class="table-responsive">
                                  <table class="table table-striped table-bordered table-hover" id="editable" >
                                      <thead>
                                      <tr>
                                          <!--<th>Area de Producción</th>-->
                                          <th>Producto</th>
                                          <th>Unidad | Kg por Producto</th>
                                          <th>UM</th>
                                          <th>Peso Kg</th>
                                          <th>Cant Req Kg|Gr</th>
                                          <th>Cant Req Pza</th>
                                          <th>Cant Disp Kg|Gr <br> (Almacén)</th>
                                          <th>Cant Disp Pzas <br> (Almacén)</th>
                                          <th>Cant Disp Kg|Gr <br> (Producción)</th>
                                          <th>Cant Disp Pzas <br> (Producción)</th>
                                      </tr>
                                      </thead>
                                      <tbody></tbody>
                                  </table>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <a href="#" onclick="cancelar()"><button type="button" class="btn btn-white" id="btnCancel">Cancelar</button></a>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary permiso_registrar" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Guardando..." id="btnSave">Guardar</button>
                                </div>
                            </div>


                        </div>
                        <input type="hidden" id="hiddenAction">
                        <input type="hidden" id="hiddenID_Aduana">

                    </div>
                </form>
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
                        <h4 class="modal-title">Importar Ordenes de Producción</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-import" action="import" method="post" enctype="multipart/form-data">


                                <div class="form-group">
                                    <label>Empresa | Proveedor</label>
                                    <select class="chosen-select form-control" id="Proveedor2" name="Proveedor2">
                                        <?php 
                                        if($cve_proveedor == "")
                                        {
                                        ?>
                                        <option value="">Seleccione</option>

                                        <?php foreach( $listaProvee->getAll("AND es_cliente = 1") AS $a ): ?>
                                        <option value="<?php echo $a->ID_Proveedor; ?>">[<?php echo $a->cve_proveedor; ?>] - <?php echo $a->Nombre; ?></option>
                                        <?php endforeach; ?>
                                        <?php 
                                        }
                                        else
                                        {
                                        ?>
                                        <?php foreach( $listaProvee->getAll("AND es_cliente = 1") AS $a ): ?>
                                        <?php 
                                        if($cve_proveedor == $a->ID_Proveedor)
                                        {
                                        ?>
                                        <option value="<?php echo $a->ID_Proveedor; ?>">[<?php echo $a->cve_proveedor; ?>] - <?php echo $a->Nombre; ?></option>
                                        <?php 
                                        }
                                        ?>
                                        <?php endforeach; ?>
                                        <?php 
                                        }
                                        ?>

                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>Seleccione Área de Producción | Almacén Destino*</label>
                                    <!--input name="txtArticuloParte" id="txtArticuloParte" type="text" class="form-control" maxlength="20"/-->
                                    <?php echo $comboAlmacen2; ?>
                                    <input type="hidden" name="idy_ubica_ot_import" id="idy_ubica_ot_import" value="">
                                </div>
                            <div class="form-group">
                                <label>Seleccionar archivo excel para importar</label>
                                <input type="file" name="file" id="file" class="form-control"  required>
                            </div>
                                <hr>
                                <div class="checkbox">
                                    <label for="check-importar">
                                    <input type="checkbox" name="check-importar" id="check-importar" value="1"><b>Realizar Producción</b></label>
                                    <div id="msj_import" style="color: #F00;display: none;"><b>Al Elegir Realizar Producción, evalúa si hay material disponible para producir y realiza el proceso de producción restando los componentes de cada Folio+Articulo Registrado en el Archivo</b></div>
                                </div>
                                <input type="hidden" name="realizar_produccion" id="realizar_produccion" value="0">


                            <div class="row">
                                <div class="col-md-12">
                            <div class="form-group" style="text-align: center;">
                                <div class="checkbox">
                                    <label for="tipo_traslado" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                    <input type="checkbox" name="tipo_traslado" id="tipo_traslado" value="0">Pedido Tipo Traslado</label>
                                    <input type="hidden" name="tipo_traslado_input" id="tipo_traslado_input" value="0">
                                </div>
                            </div>
                                </div>

                                <div class="col-md-12">

                                <div class="form-group col-md-12 tipo_traslado_int_ext" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4">
                                    <label>Tipo de Traslado:</label> 
                                        </div>
                                    
                                        <div class="col-md-4">
                                    <label>
                                    <input type="radio" name="traslado_interno_externo" checked id="tipo_interno" value="RI"> Interno</label>
                                        </div>

                                        <div class="col-md-4">
                                    <label>
                                    <input type="radio" name="traslado_interno_externo" id="tipo_externo" value="R"> Externo</label>
                                        </div>

                                        <input type="hidden" name="traslado_interno_externo_input" id="traslado_interno_externo_input" value="RI">

                                    </div>
                                </div>

                                </div>

                                <div class="form-group" style="display:none;margin: 15px;" id="almacen_destino">
                                    <label>Solicitar Productos al Almacén: </label>
                                    <select class="form-control" name="almacen_dest" id="almacen_dest">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll('traslado') AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <hr>

                                </div>
                            </div>

                            <div class="row" id="loadgif" style="text-align: center;padding: 15px; display: none;font-size: 16px;top: 15px;position: relative;">
                                <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
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
                    </div>
                    <div class="modal-footer">
                  <!--
                      <div class="col-md-6" style="text-align: left">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                  -->
                        <div class="col-md-6" style="text-align: left">
                            <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                        </div>
                        <div class="col-md-6" style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <?php 
                            if($_SESSION['cve_usuario'] == 'wmsmaster')
                            {
                            ?>
                            <button id="btn-prueba" type="button" class="btn btn-primary">Enviar Infinity</button>
                            <button id="btn-prueba-sap" type="button" class="btn btn-primary" style="display: none;">Enviar SAP</button>
                            <?php 
                            }
                            ?>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>

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
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<!-- Data picker -->
<!--<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>-->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<script type="text/javascript" src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="http://cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<!--<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>-->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>

<!--<script src="/js/jquery.auto-complete.js"></script>-->

<script type="text/javascript">


        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */
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
                if (data.success == true) {
                    setTimeout(function() {
                        $('#almacen').val(data.codigo.id).trigger('chosen:updated');
                        FoliosPedidos(data.codigo.id);
                        //filtralo();
                    }, 1000);

                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
    almacenPrede();


    $("#almacen").change(function(){
        FoliosPedidos($(this).val());
    });

    function FoliosPedidos(almacen) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                almacen: almacen,
                action: 'pedidos_listos_por_asignar'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/manufactura/ordentrabajo/index.php',
            success: function(data) {
                console.log("SUCCESS", data);
                $('#folios_pedidos').empty();
                $('#folios_pedidos').append(data.data);
                $('#folios_pedidos').chosen().trigger('chosen:updated');
            },
            error: function(res) {
                console.log("ERROR", res);
            }
        });
    }

    function cancelar() {
        $('#editar').prop('disabled', false);
        $('#btnSave').prop('disabled', false);
        $('#folios_pedidos').prop('disabled', false);
        $('#_rowPaso3').hide();
        $('#editable tbody tr').remove();
        $("#txtArticuloParte").chosen().val("");
        $("#txtArticuloParte").chosen().trigger("chosen:updated");
        $("#txtArticuloParte").prop('disabled', false).trigger('chosen:updated');

        $("#cboZonaAlmacen").chosen().val("");
        $("#cboZonaAlmacen").chosen().trigger("chosen:updated");
        $("#cboZonaAlmacen").prop('disabled', false).trigger('chosen:updated');

        $("#txtArticuloParte").chosen().val("");
        $("#txtArticuloParte").chosen().trigger("chosen:updated");
        $("#txtArticuloParte").prop('disabled', false).trigger('chosen:updated');

        $("#cboUnidadesMedida").chosen().val("");
        $("#cboUnidadesMedida").chosen().trigger("chosen:updated");
        $("#cboUnidadesMedida").prop('disabled', false).trigger('chosen:updated');

        //$("#txtNroLPTarima").prop('disabled', false);
        //$("#txtNroOrdenProduccion").prop('disabled', false);
        $("#txtCantidadLP").prop('disabled', false);

        $("#txtNroLPTarima").val("");
        $("#txtCantidadLP").val("");
        $("#hiddenLote").val("");

        $("#_titleArt").html("");
        $("#_divFechaCaducidad").hide();
    }

    $("#folios_pedidos").change(function()
    {
        var folio = '', producto = '', cantidad = '', arr_folio_producto;
        if($(this).chosen().val() != '')
        {
            arr_folio_producto = $(this).chosen().val();
            folio    = arr_folio_producto.split("::::")[0];
            producto = arr_folio_producto.split("::::")[1];
            cantidad = arr_folio_producto.split("::::")[2];

            $("#txtArticuloParte").chosen().val(producto);
            $("#txtArticuloParte").chosen().trigger("chosen:updated");
            $("#txtArticuloParte").prop('disabled', true).trigger('chosen:updated');

            $("#txtCantidadLP, #txtNroLPTarima").val(cantidad);
            $("#txtCantidadLP, #txtNroLPTarima").prop('disabled', true);

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action : "buscarPzas",
                    NroParte : $('#txtArticuloParte option:selected').val()
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
                url: '/api/manufactura/ordentrabajo/index.php',
                success: function(data) {
                    console.log("SUCCESS buscarPzas", data);
                    if (data.success == false) {
                        alert(data.err);
                    } else {
                        num_multiplo = data.num_multiplo;
                        cajas_palet = data.cajas_palet;
                        //console.log("data.unidadMedida = ", data.unidadMedida);
                        //console.log("data.num_multiplo = ", data.num_multiplo);
                        //console.log("data.cajas_palet = ", data.cajas_palet);
                        console.log("data.control_peso = ", data.control_peso);

                        if(data.control_peso == 'S')
                            $("#control_peso").val('S');
                        else
                            $("#control_peso").val('N');

                        $("#cboUnidadesMedida").val(data.unidadMedida);
                        $("#cboUnidadesMedida").chosen().trigger("chosen:updated");
                    }
                }, error: function(data)
                {
                    console.log("ERROR", data);
                }
            });

        }
        else 
        {
            $('#txtCantidadLP, #txtNroLPTarima').val("");

            $("#txtArticuloParte").chosen().val("");
            $("#txtArticuloParte").chosen().trigger("chosen:updated");
            $("#txtArticuloParte").prop('disabled', false).trigger('chosen:updated');

            $("#txtCantidadLP").val("");
            $("#txtCantidadLP").prop('disabled', false);

            $("#cboUnidadesMedida").val("");
            $("#cboUnidadesMedida").chosen().trigger("chosen:updated");
        }

    });

    var $modalWaitSaving = null;
    var $modalSaved = null;

    $('#btnSave').on('click', function() 
    {
      
      if($("#fechaot").val() == '')
      {
        swal('Error','Registre la fecha de la orden de trabajo','warning');
        return;
      }

      if ($("#hiddenLote").val()=="") 
      {
        if ($("#fechaCaducidad").val()=="") 
        {
          swal('Error','Ingrese la Fecha de Caducidad...','warning');
          return;
        }
      }
      var arrComp = [];
      var error = false;


    var folio = '', producto = '', arr_folio_producto;
    if($("#folios_pedidos").chosen().val() != '')
    {
        arr_folio_producto = $("#folios_pedidos").chosen().val();
        folio    = arr_folio_producto.split("::::")[0];
        producto = arr_folio_producto.split("::::")[1];
    }

      $('[id^="hiddencode_"]').each(function()
      {
        var __id = $(this).attr("id");
        var _uid = str_replace_all(__id, "hiddencode_", "");
        var val = $(this).val();
        var desc = $(`#hiddendesc_${val}_`+_uid).val();
        var zonaAlmacen = $(`#hiddenzonaalmacen_${val}_`+_uid).val();
        var UM = $(`#hiddenUnidadMedida_${val}_`+_uid).val();
        var UMDesc = $(`#hiddenUnidadMedidaDesc_${val}_`+_uid).val();
        var Cantidad = $(`#hiddenCantidad_${val}_`+_uid).val();
        var CantidadDisponible = $(`#hiddenCantidadDisponible_${val}_`+_uid).val();
        var CantidadUsada = ($(`#CantidadUsada_${val}_`+_uid).val()==0)?($(`#CantidadUsadaKg_${val}_`+_uid).val()):($(`#CantidadUsada_${val}_`+_uid).val());
        if (CantidadUsada=="")
        {
          error = true;
        }
        arrComp.push({
          NroParte : $('#txtArticuloParte').val(),
          code : val,
          descripcion : val,
          Cantidad : Cantidad,
          CantidadDisponible : CantidadDisponible,
          CantidadUsada : CantidadUsada,
          UM : UM,
          zonaAlmacen : zonaAlmacen,
          UMDesc : UMDesc
        });
      });
      if (error == true) 
      {
        swal('Error','Ingrese un al menos un Valor en la Cantidad...','warning');
        return;
      }
      if (arrComp.length==0) 
      {
        swal('Error','Ingrese un al menos un Producto...','warning');
        return;
      }

      if($("#new_OT").val() == '')
      {
        swal('Error','Debe Ingresar Número de Orden de Trabajo','warning');
        return;
      }

      $modalWaitSaving = $("#pleaseWaitSaving");
      $modalWaitSaving.modal('show');

      console.log("arrComp = ", arrComp);
      console.log("Almacén = ", $("#almacen").chosen().val());
      console.log("idy_ubica_ot = ", $("#idy_ubica_ot").val());
      console.log("idy_ubica_ot_dest = ", $("#idy_ubica_ot_dest").val());



      //return;

      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          NroParte : $('#txtArticuloParte').chosen().val(),
          NroParteDesc : $("#txtArticuloParte option:selected").text(),
          ZonaAlmacen : $("#cboZonaAlmacen").chosen().val(),
          idy_ubica: $("#idy_ubica_ot").val(),
          idy_ubica_dest: $("#idy_ubica_ot_dest").val(),
          ArticuloParte : $("#txtArticuloParte").chosen().val(),
          UnidadesMedida : $("#cboUnidadesMedida").chosen().val(),
          folio_rel : folio,
          fecha_ot : $("#fechaot").val(),
          almacen : $("#almacen").val(),
          NroLPTarima : $("#txtNroLPTarima").val(),
          NroOrdenProduccion : $("#txtNroOrdenProduccion").val(),
          num_OT : $("#new_OT").val(),
          CantidadLP : $("#txtCantidadLP").val(),
          Proveedor : $("#Proveedr").val(),
          FechaCaduca : '', //$("#fechaCaducidad").val(),
          Lote : $("#hiddenLote").val(),
          action : "saveComponentes",
          arrComp : arrComp
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
        url: '/api/manufactura/ordentrabajo/index.php',
        success: function(data) 
        {
            console.log(data);
          if (data.success == false) 
          {
            alert("Ocurrio un Error, Intente mas tarde...");
            $modalWaitSaving.modal('hide');
          }
          else 
          {
                $modalWaitSaving.modal('hide');
                swal({
                    title: 'Orden '+data.NroOrdenProduccion+' creada',
                    text: '\nOrden de Trabajo: ' + data.NumOrdenTrabajo +'\nProducto Compuesto ' + data.NroParte,
                    type: "success",

                    showCancelButton: false,
                    cancelButtonText: "Cancelar",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "OK",
                    closeOnConfirm: true
                },
                function(e) {
                    console.log(e);
                    window.location.reload();
                });
            //$modalWaitSaving.modal('hide');
            //$("#txtNroOrdenProduccion").val(data.NroOrdenProduccion);
            //$modalSaved = $("#modalSaved");
            //$modalSaved.modal('show');
            //$("#modalContent").html('<h3>N&uacute;mero: ' + data.NroOrdenProduccion + '   -   Numero de Orden de Trabajo: ' + data.//NumOrdenTrabajo +'</h3><br><br><h3>Producto Compuesto ' + data.NroParte + '<h3>');
          }
        }, error: function(data)
                {
                    console.log("ERROR SAVE", data);
                }
      });
    });

    function str_replace_all(string, str_find, str_replace){
        try{
            return string.replace( new RegExp(str_find, "gi"), str_replace ) ;
        } catch(ex){
            return string;
        }
    }
</script>

<script>
    $(document).ready(function(){
        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
    });

    var $modalWait = null;

    $('#editar').click(function() {

        if($("#cboZonaAlmacen").chosen().val() == '') {
            swal(
                'Error',
                'Por favor seleccione una zona de almacen',
                'error'
                );
            return;
        }

        $("#idy_ubica_ot").val($("#cboZonaAlmacen").find(':selected').data('idy_ubica'));
        $("#idy_ubica_ot_dest").val($("#cboZonaAlmacenDest").find(':selected').data('idy_ubica'));

        if($("#Proveedr").chosen().val() == '') {
            swal(
                'Error',
                'Por favor seleccione Empresa | Proveedor',
                'error'
                );
            return;
        }

        if($('#txtArticuloParte').chosen().val() == '') {
            swal(
                'Error',
                'Por favor seleccione un artiulo a producir',
                'error'
                );
            return;
        }

        if($("#txtCantidadLP").val() == '') {
            swal(
                'Error',
                'Por favor seleccione cantidad a producir',
                'error'
                );
            return;
        }

        if($("#cboUnidadesMedida").chosen().val() == '') {
            swal(
                'Error',
                'Por favor seleccione una unidad de medida',
                'error'
                );
            return;
        }

        if($("#txtNroOrdenProduccion").val() == '') {
            return;
        }

        var folio = '', producto = '', arr_folio_producto;
        if($("#folios_pedidos").chosen().val() != '')
        {
            arr_folio_producto = $("#folios_pedidos").chosen().val();
            folio    = arr_folio_producto.split("::::")[0];
            producto = arr_folio_producto.split("::::")[1];
        }

        if($("#txtArticuloParte").chosen().val() != producto && producto != '') {
            swal(
                'Error',
                'El Artículo del Folio que desea relacionar debe ser igual al Artículo que desea producir',
                'error'
                );
            return;
        }


        $modalWait = $("#pleaseWaitDialog");
        $modalWait.modal('show'); 
        $('#editable tbody tr').remove();

        console.log("almacen :", $('#almacen').val());
        console.log("NroParte :", $('#txtArticuloParte').val());
        console.log("Total :", $("#txtNroLPTarima").val());
        console.log("ZonaAlmacen :", $('#cboZonaAlmacen').val());
        console.log("ZonaAlmacenDesc :", $("#cboZonaAlmacen option:selected").text());

        var folio = '', producto = '', arr_folio_producto;
        if($("#folios_pedidos").chosen().val() != '')
        {
            arr_folio_producto = $("#folios_pedidos").chosen().val();
            folio    = arr_folio_producto.split("::::")[0];
            producto = arr_folio_producto.split("::::")[1];
        }


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "buscarComponentes",
                almacen : $('#almacen').val(),
                NroParte : $('#txtArticuloParte').val(),
                folio_rel: folio,
                Total : $("#txtNroLPTarima").val(),
                idy_ubica: $("#idy_ubica_ot").val(),
                ZonaAlmacen : $('#cboZonaAlmacen').val(),
                unidadMedida: $("#cboUnidadesMedida").val(),
                ZonaAlmacenDesc : $("#cboZonaAlmacen option:selected").text(),
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
            url: '/api/manufactura/ordentrabajo/index.php',
            success: function(data) {
                console.log("SUCCESS: ", data);
                if (data.success == false) {
                    alert(data.err);
                }else{
                    if (data.existsComp==true) {
                        var found = false;
                      
                        for (var i = 0; i < data.arrArts.length; i++) {
                            var uid = data.arrArts[i].uid;
                            var div1 = data.arrArts[i].col1;
                            var div2 = data.arrArts[i].col2;
                            var div3 = data.arrArts[i].col3;
                            var div4 = data.arrArts[i].col4;
                            var div04 = data.arrArts[i].col04;
                            var div5 = data.arrArts[i].col5;
                            var div6 = data.arrArts[i].col6;
                            var div7 = data.arrArts[i].col7;
                            var div8 = data.arrArts[i].col8;
                            var div9 = data.arrArts[i].col9;
                            var div10 = data.arrArts[i].col10;
                            var lote = data.arrArts[i].lote;
                            $("#hiddenLote").val(lote);
                            
//#Area de Producción | Producto | Unidad/Kg por Producto | UM | Cant Requerida Kg | Cant Requerida Pza | Cantidad Disponible Kgs | Cantidad Disponible Pzas
                            var _html = "<tr class=\"gradeX\" id=\"tableprods_" + uid + "\">" +
                                //"<td id=\"col1_" + uid + "\">" + div1 + "</td>" +
                                "<td id=\"col2_" + uid + "\">" + div2 + "</td>" +
                                "<td id=\"col3_" + uid + "\" style='text-align: right;'>" + div3 + "</td>" +
                                "<td id=\"col4_" + uid + "\" style='text-align: right;'>" + div4 + "</td>" +
                                "<td id=\"col04_" + uid + "\" style='text-align: right;'>" + div04 + "</td>" +
                                "<td id=\"col5_" + uid + "\" style='text-align: right;'>" + div5 + "</td>" +
                                "<td id=\"col6_" + uid + "\" style='text-align: right;'>" + div6 + "</td>" +
                                "<td id=\"col8_" + uid + "\" style='text-align: right;'>" + div8 + "</td>" +
                                "<td id=\"col7_" + uid + "\" style='text-align: right;'>" + div7 + "</td>" +
                                "<td id=\"col10_" + uid + "\" style='text-align: right;'>" + div10 + "</td>" +
                                "<td id=\"col9_" + uid + "\" style='text-align: right;'>" + div9 + "</td>" +
                                "</tr>";
                            $('#editable > tbody:last-child').append(_html);
                            found = true;
                        }
                        if (found) {
                            $('#_rowPaso3').show();
                            $("#folios_pedidos").prop('disabled', true).trigger('chosen:updated');
                            $("#cboZonaAlmacen").prop('disabled', true).trigger('chosen:updated');
                            $("#txtArticuloParte").prop('disabled', true).trigger('chosen:updated');
                            $("#cboUnidadesMedida").prop('disabled', true).trigger('chosen:updated');
                            //$("#txtNroLPTarima").prop('disabled', true);
                            //$("#txtNroOrdenProduccion").prop('disabled', true);
                            //$("#txtCantidadLP").prop('disabled', true);
                            //if ($("#hiddenLote").val()=="") $("#_divFechaCaducidad").show();
                        }

                        //$('#editar').prop('disabled', true);
                    } else {
                         swal(
                            'Error',
                            'No Existen Productos para este Artículo Compuesto',
                            'error'
                            );
                    }
                    $modalWait.modal('hide');
                }
            }, error: function(data)
            {
                console.log("ERROR: ", data);
            }
        });
    });

    function uniqueID(){
        function chr4(){
            return Math.random().toString(16).slice(-4);
        }
        return chr4() + chr4() +
            '-' + chr4() +
            '-' + chr4() +
            '-' + chr4() +
            '-' + chr4() + chr4() + chr4();
    }

    var oTable2 = $('#dataTableProductos').dataTable( {
        dom: 'tipr',
        responsive: true,
        "searching": true,
        "bProcessing": true,
        "serverSide": true,
        "pagingType": "full_numbers",
        "language": {
            "lengthMenu": "Mostrando _MENU_ registros",
            "zeroRecords": "",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "",
            "sSearch": "Filtrar:",
            "sProcessing":   	"Cargando...",

            "oPaginate": {
                "sNext": "Sig",
                "sPrevious": "Ant",
                "sLast": "Ultimo",
                "sFirst": "Primero",
            }
        },
        "ajax": {
            "url": "/api/manufactura/ordentrabajo/index.php",
            "type": "POST",
            "data": {
                "action" : "loadPopup",
                "criterio" : $("#txtBusqueda").val(),
                "action" : "loadPopup"
            },
            "columns": [
                { "data": "cve_articulo" },
                { "data": "des_articulo" }
            ],
        },
        "createdRow": function ( row, data, index ) {
            var code = data[0];
            $('td', row).eq(0).html("<a href=\"#\" onclick=\"agregarProductoCompuesto('"+data[0]+"', '"+data[1]+"')\">"+data[0]+"</a>");
            $('td', row).eq(1).html("<a href=\"#\" onclick=\"agregarProductoCompuesto('"+data[0]+"', '"+data[1]+"')\">"+data[1]+"</a>");
            $(row).attr('id', 'tr_'+code);
        }

    } );

    function buscarComponente(uid) {
        $('#hiddenuid').val(uid)
        $modal2 = $('#conModal');
        $modal2.modal('show');
    }

    function doSearch() {
        oTable2.DataTable().search($("#txtBusqueda").val()).draw();
    }

    var table = $('#editable').dataTable({
        responsive: true,
        "autoWidth": false,
        "bInfo":false
        ,"bLengthChange":false
        ,"bFilter":false
        ,"bPaginate":false
        ,"bSort": false
        ,"aoColumns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null
        ],
        "createdRow": function ( row, data, index ) {
            //$(row).attr('id', 'first_row');
        },
        "initComplete": function () {
            $('#editable tbody .dataTables_empty').remove();
        }
    });

    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }
/*
    $('.input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'dd-mm-yyyy'
    });

*/
/*
    //enable datepicker
    function pickDate( cellvalue, options, cell ) {
        setTimeout(function(){
            $(cell) .find('input[type=text]')
                .datepicker({format:'dd-mm-yyyy' , autoclose:true});
        }, 0);
    }
*/
    $("#txtCantidadLP").keydown(function (event) {
        if($("#control_peso").val() == 'N')
        {
            if (event.shiftKey) {
                event.preventDefault();
            }

            if (event.keyCode == 46 || event.keyCode == 8) {
            }
            else {
                if (event.keyCode < 95) {
                    if (event.keyCode < 48 || event.keyCode > 57) {
                        event.preventDefault();
                    }
                }
                else {
                    if (event.keyCode < 96 || event.keyCode > 105) {
                        event.preventDefault();
                    }
                }
            }
        }
    });

    var num_multiplo = 0;
    var cajas_palet = 0;

    $('#txtArticuloParte').on('change', function(event, params) {
        if($('#txtArticuloParte').val() != '') {

            $('#txtCantidadLP, #txtNroLPTarima').val("");
            $("#cboUnidadesMedida").empty();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    action : "buscarPzas",
                    NroParte : $('#txtArticuloParte option:selected').val()
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
                url: '/api/manufactura/ordentrabajo/index.php',
                success: function(data) {
                    console.log("SUCCESS", data);
                    if (data.success == false) {
                        alert(data.err);
                    } else {
                        num_multiplo = data.num_multiplo;
                        cajas_palet = data.cajas_palet;
                        $("#cboUnidadesMedida").append(data.comboUM);

                        if($("#cboUnidadesMedida").find(':selected').data('um')=='XBX')
                            $("#num_multiplo").val(num_multiplo);
                        else
                            $("#num_multiplo").val(1);
                        

                        console.log("##num_multiplo = ", $("#num_multiplo").val());

                        //console.log("data.unidadMedida = ", data.unidadMedida);
                        //console.log("data.num_multiplo = ", data.num_multiplo);
                        //console.log("data.cajas_palet = ", data.cajas_palet);
                        console.log("data.control_peso = ", data.control_peso);

                        if(data.control_peso == 'S')
                            $("#control_peso").val('S');
                        else
                            $("#control_peso").val('N');

                        $("#cboUnidadesMedida").val(data.unidadMedida);
                        $("#cboUnidadesMedida").chosen().trigger("chosen:updated");
                    }
                }, error: function(data)
                {
                    console.log("ERROR", data);
                }
            });
        }
    });

    $('#cboUnidadesMedida').on('change', function(event, params) {
        if($('#txtCantidadLP').val() != '' && $('#txtArticuloParte').val() != '') {
            var um = $('#cboUnidadesMedida option:selected').val();
            var cant = $('#txtCantidadLP').val();
            var total = 0;
            /*
            switch (um) {
                case "1":
                    total = cant * 1;
                    $("#txtNroLPTarima").val(total);
                    break;
                case "2":
                    total = cant * num_multiplo;
                    $("#txtNroLPTarima").val(total);
                    break;
                case "3":
                    total = cant * num_multiplo * cajas_palet;
                    $("#txtNroLPTarima").val(total);
                    break;
            }
            */


        } else {
            if($('#txtCantidadLP').val() == '')
            swal(
                'Error',
                'Registre una Cantidad',
                'error'
            );
            else
            swal(
                'Error',
                'Seleccione un Artículo',
                'error'
            );
        }

            if($("#cboUnidadesMedida").find(':selected').data('um')=='XBX')
            {
                $("#num_multiplo").val($("#cboUnidadesMedida").find(':selected').data('nmultiplo'));
            }
            else
                $("#num_multiplo").val(1);

            var cant = $('#txtCantidadLP').val();
            $("#txtNroLPTarima").val(cant*$("#num_multiplo").val());
    });

    $('#txtCantidadLP').keyup(function() {
        if ($('#txtCantidadLP').val() != "") {
            var um = $('#cboUnidadesMedida option:selected').val();
            var cant = $('#txtCantidadLP').val();
            var total = 0;

            console.log("#num_multiplo = ", $("#num_multiplo").val(), "cant*num_multiplo = ", cant*$("#num_multiplo").val());
            $("#txtNroLPTarima").val(cant*$("#num_multiplo").val());

            console.log("um = ", um);
            /*
            switch (um) {
                case "1":
                    total = cant * 1;
                    $("#txtNroLPTarima").val(total);
                    break;
                case "2":
                    total = cant * num_multiplo;
                    $("#txtNroLPTarima").val(total);
                    break;
                case "3":
                    total = cant * num_multiplo * cajas_palet;
                    $("#txtNroLPTarima").val(total);
                    break;
            }
            */
        }
    });

    $("#tipo_traslado").click(function()
    {
        if($("#tipo_traslado").is(':checked'))
        {
            $("#check-importar").prop("checked", false);
            $("#msj_import").hide();
            $("#tipo_traslado_input").val("1");
            //$(".tipo_traslado_int_ext").show();
            //console.log("folio_tr = ",$("#folio_tr").val());
            //$("#folio").val($("#folio_tr").val());
            $("#almacen_destino").show();
            //fillSelectArti("S");
            //fillSelectLP();
            //$("#Articul span").show();
            //BuscarCliente("");
            //BuscarCliente("Borrar_La_Lista_de_Clientes");

        $("#almacen_dest").val("");
        $("#almacen_dest option").show();
        $("#almacen_dest option[value=" + $(this).val() + "]").hide();
        //$("#almacen_dest").val($("#almacen_dest option:first").val());
        //$("#almacen_dest").val($("#almacen_dest option:last").val());
        //$("#almacen_dest").val("");
        $("#almacen_dest").trigger("change");
        $("#almacen_dest").trigger("chosen:updated");

        }
        else
        {
            $("#tipo_traslado_input").val("0");
            $(".tipo_traslado_int_ext").hide();
            //$("#folio").val($("#folio_fol").val());
            $("#almacen_destino").hide();
            //$("#tipo_externo").prop("checked", true);
            //fillSelectArti("N");
            //fillSelectLP();
            //$("#Articul span").hide();
        }
    });


    function nOrden() 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action : "nOrden"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
            url: '/api/manufactura/ordentrabajo/index.php',
            success: function(data) {
                if (data.success == false) {
                    alert(data.err);
                } else {
                    console.log("nOrden = ", data.NroOrdenProduccion);
                    $("#txtNroOrdenProduccion").val(data.NroOrdenProduccion);
                }
            }
        });
    }


function EnviarConsumoInfinity(folios)
{

    console.log("Folios Enviar Infinity = ", folios);
    //return;
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/adminordentrabajo/update/index.php',
        data: 
        {
          action: 'EnviarConsumoInfinity',
          folios: folios
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS INFINITY", data);
        }, error: function(data) 
        {
          console.log("ERROR INFINITY", data);
        }
      });
}


$("#btn-prueba").click(function(){
  $.ajax({
    type: "POST",
    //dataType: "json",

    url: '/api/adminordentrabajo/update/index.php',
    data: 
    {
      action: 'prueba_infinity'
    },
    //beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
    success: function(data) 
    {
        console.log("SUCCESS INFINITY: ", data);
    }, error: function(data) 
    {
      console.log("ERROR INFINITY: ", data);
    }
  });

});

$("#btn-prueba-sap").click(function(){
  $.ajax({
    type: "POST",
    dataType: "json",

    url: '/api/webserviced_win_mysql1.php',
    data: 
    {
      action: 'prueba_sap'
    },
    //beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
    success: function(data) 
    {
        console.log("SUCCESS SAP: ", data);
    }, error: function(data) 
    {
      console.log("ERROR SAP: ", data);
    }
  });

});


$('#btn-layout').on('click', function(e) {
  //console.log("Layout_EntradasRL", window.location.href, '/Layout/Layout_EntradasRL.xlsx');
  <?php //echo $_SERVER['HTTP_HOST']; ?>
  window.location.href = '/Layout/Layout_OT.xlsx';

}); 

$("#check-importar").click(function()
{
    $("#msj_import").hide();
    if($("#check-importar").is(":checked"))
    {
        $("#msj_import").show();

        if($("#tipo_traslado").is(":checked"))
        {
            $("#check-importar").prop("checked", false);
            $("#msj_import").hide();
        }
    }
});
  
$('#btn-import').on('click', function() {

    console.log("idy_ubica = ", $("#cboZonaAlmacen-import").find(':selected').data('idy_ubica'));
    //return;
    $("#realizar_produccion").val(0);
    $("#msj_import").hide();
    if($("#check-importar").is(":checked"))
    {
        $("#realizar_produccion").val(1);
        $("#msj_import").show();
    }
        console.log("checked Modificar", $("#realizar_produccion").val());


    if($("#tipo_traslado").is(':checked') && $("#almacen_dest").val() == '')
    {
        swal("Error", "Debe Seleccionar un Almacén Destino para el traslado", "error");
        $("#tipo_traslado_input").val("0");
        return;
    }
    else
    {
        //$("#traslado_interno_externo_input").val("1");
        $("#traslado_interno_externo_input").val($("input[name=traslado_interno_externo]:checked").val());
    }


    if($("#cboZonaAlmacen-import").val() == "")
    {
        swal("Error", "Debe Seleccionar un Área de Producción", "error");
        return;
    }

    $("#idy_ubica_ot_import").val($("#cboZonaAlmacen-import").find(':selected').data('idy_ubica'));

    if($("#Proveedor2").val() == "")
    {
        swal("Error", "Debe Seleccionar un Proveedor", "error");
        return;
    }

    if($("#file").val() == "")
    {
        swal("Error", "Debe Seleccionar un Archivo XLSX", "error");
        return;
    }

    var data_folios = "", res_folios = "", intervalo = '';
    function revisar_folios()
    {
        console.log("revisar_folios()", data_folios);
        $.ajax({
            //url: '/orden-de-produccion/revisar_progreso',
            url: '/api/ordenproduccion/lista/index.php',
            type: 'POST',
            dataType: "json",
            async:true,
            data: {
                folios: data_folios,
                action: 'revisar_folios'
            },
            //cache: false,
            //contentType: false,
            //processData: false,
        success: function(dataFolios) {
            console.log("PROGRESS()", dataFolios);
            res_folios = dataFolios;
            console.log("SUCCESS PROGRESS: ", dataFolios.porcentaje);
            if(dataFolios.porcentaje == 100)
            {
                clearInterval(intervalo);
                console.log("LISTO LA IMPORTACION");
                  swal({
                    title: 'Éxito',
                    text: "Transformaciones Realizadas",
                    type: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    showCancelButton: false,
                    cancelButtonText: 'No',
                    closeOnConfirm: true,
                    closeOnCancel: true
                  }, function(yes){
                        window.location.reload();
                  });
            }

            },
        error: function(dataFolios) {
            console.log("ERROR PROGRESS: ", dataFolios);

            }
        });
    }

    console.log("idy_ubica_hidden = ", $("#idy_ubica_ot_import").val());
    //return;
    $("#loadgif").show();

    $('#btn-import').prop('disabled', true);
    var bar = $('.progress-bar');
    var percent = $('.percent');
    //var status = $('#status');

    var formData = new FormData();
    formData.append("clave", "valor");

    var url = '/orden-de-produccion/importar';
    //url = '/orden-de-produccion/importar_foam';
    //if($("#instancia").val() == 'dev')
    //{
    //    url = '/orden-de-produccion/importar';
    //}

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                orden: 'BEGIN;',
                action: 'begin_commit_rollback'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/configuraciongeneral/update/index.php',
            success: function(data) {
                console.log(data);
            },
            error: function(res) {
                console.log("ERROR -> ", data);
            }
        });

    $.ajax({
        // Your server script to process the upload
        url: url,
        type: 'POST',

        // Form data
        data: new FormData($('#form-import')[0]),

        // Tell jQuery not to process data or worry about content-type
        // You *must* include these options!
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(data) {
            //$('.progress').show();
            ////console.log("data beforeSend", data.porcentaje);
            //var percentVal = '0%';
            //bar.width(percentVal);
            //percent.html(percentVal);
  /*
            $.ajax({
                url: '/orden-de-produccion/get_folios',
                type: 'POST',
                data: new FormData($('#form-import')[0]),
                cache: false,
                contentType: false,
                processData: false,
            success: function(data) {
                console.log("SUCCESS FOLIOS: ", data);
                    data_folios = data.folios;
                }, error: function(data) {
                    console.log("ERROR FOLIOS: ", data);
                }
            });
*/
        },
  /*
        always: function(data) {
            $('.progress').show();
            console.log("data always", data);
            //var percentVal = '0%';
            //bar.width(percentVal);
            //percent.html(percentVal);
        },
        // Custom XMLHttpRequest
        progress: function(e) {
                //make sure we can compute the length
                console.log("progress: ", e);
                if(e.lengthComputable) {
                    //calculate the percentage loaded
                    var pct = (e.loaded / e.total) * 100;

                    //log percentage loaded
                    //console.log(pct);
                    //$('#progress').html(pct.toPrecision(3) + '%');
                }
                //this usually happens when Content-Length isn't set
                else {
                    console.warn('Content Length not reported!');
                }
            },
*/
/*
        xhr: function(data) {
            console.log("data xhr", data);

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
          },*/
        success: function(data) {
            console.log("SUCCESS Import: ", data);
            setTimeout(

                function(){if (data.status == 200) {
                    //swal("Exito", data.statusText, "success");
                    $('#importar').modal('hide');
                    //setTimeout(function(){
                    //    window.location.reload();
                    //}, 2000);
                    //EnviarConsumoInfinity(data.folios_creados);
                  swal({
                    title: 'Éxito',
                    text: data.statusText,
                    type: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    showCancelButton: false,
                    cancelButtonText: 'No',
                    closeOnConfirm: true,
                    closeOnCancel: true
                  }, function(yes){
                        window.location.reload();
                  });
                  $('#btn-import').prop('disabled', false);
                  $("#loadgif").hide();
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        orden: 'COMMIT;',
                        action: 'begin_commit_rollback'
                    },
                    beforeSend: function(x) {
                        if (x && x.overrideMimeType) 
                        {
                            x.overrideMimeType("application/json;charset=UTF-8");
                        }
                    },
                    url: '/api/configuraciongeneral/update/index.php',
                    success: function(data) {
                        console.log(data);
                    },
                    error: function(res) {
                        console.log("ERROR -> ", data);
                    }
                });

                }
                /*else if (data.status == 250) {
                    console.log("porcentaje", data.porcentaje);
                }*/
                else {
                    //swal("Error", data.statusText, "error");
                    //$('#btn-import').prop('disabled', false);
                }
                $("#loadgif").hide();
            },1000);

        }/*,complete: function(data) {
            console.log("SUCCESS Import: ", data);
            setTimeout(
                function(){if (data.status == 200) {
                    //swal("Exito", data.statusText, "success");
                    $('#importar').modal('hide');
                    //setTimeout(function(){
                    //    window.location.reload();
                    //}, 2000);
                    //EnviarConsumoInfinity(data.folios_creados);
                  swal({
                    title: 'Éxito',
                    text: 'Producción Realizada con éxito',
                    type: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    showCancelButton: false,
                    cancelButtonText: 'No',
                    closeOnConfirm: true,
                    closeOnCancel: true
                  }, function(yes){
                        window.location.reload();
                  });
                  $('#btn-import').prop('disabled', false);
                  $("#loadgif").hide();
                }
                else {
                    //swal("Error", data.statusText, "error");
                    //$('#btn-import').prop('disabled', false);
                }
                $("#loadgif").hide();
            },1000)
        }*/, error: function(data){
            console.log("ERROR: ", data);
/*
            setTimeout(function(){

                if(data_folios == '')
                {
                    $.ajax({
                        url: '/orden-de-produccion/get_folios',
                        type: 'POST',
                        data: new FormData($('#form-import')[0]),
                        cache: false,
                        contentType: false,
                        processData: false,
                    success: function(data) {
                        console.log("SUCCESS FOLIOS: ", data);
                            data_folios = data.folios;
                        }, error: function(data) {
                            console.log("ERROR FOLIOS: ", data);
                        }
                    });
                }


                intervalo = setInterval(revisar_folios, 2000);

            }, 1000);
*/

            //$('#btn-import').prop('disabled', false);

            //swal("Error", "Error en Importación", "error");
            //$("#loadgif").hide();

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                orden: 'ROLLBACK;',
                action: 'begin_commit_rollback'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/configuraciongeneral/update/index.php',
            success: function(data) {
                console.log(data);

                    $('#importar').modal('hide');
                    //setTimeout(function(){
                    //    window.location.reload();
                    //}, 2000);
                    //EnviarConsumoInfinity(data.folios_creados);
                  swal({
                    title: 'Importación Realizada',
                    text: 'Por favor Revise los registros de esta importación, Pudieron haber faltado algunos registros',
                    type: 'warning',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    showCancelButton: false,
                    cancelButtonText: 'No',
                    closeOnConfirm: true,
                    closeOnCancel: true
                  }, function(yes){
                        window.location.reload();
                  });
                  $('#btn-import').prop('disabled', false);
                  $("#loadgif").hide();

            },
            error: function(res) {
                console.log("ERROR -> ", data);
            }
        });

        }
    })/*.progress(function(data){
        console.log("porcentaje", data.porcentaje);
  })*/;


});

    $(function($) {
/*
        $('#data_1').datetimepicker({
                locale: 'es',
                format: 'DD-MM-YYYY',
                useCurrent: false
        });
*/
    });


</script>
