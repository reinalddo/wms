<?php
include $_SERVER['DOCUMENT_ROOT']."/app/host.php";
$listaZR = new \CortinaEntrada\CortinaEntrada();
$listaProvee = new \Proveedores\Proveedores();
$listaAlma = new \Almacen\Almacen();
$listaProyectos = new \Proyectos\Proyectos();
$listaArtic = new \Articulos\Articulos();
$listaUser = new \Usuarios\Usuarios();
$listaPriori = new \TipoPrioridad\TipoPrioridad();
$listaOC = new \OrdenCompra\OrdenCompra();
$listaAP = new \AlmacenP\AlmacenP();
$listaProto= new \Protocolos\Protocolos();
$grupoArticulos = new \GrupoArticulos\GrupoArticulos();
$SubGrupoArticulos = new \SubGrupoArticulos\SubGrupoArticulos();
$nuevos_pedidos = new \NuevosPedidos\NuevosPedidos();
$unidad_medida = new \UnidadesMedida\UnidadesMedida();
$listaContactos = new \Contactos\Contactos();

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//$clientes  = new \Clientes\Clientes();
//$clientes = $clientes->getAll();
//$usuario = "";
$usuario = $_SESSION['id_user'];
$id_almacen = "";
$id_proveedor = "";
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

$confSql = \db()->prepare("SELECT IFNULL(Valor, '0') AS SFA FROM t_configuraciongeneral WHERE cve_conf = 'SFA' LIMIT 1");
$confSql->execute();
$ValorSFA = $confSql->fetch()['SFA'];


    $cliente_almacen_style = ""; $cve_cliente = ""; $clave_almacen_cliente = ""; $nombre_almacen_cliente = ""; $nombre_cliente = "";
    //if(isset($_SESSION['es_cliente'])) 
    //{
        if($_SESSION['es_cliente'] == 1) 
        {
            $cve_usuario = $_SESSION['cve_usuario'];
            $cliente_almacen_style = "style='display: none;'";
            $cve_cliente = $_SESSION['cve_cliente'];

            $sqlCliente = "SELECT c.id AS id_almacen, c.clave, c.nombre  FROM c_almacenp c INNER JOIN c_usuario u ON u.cve_usuario = '$cve_usuario' AND u.cve_almacen = c.clave";
            if (!($res = mysqli_query($conn, $sqlCliente))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

                $row = mysqli_fetch_array($res);
                $id_almacen = $row['id_almacen'];
                $clave_almacen_cliente = $row['clave'];
                $nombre_almacen_cliente = $row['nombre'];
                $_SESSION['id_almacen'] = $id_almacen;

            $sqlCliente = "SELECT RazonSocial  FROM c_cliente WHERE Cve_Clte = '$cve_cliente'";
            if (!($res = mysqli_query($conn, $sqlCliente))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

                $row = mysqli_fetch_array($res);
                $nombre_cliente = $row['RazonSocial'];

        }
    //}
        else
        {
            $id_almacen = $_SESSION['id_almacen'];
            $id_proveedor = $_SESSION['id_proveedor'];
        }

/*
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
*/


    $rutasvpSql = \db()->prepare("SELECT *  FROM t_ruta WHERE venta_preventa = 1 AND Activo = 1 AND cve_almacenp = {$id_almacen}");
    $rutasvpSql->execute();
    $rutasvp = $rutasvpSql->fetchAll(PDO::FETCH_ASSOC);

    $sqlSFA = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SFA'";
    if (!($res = mysqli_query($conn, $sqlSFA))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $valor_sfa = "0";
    if(mysqli_num_rows($res) == 0)
        $valor_sfa = "0";
    else
    {
        $row = mysqli_fetch_array($res);
        $valor_sfa = $row['Valor'];
    }

$sql_proveedor = "";
if($id_proveedor)
    $sql_proveedor = " AND ID_Proveedor = {$id_proveedor} ";

$OT_terminadas = \db()->prepare("SELECT * FROM t_ordenprod WHERE Status = 'T' AND cve_almac = {$id_almacen} {$sql_proveedor} AND CONCAT(Cve_Articulo, Cve_Lote) IN (SELECT CONCAT(cve_articulo, lote) FROM ts_existenciatarima WHERE cve_almac = {$id_almacen})");
$OT_terminadas->execute();
$ots_con_lp = $OT_terminadas->fetchAll(PDO::FETCH_ASSOC);

$LP_list = \db()->prepare("
    SELECT DISTINCT CveLP 
    FROM c_charolas 
    WHERE CONVERT(clave_contenedor, CHAR) IN (SELECT DISTINCT CONVERT(v.Cve_Contenedor, CHAR) FROM V_ExistenciaGral v LEFT JOIN c_ubicacion u ON CONVERT(u.idy_ubica, CHAR) = CONVERT(v.cve_ubicacion, CHAR) WHERE CONVERT(v.cve_almac, CHAR) = CONVERT('{$id_almacen}', CHAR) AND CONVERT(u.picking, CHAR) = CONVERT('S', CHAR) AND CONVERT(v.tipo, CHAR) = CONVERT('ubicacion', CHAR)) 
    AND CONVERT(IDContenedor, CHAR) NOT IN (SELECT DISTINCT CONVERT(nTarima, CHAR) FROM td_pedidoxtarima) AND CONVERT(cve_almac, CHAR) = CONVERT('{$id_almacen}', CHAR) AND CONVERT(IFNULL(CveLP, ''), CHAR) != CONVERT('', CHAR)

    UNION 

    SELECT DISTINCT c.CveLP 
    FROM c_charolas c
    INNER JOIN ts_existenciatarima t ON CONVERT(t.ntarima, CHAR) = CONVERT(c.IDContenedor, CHAR) 
    INNER JOIN td_pedidoxtarima p ON CONVERT(p.nTarima, CHAR) = CONVERT(t.ntarima, CHAR) 
    INNER JOIN th_pedido th ON CONVERT(th.fol_folio, CHAR) = CONVERT(p.fol_folio, CHAR) #AND th.status NOT IN ('A', 'S')
    #INNER JOIN t_recorrido_surtido r ON r.claveEtiqueta = p.nTarima
    WHERE CONVERT(IFNULL(c.CveLP, ''), CHAR) != CONVERT('', CHAR) AND CONVERT(t.cve_almac, CHAR) = CONVERT('{$id_almacen}', CHAR) AND t.existencia > 0 
    AND CONVERT(c.IDContenedor, CHAR) NOT IN (SELECT nTarima FROM td_pedidoxtarima WHERE CONVERT(fol_folio, CHAR) IN (SELECT CONVERT(fol_folio, CHAR) FROM th_pedido WHERE CONVERT(STATUS, CHAR) IN (CONVERT('A', CHAR), CONVERT('S', CHAR))))
    ORDER BY CveLP");
$LP_list->execute();
$lp_lista = $LP_list->fetchAll(PDO::FETCH_ASSOC);

if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res)['charset'];
    mysqli_set_charset($conn , $charset);


$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];


?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">
 <link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">

<div class="wrapper wrapper-content  animated" id="FORM" >

<input type="hidden" id="pedido_tipo_LP" value="">
<input type="hidden" id="pedido_tipo_proyecto" value="">
<input type="hidden" id="folio_fol" value="">
<input type="hidden" id="folio_tr" value="">
<input type="hidden" id="lista_p_preciomin" value="">
<input type="hidden" id="lista_p_preciomax" value="">
<input type="hidden" id="lista_d_descmin" value="">
<input type="hidden" id="lista_d_descmax" value="">
<input type="hidden" id="cve_cia" value="<?php echo $_SESSION['cve_cia']; ?>">
<input type="hidden" id="sfa_activo" value="<?php echo $ValorSFA; ?>">

    <?php
    ?>

    <input type="hidden" id="cve_cliente" value="<?php echo $cve_cliente; ?>">
    <?php /* ?><input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>"><?php */ ?>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Registro de Pedidos</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-md-6 b-r b-b">
								<div class="form-group">
                                    <label>Folio*</label> 
                                    <input id="folio" name="folio" type="text" class="form-control" onBlur="validarFolio()" disabled>
                                </div>
								<div class="form-group">
                                    <label>Almacén*</label>
                                    <select class="form-control" name="almacen" id="almacen">
                                        <?php //if(isset($_SESSION['es_cliente']))
                                            if($_SESSION['es_cliente'] == 1)
                                            { ?>
                                            <option value="<?php echo $id_almacen; ?>"><?php echo "(".$clave_almacen_cliente.") - ".$nombre_almacen_cliente; ?></option>
                                        <?php }
                                              else 
                                              {
                                                ?>
                                                <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                        <?php endforeach; ?>

                                        <?php 
                                              }
                                         ?>
                                    </select>

                                </div>

                                <div class="form-group col-md-6" style="padding-left: 0;">
                                    <?php //if(!isset($_SESSION['es_cliente']))
                                    if($_SESSION['es_cliente'] != 1)
                                    { ?>
                                  <label>Cliente*</label>
                                  <input class="form-control" name="cliente_buscar" id="cliente_buscar" placeholder="Código del Cliente">
                                  <input class="form-control" name="cliente" id="cliente" placeholder="Código del Cliente" style="display: none;">
                                  <?php 
                                    }
                                  /*
                                  ?>
                                  <select class="form-control chosen-select" name="cliente" id="cliente">
                                    <option value="">Seleccione</option>
                                    <?php */ /* if(!empty($clientes)): ?>
                                      <?php foreach($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente->Cve_Clte ?>"><?php echo $cliente->Cve_Clte."-".$cliente->RazonSocial?></option>
                                      <?php endforeach; ?>
                                    <?php endif; */ /* ?>
                                  </select>
                                  <?php 
                                  */
                                  ?>
                                </div>

                                <div class="form-group col-md-6" style="padding-right: 0;">
                                    <label>Prioridad</label>
                                    <select class="form-control" name="prioridad" id="prioridad">
                                            <!--<option value="">Seleccione Prioridad</option>-->
                                          <?php foreach( $listaPriori->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->ID_Tipoprioridad; ?>" <?php /*if($a->ID_Tipoprioridad == 2) echo "selected";*/ ?>><?php echo $a->ID_Tipoprioridad."-".$a->Descripcion; ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                    <label>Clave y Nombre Cliente</label>
                                         <?php /* ?><input id="desc_cliente" name="desc_cliente" type="text" class="form-control" value="" disabled><?php */ ?>
                                         <select id="desc_cliente" name="desc_cliente" class="form-control">
                                            <?php 
                                            //if(isset($_SESSION['es_cliente']))
                                            if($_SESSION['es_cliente'] == 1)
                                            {
                                            ?>
                                                <option value="<?php echo $cve_cliente; ?>"> [<?php echo $cve_cliente; ?>] - <?php echo $nombre_cliente; ?></option>
                                            <?php 
                                            }
                                            else
                                            {
                                            ?>
                                            <option value="">Sin Cliente</option>
                                            <?php
                                            }
                                            ?>
                                         </select>
                                    </div>


                                <div class="form-group col-md-6">
                                    <label>Rutas Venta | Preventa</label>
                                        <select id="rutas_ventas" class="form-control">
                                            <option value="">Ruta</option>
                                            <?php foreach( $rutasvp AS $p ): ?>
                                                <option value="<?php echo $p["cve_ruta"];?>"><?php echo $p["cve_ruta"]." ".$p["descripcion"]; ?></option>
                                            <?php endforeach;  ?>
                                        </select>
                                </div>

                                <div class="form-group col-md-2">
                                    <label>Dia Operativo
                                    <input type="text" name="ultimo_diao" id="ultimo_diao" class="form-control" value="" readonly>
                                    </label>
                                </div>

    <input type="hidden" id="num_oc_obligatorio" value="<?php if(strpos($_SERVER['HTTP_HOST'], 'alfatrading') === true) echo "1"; else echo "0"; ?>">
                                <div class="form-group col-md-4">
                                <label>Número OC Cliente</label>
                                     <input id="nroOC" name="nroOC" type="text" class="form-control" value="">
                                </div>


                                </div>


                                <div class="form-group">
                                    <label>Usuario que  solicita</label>
                                    <?php /* ?><input id="vendedor" name="vendedor" type="text" class="form-control"><?php   chosen-select */ ?>
                                    <select class="form-control" name="vendedor" id="vendedor">
                                        <option value="">Seleccione</option>
                                        <?php /*foreach( $listaUser->getAll() AS $a ): ?>
                                          <?php 
                                          if($a->es_cliente == 3)
                                          {
                                          ?>
                                            <option value="<?php echo $a->cve_usuario; ?>"><?php echo "( ".$a->cve_usuario." ) - ".$a->nombre_completo; ?></option>
                                          <?php 
                                          }
                                          ?>
                                        <?php endforeach;*/ ?>
                                    </select>

                                </div>


                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group" id="data_1">
                                    <label>Fecha de entrega solicitada*</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input id="fechaSolicitud" name="fechaSolicitud" type="text" class="form-control">
                                    </div>
                                </div>

                                
                                <div class="form-group col-md-3" <?php if($valor_sfa != "1"){echo "style='display:none;'";} ?>><label>Tipo de Venta</label> 
                                  <br>
                                    <label>
                                    <input type="radio" name="tipo_venta" id="tipo_venta" value="venta"> Venta</label>
                                    <br>
                                    <label>
                                    <input type="radio" name="tipo_venta" checked id="tipo_preventa" value="preventa"> Pre Venta</label>
                                </div>

                                <div class="form-group col-md-3"><label>Tipo de Negociación</label> 
                                  <br>
                                    <label>
                                    <input type="radio" name="tipo_negociacion" id="tipo_credito" value="Credito"> Crédito</label>
                                    <br>
                                    <label>
                                    <input type="radio" name="tipo_negociacion" checked id="tipo_contado" value="Contado"> Contado</label>
                                </div>

                                <div class="form-group col-md-6" style="display: none;" id="select_forma_pago"><label>Forma de Pago</label> 
                                  <br>
                                    <select class="form-control" name="forma_pago" id="forma_pago">
                                          <?php foreach( $nuevos_pedidos->getFormasPago($_SESSION['cve_almacen']) AS $a ): ?>
                                            <option value="<?php echo $a->IdFpag; ?>"><?php echo $a->Clave."-".$a->Forma; ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>
                                  <br><br><br><br><br>
                                <div class="form-group">
                                    <label>Horario Planeado</label>
                                    <div class="input-group date" style="display: block;">

                                        <div style="display: inline-block;">
                                        <label><b>Desde</b></label>
                                        <br>
                                        <input id="hora_desde" name="hora_desde" type="time" class="form-control" 
                                        style="
                                            display: inline-block;
                                            margin-right: 20px;
                                            width: 200px;">
                                        </div>

                                        <div style="display: inline-block;">
                                        <label><b>Hasta</b></label>
                                        <br>
                                        <input id="hora_hasta" name="hora_hasta" type="time" class="form-control" style="
                                            display: inline-block;
                                            margin-right: 20px;
                                            width: 200px;"></div>
                                        </div>
                                        <br>
                                    </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Dirección de Envío *</label> 
                                            <div class="input-group date">                                                
                                                <select class="form-control chosen-select" name="destinatario" id="destinatario" disabled></select>
                                                <span class="input-group-addon" style="padding:0px !important; ">                                                
                                                    <button type="button" disabled id="agregar_destinatario" class="btn btn-success permiso_registrar" data-toggle="modal" data-target="#modal_destinatario">
                                                        Agregar Destinatario
                                                    </button>
                                                </span>
                                            </div>
                                        </div>

                                    </div>                              
                                </div>

                                 <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <textarea id="txt-direc" class="form-control" disabled></textarea>
                                        </div>
                            
                                    </div>                              
                                </div>

                                <div class="form-group">
                                    <label>Contacto</label>
                                    <select class="form-control chosen-select" name="contacto_pedido" id="contacto_pedido">
                                            <option value="">Seleccione</option>
                                        <?php foreach( $listaContactos->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre." ".$a->apellido; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>

                            <div class="row">
                                <?php 
                                //if(!isset($_SESSION['es_cliente']))
                                if($_SESSION['es_cliente'] != 1)
                                {
                                 ?>
                                <div class="col-md-12">
                            <div class="form-group" style="text-align: center;">
                                <div class="checkbox">
                                    <label for="tipo_traslado" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                    <input type="checkbox" name="tipo_traslado" id="tipo_traslado" value="0">Pedido Tipo Traslado</label>
                                </div>
                            </div>
                                </div>
                                <?php 
                                }
                                ?>

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
                                    </div>
                                </div>

                                </div>

                            </div>

                                <div class="form-group" style="display:none;" id="almacen_destino">
                                    <label>Solicitar Productos al Almacén: </label>
                                    <select class="form-control" name="almacen_dest" id="almacen_dest">
                                        <option value="">Seleccione</option>
                                        <?php foreach( $listaAP->getAll('traslado') AS $a ): ?>
                                            <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>

                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Descripción Detallada</label>
                                    <textarea id="observacion" name="observacion" class="form-control" rows="5" style="resize: none; background-color: #fff;"></textarea>
                                </div>
                            </div>
                        </div>
                        <?php 
                        //*********************************************************************************************
                        //*********************************************************************************************
                        //*********************************************************************************************
                        /*
                        ?>
                        <div class="row" style="display: none;">  
                            <div class="ibox-title">
                                <div class="row">
                                    <div class="col-md-4" id="_title">
                                        <h3>Detalles del Pedido</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 b-t">
                                <br>
                                <div class="form-group">
                                    <label>Artículo*</label>
                                    <select name="articuloX" id="articuloX" class="chosen-select form-control">
                                        <option value="">Seleccione Artículo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 b-t">
                                <br>
                                <div class="form-group">
                                    <label>Cantidad de Piezas</label> 
                                    <input id="cantPzas" name="cantPzas" type="number" oninput="parseFloat($(this).val()).toFixed(4)"  type="number" step="0.01" class="form-control">
                                </div>                                
                            </div>
                            <div class="col-md-4 b-t">
                                <br>
                                <div class="form-group">
                                    <label>Caducidad minima (meses)</label> 
                                    <input id="caducidadMin" name="caducidadMin" type="number" class="form-control">
                                </div>
                            </div>
                        </div>
                        <?php 
                        */
                        //*********************************************************************************************
                        //*********************************************************************************************
                        //*********************************************************************************************
                        ?>
                              <div class="row" style="margin: 0px;">
                                <div class="form-group col-md-9" style="padding: 15px; border: 1px solid; border-color: #dedede; border-radius: 15px"><!--#1ab394-->
                                <div class="row">

                                <?php 
                                /*
                                ?>
                                  <div class="form-group col-md-3" id="grupo_marca" >
                                      <label>Grupo | Marca</label>
                                      <select name="gr_marca" id="gr_marca" class="chosen-select form-control">
                                          <option value="">Seleccione Grupo | Marca</option>
                                        <?php foreach( $grupoArticulos->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->cve_gpoart; ?>"><?php echo "(".$a->cve_gpoart.") - ".$a->des_gpoart; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                  </div>

                                  <div class="form-group col-md-3" id="modelo_clasif" >
                                      <label>Modelo | Clasif</label>
                                                <!--country      basic-->
                                      <select name="mod_clasif" id="mod_clasif" class="chosen-select form-control">
                                          <option value="">Seleccione Modelo | Clasif</option>
                                        <?php foreach( $SubGrupoArticulos->getAll() AS $a ): ?>
                                            <option value="<?php echo $a->cve_sgpoart; ?>"><?php echo "(".$a->cve_sgpoart.") - ".$a->des_sgpoart; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                  </div>
                                  <?php 
                                  */
                                  ?>

                                  <div class="form-group col-md-3" id="grupo_marca" style="display: none;">
                                      <label>Órdenes de Trabajo</label>
                                      <select name="ots_con_lp" id="ots_con_lp" class="chosen-select form-control">
                                          <option value="">Seleccione OT</option>
                                        <?php foreach( $ots_con_lp AS $a ): ?>
                                            <option value="<?php echo $a["Folio_Pro"]; ?>"><?php echo $a["Folio_Pro"]; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                  </div>

                                  <div class="form-group col-md-3" id="modelo_clasif" style="display: none;">
                                      <label>License Plate</label>
                                                <!--country      basic-->
                                      <select name="lp_ot" id="lp_ot" class="chosen-select form-control">
                                          <option value="">Seleccione LP</option>
                                      </select>
                                  </div>
                                  <div class="form-group col-md-3" id="modelo_clasif">
                                      <label>License Plate</label>
                                                <!--country      basic-->
                                      <select name="lp_lista" id="lp_lista" class="chosen-select form-control">
                                          <option value="">Seleccione LP</option>
                                        <?php  foreach( $lp_lista AS $a ): ?>
                                            <option value="<?php echo $a["CveLP"]; ?>"><?php echo $a["CveLP"]; ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                  </div>

                                  <div class="form-group col-md-3">
                                      <label>Proyecto</label>
                                                <!--country      basic-->
                                      <select name="pry_lista" id="pry_lista" class="chosen-select form-control">
                                          <option value="">Seleccione Proyecto</option>
                                            <?php foreach( $listaProyectos->getAllProyectosOcupados($_SESSION['id_almacen']) AS $a ): ?>
                                                <option value="<?php echo $a->Cve_Proyecto; ?>"><?php echo "( ".$a->Cve_Proyecto." ) - ".$a->Des_Proyecto; ?></option>
                                            <?php endforeach; ?>
                                      </select>
                                  </div>

                                  <div class="form-group col-md-3" id="Articul">

                                      <label>Artículo* <span> | Existencia: <span></span></span></label>
                                                <!--country      basic-->
                                      <select name="articulo" id="articulo" class="chosen-select form-control" <?php if($ValorSFA == 1) { ?> title="Productos Agregados a una Lista de Precios con Precio Mayor a cero" <?php } ?>>
                                          <option value="">Seleccione Artículo</option>
                                      </select>
                                      <input id="ancho" type="hidden" class="form-control">
                                      <input id="alto" type="hidden" class="form-control">
                                      <input id="fondo" type="hidden" class="form-control">
                                  </div>
                                  <div class="form-group col-md-3">
                                      <label>Lote|Serie (en existencia)</label> 
                                      <input type="hidden" id="tiene_lote"  value="">
                                      <input type="hidden" id="tiene_serie" value="">
                                      <select name="lote_serie" id="lote_serie" class="chosen-select form-control">
                                          <option value="">Seleccione</option>
                                      </select>
                                  </div>
                                  <div class="row">
                                  <div class="form-group col-md-3" style="float: right; padding-right: 30px;">
                                      <label>Lote|Serie Alterno</label> 
                                      <select name="lote_serieAlt" id="lote_serieAlt" class="chosen-select form-control">
                                          <option value="">Seleccione</option>
                                      </select>
                                  </div>
                                  </div>

                                </div>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Precio Unitario</label> 
                                        <input type="hidden" id="PUhidden" value="0"/>
                                        <input id="precioUnitario" dir="rtl" type="text" placeholder="$ 00.00 " class="form-control" readonly>
                                    </div>

                                  <div class="form-group col-md-3">
                                      <label>Cantidad</label> 
                                      <input type="hidden" id="modo_peso" value="1">
                                            <!--CantPiezas-->
                                      <input id="CantPiezas" dir="rtl" type="text" placeholder="Cantidad" class="form-control" maxlength="8" autocomplete="off">
                                      <input id="CantPiezasPeso" dir="rtl" type="text" placeholder="Cantidad Kg" class="form-control" maxlength="8" style="display: none;" autocomplete="off">
                                  </div>
                                  <div class="form-group col-md-3" style="display: none;">
                                        <label>Caducidad minima (meses)</label> 
                                        <input id="caducidadMin" dir="rtl" name="caducidadMin" type="number" class="form-control">
                                  </div>

                                    <div class="form-group col-md-3">
                                      <!--<input type="hidden" id="tiene_UM"  value="">-->
                                        <label>Unidad de Medida</label>
                                        <select class="form-control" name="id_unimed" id="id_unimed" disabled>
                                            <option value="">Seleccione</option>
                                            <?php foreach( $unidad_medida->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->id_umed; ?>"><?php echo '( '.$a->cve_umed.' ) '.$a->des_umed; ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>


                                      <div class="form-group col-md-3">
                                          <label>Peso (Kgs)</label> 
                                          <input type="hidden" id="pesohidden" value="0"/>
                                          <input id="peso" dir="rtl" disabled type="text" placeholder="Peso" class="form-control">
                                      </div>
                                      <input id="status" type="hidden" placeholder="Cantidad" class="form-control">
                                </div>

                                <div class="row">

                                    <div class="form-group col-md-3">
                                        <label>SubTotal</label> 
                                        <input type="hidden" id="importeHidden" value="0"/>
                                        <input id="importeTotal" dir="rtl" disabled type="text" placeholder="$ 00.00 " class="form-control">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label>Descuento $</label> 
                                        <input id="descuentomonto" dir="rtl" type="text" placeholder="Descuento" readonly class="form-control">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label>Descuento %</label> 
                                        <input id="descuento" dir="rtl" type="text" readonly placeholder="Descuento %" class="form-control">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label>IVA</label> 
                                        <input type="hidden" id="iva" value="0">
                                        <input id="iva_show" dir="rtl" disabled type="text" placeholder="0% " class="form-control" value="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9"></div>
                                    <div class="form-group col-md-3">
                                        <label>Importe Total</label> 
                                        <input id="importeArticulo" dir="rtl" disabled type="text" placeholder="$ 00.00 " class="form-control" value="0">
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-3 foto_producto" style="background-color: transparent;
                                                                           height: 250px;
                                                                           background-repeat: no-repeat;
                                                                           background-size: contain;
                                                                           background-position: center;
                                                                           border: 5px solid transparent;
                                                                           border-radius: 10px;"></div>
                              </div>

                    <!--
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-primary" id="btnAgregar">Agregar</button>
                            </div>
                        </div>
                    -->
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="ibox-content">
                                    <div style="width: 100%; text-align: center; margin-bottom: 20px;">
                                        <button type="button" class="btn btn-primary" id="btnAgregar">Agregar</button>
                                        <button type="button" id="exportExcel" class="btn btn-primary">
                                            <i class="fa fa-file-excel-o"></i>
                                            Excel
                                        </button>
                                        <a id="exportPDF" class="btn btn-danger" href="#"><?php //target="_blank" ?>
                                            <i class="fa fa-file-pdf-o"></i>
                                            Imprimir Venta
                                        </a>
                                    </div>

                        <div class="row">
                            <div class="col-md-12">
                        <div class="form-group" style="text-align: center;">
                            <div class="checkbox">
                                <label for="tipo_ptl" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="tipo_ptl" id="tipo_ptl" value="0">Pedido Tipo PTL</label>
                            </div>
                        </div>
                            </div>

                        </div>


                                    </div>
                                    <div class="jqGrid_wrapper">
                                        <table id="grid-table"></table>
                                        <div id="grid-pager"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label> Total Articulos</label> 
                                <input name="totalArt" id="totalArt" disabled type="number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label> Total Piezas</label> 
                                <input name="totalPiez" id="totalPiez" disabled type="number" class="form-control">
                            </div>
                            <div class="form-group col-md-4" style="float: right;">
                                <label>Importe Total Pedido</label> 
                                <input type="hidden" id="importeTotalOrdenHide" value="0">
                                <input type="hidden" id="importeTotalOrdenHideAgr" value="0">
                                <input type="hidden" id="restarHide" value="0">
                                <input id="importeOrden" disabled type="text" placeholder="$ 00.00 " class="form-control" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary permiso_registrar" id="btnRegistrar">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="articulos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <h3 class="modal-title">Cargando artículos del almacén</h3>
                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_destinatario" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar Destinatario</h4>
                </div>
                <div class="modal-body">

                <div class="col-md-6 b-r" style="z-index: 100;">
                    <div class="form-group">
                        <label>Consecutivo</label>
                        <input type="text" id="consecutivo" class="form-control" disabled value="0">
                    </div>
                    <div class="form-group">
                        <label>Cliente</label>
                        <input type="text" id="agregar_cliente" disabled class="form-control">
                        <input type="hidden" id="hidden_agregar_cliente">
                    </div>
                    <div class="form-group">
                        <label>Nombre / Razón Social</label>
                        <input type="text" class="form-control" style="text-transform:uppercase;" id="destinatario_razonsocial">
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" style="text-transform:uppercase;" id="destinatario_direccion">
                    </div>
                    <div class="form-group">
                        <label>Colonia</label>
                        <input type="text" class="form-control" style="text-transform:uppercase;" id="destinatario_colonia">
                    </div>
                    <div class="form-group">
                        <label>CP | CD</label>
                        <?php if(isset($codDane) && !empty($codDane)): ?>
                            <select id="destinatario_dane" class="form-control chosen-select">
                                <option value="">Código</option>
                                <?php foreach( $codDane AS $p ): ?>
                                    <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="destinatario_dane" id="destinatario_dane" class="form-control">
                        <?php endif; ?> 
                    </div>
                    <div class="form-group">
                        <label>Alcaldía | Municipio</label>
                        <input type="text" class="form-control" id="destinatario_estado" readonly>
                    </div>
                    <div class="form-group">
                        <label>Ciudad | Departamento</label>
                        <input type="text" class="form-control" id="destinatario_ciudad" readonly>
                    </div>

                </div>

                <div class="col-md-6" style="z-index: 100;">
                    <div class="form-group">
                        <label>Contacto</label>
                        <input type="text" class="form-control" id="destinatario_contacto">
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="destinatario_telefono">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="txtEmailDest">
                    </div>
                    <div class="form-group">
                        <label>Latitud</label>
                        <input type="number" class="form-control" id="txtLatitudDest">
                    </div>
                    <div class="form-group">
                        <label>Longitud</label>
                        <input type="number" class="form-control" id="txtLongitudDest">
                    </div>

                </div>
                <div style="text-align: right;position: relative;top: 110px;width: 100%; z-index: 0;" class="row">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="guardar_destinatario">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>







<div class="modal fade" id="modal-perfil-usuario" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center  modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Validar perfil de usuario</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" id="password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="verificaCredenciales()">Verificar</button>
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

<!-- Select -->
<script src="/js/plugins/chosen/chosen.jquery.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>

<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>

<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>

<!-- Select -->
<script src="/js/select2.js"></script>

<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/clockpicker/clockpicker.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<script src="/js/utils.js"></script>

<script type="text/javascript">

    var exportDataGrid = new ExportDataGrid();

    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */ 
    var importeOrden_tabla = 0;

    function CargarClientes(almacen_clientes){

        console.log("val_almacen = ", almacen_clientes);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'getClientes',
                almacen: almacen_clientes
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/lista/index.php',
            success: function(data) {
                //console.log("SUCCESS: ", data);
                var combo = data.combo;
                var cliente = document.getElementById("cliente");
                if(data.combo !== ''){
                    cliente.innerHTML = data.combo;
                }

                $(".chosen-select").trigger("chosen:updated");
            },
            error: function(res){
                console.log("ERROR getClientes: ", res);
            }
        });

    }

    function Fecha_Actual()
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'fecha_actual'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/lista/index.php',
            success: function(data) 
            {
                 $("#fechaSolicitud").val(data.fecha_actual);
            },
            error: function(res){
                console.log(res);
            }
        });
    }

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
                    document.getElementById('almacen').value = data.codigo.id;
                    setTimeout(function() {
                        $('#almacen').trigger('change');
                    }, 1000);
                    var almacen_clientes = $("#almacen").val();

                    //CargarClientes(almacen_clientes);
                }
            },
            error: function(res){
                window.console.log(res);
            }
        });


    }
    almacenPrede();
    Fecha_Actual();

    $('#almacen').change(function(e){
        $("#almacen_dest").val("");
        $("#almacen_dest option").show();
        $("#almacen_dest option[value=" + $(this).val() + "]").hide();
        //$("#almacen_dest").val($("#almacen_dest option:first").val());
        //$("#almacen_dest").val($("#almacen_dest option:last").val());
        //$("#almacen_dest").val("");
        $("#almacen_dest").trigger("change");
        $("#almacen_dest").trigger("chosen:updated");
    });

    $("#tipo_venta, #tipo_preventa").change(function(e){

        if($("input[name=tipo_venta]:checked").val() == 'venta')
            $("#select_forma_pago").show();
        else
            $("#select_forma_pago").hide();

    });

    /////////////////////////////Aqui se contruye el Grid////////////////////////////////////////

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
        });

        $(grid_selector).jqGrid({
            datatype: "local",
            height: 'auto',
            width: 'auto',
			shrinkToFit: false,
            mtype: 'POST',
            colNames:[
                'License Plate (LP)',
                'Clave Cliente',
                'Clave Prioridad',
                'Clave', 
                'Articulo', 
                'Descripción Detallada',
                'Lote|Serie',
                'Cantidad', 
                'id_unimed',
                'Unidad de Medida',

                'Peso (Kgs)',
                'Precio Unitario',
                'Descuento $',
                'SubTotal',
                'IVA',
                'Total',
                'Caducidad Minima (meses)', 

                'Fecha Registro',
                'Cliente',
                'Destinatario',
                'Fecha Compromiso Entrega',
                'Proyecto',
                'Prioridad',
                'Acciones'
            ],
            colModel:[
                {name:'lp',index:'lp',width:150, editable:false, align: 'left',sortable:false},
                {name:'c_cliente',index:'c_cliente', hidden: true},
                {name:'c_prioridad',index:'c_prioridad', hidden: true},
                {name:'clave',index:'clave',width:100, editable:false, align: 'center',sortable:false},
                {name:'articulo',index:'articulo',width:250, align: 'left', editable:false, sortable:false},
                {name:'des_detallada',index:'des_detallada',width:250, align: 'left', editable:false, sortable:false, hidden: true},
                {name:'lote_serie',index:'lote_serie',width:100, editable:false, align: 'left',sortable:false},
                {name:'cantPiezas',index:'cantPiezas',width:135, align: 'right', editable:false, sortable:false},
                {name:'id_unimed',index:'id_unimed',width:135, align: 'left', editable:false, sortable:false, hidden: true},
                {name:'unidad_text',index:'unidad_text',width:135, align: 'left', editable:false, sortable:false},

                {name:'peso',index:'peso',width:120, align: 'right', editable:false, sortable:false},
                {name:'precio_unitario',index:'precio_unitario',width:150, align: 'right', editable:false, sortable:false},
                {name:'descuento',index:'descuento',width:150, align: 'right', editable:false, sortable:false},
                {name:'importe_total',index:'importe_total',width:150, align: 'right', editable:false, sortable:false},
                {name:'iva',index:'iva',width:150, align: 'right', editable:false, sortable:false},
                {name:'total',index:'total',width:150, align: 'right', editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:175, align: 'center', editable:false, sortable:false},

                {name:'fecha_registro',index:'fecha_registro',width:120, align: 'center', editable:false, sortable:false},
                {name:'cliente',index:'cliente',width:175, align: 'left', editable:false, sortable:false},
                {name:'destinatario',index:'destinatario',width:175, align: 'left', editable:false, sortable:false},
                {name:'fecha_compromiso',index:'fecha_compromiso',width:175, align: 'center', editable:false, sortable:false},
                {name:'proyecto',index:'proyecto',width:175, align: 'left', editable:false, sortable:false},
                {name:'prioridad',index:'prioridad',width:175, align: 'left', editable:false, sortable:false},
                {name:'myac',index:'', width:80, fixed:true, align: 'center', sortable:false, resize:false, formatter:imageFormat2}
            ],
            rowNum:30,
            rowList:[30,40,50],
            pager: pager_selector,
            sortname: 'clave',
            pgbuttons: false,
            viewrecords: true,
            userDataOnFooter: true,
            footerrow: true,
            loadonce: true,
            sortorder: "desc",
            gridComplete: function(){
            }
        });

        $(window).triggerHandler('resize.jqGrid');
        function imageFormat2( cellvalue, options, rowObject ){
            var correl = options.rowId;
            var html = '<a href="#" onclick="borrarAdd(\''+correl+'\')"><i class="fa fa-eraser" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }
        
    });


    function borrarAdd(_codigo) {
        swal({
            title: "¿Está seguro que desea borrar el artículo?",
            text: "Está a punto de borrar un artículo recibido y esta acción no se puede deshacer",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false
            },
            function(){

                var rowData = $('#grid-table').jqGrid ('getRowData', _codigo);

                $("#totalPiez").val((parseFloat($("#totalPiez").val())-parseFloat(rowData.cantPiezas)));
                $("#totalArt").val((parseFloat($("#totalArt").val()))-1);
                $("#importeOrden").val( parseFloat($("#importeOrden").val() )-( parseFloat(rowData.importe_total) + parseFloat(rowData.iva) ));


                $("#grid-table").jqGrid('delRowData', _codigo);
                swal("Borrado", "El articulo ha sido borrado exitosamente", "success");
                   
        });
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    
     function VerificarPedido()
     {
//*************************************************************************************
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                Fol_folio: $("#folio").val(),
                action : "VerificarFolio"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
             url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                console.log("data pedido VerificarFolio = ", data);
                if (data.existe > 0) {
                    swal({
                        title: "Folio Existente",
                        text: "El Folio "+$("#folio").val()+" Ya existe, se procederá a cambiar a un Folio Disponible",
                        type: "warning",
                        showCancelButton: false,
                        confirmButtonColor: "#1c84c6",
                        confirmButtonText: "OK",
                        cancelButtonText: "Cancelar",
                        closeOnConfirm: false
                        },
                        function(){

                            //setTimeout(function(){
                            //if($("#tipo_traslado").is(':checked'))
                            //    folio_consecutivo_traslado();
                            //else
                            //    folio_consecutivo();
                            //}, 1000);
                            registrarPedido();
                            //setTimeout(function(){
                            //swal("Éxito", "Se ha cambiado el pedido con el Folio Disponible: "+$("#folio").val(), "success");
                            //}, 1000);
                    });

                    //swal("Folio Existente", "El Folio Ya existe, se procederá a cambiar a un Folio Disponible", "warning");
                }
                else
                {
                    registrarPedido();
                }
            }, error: function(data) {
                console.log("error pedido = ", data);
            }
        });
