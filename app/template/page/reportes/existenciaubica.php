<?php
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

    $almacenes = new \AlmacenP\AlmacenP();
    $model_almacen = $almacenes->getAll();
    $listaProyectos = new \Proyectos\Proyectos();

    $id_almacen_existencias = $_SESSION['id_almacen'];
    $cliente_almacen_style = ""; $cve_proveedor = ""; $cve_proveedor_login = 0; $almacen_cliente = ""; $cve_cliente = ""; $almacen_proveedor = "";
    $id_almacen_lotes = "";$almacen_cliente = "";


    if($_SESSION['es_cliente'] == 1) 
    {
      $usuario = $_SESSION["cve_usuario"];
      $cliente_almacen_style = "style='display: none;'";
      $cve_cliente = $_SESSION['cve_cliente'];
      $alm_cliente = \db()->prepare("SELECT a.id as id_almacen 
                                     FROM c_usuario u 
                                     LEFT JOIN c_almacenp a ON a.clave = u.cve_almacen
                                     WHERE u.cve_usuario = '$usuario' AND u.cve_cliente = '$cve_cliente'");
      $alm_cliente->execute();
      $almacen_cliente = $alm_cliente->fetch()['id_almacen'];
      $id_almacen_existencias = $almacen_cliente;

      $prov_cliente = \db()->prepare("SELECT ID_Proveedor
                                     FROM c_cliente
                                     WHERE Cve_Clte = '$cve_cliente'");
      $prov_cliente->execute();
      $proveedor_cliente = $prov_cliente->fetch()['ID_Proveedor'];

    }

    if($_SESSION['es_cliente'] == 2) 
    {
        $cliente_almacen_style = "style='display: none;'";
        $cve_proveedor = $_SESSION['cve_proveedor'];
        $usuario = $_SESSION["cve_usuario"];

          $alm_proveedor = \db()->prepare("SELECT a.id as id_almacen 
                                         FROM c_usuario u 
                                         LEFT JOIN c_almacenp a ON a.clave = u.cve_almacen
                                         WHERE u.cve_usuario = '$usuario' AND u.cve_proveedor = '$cve_proveedor'");
          $alm_proveedor->execute();
          $almacen_proveedor = $alm_proveedor->fetch()['id_almacen'];
          $id_almacen_existencias = $almacen_proveedor;
    }

  $folios_excel = \db()->prepare("SELECT COUNT(*) AS existe FROM t_configuraciongeneral WHERE cve_conf = 'mostrar_folios_excel_existencias' AND Valor = '1'");
  $folios_excel->execute();
  $mostrar_folios_excel_existencias = $folios_excel->fetch()['existe'];

  $formato_reporte = \db()->prepare("SELECT COUNT(*) AS existe FROM t_configuraciongeneral WHERE cve_conf = 'existencias_con_formato' AND Valor = '1'");
  $formato_reporte->execute();
  $mostrar_formato_reporte = $formato_reporte->fetch()['existe'];

  $exist_cajas = \db()->prepare("SELECT COUNT(*) AS existe FROM ts_existenciacajas WHERE Cve_Almac = $id_almacen_existencias");
  $exist_cajas->execute();
  $mostrar_exist_cajas = $exist_cajas->fetch()['existe'];

    if($almacen_proveedor == "" && $almacen_cliente == "") $id_almacen_lotes = $_SESSION['id_almacen'];
    else if($almacen_cliente != "") $id_almacen_lotes = $almacen_cliente;
    else $id_almacen_lotes = $almacen_proveedor;

    $lotes = new \Lotes\Lotes();
    $lotes_select = $lotes->getAllLS($id_almacen_lotes);
    $lotes_alternos_select = $lotes->getAllLote_Alterno($id_almacen_lotes);

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1 ");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

?>

<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<!--<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>-->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style>
    .bt{margin-right: 10px;}
    .btn-blue{
        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }
</style>

<?php 

    if(isset($_SESSION['id_proveedor']))
    {
        $cve_proveedor = $_SESSION['id_proveedor'];
        $cve_proveedor_login = 1;
    }


?>
    <input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
    <input type="hidden" id="almacen_proveedor" value="<?php echo $almacen_proveedor; ?>">
    <input type="hidden" id="cve_proveedor_login" value="<?php echo $cve_proveedor_login; ?>">
    <input type="hidden" id="almacen_cliente" value="<?php echo $almacen_cliente; ?>">
    <input type="hidden" id="cve_cliente" value="<?php echo $cve_cliente; ?>">
    <input type="hidden" id="proveedor_cliente" value="<?php echo $proveedor_cliente; ?>">
    <input type="hidden" id="mostrar_folios_excel_existencias" value="<?php echo $mostrar_folios_excel_existencias; ?>">
    <input type="hidden" id="instancia" value="<?php echo $instancia; ?>">
    <input type="hidden" id="mostrar_formato_reporte" value="<?php echo $mostrar_formato_reporte; ?>">

<div class="wrapper wrapper-content  animated " id="list">
    <h3>Existencia Ubicación*</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-3" <?php echo $cliente_almacen_style; ?>>
                            <div class="form-group">
                                <label for="email">Almacén</label>
                                <select name="almacen" id="almacen" class="chosen-select form-control">
                                    <option value="">Seleccione Almacén</option>
                                    <?php foreach( $model_almacen AS $almacen ): ?>
                                        <?php if($almacen->Activo == 1):?>
                                            <option value="<?php echo $almacen->id; ?>"><?php echo"($almacen->clave)". $almacen->nombre; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="zona">Zona de Almacenaje</label>
                                <select name="zona" id="zona" class="chosen-select form-control">
                                    <option value="">Seleccione Zona</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="articulo">Articulo</label>
                                <select name="articulo" id="articulo" class="chosen-select form-control">
                                    <option value="">Seleccione Articulo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="email">Lote|Serie</label>
                                <select name="lotes" id="lotes" class="chosen-select form-control">
                                    <option value="">Seleccione Lote|Serie</option>
                                    <?php foreach( $lotes_select AS $lote ): ?>
                                        <option value="<?php echo $lote->Lote; ?>"><?php echo $lote->Lote; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                              <div class="form-group">
                                  <label for="contenedor">Pallet|Contenedor</label>
                                  <select name="contenedor" id="contenedor" class="chosen-select form-control">
                                      <option value="">Seleccione contendor</option>
                                  </select>
                                  <br><br>
                                <label for="selectLP">LP</label>
                                <input name="selectLP" id="selectLP" class="form-control" placeholder="LP">
                                  <br>
                                <label for="factura_oc">Factura</label>
                                <input name="factura_oc" id="factura_oc" class="form-control" placeholder="Factura OC">

                              </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="proveedor">Empresa</label>
                                <select name="proveedor" id="proveedor" class="chosen-select form-control">
                                    <option value="">Seleccione Empresa</option>
                                </select>
                                <br><br>
                                <label for="zona">Productos Obsoletos</label>
                                <select name="art_obsoletos" id="art_obsoletos" class="form-control">
                                    <option value="2">Mostrar Todos</option>
                                    <option value="0">No</option>
                                    <option value="1">Si</option>
                                </select>
                                  <br>
                                <label for="proyecto_existencias">Proyecto</label>
                                <select class="form-control chosen-select" name="proyecto_existencias" id="proyecto_existencias">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaProyectos->getAllProyectos($_SESSION['id_almacen']) AS $a ): ?>
                                        <option value="<?php echo $a->Cve_Proyecto; ?>"><?php echo " ( ".$a->Cve_Proyecto." ) ".$a->Des_Proyecto; ?></option>
                                    <?php endforeach; ?>
                                </select>


                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="grupo">Grupo</label>
                                <select name="grupo" id="grupo" class="chosen-select form-control">
                                    <option value="">Seleccione Grupo</option>
                                </select>
                            </div>
                        <?php 
                        if($instancia == "welldex")
                        {
                        ?>
                            <label>Referencia Well: </label>
                            <input name="refWell" id="refWell" type="text" placeholder="" value="" class="form-control">
                        <?php 
                        }else
                        {
                        ?>
                            <div class="form-group">
                                <label for="clasificacion">Clasificación</label>
                                <select name="clasificacion" id="clasificacion" class="chosen-select form-control">
                                    <option value="">Seleccione Clasificación</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email">Lote Alterno</label>
                                <select name="lotes_alternos" id="lotes_alternos" class="chosen-select form-control">
                                    <option value="">Seleccione Lote Alterno</option>
                                    <?php foreach( $lotes_alternos_select AS $lote ): ?>
                                        <option value="<?php echo $lote->Lote; ?>"><?php echo $lote->Lote; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php 
                        }
                        ?>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="BL">BL</label>
                                <input name="BL" id="selectBL" class="form-control" placeholder="BL">
                            </div>

                        <?php 
                        if($instancia == "welldex")
                        {
                        ?>
                            <label>Pedimento: </label>
                            <input name="pedimentoW" id="pedimentoW" type="text" placeholder="" value="" class="form-control">
                        <?php 
                        }
                        ?>

                        <div class="form-group">
                            <label for="picking">Picking</label>
                            <select name="picking" id="picking" class="chosen-select form-control">
                                <option value="">Todos</option>
                                <option value="S">Si</option>
                                <option value="N">No</option>
                            </select>
                        </div>

                        </div>

                        <div class="col-lg-1">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <button id="search" name="singlebutton" class="btn btn-primary">Buscar</button>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <button id="boton_pdf" name="singlebutton" class="btn btn-primary">PDF</button>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <label for="email">&#160;&#160;</label>
                            <div class="form-group">
                                <a href="#" id="boton_excel" class="btn btn-primary">Excel</a>
                            </div>

                        </div>
                        <?php 
                        //if($_SESSION['cve_usuario'] == 'reinaldo' || $_SESSION['cve_usuario'] == 'wmsmaster')
                        //{
                        ?>
                        <div class="col-lg-3" style="float:right;">
                            <button id="enviar_email" class="btn btn-primary"><i class="fa fa-envelope"></i> Enviar Reporte por Email</button>
                        </div>
                        <?php 
                        //}
                        ?>

                    </div>
                    <div class="row" style="margin-top:15px">

                        <?php 
                        if($mostrar_exist_cajas > 0)
                        {
                        ?>
                        <div class="col-md-12" style="text-align: center;">
                            <div class="checkbox">
                                <label for="existencia_cajas" style="font-weight: 600;font-size: 14px; -moz-user-select: none; -webkit-user-select: none;-ms-user-select: none; user-select: none;">
                                <input type="checkbox" name="existencia_cajas" id="existencia_cajas" value="0">Ver Existencias con cajas</label>
                            </div>
                        </div>
                        <?php 
                        }
                        ?>

                        <div class="col-md-4">
                            <label>Total de Productos</label>
                            <input id="totalproductos" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                        </div>
                        <div class="col-md-4">
                            <label>Total de Unidades</label>
                            <input id="totalunidades" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                        </div>
                        <div class="col-md-4">
                            <label>Total de LP's</label>
                            <input id="totallp" type="text" value ="" class="form-control" style="text-align: center" disabled><br>
                            </div>
                    </div>
                </div>
            </div> 
            <div class="col-lg-2">
                <div class="checkbox" id="chb_asignar">
                    <label for="btn-asignarTodo">
                      <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Seleccionar Todo
                    </label>
                </div>
            </div>
            <?php 
            /*
            ?>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table id="example"  class="table table-hover table-striped no-margin">
                        <thead>
                            <div><label id="codigo_BL">Codigo BL:</label></div>
                            <tr>
                                <th scope="col" style="width: 80px !important; min-width: 80px !important;">Acciones</th>
                                <th>Código BL*</th>
                                <th>Picking</th>
                                <th>QA</th>
                                <th>Pallet|Contenedor</th>
                                <th>License Plate (LP)</th>
                                <th>Clave</th>
                                <th>Descripción</th>
                                <th>Lote</th>
                                <th>Caducidad</th>
                                <th>N. Serie</th>
                                <th>Total</th>
                                <th>RP</th>
                                <th>Cant QA</th>
                                <th>Obsoletos</th>
                                <th>Pallet</th>
                                <th>Caja</th>
                                <th>Piezas | Kgs</th>
                                <th>Disponible</th>
                                <th>Almacén</th>
                                <th>Zona de Almacenaje</th>
                                <th>Proveedor</th>
                                <th>Fecha de Ingreso</th>
                                <th>Folio OC</th>
                                <th>Costo Promedio</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <?php 
            */
            ?>

                <div class="ibox-content">
                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>
                </div>

            <div class="inbox">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <?php if(($cve_proveedor == '' || $cve_proveedor_login == 1) && $cve_cliente == '') { ?>
                                <button id="btn-asignar" onclick="asignar()" type="button" type="button" class="btn btn-m btn-primary" style="padding-right: 20px;">Enviar a QA | Cuarentena</button>
                                <?php } ?>
                                <br><br><br>
                                <label>Importe Total</label> 
                                <input id="totalPromedio" name="totalPromedio" type="text" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal inmodal" id="DetalleExistencia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 1000px;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="CerrarDetalle()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Detalle Existencia</h4>
                <br>
                <h3>BL: <span id="bl_exist"></span> | Clave: <span id="clave_exist"></span> | Lote/Serie: <span id="lote_exist"></span> | Caducidad: <span id="caducidad_exist"></span></h3>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <table class="table table-striped">
                        <tr>
                            <td><b>Total: </b></td>    <td><input type="text" class="form-control" readonly id="total_exist" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>Total Kg|LT: </b></td><td><input type="text" class="form-control" readonly id="total_kg_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>RP Pz|GNL: </b></td><td><input type="text" class="form-control" readonly id="rp_pz_exist" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>RP Kg|LT:</b></td>   <td><input type="text" class="form-control" readonly id="rp_kg_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Cant QA: </b></td>   <td><input type="text" class="form-control" readonly id="qa_exist" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>Obsoletos: </b></td> <td><input type="text" class="form-control" readonly id="obsoletos_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Pallet: </b></td>    <td><input type="text" class="form-control" readonly id="pallet_exist" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>Cajas: </b></td>     <td><input type="text" class="form-control" readonly id="cajas_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td><b>Piezas|Kgs: </b></td><td><input type="text" class="form-control" readonly id="piezas_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                        </tr>
                        <tr>
                            <td><b>Disponible Pz: </b></td><td><input type="text" class="form-control" readonly id="disp_pz_exist" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>Disponible Kg: </b></td><td><input type="text" class="form-control" readonly id="disp_kg_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Costo Promedio: </b></td><td><input type="text" class="form-control" readonly id="costo_prom_exist" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>Subtotal: </b></td>      <td><input type="text" class="form-control" readonly id="subtotal_exist" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td><b>Folio: </b></td><td><input type="text" class="form-control" readonly id="folio_oc" value="0" dir="rtl" style="background-color: #fff;"></td> 
                            <td><b>Factura: </b></td><td><input type="text" class="form-control" readonly id="factura_oc_detalle" value="0" dir="rtl" style="background-color: #fff;"></td>
                            <td><b>Fecha Ingreso: </b></td>      <td><input type="text" class="form-control" readonly id="fecha_ingreso" value="0" dir="rtl" style="background-color: #fff;"></td>
                        </tr>
                        <tr>
                            <td><b>Proyecto: </b></td><td style="display: flex;align-items: center;"><input type="text" class="form-control editar_proyecto" readonly id="proyecto" value="" dir="rtl" style="background-color: #fff;"><span id="editar_proyecto" class="editar_proyecto" style="cursor:pointer;margin-left: 10px;"><i class="fa fa-edit" alt="Editar Proyecto" title="Editar Proyecto"></i></span>
                                <input type="hidden" name="datos_proyecto" id="datos_proyecto" data-bl="" data-articulo="" data-lote="" data-lp="" data-proveedor="" data-almacen="">
                                <select class="form-control chosen-select" name="editar_proyecto_existencias" id="editar_proyecto_existencias" class="editar_proyecto_existencias" style="display: none;">
                                    <option value="">Seleccione</option>
                                    <?php foreach( $listaProyectos->getAllProyectos($_SESSION['id_almacen']) AS $a ): ?>
                                        <option value="<?php echo $a->Cve_Proyecto; ?>"><?php echo " ( ".$a->Cve_Proyecto." ) ".$a->Des_Proyecto; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span id="editar_proyecto_ok" class="editar_proyecto_existencias" style="cursor:pointer;margin-left: 10px;display: none;"><i class="fa fa-check" alt="Aceptar" title="Aceptar"></i></span>
                                <span id="editar_proyecto_NO" class="editar_proyecto_existencias" style="cursor:pointer;margin-left: 10px;display: none;"><i class="fa fa-times" alt="Cancelar" title="Cancelar"></i></span>



                            </td> 
                            <td></td><td></td>
                            <td></td><td></td>
                        </tr>
                    </table>
                </div>



            </div>
            <div class="modal-footer">
                <a href="#" onclick="CerrarDetalle()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="DetalleExistenciaLoading" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 1000px;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <h4 class="modal-title">Detalle Existencia</h4>
                <br>
                <h3>BL:  | Clave:  | Lote/Serie:  | Caducidad: </h3>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <div class="row" id="loadgif" style="text-align: center;padding: 15px; font-size: 16px;top: 15px;position: relative;">
                        <div style="width: 50px;height: 50px;background-image: url(/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                    </div>
                </div>



            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="DetalleExistenciaCajas" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" style="position: relative;/* height: 100%; */overflow-y: scroll;overflow-x: hidden;width: 1000px;height: 1000px;max-height: 80%;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header" id="modaltitle">
                <a href="#" onclick="CerrarDetalleCajas()"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></a>
                <h4 class="modal-title">Detalle de la Caja</h4>
                <br>
                <h3>BL: <span id="bl_cajas"></span> | Clave: <span id="clave_cajas"></span> | Lote/Serie: <span id="lote_cajas"></span> | Caducidad: <span id="caducidad_cajas"></span></h3>
            </div>
            <div class="modal-body">

                <div class="form-group" style="overflow-x:scroll;">
                        <div class="jqGrid_wrapper">
                            <table id="grid-table3"></table>
                            <div id="grid-pager3"></div>
                        </div>
                </div>



            </div>
            <div class="modal-footer">
                <a href="#" onclick="CerrarDetalleCajas()"><button type="button" class="btn btn-white" id="btnCancel">Cerrar</button></a>&nbsp;&nbsp;
                &nbsp;&nbsp;
            </div>
        </div>
    </div>
</div>

<!--Modal Enviar a QA | Cuarentena-->
<div class="modal fade" id="modal-asignar-motivo" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Enviar QA | Cuarentena</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="motivo" class="text-center">Motivos</label>
                            <select id="motivo_selector" class="form-control">
                                <option value="">Seleccione motivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button onclick="asignarmotivo()" id="asignarmotivo" class="btn btn-primary pull-right" type="button">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Guardar
                    </button>
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
<script src="/js/select2.js"></script>
<!-- Data picker -->
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="/js/plugins/dataTables/jquery.dataTables.min.js"></script>
<script src="/js/plugins/dataTables/dataTables.buttons.min.js"></script>
<script src="/js/plugins/dataTables/buttons.flash.min.js"></script>
<script src="/js/plugins/dataTables/jszip.min.js"></script>
<script src="/js/plugins/dataTables/pdfmake.min.js"></script>
<script src="/js/plugins/dataTables/vfs_fonts.js"></script>
<script src="/js/plugins/dataTables/buttons.html5.min.js"></script>
<script src="/js/plugins/dataTables/buttons.print.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/table2excel/jquery.table2excel.min.js"></script>

<div id="tabla_excel" style="display: none;">

</div>
<?php 
/*
?>
<table id="exportTable" style="display: none;" data-tableName="Existencias">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Country</th>
    </tr>
    <tr>
        <td>John Doe</td>
        <td>john@gmail.com</td>
        <td>USA</td>
    </tr>
    <tr>
        <td>Michael Addison</td>
        <td>michael@gmail.com</td>
        <td>UK</td>
    </tr>
    <tr>
        <td>Sam Farmer</td>
        <td>sam@gmail.com</td>
        <td>France</td>
    </tr>
</table>
<?php 
*/
?>
<script>
    $(document).ready(function()
    {
        $(function() 
        {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
        /**Busca y selecciona el almacen predeterminado para el usuario. */ 

        function almacenPrede()
        {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/almacenPredeterminado/index.php',
                data: {
                    idUser: '<?php echo $_SESSION["id_user"]?>',
                    action: 'search_almacen_pre'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                success: function(data) {
                    var alma = "";
                    if (data.success == true) 
                    {
                      alma = document.getElementById('almacen');
                    }

                    console.log("almacen_cliente = ", $("#almacen_cliente").val());
                    console.log("almacen_proveedor = ", $("#almacen_proveedor").val());
                    
                      setTimeout(function() {
                        if($("#almacen_cliente").val() == '' && $("#almacen_proveedor").val() == '')
                            alma.value = data.codigo.id;
                        else if($("#almacen_proveedor").val() != '')
                            alma.value = $("#almacen_proveedor").val();
                        else
                            alma.value = $("#almacen_cliente").val();

                          $(alma).trigger("chosen:updated");
                          $('#almacen').trigger('change');
                          $('#search').trigger('click');
                      }, 1000);
                },
                error: function(res){
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

//table();
/*
        $('#example').DataTable( {
            "processing": true,
            dom: 'Bfrtip',
            buttons:
            [
                {
                    extend: 'excelHtml5',
                    title: 'Existencias en ubicación'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Existencias en ubicación'
                }
            ],
            "bFilter": false,
            responsive: true,
            "language": 
            {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros - :(!!",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "Filtrar:",
                "sProcessing":       "Cargando...",
                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst":     "Primero",
                }
            },
      });
*/

        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
        $(".dt-buttons a.btn.btn-primary.btn-sm.bt").unbind();
        $('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind();
    });
  
    function totales()
    {
        console.log("cve_proveedor = ", $("#cve_proveedor").val());
        console.log("lp 2:", $("#selectLP").val());
        console.log("Almacen = ", ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())));
        $.ajax({
            url:"/api/reportes/lista/existenciaubica.php",
            type: "POST",
            data: {
                /*
                almacen: $("#almacen").val(),
                bl: $("#selectBL").val(),
                articulo: $("#articulo").val(),
                zona: $("#zona").val(),
                contenedor: $("#contenedor").val(),
                action:"totales"
                */
                almacen: ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())),
                proveedor: $("#proveedor").val(),
                existencia_cajas: ($("#existencia_cajas").is(':checked'))?(1):(0),
                cve_proveedor: $("#cve_proveedor").val(),
                cve_cliente: $("#cve_cliente").val(),
                picking: $("#picking").val(),
                grupo: $("#grupo").val(),
                clasificacion: $("#clasificacion").val(),
                bl: $("#selectBL").val(),
                lote_alterno: $("#lotes_alternos").val(),
                art_obsoletos: $("#art_obsoletos").val(),
                refWell: $("#refWell").val(),
                pedimentoW: $("#pedimentoW").val(),
                lp: $("#selectLP").val(),
                factura_oc: $("#factura_oc").val(),
                proyecto_existencias: $("#proyecto_existencias").val(),
                articulo: $("#articulo").val(),
                zona: $("#zona").val(),
                search: true,
                contenedor: $("#contenedor").val(),
                action: "totales"
            },
            success: function(data){
                console.log("SUCCESS: ", data);
                //console.log("SUCCESS: ", data.data[0]);
                console.log("productos = ", data.productos);
                console.log("unidades = ", data.unidades);
                //$("#totalproductos").val(data.data[0].productos);
                //$("#totalunidades").val(data.data[0].unidades);
                $("#totalproductos").val(data.productos);
                $("#totalunidades").val(data.unidades);
                $("#totallp").val(data.productos_con_lp_diferentes);
/*
                if(data.productos == undefined || data.unidades == undefined)
                {
                    console.log("Reanudar Totales");
                    totales();
                }
*/
            }, error: function(data){
                console.log("ERROR: ", data);
            }
        })
    }

    function table(search = false)
    {
        /*
        $('#example').DataTable( {
            "processing": true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Existencia en ubicación'
                },
                {
                    extend: 'pdfHtml5',
                      title: 'Existencia en ubicación'
                }
            ],
            responsive: true,
            "language": {
                "lengthMenu": "Mostrando _MENU_ registros",
                "zeroRecords": "Sin Registros - :(!!",
                "info": "Pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No Existen Registros",
                "infoFiltered": "(Filtrando de un total de _MAX_ registros)",
                "sSearch": "Filtrar:",
                "sProcessing":       "Cargando...",
                "oPaginate": {
                    "sNext": "Sig",
                    "sPrevious": "Ant",
                    "sLast": "Ultimo",
                    "sFirst":     "Primero",
                }
            },
            "processing": true,
            "bDestroy": true,
            "serverSide": true,
            "bFilter": false,
            "columns": [
                { "data": "acciones", "formatter": "imageFormat"},
                { "data": "codigo" },
                { "data": "tipo_ubicacion" },
                { "data": "QA" },
                { "data": "contenedor" },
                { "data": "LP" },
                { "data": "clave" },
                { "data": "descripcion" },
                { "data": "lote" },
                { "data": "caducidad" },
                { "data": "nserie" },
                { "data": "cantidad" , "className": "dt-right"},
                { "data": "RP" , "className": "dt-right"},
                { "data": "Prod_QA" , "className": "dt-right"},
                { "data": "Obsoletos" , "className": "dt-right"},
                { "data": "Pallet", "className": "dt-right" },
                { "data": "Caja", "className": "dt-right" },
                { "data": "Piezas", "className": "dt-right" },
                { "data": "Libre", "className": "dt-right" },
                { "data": "almacen" },
                { "data": "zona" },
                { "data": "proveedor" },
                { "data": "fecha_ingreso" },
                { "data": "folio_oc" },
                { "data": "costoPromedio", "className": "dt-right" },
                { "data": "subtotalPromedio", "className": "dt-right" }
            ],
            "ajax": {
                "url": "/api/reportes/lista/existenciaubica.php",
                "type": "GET",
                "data": {
                    "almacen" : $("#almacen").val(),
                    "proveedor": $("#proveedor").val(),
                    "cve_proveedor": $("#cve_proveedor").val(),
                    "bl" : $("#selectBL").val(),
                    "articulo" : $("#articulo").val(),
                    "zona" : $("#zona").val(),
                    "search": search,
                    "contenedor" : $("#contenedor").val(),
                    "action":"existenciaUbicacion",
                }//, success: function(data){console.log("Datos = ", data);}
            }
        });
        */

//**************************************************************************************************
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";
        console.log("lp 3:", $("#selectLP").val());

        setGridWidth(grid_selector);

        $(grid_selector).jqGrid({
            
            url:'/api/reportes/lista/existenciaubica.php',
            datatype: "json",
            shrinkToFit: false,
            height:'auto',
            postData: {
                    almacen: ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())),
                    proveedor: $("#proveedor").val(),
                    existencia_cajas: ($("#existencia_cajas").is(':checked'))?(1):(0),
                    cve_proveedor: $("#cve_proveedor").val(),
                    cve_cliente: $("#cve_cliente").val(),
                    picking: $("#picking").val(),
                    grupo: $("#grupo").val(),
                    clasificacion: $("#clasificacion").val(),
                    bl: $("#selectBL").val(),
                    lote_alterno: $("#lotes_alternos").val(),
                    art_obsoletos: $("#art_obsoletos").val(),
                    refWell: $("#refWell").val(),
                    pedimentoW: $("#pedimentoW").val(),
                    lp: $("#selectLP").val(),
                    factura_oc: $("#factura_oc").val(),
                    proyecto_existencias: $("#proyecto_existencias").val(),
                    lotes : $("#lotes").val(),
                    articulo : $("#articulo").val(),
                    zona : $("#zona").val(),
                    search: search,
                    contenedor : $("#contenedor").val(),
                    action:"existenciaUbicacion"
            },

            mtype: 'GET', //lilo
            colNames:['Detalle','Enviar QA', 'Folio OT', 'Referencia Well', 'Pedimento Well', 'Clas. ABC','Código BL*', 'Pallet|Contenedor', 'License Plate (LP)', 'Clas. ABC','Clave', 'CB. Pieza', 'CB. Caja', 'CB. Pallet','Clasificación','Descripción', 'Zona','Picking','QA', 'Grupo', 'Lote | Serie', 'Caducidad', 'Lote Alterno', 'Total', 'Total Kg|LT', 'U. Med','#Caja', /*'RP Pz|GNL','RP Kg|LT','Cant QA','Obsoletos','Pallet','Caja', 'Piezas | Kgs','Disponible Pz', 'Disponible Kg',*/ 'Fecha de Ingreso', 'Folio OC', /*'Costo Promedio', 'Subtotal',*/ 'Empresa', 'Proveedor','Almacén', 'Zona de Recepción', 'idy_ubica', 'id_proveedor', 'ntarima'],
            colModel:[
                {name: 'myac',index: '',width: 70,fixed: true,sortable: false,resize: false, align:"center",formatter: imageFormat},
                {name:'acciones',index:'acciones', width:80, editable:false, sortable:false, align:"center", hidden: <?php if(($cve_proveedor!= '' && $cve_proveedor_login == 0) || $cve_cliente != '') echo 'true'; else echo 'false'; ?>},
                {name:'folio_ot',index:'folio_ot', width:120, editable:false, sortable:false, align: 'left'},
                {name:'ref_well',index:'ref_well', width:120, editable:false, sortable:false, align: 'left', hidden: <?php if($instancia == 'welldex') echo "false";else echo "true"; ?>},
                {name:'pedim_well',index:'pedim_well', width:120, editable:false, sortable:false, align: 'left', hidden: <?php if($instancia == 'welldex') echo "false";else echo "true"; ?>},
                {name:'control_abc',index:'control_abc', width:80, editable:false, sortable:false, align: 'center', hidden: <?php if(($cve_proveedor!= '' && $cve_proveedor_login == 0) || $cve_cliente != '') echo 'true'; else echo 'false'; ?>},
                {name:'codigobl',index:'codigobl', width:110, editable:false, sortable:false},
                {name:'pallet_contenedor',index:'pallet_contenedor',width:150, editable:false, align:"justify", sorttype: "text"},
                {name:'licenseplate',index:'licenseplate',width:150,editable:false},
                {name:'clasif_abc',index:'clasif_abc', width:80, editable:false, sortable:false, align: 'center', hidden: <?php if(($cve_proveedor!= '' && $cve_proveedor_login == 0) || $cve_cliente != '') echo 'true'; else echo 'false'; ?>},
                {name:'clave_articulo',index:'clave_articulo',width:150,editable:false},
                {name:'codigo_barras_pieza',index:'codigo_barras_pieza',width:150,editable:false},
                {name:'codigo_barras_caja',index:'codigo_barras_caja',width:150,editable:false, hidden: true},
                {name:'codigo_barras_pallet',index:'codigo_barras_pallet',width:150,editable:false, hidden: true},
                {name:'clasificacion',index:'clasificacion',width:200,editable:false, hidden: <?php if(($cve_proveedor!= '' && $cve_proveedor_login == 0) || $cve_cliente != '') echo 'true'; else echo 'false'; ?>},
                {name:'descripcion',index:'descripcion',width:250,editable:false},
                {name:'zona_alm',index:'zona_alm', width:200, fixed:true, sortable:false, resize:false, hidden: <?php if(($cve_proveedor!= '' && $cve_proveedor_login == 0) || $cve_cliente != '') echo 'true'; else echo 'false'; ?>},
                {name:'picking',index:'picking', width:80, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'qa',index:'qa', width:50, fixed:true, sortable:false, resize:false, align:"center"},
                {name:'grupo',index:'grupo',width:200,editable:false, hidden: <?php if(($cve_proveedor!= '' && $cve_proveedor_login == 0) || $cve_cliente != '') echo 'true'; else echo 'false'; ?>},
                {name:'lote',index:'lote', width:110, editable:false, sorttype: "text"},
                {name:'caducidad',index:'caducidad', width:110, editable:false, align:"center"},
                {name:'lote_alterno',index:'lote_alterno', width:110, editable:false, sorttype: "text"},
                {name:'total',index:'total', width:100, editable:false, align:"right", sorttype: "int"},
                {name:'total_kg',index:'total_kg', width:100, editable:false, align:"right", sorttype: "int"},
                {name:'umed',index:'umed', width:120, editable:false, sortable:false, align: 'center'},
                {name:'ncaja',index:'ncaja', width:120, editable:false, sortable:false, align: 'right'},

                //{name:'rp_kgpz',index:'rp_kgpz', width:100, editable:false, align:"right", sorttype: "int"},
                //{name:'rp',index:'rp', width:100, editable:false, align:"right", sorttype: "int"},
                //{name:'cant_qa',index:'cant_qa', width:100, editable:false, sortable:false, align:"right"},
                //{name:'obsoletos',index:'obsoletos', width:100, editable:false, sortable:false, align:"right"},
                //{name:'pallet',index:'pallet', width:100, editable:false, sortable:false, align:"right"},
                //{name:'caja',index:'caja', width:100, editable:false, sortable:false, align:"right"},
                //{name:'piezas',index:'piezas', width:100, editable:false, sortable:false, align:"right"},
                //{name:'disponible',index:'disponible',width:100, editable:false, sortable:false, align:"right"},
                //{name:'disponible_kg',index:'disponible_kg',width:100, editable:false, sortable:false, align:"right"},

                {name:'fecha_ingreso',index:'fecha_ingreso', width:100, fixed:true, sortable:false, resize:false, align:"center", hidden: true},
                {name:'folio_oc',index:'folio_oc', width:80, fixed:true, sortable:false, resize:false, hidden: true},

                //{name:'costo_promedio',index:'costo_promedio', width:110, fixed:true, sortable:false, resize:false, align:"right"},
                //{name:'subtotal',index:'subtotal', width:100, fixed:true, sortable:false, resize:false, align:"right"},
                {name:'empresa',index:'empresa', width:200, fixed:true, sortable:false, resize:false},
                {name:'proveedor',index:'proveedor', width:200, fixed:true, sortable:false, resize:false},
                {name:'almacen',index:'almacen', width:200, fixed:true, sortable:false, resize:false},
                {name:'zona_recepcion',index:'zona_recepcion', width:200, fixed:true, sortable:false, resize:false, hidden: true},
                {name:'idy_ubica',index:'idy_ubica', width:200, fixed:true, sortable:false, resize:false, hidden: true},
                {name:'id_proveedor',index:'id_proveedor', width:200, fixed:true, sortable:false, resize:false, hidden: true},
                {name:'ntarima',index:'ntarima', width:200, fixed:true, sortable:false, resize:false, hidden: true},
            ],
            loadonce: false,
            rowNum:10,
            rowList:[10,30,40,50],
            pager: "#grid-pager",
            sortname: 'codigobl',
            sortorder: "desc",
            viewrecords: true,
            gridComplete: function(){
                //$("#grid-table").setGridParam({datatype: 'local'});
                
            },
            loadComplete:function(data){
              //var datos = data.rows;
              //for(var i=0; i<datos.length; i++)
              //{
              //  console.log("DEmo",datos[i].cell[1]);
              //  var id_ubicacion = datos[i].cell[1];
              //  traer_totales(id_ubicacion);
              //}
              console.log("*********************");
              console.log(data);

                console.log("productos = ", data.productos);
                console.log("unidades = ", data.unidades);
                //$("#totalproductos").val(data.data[0].productos);
                //$("#totalunidades").val(data.data[0].unidades);
                $("#totalproductos").val(data.productos);
                $("#totalunidades").val(data.unidades);
                $("#totallp").val(data.productos_con_lp_diferentes);

                if($("#permiso_registrar").val() == 0)
                {
                    $(".column-asignar, #btn-asignar, #chb_asignar").hide();
                }



              //$("#num_ubicaciones").text(data.records);
             // $("#codigo_BL_name").html("BL: "+data.bl);
            }, loadError: function(data){
                console.log("ERROR = ", data);
                if($("#permiso_registrar").val() == 0)
                {
                    $(".column-asignar, #btn-asignar, #chb_asignar").hide();
                }
            }
        });

        // Setup buttons
        $("#grid-table").jqGrid('navGrid', '#grid-pager',
                                {edit: false, add: false, del: false, search: false,reloadGridOptions: { fromServer: true }},
                                {height: 200, reloadAfterSubmit: true}
                               );

        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        function imageFormat(cellvalue, options, rowObject) 
        {
            var bl = rowObject[6];
            var clave = rowObject[10];
            var lote = rowObject[20];
            var caducidad = rowObject[21];
            var lp = rowObject[8];
            var ncaja = rowObject[26];
            var idy_ubica = rowObject[33];
            var id_proveedor = rowObject[34];
            var ntarima = rowObject[35];

            var html = '';
            html += '<a href="#" onclick="verDetalle(\'' + bl + '\', \'' + clave + '\', \'' + lote + '\', \'' + caducidad + '\', \'' + idy_ubica + '\', \'' + id_proveedor + '\', \'' + ntarima + '\')"><i class="fa fa-search" alt="Ver Detalle" title="Ver Detalle Existencia"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if(ncaja[0] == '(')
            {
                html += '<a href="#" onclick="verDetalleCajas(\'' + bl + '\', \'' + clave + '\', \'' + lote + '\', \'' + caducidad + '\', \'' + lp + '\', \'' + idy_ubica + '\')"><i class="fa fa-archive" alt="Ver Detalle Cajas" title="Ver Detalle Cajas"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            return html;
        }

//**************************************************************************************************

        totales();


    //}?
    }
    
        $(".dt-button.buttons-excel").attr('class','btn btn-primary btn-sm bt');
        $(".dt-button.buttons-pdf").attr('class', 'btn btn-danger btn-sm bt');
    
        $("#btn-asignarTodo").change(function(e) {

            if($(this).is(":checked"))
            {
                var articulo_qa = $("#articulo").val();
                var lotes_qa = $("#lotes").val();
                var grupo_qa = $("#grupo").val();
                var clasificacion_qa = $("#clasificacion").val();
                var contenedor_qa = $("#contenedor").val();
                var zona_qa = $("#zona").val();
                var bl_qa = $("#selectBL").val();
                var art_obsoletos_qa = $("#art_obsoletos").val();
                var lp_qa = $("#selectLP").val();

                if(articulo_qa == "" && lotes_qa == "" && grupo_qa == "" && contenedor_qa == "" && zona_qa == "" && bl_qa == "" && lp_qa == "" && art_obsoletos_qa == "2")
                {
                    swal("Error", "Debe Seleccionar Al menos 1 filtro para poder enviar varios artículos a cuarentena", "error");
                    $(this).prop("checked", false);
                    return;
                }
            }

            var val = $(e.currentTarget).prop('checked');
            $.each( $('.column-asignar'), function (index, item) {
                $(item).first().prop('checked', val) 
            });               
            $("#btn-asignar").show();

            if($(this).is(":checked"))
            {
                swal("Advertencia", "Cuando Envía a Cuarentena y Marca Seleccionar Todo, se envían todos los registros que cumplan con los filtros que tiene marcados en esta sesión. No importa si están seleccionados o no los registros en la tabla.", "warning");


            }

        });

//*******************************************************************************************************************
//*******************************************************************************************************************
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
            url:'/api/reportes/lista/existenciaubica.php',
            datatype: "local",
            shrinkToFit: false,
      height:'auto',
            mtype: 'POST',
            colNames:['Ubicacion', 'LP', 'Caja', 'Clave Articulo', 'Descripción','Lote', 'Caducidad', 'Piezas Por Caja'],
            colModel:[
                {name:'ubicacion',index:'ubicacion',width:150, editable:false, sortable:false},
                {name:'LP',index:'LP',width:150, editable:false, sortable:false},
                {name:'CJ',index:'CJ',width:250, editable:false, sortable:false},
                {name:'clave',index:'clave',width:150, editable:false, sortable:false},
                {name:'descripcion',index:'descripcion',width:250, editable:false, sortable:false},
                {name:'lote',index:'lote',width:150, editable:false, sortable:false},
                {name:'caducidad',index:'caducidad',width:150, editable:false, sortable:false, align:'center'},
                {name:'cantidad',index:'cantidad', width:150, editable:false, sortable:false, align:'right'}
            ],
            rowNum:10,
            rowList:[10,20,30],
            pager: pager_selector,
            sortname: 'cve_gpoart',
            viewrecords: true,
            sortorder: "desc",
            loadComplete: function(data){console.log("Detalle Caja Data = ", data);},
            ondblClickRow: function (rowid, iRow,iCol) {
                var row_id = $(grid_selector).getGridParam('selrow');
                $(grid_selector).editRow(row_id, true);
            }
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

//*******************************************************************************************************************
//*******************************************************************************************************************
        function traer_BL()
        {
          $.ajax({
                url: "/api/reportes/lista/existenciaubica.php",
                 type: "POST",
                    data: {
                      almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                      action:"traer_BL"
                    },
                    success: function (data) {

                      $("#codigo_BL").text("Codigo BL:"+" "+data);
                    }
            });
        }
        traer_BL();

        function generar_excel()
        {
          $.ajax({
                url: "/api/reportes/lista/existenciaubica.php",
                 type: "GET",
                    data: {
                    almacen: ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())),
                    proveedor: $("#proveedor").val(),
                    existencia_cajas: ($("#existencia_cajas").is(':checked'))?(1):(0),
                    cve_proveedor: $("#cve_proveedor").val(),
                    cve_cliente: $("#cve_cliente").val(),
                    picking: $("#picking").val(),
                    grupo: $("#grupo").val(),
                    clasificacion: $("#clasificacion").val(),
                    bl: $("#selectBL").val(),
                    lote_alterno: $("#lotes_alternos").val(),
                    art_obsoletos: $("#art_obsoletos").val(),
                    refWell: $("#refWell").val(),
                    pedimentoW: $("#pedimentoW").val(),
                    lp: $("#selectLP").val(),
                    factura_oc: $("#factura_oc").val(),
                    proyecto_existencias: $("#proyecto_existencias").val(),
                    lotes : $("#lotes").val(),
                    articulo : $("#articulo").val(),
                    zona : $("#zona").val(),
                    search: search,
                    contenedor : $("#contenedor").val(),
                    reporte_excel: true,
                    action:"existenciaUbicacion"
                    },
                    success: function (data) {

                      $("#codigo_BL").text("Codigo BL:"+" "+data);
                    }
            });
        }

        $("#editar_proyecto").click(function(e)
        {
            $(".editar_proyecto").hide();
            $(".editar_proyecto_existencias, #editar_proyecto_existencias_chosen").show();
        });

        $("#editar_proyecto_NO").click(function(e)
        {
            $(".editar_proyecto").show();
            $(".editar_proyecto_existencias, #editar_proyecto_existencias_chosen").hide();

        });

        $("#editar_proyecto_ok").click(function(e)
        {
            $(".editar_proyecto").show();
            $(".editar_proyecto_existencias, #editar_proyecto_existencias_chosen").hide();

            var idy_ubica_pry = $("#datos_proyecto").data("idy_ubica");
            var articulo_pry = $("#datos_proyecto").data("articulo");
            var lote_pry = $("#datos_proyecto").data("lote");
            var ntarima_pry = $("#datos_proyecto").data("ntarima");
            var id_proveedor_pry = $("#datos_proyecto").data("id_proveedor");
            var almacen_pry = $("#datos_proyecto").data("almacen");

            console.log("idy_ubica_pry = ", idy_ubica_pry);
            console.log("articulo_pry = ", articulo_pry);
            console.log("lote_pry = ", lote_pry);
            console.log("ntarima_pry = ", ntarima_pry);
            console.log("id_proveedor_pry = ", id_proveedor_pry);
            console.log("almacen_pry = ", almacen_pry);
            console.log("proyecto = ", $("#editar_proyecto_existencias").val());

            //return;
            
              $.ajax({
                    url: "/api/reportes/lista/existenciaubica.php",
                     type: "POST",
                        data: {
                        idy_ubica_pry: idy_ubica_pry,
                        articulo_pry: articulo_pry,
                        lote_pry: lote_pry,
                        ntarima_pry: ntarima_pry,
                        id_proveedor_pry: id_proveedor_pry,
                        almacen_pry: almacen_pry,
                        proyecto: $("#editar_proyecto_existencias").val(),
                        action:"EditarProyecto"
                        },
                        success: function (data) {

                            console.log("EDIT PROYECTO: ", data);
                            $("#proyecto").val($("#editar_proyecto_existencias").val());
                        }
                });

        });

        $("#existencia_cajas").click(function(e)
        {
            $("#search").click();
        });

        //Excel
        $("#boton_excelX").click(function(e)
        {
            //existenciaubicacion/exportar_excel?almacen=3&proveedor=&zona=&articulo=&bl=&art_obsoletos=&contenedor=&cve_proveedor=&cve_cliente=&grupo=&clasificacion=&lp=&refWell=&pedimentoW=&picking=
            var almac_ex = ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val()));
          //  $(this).attr("href", "/existenciaubicacion/exportar_excel?almacen="+almac_ex+"&proveedor="+$("#proveedor").val()+"&zona="+$("#zona").val()+"&articulo="+$("#articulo").val()+"&bl="+$("#selectBL").val()+"&art_obsoletos="+$("#art_obsoletos").val()+"&contenedor="+$("#contenedor").val()+"&cve_proveedor="+$("#cve_proveedor").val()+"&cve_cliente="+$("#cve_cliente").val()+"&grupo="+$("#grupo").val()+"&clasificacion="+$("#clasificacion").val()+"&lp="+$("#selectLP").val()+"&refWell="+$("#refWell").val()+"&pedimentoW="+$("#pedimentoW").val()+"&picking="+$("#picking").val());
          let href = "/existenciaubicacion/exportar_excel?almacen="+almac_ex+"&proveedor="+$("#proveedor").val()+"&zona="+$("#zona").val()+"&articulo="+$("#articulo").val()+"&bl="+$("#selectBL").val()+"&art_obsoletos="+$("#art_obsoletos").val()+"&contenedor="+$("#contenedor").val()+"&cve_proveedor="+$("#cve_proveedor").val()+"&cve_cliente="+$("#cve_cliente").val()+"&grupo="+$("#grupo").val()+"&clasificacion="+$("#clasificacion").val()+"&lp="+$("#selectLP").val()+"&refWell="+$("#refWell").val()+"&pedimentoW="+$("#pedimentoW").val()+"&picking="+$("#picking").val();
          console.log("href: ", href);
          $this.attr("href", href);
            //exportTableToExcel('tblData', 'members-data');

            //exportReportToExcel();

/*
                    //var table = $(this).prev('.exportTable');
                    var table = $('#exportTable');
                    console.log(table);
                    if(table && table.length){
                        //var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
                        $(table).table2excel({
                            //exclude: ".noExl",
                            name: "Existencias Ubicacion",
                            filename: "ExistenciasUbicacion.xlsx",//+ new Date().toISOString().replace(/[\-\:\.]/g, "")
                            fileext: ".xlsx",
                            exclude_img: true,
                            exclude_links: true,
                            exclude_inputs: true,
                            preserveColors: false //preserveColors
                        });
                    }
*/

            //window.open('data:application/vnd.ms-excel,' + encodeURIComponent($('#tblData').html()));
            //e.preventDefault();


  /*
          var form = document.createElement("form"),
              input1 = document.createElement("input"),
              input2 = document.createElement("input"),
              input3 = document.createElement("input"),
              input4 = document.createElement("input"),
              input5 = document.createElement("input");
          input1.setAttribute('name', 'nofooternoheader');
          input1.setAttribute('value', 'true');
          input2.setAttribute('name', 'almacen');
          input2.setAttribute('value', document.getElementById("almacen").value);
          input3.setAttribute('name', 'zona');
          input3.setAttribute('value', document.getElementById("zona").value);
          input4.setAttribute('name', 'articulo');
          input4.setAttribute('value', document.getElementById("articulo").value);
          input5.setAttribute('name', 'action');
          input5.setAttribute('value', 'exportExcelExistenciaUbica');
          form.setAttribute('action', '/api/reportes/lista/existenciaubica.php');
          form.setAttribute('method', 'post');
          form.setAttribute('target', '_blank');
          form.appendChild(input1);
          form.appendChild(input2);
          form.appendChild(input3);
          form.appendChild(input4);
          form.appendChild(input5);
          document.body.appendChild(form);
          form.submit();
*/
        });

    function setGridWidth(grid_selector){
        
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() - 60 );
        });

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
    }

    function enviar_email(enlace, correos, usuarios, almacen, proveedor, existencia_cajas, lote, cve_proveedor, cve_cliente, zona, articulo, bl, art_obsoletos, refWell, pedimentoW, lp, factura_oc, proyecto_existencias, grupo, clasificacion, mostrar_folios_excel_existencias, contenedor)
    {
        console.log("******************************");
        console.log("usuarios", usuarios);
        console.log("correos", correos);
        console.log("enlace", enlace);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                enlace: enlace,
                correos: correos,
                usuario: usuarios,
                almacen: almacen,
                proveedor: proveedor,
                existencia_cajas: existencia_cajas,
                lote: lote,
                cve_proveedor: cve_proveedor,
                cve_cliente: cve_cliente,
                zona: zona,
                articulo: articulo,
                bl: bl,
                art_obsoletos: art_obsoletos,
                refWell: refWell,
                pedimentoW: pedimentoW,
                lp: lp,
                factura_oc: factura_oc,
                proyecto_existencias: proyecto_existencias,
                grupo: grupo,
                clasificacion: clasificacion,
                mostrar_folios_excel_existencias: mostrar_folios_excel_existencias,
                contenedor: contenedor,
                action: 'email_existencias'
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

        $("#enviar_email").click(function()
        {
            //resuelvo el caso de cual email voy a enviar:

            var almacen = ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val()));
            var proveedor = $("#proveedor").val();
            var existencia_cajas = ($("#existencia_cajas").is(':checked'))?(1):(0);
            var lote = $("#lotes").val();
            var cve_proveedor = $("#cve_proveedor").val();
            var cve_cliente = $("#cve_cliente").val();
            var zona = $("#zona").val();
            var articulo = $("#articulo").val();
            var bl = $("#selectBL").val();
            var art_obsoletos = $("#art_obsoletos").val();
            var refWell = $("#refWell").val();
            var pedimentoW = $("#pedimentoW").val();
            var lp = $("#selectLP").val();
            var factura_oc = $("#factura_oc").val();
            var proyecto_existencias = $("#proyecto_existencias").val();
            var grupo = $("#grupo").val();
            var clasificacion = $("#clasificacion").val();
            var mostrar_folios_excel_existencias = $("#mostrar_folios_excel_existencias").val();
            var contenedor = $("#contenedor").val();

            var caso = 4;
            if(proveedor == '' && proyecto_existencias == '')
            {
                console.log("Caso 1: Envio a correo de la empresa firmada");
                caso = 1;
            }
            else if(proveedor != '')
            {
                console.log("Caso 2: Envio a correo del cliente seleccionado");
                caso = 2;
            }
            else if(proyecto_existencias != '')
            {
                console.log("Caso 3: Envio a correo de los clientes pertenecientes al proyecto");
                caso = 3;
            }
            else
            {
                console.log("Caso 4: No hay correos disponibles para enviar");
            }

            //var enlace = "/existenciaubicacion/exportar_excel?almacen="+almacen+"&proveedor="+proveedor+"&existencia_cajas="+existencia_cajas+"&lote="+lote+"&cve_proveedor="+cve_proveedor+"&cve_cliente="+cve_cliente+"&zona="+zona+"&articulo="+articulo+"&bl="+bl+"&art_obsoletos="+art_obsoletos+"&refWell="+refWell+"&pedimentoW="+pedimentoW+"&lp="+lp+"&factura_oc="+factura_oc+"&proyecto_existencias="+proyecto_existencias+"&grupo="+grupo+"&clasificacion="+clasificacion+"&mostrar_folios_excel_existencias="+mostrar_folios_excel_existencias+"&contenedor="+contenedor;

            //var enlace = "/api/koolreport/excel/reporte_existencias/export.php?almacen="+almacen+"&proveedor="+proveedor+"&existencia_cajas="+existencia_cajas+"&lote="+lote+"&cve_proveedor="+cve_proveedor+"&cve_cliente="+cve_cliente+"&zona="+zona+"&articulo="+articulo+"&bl="+bl+"&art_obsoletos="+art_obsoletos+"&refWell="+refWell+"&pedimentoW="+pedimentoW+"&lp="+lp+"&factura_oc="+factura_oc+"&proyecto_existencias="+proyecto_existencias+"&grupo="+grupo+"&clasificacion="+clasificacion+"&mostrar_folios_excel_existencias="+mostrar_folios_excel_existencias+"&contenedor="+contenedor;

            var enlace = "/api/reportes/update?action=existenciaUbicacion&almacen="+almacen+"&proveedor="+proveedor+"&existencia_cajas="+existencia_cajas+"&lote="+lote+"&cve_proveedor="+cve_proveedor+"&cve_cliente="+cve_cliente+"&zona="+zona+"&articulo="+articulo+"&bl="+bl+"&art_obsoletos="+art_obsoletos+"&refWell="+refWell+"&pedimentoW="+pedimentoW+"&lp="+lp+"&factura_oc="+factura_oc+"&proyecto_existencias="+proyecto_existencias+"&grupo="+grupo+"&clasificacion="+clasificacion+"&mostrar_folios_excel_existencias="+mostrar_folios_excel_existencias+"&contenedor="+contenedor;

            console.log("Enlace = ", enlace);

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    //enlace: enlace,
                    //folio: folio,
                    //usuario: usuario_destinatario_email,
                    proveedor: proveedor,
                    proyecto_existencias: proyecto_existencias,
                    caso: caso,
                    cve_cia: <?php echo $_SESSION['cve_cia']; ?>,
                    action: 'get_correos_existencias'
                },
                beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                },
                url: '/api/emails/enviar.php',
                success: function(data) {
                        console.log("Correos a Enviar", data);
                        if(caso == 1)
                        {
                            var datos = data.data;
                            console.log("Correos a Enviar 2", datos);
                            var em = datos.split(";;;;;");
                            console.log("Correos a Enviar 3", em);

                            if(em[0] == 'formato_email_incorrecto')
                            {
                                swal("Error", "El formato de email registrado en la compañia es incorrecto ("+em[1]+")", "error");
                                return;
                            }
                            else
                            {
                                var msj = "Contacto: "+em[0]+"\n"+"Correo: "+em[1];
                                swal({
                                    title: "Correos a Enviar:",
                                    text: msj,
                                    type: "warning",

                                    cancelButtonText: "Cancelar",
                                    cancelButtonColor: "#14960a",
                                    showCancelButton: true,

                                    confirmButtonColor: "#55b9dd",
                                    confirmButtonText: "Enviar",
                                    closeOnConfirm: true
                                }, function() {
                                    //ReloadGrid();
                                    //window.location.reload();
                                    console.log("Enviar correo: ", em[1]);
                                    enviar_email(enlace, em[1], em[0], almacen, proveedor, existencia_cajas, lote, cve_proveedor, cve_cliente, zona, articulo, bl, art_obsoletos, refWell, pedimentoW, lp, factura_oc, proyecto_existencias, grupo, clasificacion, mostrar_folios_excel_existencias, contenedor);
                                    setTimeout(function(){
                                        swal("Correo Enviado", "El Reporte ha sido enviado a: "+em[1]+"", "success");
                                    },500);
                                    
                                });
                            }
                        }
                        else //if(caso == 2)
                        {
                            var datos = data.data;
                            console.log("Correos a Enviar 2", datos);
                            var em = datos.split(";;;;;");
                            console.log("Correos a Enviar 3", em);

                            if(em[0] == 'formato_email_incorrecto')
                            {
                                swal("Error", "El formato de email registrado en la compañia es incorrecto ("+em[1]+")", "error");
                                return;
                            }
                            else
                            {
                                var contactos_arr = data.contactos;
                                var correos_arr = data.correos;

                                var msj = "", max_mostrar = 4;
                                for(var i = 0; i < correos_arr.length; i++)
                                {
                                    if(i == max_mostrar)
                                    {
                                        msj += "\n\n+"+(correos_arr.length-max_mostrar)+" correos restantes a enviar ...";
                                        break;
                                    }
                                    msj += "Contacto: "+contactos_arr[i]+"\n"+"Correo: "+correos_arr[i]+"\n\n";
                                }


                                swal({
                                    title: "Correos a Enviar:",
                                    text: msj,
                                    type: "warning",

                                    cancelButtonText: "Cancelar",
                                    cancelButtonColor: "#14960a",
                                    showCancelButton: true,

                                    confirmButtonColor: "#55b9dd",
                                    confirmButtonText: "Enviar",
                                    closeOnConfirm: true
                                }, function() {
                                    //ReloadGrid();
                                    //window.location.reload();
                                    console.log("Enviar correos: ", correos_arr);
                                    enviar_email(enlace, correos_arr, contactos_arr, almacen, proveedor, existencia_cajas, lote, cve_proveedor, cve_cliente, zona, articulo, bl, art_obsoletos, refWell, pedimentoW, lp, factura_oc, proyecto_existencias, grupo, clasificacion, mostrar_folios_excel_existencias, contenedor);
                                    setTimeout(function(){
                                        swal("Correo(s) Enviado(s)", "El Reporte ha sido enviado", "success");
                                    },500);
                                });
                            }

                        }
                        //fillSelectZona(data.codigo.id);
                },
                error: function(data){
                    console.log("ERROR: Correos a Enviar", data);
                }
            });


        });

        //PDF
        //$('.dt-buttons a.btn.btn-danger.btn-sm.bt').unbind().bind('click', function(e)
        $('#boton_excel').click(function(e)
        {
            console.log("#almacen = ", $("#almacen").val());
            console.log("#proveedor = ", $("#proveedor").val());
            console.log("#zona = ", $("#zona").val());
            console.log("#articulo = ", $("#articulo").val());
            console.log("#selectBL = ", $("#selectBL").val());
            console.log("#art_obsoletos = ", $("#art_obsoletos").val());
            console.log("#contenedor = ", $("#contenedor").val());
            console.log("#mostrar_folios_excel_existencias = ", $("#mostrar_folios_excel_existencias").val());
            console.log("#factura_oc = ", $("#factura_oc").val());


            $("#tabla_excel").empty();
            $.ajax({
                type: "POST",
                url: "/api/reportes/update/index.php",
                data: {
                    "action": "existenciaUbicacion",
                    "almacen": ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())),
                    "proveedor": $("#proveedor").val(),
                    "existencia_cajas": ($("#existencia_cajas").is(':checked'))?(1):(0),
                    "lote": $("#lotes").val(),
                    "cve_proveedor": $("#cve_proveedor").val(),
                    "cve_cliente": $("#cve_cliente").val(),
                    "zona" : $("#zona").val(),
                    "articulo" : $("#articulo").val(),
                    "bl": $("#selectBL").val(),
                    "lote_alterno": $("#lotes_alternos").val(),
                    "art_obsoletos": $("#art_obsoletos").val(),
                    "refWell": $("#refWell").val(),
                    "pedimentoW": $("#pedimentoW").val(),
                    "lp": $("#selectLP").val(),
                    "factura_oc": $("#factura_oc").val(),
                    "proyecto_existencias": $("#proyecto_existencias").val(),
                    "grupo": $("#grupo").val(),
                    "clasificacion": $("#clasificacion").val(),
                    "mostrar_folios_excel_existencias": $("#mostrar_folios_excel_existencias").val(),
                    "contenedor": $("#contenedor").val()

                },
                success: function(data, textStatus, xhr)
                {
                    //console.log("Reporte Existencia Excel SQL = ", data);
                    var data = JSON.parse(data).data;
                    //console.log("Reporte Existencia Excel= ", data);
                    //return;
                    if(data.length == 0)
                    {
                        swal("Error", "No hay datos Disponibles para estos filtros", "error");
                        return;
                    }
                    
                    //return;
                    //var content_wrapper = document.createElement('div');
                    //var table = document.createElement('table');
                    //table.style.width = "100%";
                    //table.style.borderSpacing = "0";
                    //table.style.borderCollapse = "collapse";
                    //var thead = document.createElement('thead');
                    //var tbody = document.createElement('tbody');
                    var head_content = '';
                    //head_content += `
                    //    <tr><th colspan="9">${data[0][0]}</th></tr>
                    //    <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
                    //`;
/*
<th style="border: 1px solid #ccc; font-size: 11px;">RP</th>
<th style="border: 1px solid #ccc; font-size: 11px;">Prod_QA</th>
<th style="border: 1px solid #ccc; font-size: 11px;">Obsoletos</th>
*/

        if($("#existencia_cajas").is(':checked'))
        head_content += `
        <table id="exportTable" style="display: none;" data-tableName="Existencias">
            <tr>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 70px; font-size: 11px;">Codigo BL</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">License Plate (LP)</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Caja</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clave</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 150px; font-size: 11px;">Descripcion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Lote | Serie</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Caducidad</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Piezas por Caja</th>
            </tr>
        `;
        else if($("#instancia").val() == 'welldex')
        head_content += `
        <table id="exportTable" style="display: none;" data-tableName="Existencias">
            <tr>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 70px; font-size: 11px;">Codigo BL</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Pallet|Cont</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">License Plate (LP)</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clave</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">CB Pieza</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clasificacion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 150px; font-size: 11px;">Descripcion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Lote | Serie</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Caducidad</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Unidad Medida</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 50px; font-size: 11px;">Total</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 50px; font-size: 11px;">RP</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 30px; font-size: 11px;">Prod QA</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Disponible</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Fecha Ingreso</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Grupo</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Proveedor</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Referencia Well</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Pedimento Well</th>
            </tr>
        `;
        else if($("#mostrar_folios_excel_existencias").val() != '0')
        head_content += `
        <table id="exportTable" style="display: none;" data-tableName="Existencias">
            <tr>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 70px; font-size: 11px;">Codigo BL</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Pallet|Cont</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">License Plate (LP)</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clave</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clave Alterna</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">CB Pieza</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clasificacion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 150px; font-size: 11px;">Descripcion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Lote | Serie</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Lote Alterno</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Caducidad</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Unidad Medida</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 50px; font-size: 11px;">Total</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 50px; font-size: 11px;">RP</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 30px; font-size: 11px;">Prod QA</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Disponible</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Costo Unitario</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Fecha Ingreso</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Folio OC</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Proyecto</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Grupo</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Proveedor</th>
            </tr>
        `;
        else 
                head_content += `
        <table id="exportTable" style="display: none;" data-tableName="Existencias">
            <tr>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 70px; font-size: 11px;">Codigo BL</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Pallet|Cont</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">License Plate (LP)</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clave</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clave Alterna</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">CB Pieza</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Clasificacion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 150px; font-size: 11px;">Descripcion</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Lote | Serie</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Lote Alterno</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Caducidad</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;">Unidad Medida</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 50px; font-size: 11px;">Total</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 50px; font-size: 11px;">RP</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; width: 30px; font-size: 11px;">Prod QA</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Disponible</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Costo Unitario</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Fecha Ingreso</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Proyecto</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Grupo</th>
                <th style="font-weight: bolder; border: 1px solid #ccc; font-size: 11px;width: 80px;">Proveedor</th>
            </tr>
        `;


                    //<th style="font-weight: bolder; border: 1px solid #ccc; text-align: center;width: 80px; font-size: 11px;">Obsoletos</th>


                    //<th style="border: 1px solid #ccc; text-align: center;width: 80px; font-size: 11px;">Fecha Ingreso</th>
                    //<th style="border: 1px solid #ccc; font-size: 11px;width: 80px;">Folio OC</th>
                    var body_content = '';
                    var total = 0, a_texto = ($("#mostrar_formato_reporte").val() == '1')?('\u200C'):("");
                    if($("#instancia").val()=="avavex" || $("#instancia").val()=="asl" || $("#instancia").val()=="dicoisa" || $("#instancia").val()=="foam")
                        a_texto = "";
                    data.forEach(function(item, index)
                    {
/*
<td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.RP}</td>
<td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.Prod_QA}</td>
<td style="border: 1px solid #ccc; text-align: right; font-size: 10px;">${item.Obsoletos}</td>
*/








                        if($("#existencia_cajas").is(':checked'))
                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${a_texto}${item.ubicacion}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.LP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.Caja}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.cve_articulo}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.des_articulo}</td>
                                <td style="border: 1px solid #ccc; width: 150px; font-size: 10px;">${a_texto}${item.cve_lote}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.caducidad}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.PiezasXCaja}</td>
                            </tr>
                        `;

                        else if($("#instancia").val()=="welldex")
                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${item.codigo}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.contenedor}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.LP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.clave}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.codigo_barras_pieza}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.des_clasif}</td>
                                <td style="border: 1px solid #ccc; width: 150px; font-size: 10px;">${item.descripcion}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.lote}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.caducidad}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.um}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.QA=='No')?(parseFloat(item.cantidad)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.RP!=0)?(parseFloat(item.RP)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.QA=='Si')?(parseFloat(item.cantidad)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${parseFloat(item.cantidad)-parseFloat(item.RP)}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 30px; font-size: 10px;">${item.fecha_ingreso}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.des_grupo}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.proveedor}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.referencia_well}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.pedimento_well}</td>
                            </tr>
                        `;
                        else if($("#mostrar_folios_excel_existencias").val() != '0')
                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${a_texto}${item.codigo}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.contenedor}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.LP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.clave}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.clave_alterna}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.codigo_barras_pieza}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.des_clasif}</td>
                                <td style="border: 1px solid #ccc; width: 150px; font-size: 10px;">${a_texto}${item.descripcion}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.lote}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.lote_alterno}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.caducidad}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${a_texto}${item.um}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.QA=='No')?(parseFloat(item.cantidad)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.RP!=0)?(parseFloat(item.RP)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.QA=='Si')?(parseFloat(item.cantidad)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${parseFloat(item.cantidad)-parseFloat(item.RP)}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${item.costoUnitario}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 30px; font-size: 10px;">${item.fecha_ingreso}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${a_texto}${item.folio}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${a_texto}${item.proyecto}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${a_texto}${item.des_grupo}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${a_texto}${item.proveedor}</td>
                            </tr>
                        `;
                        else 
                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${a_texto}${item.codigo}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.contenedor}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.LP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.clave}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.clave_alterna}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.codigo_barras_pieza}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.des_clasif}</td>
                                <td style="border: 1px solid #ccc; width: 150px; font-size: 10px;">${a_texto}${item.descripcion}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.lote}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${a_texto}${item.lote_alterno}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.caducidad}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${a_texto}${item.um}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.QA=='No')?(parseFloat(item.cantidad)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.RP!=0)?(parseFloat(item.RP)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${(item.QA=='Si')?(parseFloat(item.cantidad)):('')}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${parseFloat(item.cantidad)-parseFloat(item.RP)}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${item.costoUnitario}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 30px; font-size: 10px;">${item.fecha_ingreso}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${a_texto}${item.proyecto}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${a_texto}${item.des_grupo}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${a_texto}${item.proveedor}</td>
                            </tr>
                        `;
                        //-parseFloat(item.Obsoletos)
                        //<td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${item.Obsoletos}</td>
                        //<td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.fecha_ingreso}</td>
                        //<td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.folio}</td>
                    });
                    body_content += `
                    </table>
                    `;
                    var tabla_excel = head_content+body_content;

                    $("#tabla_excel").append(tabla_excel);



                    var table = $('#exportTable');
                    //console.log(table);
                    if(table && table.length){
                        //var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
                        $(table).table2excel({
                            //exclude: ".noExl",
                            name: "Existencias",
                            filename: "ExistenciasUbicacion.xls",//+ new Date().toISOString().replace(/[\-\:\.]/g, "")
                            fileext: ".xls",
                            exclude_img: true,
                            exclude_links: true,
                            exclude_inputs: true,
                            preserveColors: false //preserveColors
                        });
                    }
                }, error: function(data)
                {
                    console.log("ERROR", data);
                }
            });
        });
    //}


        $('#boton_pdf').click(function(e)
        {
            var title = 'Reporte de Existencia por Ubicación';
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';
            var nombre_BL = '';
/*
            $.ajax({
                url: "/api/reportes/lista/existenciaubica.php",
                type: "POST",
                data: {
                    almacen : $("#almacen").val(),
                    action:"traer_BL"
                },
                success: function (data) {
                    nombre_BL = data;
                }
            });
*/
            //url: "/api/reportes/lista/existenciaubica.php",
            console.log("#almacen = ", $("#almacen").val());
            console.log("#proveedor = ", $("#proveedor").val());
            console.log("#zona = ", $("#zona").val());
            console.log("#articulo = ", $("#articulo").val());
            console.log("#selectBL = ", $("#selectBL").val());
            console.log("#art_obsoletos = ", $("#art_obsoletos").val());
            console.log("#contenedor = ", $("#contenedor").val());

            $.ajax({
                type: "POST",
                url: "/api/reportes/update/index.php",
                data: {
                    "action": "existenciaUbicacion",
                    "almacen": ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                    "proveedor": $("#proveedor").val(),
                    "existencia_cajas": ($("#existencia_cajas").is(':checked'))?(1):(0),
                    "cve_proveedor": $("#cve_proveedor").val(),
                    "cve_cliente": $("#cve_cliente").val(),
                    "zona" : $("#zona").val(),
                    "articulo" : $("#articulo").val(),
                    "bl": $("#selectBL").val(),
                    "lote_alterno": $("#lotes_alternos").val(),
                    "art_obsoletos": $("#art_obsoletos").val(),
                    "refWell": $("#refWell").val(),
                    "pedimentoW": $("#pedimentoW").val(),
                    "lp": $("#selectLP").val(),
                    "factura_oc": $("#factura_oc").val(),
                    "proyecto_existencias": $("#proyecto_existencias").val(),
                    "grupo": $("#grupo").val(),
                    "clasificacion": $("#clasificacion").val(),
                    "contenedor": $("#contenedor").val()

                },
                success: function(data, textStatus, xhr)
                {
                    console.log("Reporte Existencia = ", data);
                    var data = JSON.parse(data).data;
                    if(data.length == 0)
                    {
                        swal("Error", "No hay datos Disponibles para estos filtros", "error");
                        return;
                    }
                    var content_wrapper = document.createElement('div');
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = '';
                    //head_content += `
                    //    <tr><th colspan="9">${data[0][0]}</th></tr>
                    //    <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
                    //`;
/*
<th style="border: 1px solid #ccc; font-size: 11px;">RP</th>
<th style="border: 1px solid #ccc; font-size: 11px;">Prod_QA</th>
<th style="border: 1px solid #ccc; font-size: 11px;">Obsoletos</th>
*/
                    head_content += `
                        <tr>
                            <th style="border: 1px solid #ccc; width: 70px; font-size: 11px;">Codigo BL</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Pallet|Cont</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">License Plate (LP)</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Clave</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Clasificación</th>
                            <th style="border: 1px solid #ccc; width: 150px; font-size: 11px;">Descripción</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Lote|Serie</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Caducidad</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;">Total</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Pallet</th>
                            <th style="border: 1px solid #ccc; width: 50px; font-size: 11px;">Caja</th>
                            <th style="border: 1px solid #ccc; width: 30px; font-size: 11px;">Piezas</th>
                            <th style="border: 1px solid #ccc; text-align: center;width: 80px; font-size: 11px;">Disponible</th>
                            <th style="border: 1px solid #ccc; font-size: 11px;width: 80px;">Grupo</th>
                        </tr>
                    `;
                    //<th style="border: 1px solid #ccc; text-align: center;width: 80px; font-size: 11px;">Fecha Ingreso</th>
                    //<th style="border: 1px solid #ccc; font-size: 11px;width: 80px;">Folio OC</th>
                    var body_content = '';
                    var total = 0;
                    data.forEach(function(item, index)
                    {
                        var subtotal = item.costoPromedio*item.cantidad;
                        total = total + subtotal;

                            //**************************************************
                            //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
                            //**************************************************
                            //Archivos donde se encuentra esta función:
                            //api\embarques\lista\index.php
                            //api\reportes\lista\existenciaubica.php
                            //api\reportes\lista\concentradoexistencia.php
                            //app\template\page\reportes\existenciaubica.php
                            //\Application\Controllers\EmbarquesController.php
                            //\Application\Controllers\InventarioController.php
                            //**************************************************
                            //clave busqueda: COLPCP
                            //**************************************************
                            var Pallet = 0, Caja = 0, Piezas = 0, cantidad_restante = 0, Lote_Serie = "";
                            var valor1 = 0, valor2 = 0;

                            if(item.piezasxcajas > 0)
                               valor1 = item.cantidad/item.piezasxcajas;

                            if(item.cajasxpallets > 0)
                               valor1 = valor1/item.cajasxpallets;
                           else
                               valor1 = 0;

                            Pallet = parseInt(valor1);

                            valor2 = 0;
                            cantidad_restante = item.cantidad - (Pallet*item.piezasxcajas*item.cajasxpallets);
                            if(!Number.isInteger(valor1) || valor1 == 0)
                            {
                                if(item.piezasxcajas > 0)
                                   valor2 = (cantidad_restante/item.piezasxcajas);
                            }
                            Caja = parseInt(valor2);

                            Piezas = 0;
                            if(item.piezasxcajas == 1) 
                            {
                                valor2 = 0; 
                                Caja = cantidad_restante;
                                Piezas = 0;
                            }
                            else if(item.piezasxcajas == 0 || item.piezasxcajas == "")
                            {
                                if(item.piezasxcajas == "") item.piezasxcajas = 0;
                                valor2 = 0; 
                                Caja = 0;
                                Piezas = cantidad_restante;
                            }
                            cantidad_restante = cantidad_restante - (Caja*item.piezasxcajas);

                            if(!Number.isInteger(valor2))
                            {
                                if(item.granel == 'S')
                                    Piezas = cantidad_restante.toFixed(parseInt($("#decimales_cantidad").val()));
                                else 
                                    Piezas = cantidad_restante;
                            }


                            if(item.control_lotes == 'S')
                                Lote_Serie = item.lote;
                            if(item.control_numero_series == 'S')
                                Lote_Serie = item.nserie;

                        //*****************************************************************************************
/*
<td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.RP}</td>
<td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.Prod_QA}</td>
<td style="border: 1px solid #ccc; text-align: right; font-size: 10px;">${item.Obsoletos}</td>
*/
                        body_content += `
                            <tr>
                                <td style="border: 1px solid #ccc; width: 70px; font-size: 10px;">${item.codigo}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.contenedor}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.LP}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.clave}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${item.des_clasif}</td>
                                <td style="border: 1px solid #ccc; width: 150px; font-size: 10px;">${item.descripcion}</td>
                                <td style="border: 1px solid #ccc; font-size: 10px;">${Lote_Serie}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;">${item.caducidad}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 80px; font-size: 10px;">${item.cantidad}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${Pallet}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 50px; font-size: 10px;">${Caja}</td>
                                <td style="border: 1px solid #ccc; text-align: right; width: 30px; font-size: 10px;">${Piezas}</td>
                                <td style="border: 1px solid #ccc; text-align: right;font-size: 10px;">${item.cantidad}</td>
                                <td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.des_grupo}</td>
                            </tr>
                        `;
                        //<td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.fecha_ingreso}</td>
                        //<td style="border: 1px solid #ccc; text-align: center; font-size: 10px;width: 80px;">${item.folio}</td>
                    });
                    body_content += `
                    `;

                    tbody.innerHTML = body_content;
                    thead.innerHTML = head_content;
                    table.appendChild(thead);
                    table.appendChild(tbody);
                    content_wrapper.appendChild(table);
                    content = content_wrapper.innerHTML;

                    //Creando formulario para ser enviado

                    var url = '<?php echo $_SERVER['HTTP_HOST']; ?>';
                    console.log("URL = ", url);
                    if(url.includes('avavex') && $("#proveedor").val() != "")
                    {
                        console.log("ESTOY EN AVAVEX");
                        title = $("#proveedor option:selected" ).text();
                        console.log("CIA = ", cia);
                    }

                    var form = document.createElement("form");
                    form.setAttribute("method", "post");
                    form.setAttribute("action", "/api/reportes/generar/pdf.php");
                    form.setAttribute("target", "_blank");

                    var input_content = document.createElement('input');
                    var input_title = document.createElement('input');
                    var input_cia = document.createElement('input');
                    input_content.setAttribute('type', 'hidden');
                    input_title.setAttribute('type', 'hidden');
                    input_cia.setAttribute('type', 'hidden');
                    input_content.setAttribute('name', 'content');
                    input_title.setAttribute('name', 'title');
                    input_cia.setAttribute('name', 'cia');
                    input_content.setAttribute('value', content);
                    input_title.setAttribute('value', title);
                    input_cia.setAttribute('value', cia);

                    form.appendChild(input_content);
                    form.appendChild(input_title);
                    form.appendChild(input_cia);

                    document.body.appendChild(form);
                    form.submit();
                }, error: function(data)
                {
                    console.log("ERROR", data);
                }
            });
        });


    $('#almacen').change(function(e) {
        var almacen= $(this).val();
        console.log("proveedor_almacen", almacen);

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                cve_almac : ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())),
                cve_proveedor: $("#cve_proveedor").val(),
                cve_cliente: $("#cve_cliente").val(),
                action : "getArticulosYZonasAlmacenConExistencia"
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) {

                console.log("OPTIONS = ", data);

                var options_articulos = $("#articulo");
                options_articulos.empty();
                options_articulos.append(new Option("Seleccione Artículo", ""));

                var options_contenedores = $("#contenedor");
                options_contenedores.empty();
                options_contenedores.append(new Option("Seleccione Pallet|Contenedor", ""));

                var options_zonas = $("#zona");
                options_zonas.empty();
                options_zonas.append(new Option("Seleccione Zona", ""));

                var options_proveedores = $("#proveedor");
                options_proveedores.empty();

                var options_grupo = $("#grupo");
                options_grupo.empty();
                options_grupo.append(new Option("Seleccione Grupo", ""));

                var options_clasificacion = $("#clasificacion");
                options_clasificacion.empty();
                options_clasificacion.append(new Option("Seleccione Clasificación", ""));

                console.log("proveedor_cliente", $("#proveedor_cliente").val());
                if($("#cve_proveedor").val() == '' && $("#proveedor_cliente").val() == '')
                    options_proveedores.append(new Option("Seleccione Empresa", ""));

                for (var i=0; i<data.articulos.length; i++)
                {
                    if(data.articulos[i].id_articulo && data.articulos[i].articulo)
                      options_articulos.append(new Option(data.articulos[i].id_articulo +" "+data.articulos[i].articulo, data.articulos[i].id_articulo));
                }
              

                for (var i=0; i<data.grupos.length; i++)
                {
                    if(data.grupos[i].id)
                      options_grupo.append(new Option(data.grupos[i].cve_grupo +" "+data.grupos[i].des_grupo, data.grupos[i].cve_grupo));
                }

                console.log("clasificacion.length = ", data.clasificacion.length);
                for (var i=0; i<data.clasificacion.length; i++)
                {
                    if(data.clasificacion[i].id)
                      options_clasificacion.append(new Option(data.clasificacion[i].cve_clasif +" "+data.clasificacion[i].des_clasif, data.clasificacion[i].cve_clasif));
                }

                for (var i=0; i<data.contenedores.length; i++)
                {
                    if(data.contenedores[i].Cve_Contenedor)
                      options_contenedores.append(new Option(data.contenedores[i].Cve_Contenedor));
                }

                for (var i=0; i<data.zonas.length; i++)
                {
                  if(data.zonas[i].clave && data.zonas[i].descripcion)
                    options_zonas.append(new Option(data.zonas[i].clave +" "+data.zonas[i].descripcion, data.zonas[i].clave));
                }
                options_zonas.append(new Option("RTS", "RTS"));
                options_zonas.append(new Option("RTM", "RTM"));

                for (var i=0; i<data.proveedores.length; i++)
                {
                  if(data.proveedores[i].proveedor)
                  {
                    proveedor = data.proveedores[i].proveedor.split("-");
                    //data.proveedores[i].proveedor
                    /*
                    if($("#cve_proveedor").val() != '' && proveedor[0] == $("#cve_proveedor").val())
                    {
                        options_proveedores.append(new Option(proveedor[1], proveedor[0]));
                        break;
                    }
                    else if($("#cve_proveedor").val() == '')
                    */

                        if($("#proveedor_cliente").val() == proveedor[0] && $("#proveedor_cliente").val() != '')
                        {
                            options_proveedores.append(new Option(proveedor[1], proveedor[0]));
                            break;
                        }
                        else if($("#proveedor_cliente").val() == '')
                            options_proveedores.append(new Option(proveedor[1], proveedor[0]));
                            
                  }

                }                
                //$("#proveedor").val($("#cve_proveedor").val());


                $("#articulo").trigger("chosen:updated");
                $("#contenedor").trigger("chosen:updated");
                $("#zona").trigger("chosen:updated");
                $("#proveedor").trigger("chosen:updated");
                $("#grupo").trigger("chosen:updated");
                $("#clasificacion").trigger("chosen:updated");
            }, error: function(data){
                console.log("ERROR SELECTS = ", data);
            }
        });
    });

    function calcularImporteExistencias()
    {
        console.log("lp 1:", $("#selectLP").val());
        $.ajax({
            type: "POST",
            dataType: "json",
            data: 
            {
                almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                articulo : $("#articulo").val(),
                lotes: $("#lotes").val(),
                zona: $("#zona").val(),
                proveedor: $("#proveedor").val(),
                existencia_cajas: ($("#existencia_cajas").is(':checked'))?(1):(0),
                bl: $("#selectBL").val(),
                lote_alterno: $("#lotes_alternos").val(),
                art_obsoletos: $("#art_obsoletos").val(),
                refWell: $("#refWell").val(),
                pedimentoW: $("#pedimentoW").val(),
                lp: $("#selectLP").val(),
                factura_oc: $("#factura_oc").val(),
                proyecto_existencias: $("#proyecto_existencias").val(),
                action : "calcularImporte"
            },
            beforeSend: function(x)
            { 
                if(x && x.overrideMimeType) 
                { 
                  x.overrideMimeType("application/json;charset=UTF-8"); 
                }
            },
            url: '/api/ubicaciones/update/index.php',
            success: function(data) 
            {
                console.log("calcularImporte = ", data);
                if (data.success == true)
                {
                  if (data.importe == null)
                  {
                    $("#totalPromedio").val("$"+" "+("0.00")); 
                  }
                    if (data.importe != null)
                    {
                      $("#totalPromedio").val("$"+" "+(data.importe)); 
                    }

                }
                else
                {
                  console.log("No Calculo el importe");
                }
            }, 
            error: function(data)
            {
                console.log("ERROR calcularImporte = ", data);
            }
        });
    }
  
      function verDetalle(bl, clave, lote, caducidad, idy_ubica, id_proveedor, ntarima)
    {
        $(".editar_proyecto").show();
        $("#editar_proyecto_existencias_chosen, .editar_proyecto_existencias").hide();
        $(".editar_proyecto_existencias").trigger("chosen:updated");
        console.log("DetalleExistencia");
        console.log("bl :", bl," - articulo :", clave, " - lote : ", lote, " - almacen:", ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()), " - zona: ", $("#zona").val());

        $("#datos_proyecto").data("idy_ubica", idy_ubica);
        $("#datos_proyecto").data("articulo", clave);
        $("#datos_proyecto").data("lote", lote);
        $("#datos_proyecto").data("ntarima", ntarima);
        $("#datos_proyecto").data("id_proveedor", id_proveedor);
        $("#datos_proyecto").data("almacen", ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()));

        $('#DetalleExistenciaLoading').show();

        
       $.ajax({
            url: "/api/reportes/lista/existenciaubica.php",
            type: "POST",
            dataType: "json",
            data: {
                bl : bl,
                articulo : clave,
                lote : lote,
                almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                zona: $("#zona").val(),
                action:"DetalleExistencia"
            },
            success: function(data) {
                console.log("DetalleExistencia = ", data);

                $('#DetalleExistenciaLoading').hide();

                $("#total_exist").val(data.cantidad);
                $("#total_kg_exist").val(data.cantidad_kg);
                $("#rp_pz_exist").val(data.peso_rp);
                $("#rp_kg_exist").val(data.RP);
                $("#qa_exist").val(data.Prod_QA);
                $("#obsoletos_exist").val(data.Obsoletos);
                $("#pallet_exist").val(data.Pallet);
                $("#cajas_exist").val(data.Caja);
                $("#piezas_exist").val((data.Piezas).toFixed(parseInt($("#decimales_cantidad").val())));
                $("#disp_pz_exist").val(data.disponible_pz);
                $("#disp_kg_exist").val(data.disponible_kg);
                $("#costo_prom_exist").val(data.costoPromedio);
                $("#subtotal_exist").val(data.subtotalPromedio);
                $("#subtotal_exist").val(data.subtotalPromedio);
                $("#folio_oc").val(data.folio);
                $("#factura_oc_detalle").val(data.factura_oc);
                $("#fecha_ingreso").val(data.fecha_ingreso);
                $("#proyecto").val(data.proyecto);

                $("#editar_proyecto_existencias, #editar_proyecto_existencias_chosen").val(data.proyecto);
                $("#editar_proyecto_existencias_chosen").hide();
                $(".editar_proyecto_existencias, #editar_proyecto_existencias").trigger("chosen:updated");

        $("#bl_exist").text(bl);
        $("#clave_exist").text(clave);
        $("#lote_exist").text(lote);
        $("#caducidad_exist").text(caducidad);
        $('#DetalleExistencia').show();

            }, error: function(data){
                console.log("DetalleExistencia ERROR = ", data);
            }
        });

    }

    function loadDetalle(bl, clave, lote, lp, idy_ubica) 
    {
      $('#grid-table3').jqGrid('clearGridData').jqGrid('setGridParam', {postData:
      {
        action: 'loadDetalle',
        almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
        bl: bl,
        clave: clave,
        lote: lote,
        idy_ubica: idy_ubica,
        lp: lp
      }, datatype: 'json'}).trigger('reloadGrid',[{current:true}]);
    }

    function verDetalleCajas(bl, clave, lote, caducidad, lp, idy_ubica)
    {

        console.log("DetalleExistenciaCajas");
        console.log("bl :", bl," - articulo :", clave, " - lote : ", lote, " - almacen:", ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()), " - zona: ", $("#zona").val());

        loadDetalle(bl, clave, lote, lp, idy_ubica);

        $("#bl_cajas").text(bl);
        $("#clave_cajas").text(clave);
        $("#lote_cajas").text(lote);
        $("#caducidad_cajas").text(caducidad);
        $('#DetalleExistenciaCajas').show();
    }

    function CerrarDetalle()
    {
        $('#DetalleExistencia').hide();
    }
    function CerrarDetalleCajas()
    {
        $('#DetalleExistenciaCajas').hide();
    }

    function traer_BL()
    {
       $.ajax({
            url: "/api/reportes/lista/existenciaubica.php",
            type: "GET",
            data: {
                almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                articulo : $("#articulo").val(),
                contenedor : $("#contenedor").val(),
                zona: $("#zona").val(),
                action:"existenciaUbicacion"
            },
            success: function(data) {}
        });
    }

    function asignar()
    {
        var folios = [];
        var asignados = [];

        //$.each( $('#example>tbody>tr'), function (index, item)
        $.each( $('#grid-table>tbody>tr'), function (index, item)
        {
            var bl = $(item).children().eq(4)[0].textContent;
            if($(item).children().eq(1).children().prop("checked"))
            {
                asignados.push([bl]);
            } 
        });
        console.log("Seleccionados", asignados);
        if(asignados.length <= 0 && !$("#btn-asignarTodo").is(":checked"))
        {
            swal("Error", "Seleccione al menos un articulo", "error");
            return;
        }
        
        $('#motivo_selector').empty();
        $modal0 = $("#modal-asignar-motivo");
        $modal0.modal('show');
        $("#motivo_selector").append(new Option("Seleccione Motivo", ""));
        $.ajax({
            url: "/api/reportes/lista/existenciaubica.php",
            type: "POST",
            dataType: "json",
            data: {
                action:"traermotivos"
            },
            success: function(data) 
            {
                console.log("DA", data);
                var j = 0;
                $.each(data.sql, function(key, value) {
                    $("#motivo_selector").append(new Option(""+value.descri+"",value.id));
                    j++;
                });
            }
        });
    }
    
    function asignarmotivo()
    { 
        var asignados = [];
        $('#modal-asignar-usuario').modal("hide");

        //$.each( $('#example>tbody>tr'), function (index, item) 
        $.each( $('#grid-table>tbody>tr'), function (index, item) 
        {
            var bl = $(item).children().eq(6)[0].textContent;
            var contenedor = $(item).children().eq(7)[0].textContent;
            var cve_articulo = $(item).children().eq(10)[0].textContent;
            var lote = $(item).children().eq(20)[0].textContent;
            if(lote=="--"){lote="";}
            //var serie = $(item).children().eq(10)[0].textContent;
            var cantidad = $(item).children().eq(22)[0].textContent;
            var cant_cuarentena = $(item).children().eq(22)[0].textContent;
            if($(item).children().eq(1).children().prop("checked"))
            {
                asignados.push([$.trim(bl),$.trim(contenedor),$.trim(cve_articulo),$.trim(lote),$.trim(cantidad), $.trim(cant_cuarentena)]);
            } 
        });
        if ($("#motivo_selector").val() == "") 
        {
            swal("Error", "Seleccione un motivo", "error");
            return;
        }
        console.log("motivos: ", $('#motivo_selector').val(), "registros:", asignados, "almacen: ", $("#almacen").val(), "fecha: ", moment().format("YYYYMM"));

        console.log("****************************************************");
        console.log("Seleccionar Todo QA = ", $("#btn-asignarTodo").is(":checked"));
        console.log("Filtro artículo = ", $("#articulo").val());
        console.log("Filtro lotes = ", $("#lotes").val());
        console.log("Filtro Grupo = ", $("#grupo").val());
        console.log("Filtro clasificacion = ", $("#clasificacion").val());
        console.log("Filtro contenedor = ", $("#contenedor").val());
        console.log("Filtro zona = ", $("#zona").val());
        console.log("Filtro BL = ", $("#selectBL").val());
        console.log("Filtro Obsoletos = ", $("#art_obsoletos").val());
        console.log("Filtro LP = ", $("#selectLP").val());
        console.log("****************************************************");

        //return;
        $.ajax({
            url: "/api/reportes/lista/existenciaubica.php",
            type: "POST",
            dataType: "json",
            data: 
            { 
                //action:"savemotivo",
                motivos: $('#motivo_selector').val(),
                registros: asignados,

                seleccionar_todo_qa: ($("#btn-asignarTodo").is(":checked"))?1:0,
                articulo_qa: $("#articulo").val(),
                lotes_qa: $("#lotes").val(),
                grupo_qa: $("#grupo").val(),
                clasificacion_qa: $("#clasificacion").val(),
                contenedor_qa: $("#contenedor").val(),
                zona_qa: $("#zona").val(),
                bl: $("#selectBL").val(),
                lote_alterno: $("#lotes_alternos").val(),
                art_obsoletos_qa: $("#art_obsoletos").val(),
                refWell: $("#refWell").val(),
                pedimentoW: $("#pedimentoW").val(),
                lp: $("#selectLP").val(),
                proyecto_existencias: $("#proyecto_existencias").val(),
                almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                fecha: moment().format("YYYYMM"),
                id_usuario: '<?php echo $_SESSION["id_user"]?>',
                action:"savemotivo"
            },
            success: function(data) 
            {
                console.log("data.success = ", data.success, "data.sql = ", data.sql, "data.sql2 = ", data.sql2, "data.procesos = ", data.procesos);
               if (data.success == true) 
               {
                  swal("Exito", "Se movio correctamente!", "success");
                  $("#modal-asignar-motivo").modal('hide');


                    if ($("#almacen").val()==""){
                        return;
                    }

                    var data = {
                                almacen: ($("#almacen_cliente").val()=='')?($("#almacen").val()):($("#almacen_cliente").val()),
                                proveedor: $("#proveedor").val(),
                                existencia_cajas: ($("#existencia_cajas").is(':checked'))?(1):(0),
                                cve_proveedor: $("#cve_proveedor").val(),
                                cve_cliente: $("#cve_cliente").val(),
                                picking: $("#picking").val(),
                                grupo: $("#grupo").val(),
                                clasificacion: $("#clasificacion").val(),
                                bl: $("#selectBL").val(),
                                lote_alterno: $("#lotes_alternos").val(),
                                art_obsoletos: $("#art_obsoletos").val(),
                                refWell: $("#refWell").val(),
                                pedimentoW: $("#pedimentoW").val(),
                                lp: $("#selectLP").val(),
                                factura_oc: $("#factura_oc").val(),
                                proyecto_existencias: $("#proyecto_existencias").val(),
                                lotes : $("#lotes").val(),
                                articulo : $("#articulo").val(),
                                zona : $("#zona").val(),
                                search: true,
                                contenedor : $("#contenedor").val(),
                                action:"existenciaUbicacion"
                            };

                  $("#grid-table").jqGrid('clearGridData')
                  .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
                  .trigger('reloadGrid',[{current:true}]);

                    table(true);

                    calcularImporteExistencias();
                    traer_BL();

               }
               else
               {
                  swal("Error", "Algo salio mal!!!", "error");
                  $("#modal-asignar-motivo").modal('hide');
               }
            }, error: function(data)
            {
                console.log("ERROR CUARENTENA: ", data);
            }

        });
    }

    $( "#search" ).click(function() {
        console.log('A1');
        //if ($("#almacen").val()==""){
        //    return;
        //}

        var data = {
                    almacen: ($("#almacen_cliente").val()=='' && $("#almacen_proveedor").val()=='')?($("#almacen").val()):(($("#almacen_cliente").val() != '')?($("#almacen_cliente").val()):($("#almacen_proveedor").val())),
                    proveedor: $("#proveedor").val(),
                    existencia_cajas: ($("#existencia_cajas").is(':checked'))?(1):(0),
                    cve_proveedor: $("#cve_proveedor").val(),
                    cve_cliente: $("#cve_cliente").val(),
                    picking: $("#picking").val(),
                    grupo: $("#grupo").val(),
                    clasificacion: $("#clasificacion").val(),
                    bl: $("#selectBL").val(),
                    lote_alterno: $("#lotes_alternos").val(),
                    art_obsoletos: $("#art_obsoletos").val(),
                    refWell: $("#refWell").val(),
                    pedimentoW: $("#pedimentoW").val(),
                    lp: $("#selectLP").val(),
                    factura_oc: $("#factura_oc").val(),
                    proyecto_existencias: $("#proyecto_existencias").val(),
                    lotes : $("#lotes").val(),
                    articulo : $("#articulo").val(),
                    zona : $("#zona").val(),
                    search: true,
                    contenedor : $("#contenedor").val(),
                    action:"existenciaUbicacion"
                };

      $("#grid-table").jqGrid('clearGridData')
      .jqGrid('setGridParam', {postData: data, datatype: 'json', page : 1})
      .trigger('reloadGrid',[{current:true}]);

        table(true);

        calcularImporteExistencias();
        traer_BL();
    });

    $(document).ready(function(){
        setTimeout(function(){
        calcularImporteExistencias();
        },2000);


        //console.log("cve_proveedor = ",$("#cve_proveedor").val());
        //if($("#cve_proveedor").val() != '')
        //{
        //    $("#proveedor").val($("#cve_proveedor").val());
        //    $("#proveedor").trigger("chosen:updated");
        //    console.log("proveedor = ",$("#proveedor").val());
        //    //$('#search').trigger('click');
        //}

    });
</script>