//*************************************************************************************
     }

function onTimeChange(time_hour) {
  var timeSplit = time_hour.split(':'),
    hours,
    minutes,
    meridian;
  hours = timeSplit[0];
  minutes = timeSplit[1];
  if (hours > 12) {
    meridian = 'PM';
    hours -= 12;
  } else if (hours < 12) {
    meridian = 'AM';
    if (hours == 0) {
      hours = 12;
    }
  } else {
    meridian = 'PM';
  }
  return (hours + ':' + minutes + ' ' + meridian);
}

    function registrarPedido(){
   
        var arrDetalle = [];

        var ids = $("#grid-table").jqGrid('getDataIDs');

        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-table').jqGrid ('getRowData', rowId);

            arrDetalle.push({
                CveLP: rowData.lp,
                Cve_articulo: rowData.clave,
                des_articulo: rowData.articulo,
                des_detallada: rowData.des_detallada,
                cve_lote: rowData.lote_serie,
                Num_cantidad: rowData.cantPiezas,
                id_unimed: rowData.id_unimed,
                Num_Meses: rowData.caducidad,
                peso: rowData.peso,
                precio_unitario: rowData.precio_unitario,
                desc_importe: rowData.descuento,
                sub_total: rowData.importe_total,
                proyecto: rowData.proyecto,
                iva: rowData.iva
            });
        }

        console.log("arrDetalle = ", arrDetalle);
        if($.isEmptyObject(arrDetalle) && $("#ots_con_lp").val() == '') 
        {
            swal("Error", "El Pedido no posee productos", "error");
            return;
        }

        var tipo_traslado = 0;
        if($("#tipo_traslado").is(':checked'))
            tipo_traslado = 1;

        var tipo_ptl = 0;
        if($("#tipo_ptl").is(':checked'))
            tipo_ptl = 1;



        console.log("tipo_traslado = ", tipo_traslado);
        console.log("tipo_traslado_int_ext = ", $("input[name=traslado_interno_externo]:checked").val());
        console.log("tipo_venta = ", $("input[name=tipo_venta]:checked").val());
        console.log("rutas_ventas = ", $("#rutas_ventas").val());
        console.log("desc_cliente = ", $("#desc_cliente").val());
        console.log("pedido_tipo_LP = ", $("#pedido_tipo_LP").val());
        console.log("pedido_tipo_proyecto = ", $("#pedido_tipo_proyecto").val());

        if($("#tipo_traslado").is(':checked') && $("#almacen_dest").val() == '')
        {
            swal("Error", "Debe Seleccionar un Almacén Destino para el traslado", "error");
            return;
        }


        if($("#num_oc_obligatorio").val() == "1" && $("#nroOC").val() == '')
        {
            swal("Error", "Por favor ingrese Número OC Cliente", "error");
            return;
        }

        if($("#almacen").val() == $("#almacen_dest").val())
        {
            swal("Error", "El almacén origen no puede ser el mismo almacén destino", "error");
            return;
        }
/*
        if($("input[name=tipo_venta]:checked").val() == 'venta' && ($("#rutas_ventas").val() == '' || $("#vendedor").val() == '' || $("#desc_cliente").val() == '' || $("#desc_cliente").val() == null))
        {
            swal("Error", "Se necesita Ruta, cliente y Vendedor para poder registrar un pedido tipo venta", "error");
            return;
        }
*/
        // && ($("#desc_cliente").val() == '' || $("#desc_cliente").val() == null || $("#vendedor").val() == '')
/*
        if($("input[name=tipo_venta]:checked").val() == 'preventa' && $("#rutas_ventas").val() != '' && $("#vendedor").val() == '')
        {
            //if( $("#vendedor").val() == '')
                swal("Error", "Por favor seleccione un Vendedor", "error");
            //else
            //    swal("Error", "Por favor seleccione un cliente", "error");
            return;
        }
*/

        if($("input[name=tipo_venta]:checked").val() == 'venta' && $("#rutas_ventas").val() != '' && ($("#desc_cliente").val() == '' || $("#desc_cliente").val() == null || $("#vendedor").val() == ''))
        {
            if( $("#vendedor").val() == '')
            {
                swal("Error", "Por favor seleccione un Vendedor", "error");
                return;
            }
            //else
            //    swal("Error", "El Tipo de Pedido debe ser Venta", "error");
            //return;
        }

        if($("input[name=tipo_venta]:checked").val() == 'venta' && $("#forma_pago").val() == '')
        {
            swal("Error", "Indique la Forma de Pago", "error");
            return;
        }

        if($("#prioridad").val() == "")
        {
            swal("Error", "Seleccione una Prioridad", "error");
            return;
        }

        console.log("OK");
        console.log("hora_desde: ", onTimeChange($("#hora_desde").val()));
        console.log("hora_hasta: ", onTimeChange($("#hora_hasta").val()));
        //return;

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                Fol_folio: $("#folio").val(),
                Fec_Pedido: moment().format("YYYY-MM-DD HH:mm:ss"),
                Fec_Entrada: moment().format("YYYY-MM-DD HH:mm:ss"),
                Fec_Entrega: moment($("#fechaSolicitud").val(), 'DD-MM-YYYY').format('YYYY-MM-DD HH:mm:ss'),
                Cve_clte: $("#desc_cliente").val(),//$("#cliente").val(),
                rutas_ventas: $("#rutas_ventas").val(),
                tipo_traslado: tipo_traslado,
                tipo_ptl: tipo_ptl,
                tipo_traslado_int_ext: $("input[name=traslado_interno_externo]:checked").val(),
                status: "A",
                ots_con_lp: $("#ots_con_lp").val(),
                //lp_ot: $("#lp_ot").val(),
                tipo_lp: $("#pedido_tipo_LP").val(),
                proyecto: $("#pry_lista").val(),
                cve_Vendedor: $("#vendedor").val(),
                Observaciones: $("#observacion").val(),
                ID_Tipoprioridad: $("#prioridad").val(),
                cve_almac: $("#almacen").val(),
                almacen_dest: $("#almacen_dest").val(),
                contacto_pedido: $("#contacto_pedido").val(),
                destinatario: $("#destinatario").val(),
                Pick_Num: $("#nroOC").val(),
                hora_desde: ($("#hora_desde").val() != '')?(onTimeChange($("#hora_desde").val())):"",
                hora_hasta: ($("#hora_hasta").val() != '')?(onTimeChange($("#hora_hasta").val())):"",
                arrDetalle: arrDetalle,
                tipo_venta: $("input[name=tipo_venta]:checked").val(),
                tipo_negociacion: $("input[name=tipo_negociacion]:checked").val(),
                forma_pago: $("#forma_pago").val(),
                Cve_Usuario: <?php echo $usuario; ?>,
                action : "add"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
             url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                console.log("data pedido = ", data);
                if (data.success == true) {
                    //swal(
                    //  '¡Exito!',
                    //  'El pedido '+data.folio+' ha sido registrado correctamente',
                    //  'success'
                    //);
                    swal({
                        title: '¡Exito!',
                        text: 'El pedido '+data.folio+' ha sido registrado correctamente',
                        type: 'success',
                        showCancelButton: false,
                        confirmButtonColor: "#1c84c6",
                        confirmButtonText: "OK",
                        cancelButtonText: "Cancelar",
                        closeOnConfirm: true
                        },
                        function(){
                            window.location.reload();
                            resetForm();
                    });


                } else {
                    swal("Error", "Ocurrió un error al guardar el pedido", "error");
                }
            }, error: function(data) {
                console.log("error pedido = ", data);
            }
        });
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    function validarUsuarioAdminstrador()
    {
      var cliente=$('#cliente').val();
      var destinatario= $('#txt-direc').val();
      var folio=$('#folio').val();
      if(folio == ''){
        console.log("Sin folio");
        swal("Error", "Coloque un folio", "error"); 
      }/*else if((cliente == 'Seleccione' || destinatario == 'Seleccione' || destinatario == '') && $("#rutas_ventas").val() == ''){
        console.log("Sin destinatario");
        swal("Error", "Coloque un destinatario", "error"); 
      }*/
      else{
         //$('#modal-perfil-usuario').modal('show');
          console.log("_cliente = ", cliente);
          console.log("_destinatario = ", destinatario);
          console.log("_folio = ", folio);
         //registrarPedido();
         VerificarPedido();
      }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    function verificaCredenciales(){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                password: $("#password").val(),
                action : 'validarCredenciales'
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                if (data.status == 200) {
                    $('#modal-perfil-usuario').modal('hide');
                    //registrarPedido();
                    VerificarPedido();
                } else {
                    swal("Error", "Ocurrio un error al validar las credenciales de administrador", "error");
                }
            }
        });
    }



    function validarFolio(){
        var folio = $("#folio").val();
        
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                Fol_folio : folio,
                action : "exists"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/nuevospedidos/update/index.php',
            success: function(data) {
                if (data.success) {
                    swal("Error", "El folio que intentas registrar ya existe", "error");
                    $("#btnRegistrar").prop('disabled', true);
                }else{
                    $("#btnRegistrar").prop('disabled', false);
                }
            }
            
        });
    }

    $('#data_1 .input-group.date').datetimepicker({
       locale: 'es',
       format: 'DD-MM-YYYY',
       useCurrent: false
    });
    
</script>

<script>
    $(document).ready(function(){


    setTimeout(function(){
        if($("#cve_cliente").val() != "")
        {
            console.log("OK CLIENTE");
           $("#almacen, #desc_cliente").trigger("change");
        }
    }, 2000);

        localStorage.setItem("consecutivo", 0);
        $('.icheck').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({ allow_single_deselect: true });

        $("#destinatario_dane").change(function() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    codigo : $("#destinatario_dane").val(),
                    action : "getDane"
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: "/api/clientes/update/index.php",
                success: function(data) {
                    if (data.success == true) {
                        $("#destinatario_ciudad").val(data.departamento);
                        $("#destinatario_estado").val(data.municipio);
                    }
                }
            });
        });

            function isEmail(email) {
              var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
              return regex.test(email);
            }

        $("#guardar_destinatario").on("click", function(){
            var cliente = $("#hidden_agregar_cliente").val(),
                razon = $("#destinatario_razonsocial").val(),
                direccion = $("#destinatario_direccion").val(),            
                colonia = $("#destinatario_colonia").val(),
                postal = $("#destinatario_dane").val(),            
                ciudad = $("#destinatario_ciudad").val(),            
                estado = $("#destinatario_estado").val(),            
                contacto = $("#destinatario_contacto").val(),            
                telefono = $("#destinatario_telefono").val();

            if(!isEmail($("#txtEmailDest").val()) && $("#txtEmailDest").val()){
                    swal('Error', 'Por favor escriba el email correctamente', 'error');
            }else if(razon === ''){
                swal("Error", "Ingrese Razón Social del destinatario", "error");
            }else if(direccion === ''){
                swal("Error", "Ingrese Dirección del destinatario", "error");
            }/*else if(postal === ''){
                swal("Error", "Ingrese Código Postal del destinatario", "error");
            }else if(contacto === ''){
                swal("Error", "Ingrese Contaccto del destinatario", "error");
            }else if(telefono === ''){
                swal("Error", "Ingrese Teléfono del destinatario", "error");
            }*/else{
                $.ajax({
                    url: '/api/nuevospedidos/update/index.php',
                    dataType: 'json',
                    method: 'POST',
                    data: {
                        action: 'agregarDestinatario',
                        cliente: cliente,
                        razon: razon,
                        direccion: direccion,
                        colonia: colonia,
                        postal: postal, 
                        ciudad: ciudad,
                        estado: estado,
                        contacto: contacto,
                        email_destinatario: $("#txtEmailDest").val(),
                        txtLatitudDest: $("#txtLatitudDest").val(),
                        txtLongitudDest: $("#txtLongitudDest").val(),
                        telefono: telefono
                    }
                }).done(function(data){
                    if(data.success){
                        swal("Éxito", "Dirección añadida exitosamente", "success");
                        $("#modal_destinatario").modal('hide');
                        BuscarDestinatario(cliente);
                        //$("#cliente").trigger("change");
                    }else{
                        swal("Error", "Ocurrió un error al guardar la dirección intenta de nuevo", "error");
                    }
                });
            }
        });

        $("#agregar_destinatario").on("click", function(){
            $.ajax({
                url: '/api/clientes/lista/index.php',
                data: {
                    action: 'obtenerClaveDestinatario',
                },
                dataType: 'json',
                type: 'GET'
            }).done(function(data){
                $("#consecutivo").val(data.clave);
                var cliente_clave = $("#cliente").val(),
                    cliente = $("#cliente").val(); //$(`#cliente option[value='${cliente_clave}']`).html();
                $("#destinatario_razonsocial").val('');
                $("#destinatario_direccion").val('');
                $("#destinatario_colonia").val('');
                $("#destinatario_ciudad").val('');
                $("#destinatario_estado").val('');
                $("#destinatario_telefono").val('');
                $("#destinatario_dane").val('');
                $(".chosen-select").trigger('chosen:updated');
                $("#agregar_cliente").val(cliente);
                $("#hidden_agregar_cliente").val(cliente_clave);
            });
        });

    function BuscarExistencia(lp, cve_articulo, lote_serie, almacen)
    {
        console.log("BuscarExistencia/LP = ", lp);
        console.log("BuscarExistencia/cve_articulo = ", cve_articulo);
        console.log("BuscarExistencia/lote_serie = ", lote_serie);
        console.log("BuscarExistencia/almacen = ", almacen);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                LP: lp,
                cve_articulo : cve_articulo,
                lote_serie:  lote_serie,
                id_almacen: almacen,
                action : "buscar_existencias"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/nuevospedidos/lista/index.php',
            success: function(data) {
                console.log("SUCCESS Existencia: ", data);
                $("#Articul span span").text(data.existencia);

            }, error: function(data){
                console.log("ERROR 1: ", data);
            }
        });
    }

    function Activar_BloquearSelectsPry(activar)
    {

        if(activar == 'bloquear')
            $("#lp_lista").prop("disabled", true);//, #articulo, #lote_serie, #CantPiezasPeso, #CantPiezas
        else
            $("#lp_lista").prop("disabled", false);//, #articulo, #lote_serie, #CantPiezas, #CantPiezasPeso

            //************************************************************************************************************************************
            //ESTO SE DESHABILITO DEFINITIVAMENTE PORQUE ESO DIJERON EL 26-04-2024 (BUSCAR CONVERSACIONES WHATSAPP)
            //************************************************************************************************************************************
            //, #descuento, #descuentomonto, #precioUnitario
            //************************************************************************************************************************************

        $('.chosen-select').trigger('chosen:updated');
    }

    $('#pry_lista').change(function(e) 
    {
        if($(this).val() != "") Activar_BloquearSelectsPry('bloquear');
        else Activar_BloquearSelectsPry('activar');
    });

    function Activar_BloquearSelects(activar)
    {
        //if(activar == 'bloquear')
        //    $("#articulo, #lote_serie, #precioUnitario, #CantPiezasPeso, #descuento").prop("disabled", true);//#CantPiezas,
        //else
            $("#articulo, #lote_serie, #CantPiezas, #CantPiezasPeso").prop("disabled", false);
            //************************************************************************************************************************************
            //ESTO SE DESHABILITO DEFINITIVAMENTE PORQUE ESO DIJERON EL 26-04-2024 (BUSCAR CONVERSACIONES WHATSAPP)
            //************************************************************************************************************************************
            //, #descuento, #descuentomonto, #precioUnitario
            //************************************************************************************************************************************

        $('.chosen-select').trigger('chosen:updated');
    }

    $('#lp_lista').change(function(e) 
    {
        if($(this).val() != "") Activar_BloquearSelects('bloquear');
        else Activar_BloquearSelects('activar');
    });

    $('#almacen_dest').change(function(e) 
    {
        if($("#tipo_traslado").is(':checked'))
        {
            fillSelectArti("S", "", "");
            fillSelectLP();
            //BuscarExistencia($("#articulo").val(), $("#lote_serie").val(), $("#almacen_dest").val());
        }

        Activar_BloquearSelects("activar");
        Activar_BloquearSelectsPry('activar');
    });

    $('#lp_lista, #articulo, #lote_serie').change(function(e) 
    {
        if($("#tipo_traslado").is(':checked'))
        {
            //fillSelectArti("S", "", "");
            BuscarExistencia($("#lp_lista").val(), $("#articulo").val(), $("#lote_serie").val(), $("#almacen_dest").val());
        }
        else
            BuscarExistencia($("#lp_lista").val(), $("#articulo").val(), $("#lote_serie").val(), $("#almacen").val());
    });

    $('#articulo').change(function(e) {
        var cve_articulo= $(this).val(),
            option = $(this).find('option:selected'),
            id = option.data('id');

            if(importeOrden.value>0 && $("#restarHide").val() == "1")
                importeOrden.value -= parseFloat($("#importeTotalOrdenHide").val());

            $("#restarHide").val("1");

            //console.log("SUCCESS articulo = ", e);
            console.log("id articulo = ", id);
            console.log("clave articulo = ", cve_articulo);
            console.log("id_almacen = ", <?php echo $_SESSION['id_almacen']; ?>);


        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_articulo : id,
                verificar_lista: 1,
                action : "load"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                    },
            url: '/api/articulos/update/index.php',
            success: function(data) {
                console.log("articulo = ", data);
                //console.log("articulo foto = ", data.fotos[0].url);
                var imagen = "noimage.jpg";
                if(data.fotos.length != 0)
                    imagen = data.fotos[0].url;
                $(".foto_producto").css("background-image", "url(../../../../img/articulo/"+imagen+")");
                //$("#observacion").val(data.des_detallada);
                //console.log("articulo costo = ", data.costo);
                
                //console.log("articulo 1 = ", $("#articulo").val());
                //console.log("articulo 2 = ", data.cve_articulo);

                console.log("articulo L = ", data.control_lotes);
                console.log("articulo S = ", data.control_numero_series);

                $('#pesohidden').val(data.peso);
                $('#peso').val(data.peso);
                $('#PUhidden').val(data.peso);
                $('#iva').val(data.mav_pctiva);
                $('#iva_show').val("0");
                //$('#iva_show').val(data.mav_pctiva); 797101

                if(data.control_numero_series == 'S')
                    $("#CantPiezas, #CantPiezasPeso").val(1);

                if(data.unidadMedida == '' || data.unidadMedida == 'null' || data.unidadMedida == null || data.unidadMedida == 0)
                {
                    swal("El Artículo No tiene Unidad de Medida", "Este artículo no tiene Unidad de Medida Registrada en el catálogo, debe ir al catálogo de producto y registrar una unidad de medida para poder realizar el pedido", "error");
                    $('#id_unimed').val("");
                }
                else
                    $('#id_unimed').val(data.unidadMedida);

                $('#tiene_lote').val(data.control_lotes);
                $('#tiene_serie').val(data.control_numero_series);

                $('#iva_show').val(0);
                if($("#articulo").val()==data.cve_articulo)
                {
                  $('#precioUnitario').val(data.costo);
                  $('#iva').val(data.mav_pctiva);
                  if(data.mav_pctiva != '')
                  {
                        if(parseFloat(data.mav_pctiva) > 0)
                        $('#iva_show').val((parseFloat(data.costo)-(parseFloat(data.costo) * (100/(100+parseFloat(data.mav_pctiva))))).toFixed(4));
                  }
                  else
                    data.mav_pctiva = 0;
                  $('#importeTotal').val(parseFloat(data.costo) - parseFloat($("#iva_show").val()));
                }
                //$('#importeTotal').val(parseFloat(data.costo) + parseFloat(data.mav_pctiva) + parseFloat($("#importeHidden").val()));
                //$('#importeHidden').val(parseFloat($("#importeTotal").val()));
                
/*
                if($('#precioUnitario').val()==data.costo)
                {
                    $('#importeTotal').val(data.costo);
                }
                else if($('#precioUnitario').val()!=data.costo)
                {
                    $('#importeTotal').val(importeTotal);
                }

*/
/*
                var agr_iva = 0;
                if(parseFloat(data.mav_pctiva) > 0 && parseFloat(data.mav_pctiva) != '')
                {
                    agr_iva = parseFloat($('#precioUnitario').val()) - (parseFloat($('#precioUnitario').val()) / (1+(parseFloat(data.mav_pctiva)/100)));
                    agr_iva = agr_iva.toFixed(4);

                    var importeTOT = parseFloat($('#precioUnitario').val()) -  parseFloat(agr_iva);
                    importeTOT = importeTOT.toFixed(4);
                    $('#importeTotal').val(importeTOT);
                    $('#iva_show').val(agr_iva);
                }

                if(parseFloat(data.mav_pctiva) > 0 && parseFloat(data.mav_pctiva) != '')
                {
                    agr_iva = parseFloat($('#precioUnitario').val()) - (parseFloat($('#precioUnitario').val()) / (1+(parseFloat(data.mav_pctiva)/100)));
                    agr_iva = agr_iva.toFixed(4);

                    var importeTOT = parseFloat($('#precioUnitario').val()) -  parseFloat(agr_iva);
                    importeTOT = importeTOT.toFixed(4);
                    $('#importeTotal').val(importeTOT);
                    $('#iva_show').val(agr_iva);
                }
*/
                $("#importeTotalOrdenHide").val(parseFloat($('#importeTotal').val()));

                //if($('#importeTotal').val() == "")
                   //$('#importeTotal').val(0);

               //if(agr_iva == "") 
                //agr_iva = 0;

                //importeOrden.value = parseFloat(importeOrden.value) + parseFloat($('#importeTotal').val()) +  parseFloat(agr_iva);
                //importeOrden.value = 0;


                //$("#CantPiezas").val("1");
                //$("#CantPiezasPeso").val("1");
                $("#CantPiezas").val("");
                $("#CantPiezasPeso").val("");


                //console.log("control_peso = ", data.control_peso);
                if(data.control_peso == 1)
                {
                  $("#CantPiezas").hide();
                  $("#CantPiezasPeso").show();
                }
                else
                {
                  $("#CantPiezas").show();
                  $("#CantPiezasPeso").hide();
                }
                $("#modo_peso").val(data.control_peso);

                $("#articulo").val(data.cve_articulo);
                $("#alto").val(data.alto);
                $("#ancho").val(data.ancho);
                $("#fondo").val(data.fondo);

                console.log("#tiene_lote", $("#tiene_lote").val());
                console.log("#tiene_serie", $("#tiene_serie").val());
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        LP: $("#lp_lista").val(),
                        cve_articulo : cve_articulo,
                        tiene_lote:  $("#tiene_lote").val(),
                        tiene_serie: $("#tiene_serie").val(),
                        id_almacen: ($("#tipo_traslado").is(':checked'))?($("#almacen_dest").val()):($("#almacen").val()),
                        action : "load_lotes_series"
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                            },
                    url: '/api/nuevospedidos/lista/index.php',
                    success: function(data) {
                        console.log("SUCCESS LOTES: ", data);
                        $("#lote_serie, #lote_serieAlt").empty();
                        $("#lote_serie").append(data.res);
                        $("#lote_serieAlt").append(data.resAlt);
                        $('.chosen-select').trigger('chosen:updated');

                        if($("#tipo_traslado").is(':checked'))
                        {
                            BuscarExistencia($("#lp_lista").val(), $("#articulo").val(), $("#lote_serie").val(), $("#almacen_dest").val());
                        }

                    }, error: function(data){
                        console.log("ERROR 1: ", data);
                    }
                });

                if($("#sfa_activo").val() == 1)
                {
//********************************************************************************************
                console.log("************** buscar_lista_relacionada ******************");
                console.log("cve_articulo :",  cve_articulo);
                console.log("id_destinatario: ", $("#destinatario").val());
                console.log("rutas_ventas: ", $("#rutas_ventas").val());

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_articulo : cve_articulo,
                        id_destinatario: $("#destinatario").val(),
                        ruta: $("#rutas_ventas").val(),
                        action : "buscar_lista_relacionada"
                    },
                    beforeSend: function(x) {if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
                    url: '/api/nuevospedidos/lista/index.php',
                    success: function(data) {
                        console.log("SUCCESS: ", data);

                        var desc_importe = 0;
                        if(data.existe_lista_descuentos != '0')
                        {
                            var descuento = (data.Maximo == 0)?data.FactorMax:data.FactorMaximo;
                            desc_importe = descuento/100;
                            $("#descuento").val(descuento);
                            if(data.TipoListaDescuentos == '1') 
                            {
                                $("#descuento, #descuentomonto, #precioUnitario").prop("readonly", true);
                            }
                            else
                            {   
                                if(data.Minimo == 0)
                                {
                                    $("#lista_d_descmin").val(data.Factor);
                                    $("#lista_d_descmax").val(data.FactorMax);
                                }
                                else
                                {
                                    $("#lista_d_descmin").val(data.Minimo);
                                    $("#lista_d_descmax").val(data.Maximo);
                                }
                            }
                        }
                        else
                        {
                            $("#descuento, #descuentomonto, #precioUnitario").val("");
                            //$("#descuento, #descuentomonto").prop("readonly", false);
                            $("#lista_d_descmin").val("");
                            $("#lista_d_descmax").val("");
                        }

                        if(data.existe_lista_precios != '0')
                        {
                            //(data.PrecioMin);
                            if(data.TipoListaPrecios == 1) 
                            {
                                $("#precioUnitario").prop("readonly", true);
                            }
                            else
                            {
                                $("#lista_p_preciomin").val(data.PrecioMinCiva);
                                $("#lista_p_preciomax").val(data.PrecioMaxCiva);
                            }

                            $("#precioUnitario").val(data.PrecioMaxCiva);
                            $('#iva_show').val(((data.PrecioMaxCiva - data.PrecioMaxSiva)-((data.PrecioMaxCiva - data.PrecioMaxSiva)*desc_importe)).toFixed(3));
                            $("#importeTotal").val(parseFloat(data.PrecioMaxSiva)-(parseFloat(data.PrecioMaxSiva)*desc_importe));
                            $("#importeArticulo").val((parseFloat($("#importeTotal").val()) + parseFloat($('#iva_show').val())).toFixed(3));
                $("#importeOrden").val( (!isNaN(parseFloat($("#importeArticulo").val())))?parseFloat($("#importeArticulo").val()):0 );
                            //var importe_articulo = $("#importeArticulo").val();
                            //$("#importeArticulo").val(parseFloat(importe_articulo)-(parseFloat(importe_articulo)*desc_importe));

                            var descuentoMonto = 0;
                            if($("#descuento").val() > 0)
                                descuentoMonto = parseFloat(data.PrecioMaxCiva) - parseFloat($("#importeTotal").val());
                            $("#descuentomonto").val(descuentoMonto.toFixed(3));
                        }
                        else
                        {
                            $("#precioUnitario").val("");
                            //$("#precioUnitario").prop("readonly", false);
                            $("#lista_p_preciomin").val("");
                            $("#lista_p_preciomax").val("");
                        }

                        if((data.PrecioMaxCiva == 0 || data.PrecioMaxCiva == 'null' || data.PrecioMaxCiva == '' || !data.PrecioMaxCiva || data.PrecioMaxSiva == 0 || data.PrecioMaxSiva == 'null' || data.PrecioMaxSiva == '' || !data.PrecioMaxSiva) && $("#sfa_activo").val() == 1 && !$("#tipo_traslado").is(':checked'))
                            {
                                if(data.existe_lista_precios != '0')
                                {
                                    if($("#rutas_ventas").val() != "" && $("#desc_cliente").val() != "")
                                    {
                                        swal("Producto Sin Precio", "No se puede vender un artículo sin precio", "error");
                                        $("#articulo").val("");
                                        $('.chosen-select').trigger('chosen:updated');
                                    }
                                    else if($("#desc_cliente").val() != "" && !$("#tipo_traslado").is(':checked'))
                                    {
                                        swal("Lista de Precios No configurada para este cliente", "Este artículo no pertenece a ninguna lista de precios relacionada con este cliente", "error");
                                        $("#articulo").val("");
                                        $('.chosen-select').trigger('chosen:updated');
                                    }
                                }
                                else
                                    $("#descuento, #descuentomonto, #precioUnitario").prop("readonly", false);
                            }
                    }, error: function(data){
                        console.log("ERROR 2: ", data);
                    }
                });
//********************************************************************************************
                }
                else
                    $("#descuento, #descuentomonto, #precioUnitario").prop("readonly", false);

            }, error: function(data){
                console.log("ERROR CHANGE: ", data);
            }
        });
    });



    $("#tipo_traslado").click(function()
    {
        if($("#tipo_traslado").is(':checked'))
        {
            $(".tipo_traslado_int_ext").show();
            console.log("folio_tr = ",$("#folio_tr").val());
            $("#folio").val($("#folio_tr").val());
            $("#almacen_destino").show();
            fillSelectArti("S", "", "");
            fillSelectLP();
            //$("#Articul span").show();
            //BuscarCliente("");
            //BuscarCliente("Borrar_La_Lista_de_Clientes");
        }
        else
        {
            $(".tipo_traslado_int_ext").hide();
            $("#folio").val($("#folio_fol").val());
            $("#almacen_destino").hide();
            $("#tipo_externo").prop("checked", true);
            fillSelectArti("N", "", "");
            fillSelectLP();
            //$("#Articul span").hide();
        }
            Activar_BloquearSelects("activar");
            Activar_BloquearSelectsPry('activar');
            $("#cliente_buscar").val("");
            $("#desc_cliente").empty();
    });

        function BuscarCliente(cliente)
        {
            console.log("Cliente = ", cliente);
            var tipo_cliente_traslado = 0;
            if($("#tipo_traslado").is(':checked'))
                tipo_cliente_traslado = 1;

            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getClientesSelect',
                    tipo_cliente_traslado: tipo_cliente_traslado,
                    cliente: cliente,
                    id_almacen: $("#almacen").val()
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS Cliente: ", data);
                    //var combo = data.combo;
                    //if(combo.length > 36 || data.direccion.length > 2){
                        var desc_cliente = document.getElementById("desc_cliente");
                        if(data.combo !== '' && data.find == true){
                            desc_cliente.innerHTML = data.combo;
                            var text = desc_cliente.options[desc_cliente.selectedIndex].text;
                            //document.getElementById("txt-direc").value = text;
                            desc_cliente.removeAttribute('disabled');
                            //$("#cliente").val(data.clave_cliente);
                            //$("#desc_cliente").val("["+data.clave_cliente+"] - "+data.nombre_cliente);
                            $("#cliente").val(data.firsTValue);
                            BuscarDestinatario(data.firsTValue);
                            console.log("#cliente = ", $("#cliente").val());
                        }
                        else
                        {
                            $("#destinatario, #agregar_destinatario").prop("disabled", true);
                            $("#cliente").val("");
                            $("#desc_cliente").val("");

                            console.log("#cliente = ", $("#cliente").val());
                        }

                        $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR getClientesSelect: ", data);
                }

            });
        }

        //$("#cliente").on('change', function(e)
        $("#cliente_buscar").on('keyup', function(e)
        {
            var cliente = e.target.value;
            //console.log("cliente.length = ", cliente.length);
            if(cliente.length > 2)
                BuscarCliente(cliente);
            else
                BuscarCliente("Borrar_La_Lista_de_Clientes");
        });

        function BuscarDestinatario(cliente)
        {
            document.getElementById("agregar_destinatario").removeAttribute("disabled");
            $("#rutas_ventas").empty();
            $.ajax({
                url: '/api/nuevospedidos/lista/index.php',
                data: {
                    action: 'getDestinatario',
                    cliente: cliente
                },
                dataType: 'json',
                method: 'GET',
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) 
                {
                    console.log("SUCCESS DEST: ", data);
                    var destinatario = document.getElementById("destinatario");
                    if(data.combo !== '' && data.find == true){
                        destinatario.innerHTML = data.combo;
                        var text = destinatario.options[destinatario.selectedIndex].text;
                        document.getElementById("txt-direc").value = text;
                        destinatario.removeAttribute('disabled');

                        var array_rutas, ruta_val = $("#destinatario").find(':selected').data('ruta');
                        ruta_val = ruta_val.toString();
                        console.log("ruta_val = ", ruta_val);

                        array_rutas = ruta_val.split(",");

                        $("#rutas_ventas").append(data.combo_rutas);
                        $("#rutas_ventas").change();
                        /*$("#rutas_ventas option").each(function(index, value){

                            console.log("index = ", index, " value = ", value, " ruta_val = ", ruta_val, "select_rutas = ", $(this).val(), "array_rutas = ", array_rutas);

                            if(ruta_val != '' && array_rutas[0] != '')
                            {
                                console.log("inArray = ", $.inArray($(this).val(), array_rutas));
                                if($.inArray($(this).val(), array_rutas) !== 0) $(this).hide();
                                else $(this).show();
                                //if(ruta_val != $(this).val()) $(this).hide();
                            }

                        });*/
                    }
                    else 
                    {
                        $("#destinatario").empty();
                        $("#txt-direc").val("");
                    }

                    /*else
                    {
                        //$("#destinatario, #agregar_destinatario").prop("disabled", true);
                        $("#rutas_ventas option").each(function(index, value){

                            $(this).hide();

                        });
                    }*/

                    if(data.tiene_credito == 'N')
                    {
                        $("#tipo_credito").prop("disabled", true);
                        $("#tipo_contado").prop("checked", true);
                        //$("input[name=tipo_negociacion]:checked").val("Contado");
                    }
                    else 
                        $("#tipo_credito").prop("disabled", false); 
                    $(".chosen-select").trigger("chosen:updated");
                }, error: function(data){
                    console.log("ERROR getDestinatario: ", data);
                }

            });
        }

        $("#rutas_ventas").change(function(){

            $("#ultimo_diao").val("");
            console.log("clave_ruta:", $(this).val());
            if(!$(this).val()) return;

              $.ajax({
                url: "/api/nuevospedidos/update/index.php",
                type: "POST",
                data: {
                  action: 'getAgentesRutas',
                  clave_ruta: $(this).val()
                },
                datatype: 'json',
                beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
                },
                success: function(res){
                  console.log(res);
                  $("#ultimo_diao").val(res.udiao);
                  $("#vendedor").empty();
                  $("#vendedor").append(res.options);
                  $(".chosen-select").trigger("chosen:updated");

                },
                error : function(res){
                  window.console.log(res);
                },
                cache: false
              });


        });

        $("#desc_cliente").on("change", function(e){
            var cliente = $(this).val();
            BuscarDestinatario(cliente);
            //console.log("Cliente = ", cliente);
        });

        $("#destinatario").on("change", function(e){
            var destinatario = document.getElementById("destinatario");
            var text = destinatario.options[destinatario.selectedIndex].text;
            document.getElementById("txt-direc").value = text;
        });

        $("#almacen").on("change", function(e){
            $("#articulos").modal('toggle')
            /*
            var articulos = document.getElementById('articulo');
            articulos.innerHTML = '';
            var seleccione = document.createElement('option');
            seleccione.value = '';
            seleccione.innerHTML = 'Seleccione Artículo';
            articulos.appendChild(seleccione);
            $.ajax({
                url: '/api/articulos/lista/index.php',
                method: 'GET',
                data: {
                    almacen: e.target.value
                },
                datatype: 'json'
            })
            .done(function(data){
                data = JSON.parse(data);
                if(data.length > 0){
                    for(var i = 0; i < data.length; i++){
                        var articulo = document.createElement('option');
                        articulo.value = data[i].cve_articulo;
                        articulo.innerHTML = `${data[i].cve_articulo} - ` + `${data[i].des_articulo}`;
                        articulos.appendChild(articulo);
                    }
                }
            })
            .always(function(){
                $(".chosen-select").trigger("chosen:updated");
                $("#articulos").modal('toggle')
            });
            */
            fillSelectArti("N", "", "");//N = No Surtible
            fillSelectLP();
        });
    var basic = document.getElementById('articulo');
    var lp_ot = document.getElementById('lp_ot');
    var lp_lista = document.getElementById('lp_lista');

    var inputPU = document.getElementById('precioUnitario');
    var inputCP = document.getElementById('CantPiezas');
    var inputCP_Peso = document.getElementById('CantPiezasPeso');
    var importeTotal = document.getElementById('importeTotal');
    var importeOrden = document.getElementById('importeOrden');
    var inputDescuento = document.getElementById('descuento');
    var importeTotalOrdenHideAgr = document.getElementById('importeTotalOrdenHideAgr');
    var iva = document.getElementById('iva');
    var importeArticulo = document.getElementById('importeArticulo');

    function updateInputs() {

        var cant_piezas = 0, descuentoPU = 0;
        if($("#modo_peso").val() == 1)
        {
            cant_piezas = $("#CantPiezasPeso").val();
            if(isNaN(cant_piezas)) cant_piezas = 0;
            console.log("cant_piezas1 = ", cant_piezas);
            inputCP = document.getElementById('CantPiezasPeso');
        }
        else 
        {
            cant_piezas = parseInt($("#CantPiezas").val());
            if(isNaN(cant_piezas)) cant_piezas = 0;
            console.log("cant_piezas2 = ", cant_piezas);
            inputCP = document.getElementById('CantPiezas');
        }

        var agr_iva = 0;
        //if(parseFloat(iva) > 0 && parseFloat(iva) != '')
        //{

            if(iva.value == "") iva.value = 0;
        agr_iva = parseFloat($('#precioUnitario').val()) - (parseFloat($('#precioUnitario').val()) / (1+(parseFloat(iva.value)/100)));
        agr_iva = parseFloat(agr_iva.toFixed(4))*parseFloat(cant_piezas);

        var importeTOT = (parseFloat($('#precioUnitario').val())*parseFloat(cant_piezas)) -  parseFloat(agr_iva);

        if(isNaN(inputDescuento.value)) inputDescuento.value = 0;

        importeTOT = parseFloat(importeTOT.toFixed(4));
        console.log("inputDescuento.value1 = ", inputDescuento.value);
        if(!isNaN(inputDescuento.value) && inputDescuento.value != '')
        {
            importeTOT -= importeTOT*(parseFloat(inputDescuento.value)/100);
            importeTOT = importeTOT.toFixed(4);

            agr_iva -= agr_iva*(parseFloat(inputDescuento.value)/100);
            agr_iva = agr_iva.toFixed(4);

            //console.log("inputDescuento.value2 = ", inputDescuento.value);
        }

        if(isNaN(importeTOT)) importeTOT = 0;
        if(isNaN(agr_iva)) agr_iva = 0;
        
        if(isNaN(importeTotalOrdenHideAgr.value)) 
        {
            importeTotalOrdenHideAgr.value = 0;
        }
        else
        {
            var importeAgr = parseFloat(importeTotalOrdenHideAgr.value);
            importeTotalOrdenHideAgr.value = importeAgr.toFixed(4);
        }
        
        $('#importeTotal').val(parseFloat(importeTOT));
        $('#iva_show').val(agr_iva.toFixed(4));

        //console.log("parseFloat(importeTotalOrdenHideAgr.value) = ", parseFloat(importeTotalOrdenHideAgr.value));
        //console.log("parseFloat(importeTOT) = ", parseFloat(importeTOT));
        //console.log("parseFloat(agr_iva) = ", parseFloat(agr_iva));

        importeOrden.value = parseFloat(importeTotalOrdenHideAgr.value) + parseFloat(importeTOT) + parseFloat(agr_iva);
        importeArticulo.value = parseFloat(importeTOT) + parseFloat(agr_iva);
        //}

        //importeTotal.value = inputPU.value*cant_piezas;

        //importeOrden.value = inputPU.value*$("#CantPiezas").val();
        //console.log("importeOrden_tabla", importeOrden_tabla);

        //if(inputPU.value == "")
            //inputPU.value = 0;
        if(cant_piezas == "")
            cant_piezas = 0;

        //importeOrden.value = parseFloat(importeOrden.value) + parseFloat(inputPU.value*cant_piezas);

        //calcularTotal();
        //$('#importeOrden').val(parseFloat($('#importeOrden').val())+parseFloat(importeTotal.value));
    }

    if (inputPU.addEventListener) 
    {
      inputPU.addEventListener('keyup', function () {
          updateInputs();
      });
    }

    if (inputDescuento.addEventListener) 
    {
         inputDescuento.addEventListener('keyup', function () {
          updateInputs();
      });
    }

    if (inputCP.addEventListener) 
    {
      inputCP.addEventListener('keyup', function (e) {

          if(!$.isNumeric(e.key))
          {
                console.log("OK");
                var cantidad = inputCP.value;
                cantidad = cantidad.replace(e.key, '');
                inputCP.value = cantidad;
                return;
          }
          updateInputs();

      });
    }


    if (inputCP_Peso.addEventListener) 
    {
      inputCP_Peso.addEventListener('keyup', function (e) 
      {
            var cantidad = inputCP_Peso.value;
            var cantidad_vec = cantidad.split("");

          if((!$.isNumeric(e.key) && e.key != '.') || cantidad_vec[0] == '.')
          {
                //console.log("OKP", tecla.indexOf('.'));
                cantidad = cantidad.replace(e.key, '');
                inputCP_Peso.value = cantidad;
                return;
          }

          if(e.key == '.')
          {
                var count_punto = 0, pos_point = 0;

                for(var i = 0; i < cantidad_vec.length; i++)
                {
                    if(cantidad_vec[i] == '.')
                    {
                        count_punto++;
                        if(count_punto == 2) 
                        {
                            pos_point = i;
                            break;
                        }
                    }
                }

                if(count_punto > 1)
                {
                    cantidad_vec[pos_point] = '';
                    inputCP_Peso.value = '';
                    for(var i = 0; i < cantidad_vec.length; i++)
                    {
                        if(i != pos_point)
                            inputCP_Peso.value += cantidad_vec[i];
                    }
                }

          }

          updateInputs();
      });
    }
/*
    $("#gr_marca, #mod_clasif").change(function(){

        fillSelectArti();

    });
*/
    $("#ots_con_lp").change(function(){

        if($(this).val() == '')
        {
            $("#btnAgregar, #articulo").prop("disabled", false);
            $(".chosen-select").trigger("chosen:updated");
        }
        else
        {
            $("#btnAgregar, #articulo").prop("disabled", true);
            $(".chosen-select").trigger("chosen:updated");
            fillSelectLP();
        }
        

    });

    $("#lp_lista, #pry_lista").change(function(){

        if($(this).val() == '')
        {
            if($("#tipo_traslado").is(':checked'))
                fillSelectArti("S", "", "");
            else
                fillSelectArti("N", "", "");

        }
        else
        {
            if($("#tipo_traslado").is(':checked'))
                fillSelectArti("S", $("#lp_lista").val(), $("#pry_lista").val());
            else
                fillSelectArti("N", $("#lp_lista").val(), $("#pry_lista").val());
        }
        

    });


    var inicio = 0;
    function fillSelectArti(surtible, lp, pry)
    {  
        //return;
      var almacen = document.getElementById("almacen").value;
      if($("#tipo_traslado").is(':checked')) almacen = $("#almacen_dest").val();

      //console.log("almacen init = ", almacen);
      //console.log("grupo = ", $("#gr_marca").val());
      //console.log("clasificacion = ", $("#mod_clasif").val());
      console.log("productos_surtibles: ",surtible);
      console.log("sfa_activo: ",$("#sfa_activo").val());
      $.ajax({
        url: "/api/articulos/lista/index.php",
        type: "GET",
        data: {
          LP: lp,
          pry: pry,
          sfa_activo: $("#sfa_activo").val(),
          productos_surtibles: surtible,
          modulo: 'nuevospedidos',
          grupo: $("#gr_marca").val(), 
          clasificacion: $("#mod_clasif").val(), 
          almacen: almacen
        },
        beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res){
          console.log("SUCCESS", res);
            $("#articulo").empty();
          fillSelect(res.arr);
          $(".chosen-select").trigger("chosen:updated");
          if(!inicio)
          $("#articulos").modal('toggle');

            inicio = 1;
        },
        error : function(res){
          window.console.log(res);
        },
        cache: false
      });

      function fillSelect(node)
      {
        console.log("node = ", node);
        var option = "<option value =''>Seleccione un Articulo ("+node.length+")</option>";
        if(node.length > 0)
        {
          for(var i = 0; i < node.length; i++)
          {

            option += "<option value = '"+htmlEntities(node[i].cve_articulo)+"' data-id='"+htmlEntities(node[i].id)+"'>"+""+htmlEntities(node[i].cve_articulo)+" - "+htmlEntities(node[i].des_articulo)+"</option>";
          }
          //console.log("node = ", option);
        }
        basic.innerHTML = option;
        $(basic).trigger("chosen:updated");
      }
    }

    var inicioLP = 0;
    function fillSelectLP()
    {  
        //return;
        console.log("fillSelectLP()");
      var almacen = document.getElementById("almacen").value;
      if($("#tipo_traslado").is(':checked')) almacen = $("#almacen_dest").val();

      //console.log("almacen init = ", almacen);
      //console.log("grupo = ", $("#gr_marca").val());
      //console.log("clasificacion = ", $("#mod_clasif").val());

      if(almacen == "")
      {
        lp_lista.innerHTML = "<option value =''>Seleccione un LP (0)</option>";
        return;
      }

      $.ajax({
        //url: "/api/adminordentrabajo/lista/index.php",
        url: "/api/nuevospedidos/lista/index.php",
        type: "GET",
        data: {
          //action: 'nuevospedidos',
          action: 'mostrar_lp_almacen',
          //folio: $("#ots_con_lp").val(), 
          almacen: almacen
        },
        beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res){
          console.log(res);
          fillSelect(res);
          $(".chosen-select").trigger("chosen:updated");
          //if(!inicioLP)
          //$("#articulos").modal('toggle');
          //  inicioLP = 1;
        },
        error : function(res){
          window.console.log(res);
        },
        cache: false
      });

      function fillSelect(node)
      {
        console.log("node = ", node);
        var option = "<option value =''>Seleccione un LP ("+node.length+")</option>";
        if(node.length > 0)
        {
          for(var i = 0; i < node.length; i++)
          {

            option += "<option value = '"+htmlEntities(node[i].CveLP)+"' data-id='"+htmlEntities(node[i].CveLP)+"'>"+""+htmlEntities(node[i].CveLP)+"</option>";
          }
          //console.log("node = ", option);
        }
        //lp_ot.innerHTML = option;
        //$(lp_ot).trigger("chosen:updated");
        lp_lista.innerHTML = option;
        $(lp_lista).trigger("chosen:updated");
      }
    }

        $("#btnRegistrar").on('click', function(e){
            validarUsuarioAdminstrador();
        });

        $("#btnAgregar").on('click', function(e){


            if($("#lista_p_preciomax").val() != "" && $("#lista_p_preciomin").val() != "")
            {
                if(parseFloat($("#precioUnitario").val()) > parseFloat($("#lista_p_preciomax").val()) || parseFloat($("#precioUnitario").val()) < parseFloat($("#lista_p_preciomin").val()))
                {
                    swal("Error", "El Precio debe estar entre los rangos "+$("#lista_p_preciomin").val()+" y "+$("#lista_p_preciomax").val(), "error");
                    return;
                }
            }

            if($("#lista_d_descmin").val() != "" && $("#lista_d_descmax").val() != "")
            {
                if(parseFloat($("#descuento").val()) > parseFloat($("#lista_d_descmax").val()) || parseFloat($("#descuento").val()) < parseFloat($("#lista_d_descmin").val()))
                {
                    swal("Error", "El porcentaje de descuento debe estar entre los rangos "+$("#lista_d_descmin").val()+" y "+$("#lista_d_descmax").val(), "error");
                    return;
                }
            }


            var clave = $("#articulo").val(),
                lote_serie = $("#lote_serie").val(),
                articulo = $.trim($(`#articulo option[value='${clave}']`).html().split(' - ')[1]),
                des_detallada = $("#observacion").val(),
                piezas = $("#CantPiezas").val(),
                caducidad = $("#caducidadMin").val(),
                id_unimed = $("#id_unimed").val(),
                unidad_text = $("#id_unimed option:selected").text(),

                peso = $("#peso").val(),
                precioUnitario = $("#precioUnitario").val(),
                iva = $("#iva_show").val(),
                importeTotal = $("#importeTotal").val(),
                importeOrden = (!isNaN(parseFloat($("#importeOrden").val())))?parseFloat($("#importeOrden").val()):0,
                total = parseFloat(importeTotal) + parseFloat(iva),

                fecha = moment().format('DD-MM-YYYY HH:mm:ss'),
                cliente_clave = $("#cliente").val(),
                rutas_vent = $("#rutas_ventas").val(),
                almacen = $("#almacen").val(),
                folio = $("#folio").val(),
                cliente = $("#cliente").val(), //$(`#cliente option[value='${cliente_clave}']`).html(),
                destinatario = $("#destinatario").val(),
                fecha_compromiso = $("#fechaSolicitud").val(),
                prioridad_clave = $("#prioridad").val(),
                proyecto = $("#pry_lista").val(),
                prioridad = $(`#prioridad option[value='${prioridad_clave}']`).html(),

                existe = $('#grid-table').jqGrid ('getRowData', clave+lote_serie), 
                ocultar_lp = true, reiniciar_valores = true;
                //existe_lote = $('#grid-table').jqGrid ('getRowData', lote_serie);
                $("#restarHide").val("0");
                if($("#modo_peso").val() == 1) piezas = $("#CantPiezasPeso").val();

                descuento = (parseFloat(precioUnitario)*parseFloat(piezas)).toFixed(3) - (parseFloat(importeTotal)+parseFloat(iva)).toFixed(3); //$("#descuento").val(),

                if(isNaN(descuento)) descuento = 0;

                //importeOrden.value = parseFloat(importeOrden.value) + parseFloat($('#importeTotal').val());
                //$("#importeOrden").val(parseFloat($("#importeOrden").val()) + parseFloat($('#importeTotal').val()));

                console.log("existe = ", existe);
                //console.log("existe_lote = ", existe_lote);
            if(!$.isEmptyObject(existe) && $("#lp_lista").val() == '') // && !$.isEmptyObject(existe_lote)
            {
                console.log("ENTRÓ EN CASO 1: Sin LP con datos correctos EDITABLES");
                //$("#grid-table").jqGrid('setCell',clave,0, clave);
                //$("#grid-table").jqGrid('setCell',clave,1, articulo);
                var valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).cantPiezas;
                piezas = parseFloat(piezas) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'cantPiezas', piezas);
                valorgrid = 0;

                valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).peso;
                peso = parseFloat(peso) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'peso', peso);
                valorgrid = 0;

                valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).precio_unitario;
                //precioUnitario = parseFloat(precioUnitario) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'precio_unitario', precioUnitario);
                //valorgrid = 0;

                valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).descuento;
                descuento = parseFloat(descuento) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'descuento', descuento.toFixed(3));
                valorgrid = 0;

                valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).importe_total;
                importeTotal = parseFloat(importeTotal) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'importe_total', importeTotal);

                valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).iva;
                iva = parseFloat(iva) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'iva', iva);

                valorgrid = $("#grid-table").jqGrid('getLocalRow', clave+lote_serie).total;
                total = parseFloat(total) + parseFloat(valorgrid);
                $("#grid-table").jqGrid('setCell',clave+lote_serie,'total', total);
                //$("#grid-table").jqGrid('setCell',clave,6, descuento);

/*
                var grid = $("#grid-table");
                grid.jqGrid('editRow', 
                    clave, true,
                    {
                        c_cliente: cliente_clave,
                        c_prioridad: prioridad_clave,
                        clave: clave,
                        articulo: articulo,
                        cantPiezas: piezas,
                        caducidad: caducidad,
                        peso: peso,
                        precio_unitario: precioUnitario,
                        descuento: descuento,
                        importe_total: importeTotal,
                        iva: iva,
                        total: total,
                        fecha_registro: fecha,
                        cliente: cliente,
                        destinatario: destinatario,
                        fecha_compromiso: fecha_compromiso,
                        prioridad: prioridad
                    }, 
                    clave);
                $("#grid-table").trigger("reloadGrid");
*/
                //$("#totalArt").val(grid.getGridParam("reccount"));
                $("#totalPiez").val(getTotalPiezas);
                $("#articulo").val('');
                $("#caducidadMin").val('');
                $("#id_unimed").val('');
                $("#CantPiezas").val('');
                $("#descuento, #descuentomonto, #precioUnitario").val('');
                $("#precioUnitario").val('');
                $('#importeOrden').val((!isNaN(parseFloat($("#importeOrden").val())))?parseFloat($("#importeOrden").val()):0);
                $('#importeTotalOrdenHideAgr').val(parseFloat($('#importeOrden').val()));
                $('#importeTotal').val(0);
                $('#importeArticulo').val(0);
                $('#peso').val(0);
                $('#iva_show, #iva').val(0);
                $("#Articul span span").text("");

                //$("#cliente_buscar").prop('disabled', true);
                //$("#desc_cliente").prop('disabled', true);
                //$("#prioridad").prop('disabled', true);
                //$("#destinatario").prop('disabled', true);
                //$("#agregar_destinatario").prop('disabled', true);
                //$("#rutas_ventas").prop('disabled', true);

                $('.chosen-select').trigger('chosen:updated');
                //swal("Error", "El artículo que intentas añadir ya existe", "error");
            }
            else if(clave === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){
                swal("Error", "Seleccione artículo", "error");
            }
            else if(almacen === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){
                swal("Error", "Seleccione almacen", "error");
            }
            else if(folio === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){
                swal("Error", "Ingrese folio", "error");
            }
            else if(id_unimed === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){
                //swal("Error", "Seleccione una Unidad de Medida", "error");   
                swal("El Artículo No tiene Unidad de Medida", "Este artículo no tiene Unidad de Medida Registrada en el catálogo, debe ir al catálogo de producto y registrar una unidad de medida para poder realizar el pedido", "error");
            }
            else if(piezas === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){//&& $("#modo_peso").val() == 0
                swal("Error", "Ingrese cantidad", "error");   
            }
            else if(cliente_clave === '' && rutas_vent == '' && !$("#tipo_traslado").is(':checked')){
                if(cliente_clave == '' && !$("#tipo_traslado").is(':checked'))
                    swal("Error", "Seleccione cliente", "error");   
                else if(!$("#tipo_traslado").is(':checked'))
                    swal("Error", "Seleccione una Ruta", "error");   
            }
            else if(destinatario === ''){
                swal("Error", "Ingrese destinatario", "error");   
            }
            else if(fecha_compromiso === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){
                swal("Error", "Seleccione fecha de compromiso", "error");   
            }
            else if(prioridad_clave === '' && $("#lp_lista").val() == '' && $("#pry_lista").val() == ''){
                swal("Error", "Seleccione prioridad", "error");   
            }
            else {
                var grid = $("#grid-table");
                if($("#lp_lista").val() == '' && $("#pry_lista").val() == '')
                {

                    if($("#pedido_tipo_LP").val() == '1')
                    {
                        swal("Error", "Ya ha seleccionado Productos Con LP, Solo se permiten Seleccionar LP en este Pedido, Puede Reiniciar y Agregar solo productos sin LP", "error");
                        return;
                    }

                    if($("#pedido_tipo_proyecto").val() == '1')
                    {
                        swal("Error", "Ya ha seleccionado Productos Con Proyectos, Solo se permiten Seleccionar productos con proyectos en este Pedido, Puede Reiniciar y Agregar solo productos que estén dentro de un proyecto", "error");
                        return;
                    }

                    console.log("ENTRÓ EN CASO 2: Sin LP con datos correctos");

                    $("#pedido_tipo_LP").val(0);
                    $("#pedido_tipo_proyecto").val(0);
                    grid.jqGrid('addRowData', 
                        clave+lote_serie, 
                        {
                            lp: '',
                            c_cliente: cliente_clave,
                            c_prioridad: prioridad_clave,
                            clave: clave,
                            lote_serie: lote_serie,
                            articulo: articulo,
                            des_detallada: des_detallada,
                            cantPiezas: (!isNaN(parseFloat(piezas).toFixed(4)))?parseFloat(piezas).toFixed(4):0,
                            caducidad: caducidad,
                            id_unimed: id_unimed,
                            unidad_text: unidad_text,
                            peso: (!isNaN(parseFloat(peso).toFixed(4)))?parseFloat(peso).toFixed(4):0,
                            precio_unitario: (!isNaN(parseFloat(precioUnitario).toFixed(4)))?parseFloat(precioUnitario).toFixed(4):0,
                            descuento: (!isNaN(parseFloat(descuento).toFixed(4)))?parseFloat(descuento).toFixed(4):0,
                            importe_total: (!isNaN(parseFloat(importeTotal).toFixed(4)))?parseFloat(importeTotal).toFixed(4):0,
                            iva: (!isNaN(parseFloat(iva).toFixed(4)))?parseFloat(iva).toFixed(4):0,
                            total: (!isNaN(parseFloat(total).toFixed(4)))?parseFloat(total).toFixed(4):0,
                            fecha_registro: fecha,
                            cliente: cliente,
                            destinatario: destinatario,
                            fecha_compromiso: fecha_compromiso,
                            proyecto: proyecto,
                            prioridad: prioridad
                        }, 
                        'last');
                        $("#totalArt").val(grid.getGridParam("reccount"));
                        $("#totalPiez").val(getTotalPiezas);
                }
                else
                {
                    if($("#pedido_tipo_LP").val() == '0' && $("#pry_lista").val() == '')
                    {
                        swal("Error", "Ya ha seleccionado Productos Sin LP, Solo se permiten artículos SIN LP en este Pedido, Puede Reiniciar y Agregar solo LP", "error");
                        return;
                    }

                    if($("#pedido_tipo_proyecto").val() == '0')
                    {
                        swal("Error", "Ya ha seleccionado Productos con proyectos, Solo se permiten artículos con proyectos registrados en este Pedido, Puede Reiniciar y Agregar solo productos con proyectos", "error");
                        return;
                    }

                    console.log("ENTRÓ EN CASO 3: CON LP con datos correctos");
                    console.log("LEER TARIMA", $("#lp_lista").val());
                    console.log("LEER PROYECTO", $("#pry_lista").val());
                    
                    var lp_selected = $("#lp_lista").val();
                    var pry_selected = $("#pry_lista").val();
                    var art_selected = $("#articulo").val();
                    var lote_selected = $("#lote_serie").val();

                    if(lp_selected != '') $("#pedido_tipo_LP").val(1);
                    if(pry_selected != '') $("#pedido_tipo_proyecto").val(1);

                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                            lp: lp_selected,
                            pry: pry_selected,
                            art_selected: art_selected,
                            lote_selected: lote_selected,
                            action : "articulos_lp"
                        },
                        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                                },
                        url: '/api/nuevospedidos/lista/index.php',
                        success: function(data) {
                            console.log("SUCCESS TARIMA: ", data.articulos_lp);
                            var art_lp = data.articulos_lp;
                            for(var i = 0; i < art_lp.length; i++)
                            {
                                //console.log("art_lp = ", art_lp[i]);

                                if(pry_selected != '' && clave == '')
                                {
                                    grid.jqGrid('addRowData', 
                                        pry_selected+art_lp[i]["cve_articulo"]+art_lp[i]["lote"]+i, 
                                        {
                                            lp: lp_selected,
                                            c_cliente: cliente_clave,
                                            c_prioridad: prioridad_clave,
                                            clave: art_lp[i]["cve_articulo"],
                                            lote_serie: art_lp[i]["lote"],
                                            articulo: art_lp[i]["des_articulo"],
                                            des_detallada: art_lp[i]["des_detallada"],
                                            cantPiezas: (!isNaN(parseFloat(art_lp[i]["existencia"]).toFixed(4)))?parseFloat(art_lp[i]["existencia"]).toFixed(4):0,
                                            caducidad: art_lp[i]["Caducidad"],
                                            id_unimed: art_lp[i]["id_umed"],
                                            unidad_text: art_lp[i]["des_umed"],
                                            peso: (!isNaN(parseFloat(art_lp[i]["peso"]).toFixed(4)))?parseFloat(art_lp[i]["peso"]).toFixed(4):0,
                                            precio_unitario: (!isNaN(parseFloat(precioUnitario).toFixed(4)))?parseFloat(precioUnitario).toFixed(4):0,
                                            descuento: (!isNaN(parseFloat(descuento).toFixed(4)))?parseFloat(descuento).toFixed(4):0,
                                            importe_total: (!isNaN(parseFloat(importeTotal).toFixed(4)))?parseFloat(importeTotal).toFixed(4):0,
                                            iva: (!isNaN(parseFloat(iva).toFixed(4)))?parseFloat(iva).toFixed(4):0,
                                            total: (!isNaN(parseFloat(total).toFixed(4)))?parseFloat(total).toFixed(4):0,
                                            fecha_registro: fecha,
                                            cliente: cliente,
                                            destinatario: destinatario,
                                            fecha_compromiso: fecha_compromiso,
                                            proyecto: proyecto,
                                            prioridad: prioridad
                                        }, 
                                        'last');
                                        $("#totalArt").val(grid.getGridParam("reccount"));
                                        $("#totalPiez").val(getTotalPiezas);

                                        if($("#lp_lista").val() != '')
                                            $("#lp_lista option[value='"+$("#lp_lista").val()+"']").hide(); 
                                        if($("#pry_lista").val() != '')
                                        {
                                            $("#pry_lista option[value='"+$("#pry_lista").val()+"']").hide(); 
                                            Activar_BloquearSelectsPry('activar');
                                        }
                                        $("#lp_lista,#pry_lista").val(""); 
                                        $("#Articul span span").text("");
                                        if(art_lp[i]["control_numero_series"] == 'S')
                                            $("#CantPiezas, #CantPiezasPeso").val(1);

                                }
                                else if(pry_selected != '' && clave != '' && clave == art_lp[i]["cve_articulo"])
                                {
                                    //lote_serie
                                    var lote_serie_selected = "";
                                    //if(lote_serie == '') lote_serie_selected = art_lp[i]["lote"]; else 
                                    lote_serie_selected = lote_serie;
                                    var existencia = (!isNaN(parseFloat(art_lp[i]["existencia"]).toFixed(4)))?parseFloat(art_lp[i]["existencia"]).toFixed(4):0;
                                    console.log("*****************");
                                    console.log("CASO 4");
                                    console.log("*****************");
                                    console.log("piezas = ", piezas);
                                    console.log("existencia = ", existencia);
                                    console.log("existencia LP = ", art_lp[i]["existencia"]);
                                    console.log("*****************");

                                    if(piezas > 1 && art_lp[i]["control_numero_series"] == 'S' && lote_serie_selected != '')
                                    {
                                        piezas = 1;
                                        swal("Artículo con Número de serie", "No se puede colocar una cantidad mayor a 1 con artículos con número de serie", "error");
                                    }

                                    if(!piezas) 
                                    {
                                        piezas = existencia; 
                                        //var imp = ($("#importeOrden").val()),
                                        //impr_agr = (!isNaN(parseFloat(imp).toFixed(4)))?parseFloat(imp).toFixed(4):0, 
                                        //pu_agr = (!isNaN(parseFloat(precioUnitario).toFixed(4)))?parseFloat(precioUnitario).toFixed(4):0,
                                        //iva_agr = (!isNaN(parseFloat(iva).toFixed(4)))?parseFloat(iva).toFixed(4):0;
                                        //$("#importeOrden").val(impr_agr+((pu_agr+iva_agr)*piezas));
                                    }
                                    if(parseFloat(piezas) <= parseFloat(existencia) || pry_selected != '')
                                    {
                                        ocultar_lp = false;
                                        grid.jqGrid('addRowData', 
                                            pry_selected+clave+lote_serie+i, 
                                            {
                                                lp: lp_selected,
                                                c_cliente: cliente_clave,
                                                c_prioridad: prioridad_clave,
                                                clave: art_lp[i]["cve_articulo"],
                                                lote_serie: lote_serie_selected,
                                                articulo: art_lp[i]["des_articulo"],
                                                des_detallada: art_lp[i]["des_detallada"],
                                                cantPiezas: piezas,
                                                caducidad: art_lp[i]["Caducidad"],
                                                id_unimed: art_lp[i]["id_umed"],
                                                unidad_text: art_lp[i]["des_umed"],
                                                peso: (!isNaN(parseFloat(art_lp[i]["peso"]).toFixed(4)))?parseFloat(art_lp[i]["peso"]).toFixed(4):0,
                                                precio_unitario: (!isNaN(parseFloat(precioUnitario).toFixed(4)))?parseFloat(precioUnitario).toFixed(4):0,
                                                descuento: (!isNaN(parseFloat(descuento).toFixed(4)))?parseFloat(descuento).toFixed(4):0,
                                                importe_total: (!isNaN(parseFloat(importeTotal).toFixed(4)))?parseFloat(importeTotal).toFixed(4):0,
                                                iva: (!isNaN(parseFloat(iva).toFixed(4)))?parseFloat(iva).toFixed(4):0,
                                                total: (!isNaN(parseFloat(total).toFixed(4)))?parseFloat(total).toFixed(4):0,
                                                fecha_registro: fecha,
                                                cliente: cliente,
                                                destinatario: destinatario,
                                                fecha_compromiso: fecha_compromiso,
                                                proyecto: proyecto,
                                                prioridad: prioridad
                                            }, 
                                            'last');
                                            $("#totalArt").val(grid.getGridParam("reccount"));
                                            $("#totalPiez").val(getTotalPiezas);
                                            $("#CantPiezas, #CantPiezasPeso").val('');
                                            $("#pry_lista").val(""); 
                                            $("#pry_lista").change();
                                    }
                                    else
                                    {
                                        console.log("LP Existencia 2");
                                        swal("Error", "La cantidad pedida de este LP es mayor a su existencia", "error");
                                        ocultar_lp = false;
                                        reiniciar_valores = false;
                                        //return;
                                    }
                                }
                                else if(lp_selected != '' && clave == '')
                                {
                                    grid.jqGrid('addRowData', 
                                        lp_selected+clave+lote_serie+i, 
                                        {
                                            lp: lp_selected,
                                            c_cliente: cliente_clave,
                                            c_prioridad: prioridad_clave,
                                            clave: art_lp[i]["cve_articulo"],
                                            lote_serie: art_lp[i]["lote"],
                                            articulo: art_lp[i]["des_articulo"],
                                            des_detallada: art_lp[i]["des_detallada"],
                                            cantPiezas: (!isNaN(parseFloat(art_lp[i]["existencia"]).toFixed(4)))?parseFloat(art_lp[i]["existencia"]).toFixed(4):0,
                                            caducidad: art_lp[i]["Caducidad"],
                                            id_unimed: art_lp[i]["id_umed"],
                                            unidad_text: art_lp[i]["des_umed"],
                                            peso: (!isNaN(parseFloat(art_lp[i]["peso"]).toFixed(4)))?parseFloat(art_lp[i]["peso"]).toFixed(4):0,
                                            precio_unitario: 0,
                                            descuento: 0,
                                            importe_total: 0,
                                            iva: 0,
                                            total: 0,
                                            fecha_registro: fecha,
                                            cliente: cliente,
                                            destinatario: destinatario,
                                            fecha_compromiso: fecha_compromiso,
                                            proyecto: proyecto,
                                            prioridad: prioridad
                                        }, 
                                        'last');
                                        $("#totalArt").val(grid.getGridParam("reccount"));
                                        $("#totalPiez").val(getTotalPiezas);

                                        if($("#lp_lista").val() != '')
                                        $("#lp_lista option[value='"+$("#lp_lista").val()+"']").hide(); 
                                        $("#lp_lista").val(""); 
                                        $("#Articul span span").text("");

                                }
                                else if(lp_selected != '' && clave != '' && clave == art_lp[i]["cve_articulo"])
                                {
                                    //lote_serie
                                    var lote_serie_selected = "";
                                    if(lote_serie == '') lote_serie_selected = art_lp[i]["lote"]; else lote_serie_selected = lote_serie;
                                    var existencia = (!isNaN(parseFloat(art_lp[i]["existencia"]).toFixed(4)))?parseFloat(art_lp[i]["existencia"]).toFixed(4):0;
                                    console.log("*****************");
                                    console.log("piezas = ", piezas);
                                    console.log("existencia = ", existencia);
                                    console.log("existencia LP = ", art_lp[i]["existencia"]);
                                    console.log("*****************");
                                    if(parseFloat(piezas) <= parseFloat(existencia) || $("#pry_lista").val() != '')
                                    {
                                        ocultar_lp = false;
                                        grid.jqGrid('addRowData', 
                                            lp_selected+clave+lote_serie+i, 
                                            {
                                                lp: lp_selected,
                                                c_cliente: cliente_clave,
                                                c_prioridad: prioridad_clave,
                                                clave: art_lp[i]["cve_articulo"],
                                                lote_serie: lote_serie_selected,
                                                articulo: art_lp[i]["des_articulo"],
                                                des_detallada: art_lp[i]["des_detallada"],
                                                cantPiezas: piezas,
                                                caducidad: art_lp[i]["Caducidad"],
                                                id_unimed: art_lp[i]["id_umed"],
                                                unidad_text: art_lp[i]["des_umed"],
                                                peso: (!isNaN(parseFloat(art_lp[i]["peso"]).toFixed(4)))?parseFloat(art_lp[i]["peso"]).toFixed(4):0,
                                                precio_unitario: 0,
                                                descuento: 0,
                                                importe_total: 0,
                                                iva: 0,
                                                total: 0,
                                                fecha_registro: fecha,
                                                cliente: cliente,
                                                destinatario: destinatario,
                                                fecha_compromiso: fecha_compromiso,
                                                proyecto: proyecto,
                                                prioridad: prioridad
                                            }, 
                                            'last');
                                            $("#totalArt").val(grid.getGridParam("reccount"));
                                            $("#totalPiez").val(getTotalPiezas);
                                            $("#CantPiezas, #CantPiezasPeso").val('');
                                            $("#lp_lista").val(""); 
                                            $("#lp_lista").change();
                                    }
                                    else
                                    {
                                        swal("Error", "La cantidad pedida de este LP es mayor a su existencia", "error");
                                        ocultar_lp = false;
                                        reiniciar_valores = false;
                                        //return;
                                    }
                                }
                            }

                        }, error: function(data){
                            console.log("ERROR TARIMA: ", data);
                        }
                    });
/*
                    var arrDetallePrecioTotal = [];

                    var ids = $("#grid-table").jqGrid('getDataIDs');
                    total = 0;
                    for (var i = 0; i < ids.length; i++)
                    {
                        var rowId = ids[i];
                        var rowData = $('#grid-table').jqGrid ('getRowData', rowId);

                        //Cve_articulo: rowData.clave,
                        //des_articulo: rowData.articulo,
                        //des_detallada: rowData.des_detallada,
                        //cve_lote: rowData.lote_serie,
                        //Num_cantidad: rowData.cantPiezas,
                        //id_unimed: rowData.id_unimed,
                        //Num_Meses: rowData.caducidad,
                        //peso: rowData.peso,
                        //precio_unitario: rowData.precio_unitario,
                        //desc_importe: rowData.descuento,
                        //sub_total: rowData.importe_total,
                        //unidad_medida: rowData.unidad_text,
                        //proyecto: rowData.proyecto,
                        total +=  rowData.importe_total+rowData.iva;
                    }
                    console.log("*************************************");
                    console.log("TOTAL FINAL = ", total);
                    console.log("*************************************");

                    $("#importeOrden").val(total);
*/
                    //return;
                }

        setTimeout(function(){
                if(reiniciar_valores == true)
                {
                    $("#articulo").val('');
                    $("#caducidadMin").val('');
                    $("#id_unimed").val('');
                    $("#CantPiezas").val('');
                    $("#descuento, #descuentomonto, #precioUnitario").val('');
                    $("#precioUnitario").val('');
                    $('#importeOrden').val(parseFloat($('#importeOrden').val()));
                    $('#importeTotalOrdenHideAgr').val(parseFloat($('#importeOrden').val()));
                    $('#importeTotal').val(0);
                    $('#importeArticulo').val(0);
                    $('#peso').val(0);
                    $('#iva_show, #iva').val(0);
                    $(".foto_producto").css("background-image", "");
                    //$("#observacion").val("");
                    $('#lote_serie').empty();


                    //if($("#lp_lista").val() != '')
                    //$("#lp_lista option[value='"+$("#lp_lista").val()+"']").hide(); 
                    //$("#lp_lista").val(""); 
                    //$("#Articul span span").text("");

                    //$("#cliente_buscar").prop('disabled', true);
                    //$("#desc_cliente").prop('disabled', true);
                    //$("#prioridad").prop('disabled', true);
                    //$("#destinatario").prop('disabled', true);
                    //$("#agregar_destinatario").prop('disabled', true);

                    $('.chosen-select').trigger('chosen:updated');
                }
            }, 700);
            }
        })
    });

    $("#destinatario").change(function()
    {

        var array_rutas, ruta_val = $(this).find(':selected').data('ruta');
        console.log("ruta_val = ", ruta_val);
        ruta_val = ruta_val.toString();
        array_rutas = ruta_val.split(",");

        $("#rutas_ventas option").each(function(index, value){

                if(array_rutas[0] != '')
                  $(this).show();
                else 
                  $(this).hide();
        });

        $("#rutas_ventas option").each(function(index, value){

            console.log("index = ", index, " value = ", value, " ruta_val = ", ruta_val, "select_rutas = ", $(this).val(), "array_rutas = ", array_rutas);

            if(ruta_val != '' && array_rutas[0] != '')
            {
                console.log("inArray = ", $.inArray($(this).val(), array_rutas));
                if($.inArray($(this).val(), array_rutas) !== 0) $(this).hide();
                else $(this).show();
                //if(ruta_val != $(this).val()) $(this).hide();
            }

        });

    });

    $("#CantPiezas").keyup(function(e) {

        var cantidad = $(this).val();

        if(!$.isNumeric(e.key))
        {
            console.log("OK2");
            cantidad = cantidad.replace(e.key, '');
            $(this).val(cantidad);
            return;
        }

        if(cantidad == "") 
        {
            cantidad = 0;
            //$(this).val(0);
        }
        var peso =$("#pesohidden").val();
        if(peso == "") peso = 0;
        var pu =$("#precioUnitario").val();
        if(pu == "") pu = 0;

        console.log("************Importe************");
        $("#peso").val(cantidad*peso);
        $("#importeTotal").val((cantidad*pu).toFixed(4));
    });
   
    $("#CantPiezasPeso").keyup(function(e) {

        var cantidad = $(this).val();
        if(cantidad == "") 
        {
            cantidad = 0;
            //$(this).val(0);
        }
        var peso =$("#pesohidden").val();
        if(peso == "") peso = 0;
        var pu =$("#precioUnitario").val();
        if(pu == "") pu = 0;

        $("#peso").val(cantidad*peso);
        $("#importeTotal").val((cantidad*pu).toFixed(4));

    });

    function getTotalPiezas(){
        var data = $('#grid-table').jqGrid('getGridParam','data');
        var total = 0;
        for(var i = 0; i < data.length; i++){
            total += parseFloat(data[i].cantPiezas);
        }
        return total;
    }
    function resetForm(){
        document.getElementById('myform').reset();
        $("#grid-table").jqGrid("clearGridData");
        $('.chosen-select').trigger('chosen:updated');
    }

    function utf8_decode (strData) { 

      var tmpArr = []
      var i = 0
      var c1 = 0
      var seqlen = 0

      strData += ''

      while (i < strData.length) {
        c1 = strData.charCodeAt(i) & 0xFF
        seqlen = 0

        if (c1 <= 0xBF) {
          c1 = (c1 & 0x7F)
          seqlen = 1
        } else if (c1 <= 0xDF) {
          c1 = (c1 & 0x1F)
          seqlen = 2
        } else if (c1 <= 0xEF) {
          c1 = (c1 & 0x0F)
          seqlen = 3
        } else {
          c1 = (c1 & 0x07)
          seqlen = 4
        }

        for (var ai = 1; ai < seqlen; ++ai) {
          c1 = ((c1 << 0x06) | (strData.charCodeAt(ai + i) & 0x3F))
        }

        if (seqlen === 4) {
          c1 -= 0x10000
          tmpArr.push(String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF)))
          tmpArr.push(String.fromCharCode(0xDC00 | (c1 & 0x3FF)))
        } else {
          tmpArr.push(String.fromCharCode(c1))
        }

        i += seqlen
      }

      return tmpArr.join('')
    }

    function folio_consecutivo() {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          action : "consecutivo_folio"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/nuevospedidos/update/index.php',
        success: function(data) 
        {
          if (data.success == true) 
          {
/*
            var dt = new Date();
            var year = dt.getUTCFullYear(); 
            var month = ("0" + (dt.getMonth() + 1)).slice(-2);
*/
            
            //$("#folio").val("S" + year + month + data.Consecutivo);
            $("#folio").val(data.Consecutivo);
            $("#folio_fol").val(data.Consecutivo);
            
          }
        }, error: function(data)
        {
          console.log("ERROR data folio_consecutivo = ",data);
        }
      });
    }
    folio_consecutivo();

    function folio_consecutivo_traslado() {
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          action : "consecutivo_folio_traslado"
        },
        beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }},
        url: '/api/nuevospedidos/update/index.php',
        success: function(data) 
        {
            console.log("SUCCESS data folio_consecutivo = ",data);
          if (data.success == true) 
          {
/*
            var dt = new Date();
            var year = dt.getUTCFullYear(); 
            var month = ("0" + (dt.getMonth() + 1)).slice(-2);
*/
            
            //$("#folio").val("S" + year + month + data.Consecutivo);
            $("#folio_tr").val(data.Consecutivo);
            
          }
        }, error: function(data)
        {
          console.log("ERROR data folio_consecutivo = ",data);
        }
      });
    }
    folio_consecutivo_traslado();


    $("#exportExcel").on("click", function(){
        window.exportDataGrid.exportExcel("grid-table","Lista_Articulos.xls");
    });
    $("#exportPDF").on("click", function(){
        //window.exportDataGrid.exportPDF("grid-table","Lista_Articulos.pdf");
        var arrDetalle = [];

        var ids = $("#grid-table").jqGrid('getDataIDs');

        for (var i = 0; i < ids.length; i++)
        {
            var rowId = ids[i];
            var rowData = $('#grid-table').jqGrid ('getRowData', rowId);

            arrDetalle.push({
                Cve_articulo: rowData.clave,
                des_articulo: rowData.articulo,
                des_detallada: rowData.des_detallada,
                cve_lote: rowData.lote_serie,
                Num_cantidad: rowData.cantPiezas,
                id_unimed: rowData.id_unimed,
                Num_Meses: rowData.caducidad,
                peso: rowData.peso,
                precio_unitario: rowData.precio_unitario,
                desc_importe: rowData.descuento,
                sub_total: rowData.importe_total,
                unidad_medida: rowData.unidad_text,
                proyecto: rowData.proyecto,
                iva: rowData.iva
            });
        }
        console.log("arrDetalle PDF = ", arrDetalle);

        if($.isEmptyObject(arrDetalle)) 
        {
            swal("Error", "El Pedido no posee productos", "error");
            return;
        }
/*
        $.ajax({
            type: "GET",
            url: '/api/koolreport/export/reportes/pedidos/venta-pedido',
            dataType: 'json',
            async: false,
            data: {
                arrDetalle: arrDetalle,
                folio: $("#folio").val(),
                cve_cia: $("#cve_cia").val(),
                desc_cliente: $("#desc_cliente").val(),
                destinatario: $("#destinatario").val()
            },
            success: function (data) {
            console.log("PDF export = ", data);
            }
        });
*/

        $(this).attr("target", "_blank");
        $(this).attr("href", "/api/koolreport/export/reportes/pedidos/venta-pedido?arrDetalle="+encodeURIComponent(JSON.stringify(arrDetalle))+"&folio="+$("#folio").val()+"&cve_cia="+$("#cve_cia").val()+"&cliente="+$("#desc_cliente").val()+"&destinatario="+$("#destinatario").val()+"&tipoventa="+$("input[name=tipo_venta]:checked").val()+"&tipo_negociacion="+$("input[name=tipo_negociacion]:checked").val()+"&sfa="+$("#sfa_activo").val()+"&cve_almacen="+<?php $cve_almac = $_SESSION['cve_almacen']; echo '"'.$cve_almac.'"'; ?>);
        //console.log("/api/koolreport/export/reportes/pedidos/venta-pedido?arrDetalle="+encodeURIComponent(JSON.stringify(arrDetalle))+"&folio="+$("#folio").val()+"&cve_cia="+$("#cve_cia").val()+"&cliente="+$("#desc_cliente").val()+"&destinatario="+$("#destinatario").val());

        //$(this).attr("href", "/api/koolreport/export/reportes/pedidos/venta-pedido?arrDetalle="+arrDetalle+"&folio="+$("#folio").val()+"&cve_cia="+$("#cve_cia").val()+"&cliente="+$("#desc_cliente").val()+"&destinatario="+$("#destinatario").val());
    });

</script>
