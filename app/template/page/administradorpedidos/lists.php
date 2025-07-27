<?php
$listaAP = new \AlmacenP\AlmacenP();
//$model_almacen = $almacenes->getAll();
//echo $_SERVER["HTTP_HOST"]."/class/SendGrid/sendgrid-php.php --------";

$R = new \Ruta\Ruta();
$rutas = $R->getAll();
$U = new \Usuarios\Usuarios();
$usuarios = $U->getAll();
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

$ciudadSql = \db()->prepare("SELECT DISTINCT(Ciudad) as ciudad  FROM th_dest_pedido ");
$ciudadSql->execute();
$ciudades = $ciudadSql->fetchAll(PDO::FETCH_ASSOC);
$AreaEmbarq = new \AreaEmbarque\AreaEmbarque();

$id_almacen = $_SESSION['id_almacen'];
$rutas_pedidos_sql = \db()->prepare("
    SELECT DISTINCT 
    GROUP_CONCAT(DISTINCT IFNULL(th.ruta, th.cve_ubicacion) ORDER BY IFNULL(th.ruta, th.cve_ubicacion) SEPARATOR ';;;;;') AS id, 
    r.cve_ruta,
    r.descripcion
    FROM th_pedido th 
    LEFT JOIN t_ruta r ON r.ID_Ruta = IFNULL(th.ruta, '') OR r.cve_ruta = IFNULL(th.cve_ubicacion, '')
    WHERE (IFNULL(th.ruta, '') != '' OR IFNULL(th.cve_ubicacion, '') != '') 
    AND th.cve_almac = $id_almacen AND r.descripcion IS NOT NULL AND IFNULL(th.ruta, th.cve_ubicacion) != ''
    GROUP BY r.cve_ruta");
$rutas_pedidos_sql->execute();
$rutas_pedidos = $rutas_pedidos_sql->fetchAll(PDO::FETCH_ASSOC);

$prioridad_pedidos_sql = \db()->prepare("SELECT * FROM t_tiposprioridad");
$prioridad_pedidos_sql->execute();
$prioridad_pedidos = $prioridad_pedidos_sql->fetchAll(PDO::FETCH_ASSOC);

$ciudad_pedidos_sql = \db()->prepare("
    SELECT DISTINCT Colonia FROM c_cliente WHERE Cve_Clte IN (SELECT Cve_Clte FROM th_pedido WHERE cve_almac = $id_almacen)
    ");
$ciudad_pedidos_sql->execute();
$ciudad_pedidos = $ciudad_pedidos_sql->fetchAll(PDO::FETCH_ASSOC);



$grupoArticulos = new \GrupoArticulos\GrupoArticulos();
$SubGrupoArticulos = new \SubGrupoArticulos\SubGrupoArticulos();
$unidad_medida = new \UnidadesMedida\UnidadesMedida();

$confSql = \db()->prepare("SELECT IFNULL(Valor, '0') AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'SAP' LIMIT 1");
$confSql->execute();
$ValorSAP = $confSql->fetch()['Valor'];

$confSql = \db()->prepare("SELECT IFNULL(Valor, '0') AS SFA FROM t_configuraciongeneral WHERE cve_conf = 'SFA' LIMIT 1");
$confSql->execute();
$ValorSFA = $confSql->fetch()['SFA'];


$confSql = \db()->prepare("SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$id_almacen') AS cr WHERE cr.con_recorrido != -1");
$confSql->execute();
$row_recorrido = $confSql->fetch();
$con_recorrido = $row_recorrido['con_recorrido'];

$confSql = \db()->prepare("SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1");
$confSql->execute();
$instancia = $confSql->fetch()['instancia'];

/*
$confSql = \db()->prepare("SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') as fecha_actual FROM DUAL");
$confSql->execute();
$fecha_actual = $confSql->fetch()['fecha_actual'];

$confSql = \db()->prepare("SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_actual FROM DUAL");
$confSql->execute();
$fecha_actual2 = $confSql->fetch()['fecha_actual'];
*/

//$confSql = \db()->prepare("SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'");
//$confSql->execute();
//$charset = $confSql->fetch()['charset'];
//mysqli_set_charset(\db() , $charset);


$confSql = \db()->prepare("SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_semana FROM DUAL");
$confSql->execute();
$fecha_semana = $confSql->fetch()['fecha_semana'];

$confSql = \db()->prepare("SELECT DATE_FORMAT(MAX(Fec_Entrada), '%d-%m-%Y') AS fecha_actual FROM th_pedido");
$confSql->execute();
$fecha_actual = $confSql->fetch()['fecha_actual'];

if(!isNikken()){
    $codDaneSql = \db()->prepare("SELECT *  FROM c_dane ");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
}

$cve_proveedor = "";
if(isset($_SESSION['id_proveedor']))
{
    $cve_proveedor = $_SESSION['id_proveedor'];
}

?>
<input type="hidden" id="cve_proveedor" value="<?php echo $cve_proveedor; ?>">
<input type="hidden" id="con_recorrido" value="<?php echo $con_recorrido; ?>">
<input type="hidden" id="instancia" value="<?php echo $instancia; ?>">
<input type="hidden" id="folios_ola_import" value="">
<input type="hidden" id="PedidoTipoLP" value="N">


<?php 
/**************************
use PHPMailer\PHPMailer\PHPMailer;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';


//require '../vendor/autoload.php';
//Create a new PHPMailer instance

$mail = new PHPMailer();

 $mail->IsSMTP(); // enable SMTP
 $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
 $mail->SMTPAuth = true; // authentication enabled
 

 //$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
 $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
 $mail->Host = "assistpro-adl.com";
 $mail->Port = 587; // or 465
 $mail->IsHTML(true);
 $mail->Username = "system@assistpro-adl.com";
 $mail->Password = "y&#ulsux%_BU";
 //$mail->SetFrom("AssistPro WMS <system@assistpro-adl.com>");
 $mail->From         = 'system@assistpro-adl.com';
 $mail->FromName     = 'AssistPro WMS';

 $mail->Subject = "Mensaje de Prueba 8";
 $mail->Body = "<b>Mensaje de prueba en HTML</b>
 <br>Este mensaje es una prueba de correo en PHP Mailer";
 $mail->AddAddress("reinalddo@gmail.com", 'Reinaldo Matheus');
 //$mail->AddAddress("reinaldojose@hotmail.com", 'Reinaldo Matheus');
 //$mail->AddAddress("aolivares@gmx.com", 'AssistPro ADVL');

 
//send the message, check for errors

//if (!$mail->send()) {
//    echo 'Mailer Error: ' . $mail->ErrorInfo;
//} else {
$mail -> ClearAllRecipients();//Mientras se recorre la misma instancia de correo, al inicio de cada iteración se debe de declarar para indicar al gestor que se trata de otro email y así sucesivamente
   // echo 'Message sent!';
//}
*****************************************/

?>

<style>
    .table{
        margin-top: 2px;
        border: 1px solid #dddddd;
    }
    /*.column-planificar input, */
    /*
    #btn-planificar
    {
        display: none;
    }
    */
/*
    .verde {color: #A9F5A9;}
    .amarillo {color: #F2F5A9;}
    .rojo {color: #F5A9A9;}
*/
    .verde {color: #76ef76;}
    .amarillo {color: #dee41b;}
    .rojo {color: #e73232;}

    #tabla-division thead tr th 
    {
        background-color: #000;
    }

    .table > thead > tr > th, 
    .table > tbody > tr > th,
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td, 
    .table > tfoot > tr > td {
        border: 1px solid #dddddd;
        line-height: 1.42857;
        padding: 4px 8px;
        vertical-align: top;
    }
    .table > thead > tr {
        /*background-color: #f7f7f7;*/
        background-color: #293846;
        color: #fff;
    }
    .table > thead > tr > th {
        font-size: 12px;
        vertical-align: middle !important;
        text-align: center;
        padding: 0px !important;
    }
    .table > tfoot > tr {
        background-color: #f7f7f7;
    }
    .table > tfoot > tr > th {
        font-weight: 400;
        font-size: 11px;
        font-family: Verdana,Arial,sans-serif;
    }
    .table > tfoot .sep{
        width: 1px;
        height: 15px;
        background-color: #dddddd;
        display: inline-block;
        margin: 0px 5px;
        position: relative;
        top:3px
    }
    .table .page{
        width: 50px;
        height: 20px;
        border: 1px solid #dddddd;
    }
    .table a {
        color: #000;
    }
  .fotos {
    width: 200px;    
  }
  @media all and (min-height: 600px) 
  {
    #modal_surtido_responsive
    {
        /*
        height: 500px;
        max-height: 500px;
        */
        overflow-y: scroll;
    }

  }
</style>

<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<!-- FooTable -->
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/animate.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/plugins/footable/footable.core.css" rel="stylesheet">

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/moment-with-locales.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.js"></script>

<!-- ClientesxRuta -->
<script src="/js/plugins/footable/footable.all.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/footable/footable.all.min.js"></script>
<script src="/js/dragdrop.js"></script>
<!-- Drag & Drop Panel -->
<!-- Sweet alert -->
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<style type="text/css">
  .class_pager
  {
    width: 100% !important;
  }
    .ui-jqgrid,
    .ui-jqgrid-view,
    .ui-jqgrid-hdiv,
    .ui-jqgrid-bdiv,
    .ui-jqgrid,
    .ui-jqgrid-htable,
    #grid-table,
    #grid-table-surtido,
    #grid-table2,
    #grid-table-surtido1,
    #grid-consolidados,
    #grid-surtidos,
    #grid-areas-embarque,
    #grid-pager,
    #grid-pager-surtido,
    #grid-pager2,
    #grid-pager-surtido1,
    #grid-pager3,
    #grid-pager4,
    #gridpager-areas-embarque {
        max-width: 100%;
    }
</style>

<!-- Administrador de pedidos -->
<div class="wrapper wrapper-content  animated fadeInRight" id="list">
    <div id="alerta"></div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-4" id="_title">
                            <h3>Administrador de Pedidos</h3>
                        </div>
                    </div>
                </div>
                <form id="myform">
                    <input type="hidden" id="hiddenAction" name="hiddenAction">
                    <input type="hidden" id="hiddenID_Pedidos">
                    <input type="hidden" id="hiddenPedidos">
                    <div class="ibox-content">
                        <div class="row" style="margin-top:15px">
                            <div class="col-md-3">
                                <label>Almacen</label>
                                <select class="chosen-select form-control" id="almacen">
                                    <option value="">Seleccione un almacen</option>
                                    <?php foreach( $listaAP->getAll() AS $a ): ?>
                                        <option value="<?php echo $a->id; ?>"><?php echo "(".$a->clave.") - ".$a->nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="email">Status de Orden</label>
                                <select name="status" id="status" class="chosen-select form-control">
                                    <option value="">Seleccione un Status</option>
                                    <option value="I">Editando</option>
                                    <option value="A" selected>Listo por asignar</option>
                                    <option value="S">Surtiendo</option>
                                    <option value="L">Pendiente de auditar</option>
                                    <option value="R">Auditando</option>
                                    <option value="P">Pendiente de empaque</option>
                                    <option value="M">Empacando</option>
                                    <option value="C">Pendiente de embarque</option>
                                    <option value="E">Embarcando</option>
                                    <option value="T">Enviado</option>
                                    <option value="F">Entregado</option>
                                    <option value="K">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <div class="input-group date" id="data_1">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechai" type="text" class="form-control" value="<?php echo $fecha_semana; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <div class="input-group date" id="data_2">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="fechaf" type="text" class="form-control" value="">
                                        <?php //if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com') echo $fecha_actual; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:0px">
                            <?php 
                            /*
                            ?>
                             <div class="col-md-3">
                                <label for="email">En</label>
                                <select name="filtro" id="filtro" class="chosen-select form-control">
                                    <option value="">Seleccione un filtro</option>
                                    <option value="o.Fol_folio">Número de Orden</option>
                                    <option value="o.Pick_Num">Número OC Cliente</option>
                                    <option value="p.Descripcion">Prioridad</option>
                                    <option value="c.RazonSocial">Cliente</option>
                                    <option value="u.nombre_completo">Usuario</option>
                                </select>
                            </div> 
                            <?php 
                            */
                            ?>
                            <div class="col-md-3">
                                <label>Ruta</label>
                                <select class="chosen-select form-control" id="ruta_pedido_list">
                                    <option value="">Seleccione una ruta</option>
                                    <?php foreach( $rutas_pedidos AS $ruta ): ?>
                                        <option value="<?php echo $ruta['id']; ?>"><?php echo "(".$ruta['cve_ruta'].") ".$ruta['descripcion']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!--<div class="col-md-3"><label>   </label></div>-->

                            <div class="col-md-3">
                                <label for="tipopedido">Tipo de Pedido</label>
                                <select name="tipopedido" id="tipopedido" class="chosen-select form-control">
                                    <option value="P">Pedidos General</option>
                                    <option value="T">Orden de Trabajo</option>
                                    <option value="R">Traslado entre almacenes Externos</option>
                                    <option value="RI">Traslado entre almacenes Internos</option>
                                    <option value="W">Wave Set (Ola)</option>
                                    <option value="W2">Ola de Olas</option>
                                    <option value="X">Cross Docking</option>
<?php 
/*
?>
                                    <option value="">Pedidos General</option>
                                    <option value="5">Pedidos Ruta</option>
                                    <option value="1">Orden de Trabajo</option>
                                    <option value="2">Traslado</option>
                                    <option value="3">Pedidos Clientes</option>
                                    <option value="4">WaveSets</option>
                                    <option value="6">Recargas</option>
                                    <option value="7">PDS</option>
<?php 
*/
?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Ciudad</label>
                                <select class="chosen-select form-control" multiple="true" id="ciudad_pedido_list" name="ciudad_pedido_list[]">
                                    <option value="">Seleccione una Ciudad</option>
                                    <?php foreach( $ciudad_pedidos AS $ciudad ): ?>
                                        <option value="<?php echo $ciudad['Colonia']; ?>"><?php echo $ciudad['Colonia']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <br><br><br><br>

                            <div class="row">
                            <div class="col-md-3">
                                <label>Buscar | Actualizar</label>
                                <input id="criteriob" type="text" placeholder="" value="" class="form-control">
                            </div>

                            <div class="col-md-3"><label>   </label>
                                <div class="input-group-btn">
                                    <button  onclick="filtralo()" type="button" class="btn btn-m btn-primary btn-block">
                                        <i class="fa fa-search"></i> Buscar | <i class="fa fa-refresh"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3"></div>
                            <div class="col-md-3">
                            <button class="btn btn-primary pull-right permiso_registrar" type="button" style="margin-left:15px; ; margin-top: 22px" data-toggle="modal" data-target="#modal-importar"><span class="fa fa-download"></span> Planificar Olas por importación</button>
                            </div>
                            </div>
                        </div>                      
                    </div>
                </div>
                <div class="row" style="margin-top:15px">
                    <div class="col-md-3">
                        <label>Total de Pedidos</label>
                        <input id="totalpedidos" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-3">
                        <label>Pedidos Asignados</label>
                        <input id="totalpeso" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-3">
                        <label>Subpedidos</label>
                        <input id="totalsubpedidos" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                    <div class="col-md-3">
                        <label>Pedidos Listos para Embarque</label>
                        <input id="volumentotal" type="text" value="0" class="form-control" style="text-align: center" disabled><br>
                    </div>
                </div>
            </div>
        </form>
    </div>

<div class="modal fade" id="fotos_oc_th" role="dialog">
    <div class="vertical-alignment-helper">
          <div class="modal-dialog vertical-align-center" style="width:80%;">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Fotos del Embarque <span id="n_folio"></span></h4>
                    </div>
                    <div class="modal-body">

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

    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-2">
                <div class="checkbox" id="chb_asignar">
                    <label for="btn-asignarTodo">
                      <input type="checkbox" name="asignarTodo" id="btn-asignarTodo">Asignar Todo
                    </label>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="checkbox"id="chb_planificar">
                    <label for="btn-planificarTodo">
                        <input type="checkbox" name="planificarTodo" id="btn-planificarTodo">Planificar Todo | Cambiar Status
                    </label>
                </div>
            </div>
            <div class="col-lg-8">
                <!--a href="/api/v2/pedidos/exportar-cabecera" target="_blank" class="btn btn-primary pull-right" style="margin-left:10px; ; margin-top: 10px"><span class="fa fa-upload"></span> Exportar</a>
                <button id="exportalo" class="btn btn-primary pull-right" >
                    <span class="fa fa-upload"></span>Exportar
                </button>-->
                <?php 
                if($_SERVER['HTTP_HOST'] == 'pascual.assistpro-adl.com' || $_SERVER['HTTP_HOST'] != 'www.pascual.assistpro-adl.com')
                {
                    if($ValorSFA == 1)
                    {
                ?>

                <a href="#" id="generarExcelSabores" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Resumen Pedidos por Sabores
                </a>
                <?php 
                    }
                }

                if($ValorSFA == 1)
                {
                ?>
                <a href="#" id="generarExcelStatus" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Resumen Pedidos por Ruta Status
                </a>
                <a href="#" id="generarExcelConsolidado" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Resumen Pedidos por Ruta Consolidado
                </a>
                <a href="#" id="generarExcel" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Resumen Pedidos por Ruta
                </a>
                <?php 
                }
                ?>
                <a href="#" id="generarExcelPedidos" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Resumen Pedidos General
                </a>
                <a href="#" id="generarExcelPedidosArticulos" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Resumen Pedidos General Artículos
                </a>
                <a href="#" id="generarReporteSalidas" class="btn btn-primary" style="margin: 10px;float: right;">
                    <span class="fa fa-file-excel-o"></span> Reporte de Salidas
                </a>
            </div>
        </div>
        <div class="jqGrid_wrapper">
            <div>
                <span>
                    <b style="font-size: 14px;">Los pedidos que se muestran al entrar son los pedidos de los últimos 7 días, use los filtros para buscar más pedidos</b><br><br>
                    <?php 
                    if($con_recorrido == 1)
                    {
                    ?>
                    <b style="font-size: 14px;">Esta Instancia Está usando Ruta de Surtido</b><br><br>
                    <?php
                    }
                    else
                    {
                    ?>
                    <b style="font-size: 14px;">Esta Instancia NO está usando Ruta de Surtido</b><br><br>
                    <?php
                    }
                    ?>
                </span>
            </div>
            <div class="table-responsive">
                <table id="dt-detalles" class="table" style="table-layout: auto;width: 100%;"> <!--lilo-->
                    <thead>
                        <tr style="height: 50px;">
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Acciones</th>
                            <th id="col-semaforo" scope="col" style="width: 70px !important; min-width: 70px !important;">Status</th>
                            <th id="col-asignar" scope="col" style="width: 70px !important; min-width: 70px !important;">Asignar</th>
                            <th id="col-planificar" scope="col" style="width: 130px !important; min-width: 130px !important;">Planificar Ola | XD <br> | Cambiar Status</th>
                            <th id="col-dividir" scope="col" style="width: 130px !important; min-width: 130px !important;">Dividir Pedido</th> 
                            <th scope="col" style="width: 150px !important; min-width:  80px !important;display: none;">Enviado</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Nro. Orden</th>
                            <?php if($instancia != 'iberofarmacos'){ ?>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Subpedido | BackOrder</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Ruta | Tienda Destino</th>
                            <?php } 
                            ?>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Cliente</th>
                            <?php if($instancia != 'iberofarmacos'){ ?>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">No. OC Cliente</th>
                            <?php } ?>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Total Factura</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Observaciones</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Fecha Pedido</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Fecha Aprobación</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Fecha Compromiso</th>
                            <?php if($instancia == 'iberofarmacos'){ ?>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Subpedido | BackOrder</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Ruta | Tienda Destino</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">No. OC Cliente</th>
                            <?php } ?>
                            <th scope="col" style="width: 100px !important; min-width: 150px !important;">Horario Entrega</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Prioridad</th>
                            <th scope="col" style="width: 150px !important; min-width: 150px !important;">Status</th>
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">Artículos</th>-->
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">Cantidad Solicitada</th>-->
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">Cantidad Surtida</th>-->
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">Volumen</th>-->
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">Peso</th>-->
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Inicio</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Fin</th>
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">Tiempo de Surtido</th>-->
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Surtidor</th>
                            <th scope="col" style="width: 100px !important; min-width: 100px !important;">Proyecto(s)</th>
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">% Surtido</th>-->
                            <!--<th scope="col" style="width: 400px !important; min-width: 400px !important;">Dirección</th>-->
                            <!--<th scope="col" style="width: 100px !important; min-width: 100px !important;">CP | CD</th>-->





                            <?php 
                            if($instancia == 'welldex')
                            {
                            ?>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Referencia (M3)</th>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Referencia Importación</th>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Clave Pedimento</th>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Factura Venta</th>
                            <th scope="col" style="width: 250px !important; min-width: 250px !important;">Pedimento Importación Definitiva</th>
                            <?php 
                            } 
                            ?>

                            <!--<th scope="col" style="width: 150px !important; min-width: 200px !important;">Ciudad | Departamento</th>-->
                            <!--<th scope="col" style="width: 150px !important; min-width: 150px !important;">Alcaldía | Municipio</th>-->
                        </tr>
                    </thead>
                    <tbody id="tbody-info"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="22">
                                Página 
                                <span class="ui-icon ui-icon-seek-prev" style="position: relative;display: inline-block;cursor: pointer;top: 3px;"></span>
                                <input class="page" id="page" type="text" value="1" style="text-align:center"/> 
                                <span class="ui-icon ui-icon-seek-next" style="position: relative;display: inline-block;cursor: pointer;top: 3px;"></span>

                                de <span class="total_pages">0</span>
                                <div class="sep"></div>
                                <select class="count">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="70">70</option>
                                    <option value="100">100</option>
                                </select>
                                <div class="sep"></div>
                                Mostrando <span class="from">0</span> - <span class="to">0</span> de <span class="total">0</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="tab-2" class="tab-pane">
            <div class="panel-body">
                <div class="ibox-content"></br></br>
                    <div class="form-group">
                        <div class="input-group-btn">
                            <button id="btn-asignar" type="button" class="btn btn-m btn-primary permiso_registrar" style="display:none; padding-right: 20px;">Asignar</button>

                            <!-- onclick="asignar()" -->
                            <button id="btn-planificar" type="button" class="btn btn-m btn-primary permiso_registrar" onclick="VerificarTiposPedidosPlanificar('', '')" style="padding-right: 20px;margin-left: 10px;">Planificar Ola</button>

                            <button id="btn-planificarXD" type="button" class="btn btn-m btn-primary" onclick="VerificarTiposPedidosPlanificar('XD', '')" style="padding-right: 20px;margin-left: 10px;display: none;">Planificar XD</button>

                            <button id="btn-cambiarstatus" type="button" class="btn btn-m btn-primary permiso_registrar" onclick="CambiarStatusVarios()" style="padding-right: 20px;margin-left: 10px;">Cambiar Status</button>

                            <?php 
                            /*
                            ?>
                            <button id="btn-planificar" type="button" class="btn btn-m btn-primary" onclick="planificar()" style="padding-right: 20px;margin-left: 10px; display: none;">Marcar como Entregado</button>
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
 </div>
</div>


<div class="modal fade" id="editarPedido" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 80%;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Editar Pedido <span id="pedidoAEditar"></span></h4>
                </div>
                <div class="modal-body" style="overflow-y: scroll;max-height: 400px;height: 400px;">

                            <!--
**********************************************************************************************************
**********************************************************************************************************
**********************************************************************************************************
-->
                              <div class="row" style="margin: 0px;">
                                <div class="form-group col-md-12" style="padding: 15px; border: 1px solid; border-color: #dedede; border-radius: 15px"><!--#1ab394-->
                                <div class="row">

                                  <div class="form-group col-md-4" id="Articul" >
                                      <label>Artículo*</label>
                                                <!--country      basic-->
                                      <select name="articulo" id="articulo" class="chosen-select form-control">
                                          <option value="">Seleccione Artículo</option>
                                      </select>
                                      <input id="ancho" type="hidden" class="form-control">
                                      <input id="alto" type="hidden" class="form-control">
                                      <input id="fondo" type="hidden" class="form-control">
                                  </div>
                                  <div class="form-group col-md-2">
                                      <label>Lote|Serie</label> 
                                      <input type="hidden" id="tiene_lote"  value="">
                                      <input type="hidden" id="tiene_serie" value="">
                                      <select name="lote_serie" id="lote_serie" class="chosen-select form-control">
                                          <option value="">Seleccione</option>
                                      </select>
                                  </div>

                                  <div class="form-group col-md-2">
                                      <label>Cantidad</label> 
                                      <input type="hidden" id="modo_peso" value="1">
                                            <!--CantPiezas-->
                                      <input id="CantPiezas" dir="rtl" type="number" placeholder="Cantidad" class="form-control" maxlength="8">
                                  </div>

                                    <div class="form-group col-md-2">
                                        <label>Unidad de Medida</label>
                                        <select class="form-control" name="id_unimed" id="id_unimed">
                                            <option value="">Seleccione</option>
                                            <?php foreach( $unidad_medida->getAll() AS $a ): ?>
                                                <option value="<?php echo $a->id_umed; ?>"><?php echo '( '.$a->cve_umed.' ) '.$a->des_umed; ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>

                                    <div class="form-group col-md-2">
                                        <input type="button" class="btn btn-primary" name="AgregarProductoEdicion" id="AgregarProductoEdicion" value="Agregar Artículo" style="margin-top: 20px;">
                                    </div>
                                </div>

                                </div>


                              </div>
<!--
**********************************************************************************************************
**********************************************************************************************************

                            -->
                            <div class="row">
                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Artículo</label>
                                </div>
                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Lote/Serie</label>
                                </div>

                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Cantidad</label>
                                </div>
                                <div class="col-md-3">
                                <label style="white-space: nowrap;">Eliminar?</label>
                                </div>
                            </div>

                            <input type="hidden" id="datos_sap_array">
                            <div id="datos_folio">
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
                    <button id="btn-editar-pedido" type="button" class="btn btn-primary">Editar</button><!--funcion de import-->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="modal-importar" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Planificar Olas por importación</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">                                          
                            <form id="form-import" action="import" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_almacen_import_olas" id="id_almacen_import_olas" value="<?php echo $_SESSION['id_almacen']; ?>">
                                <div class="form-group">
                                    <label>Seleccione el archivo a importar</label>
                                    <input type="file" name="olas_import" id="olas_import" class="form-control" required>
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
                    </div>
                    <div class="modal-footer">
                      <div class="col-md-6" style="text-align: left">
                        <button id="btn-layout" type="button" class="btn btn-primary">Descargar Layout</button>
                      </div>
                        <div style="text-align: right">
                            <button id="btn-import" type="button" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Administrador de ola -->
    <div class="wrapper wrapper-content  animated fadeInRight" id="planificarOla">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Planificacion de <span class="OlaXD">Ola</span></h3>
                            </div>
                        </div>
                    </div>
                    <form id="myform">
                        <input type="hidden" id="hiddenAction" name="hiddenAction">
                        <input type="hidden" id="hiddenID_Pedidos">
                        <input type="hidden" id="hiddenPedidos">
                        <div class="ibox-content">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Status de Orden</label>
                                    <input id="p_status" type="text" placeholder="" value="Listo por Asignar" class="form-control" disabled><br>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Numero de <span class="OlaXD">Ola</span></label>
                                    <input id="txt-nro-ola" type="text" placeholder="" value="" class="form-control" disabled><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Fecha de Entrega</label>
                                    <div class="input-group date" id="data_3" disabled>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="txt-ola-fechaentrega" type="text" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Peso total de la <span class="OlaXD">Ola</span></label>
                                    <input id="txt-peso-ola" type="text" value="0" class="form-control" disabled><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Volumen Total</label>
                                    <input id="txt-volumen-ola" type="text" value="0" class="form-control" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Total Numero de Ordenes</label>
                                    <input id="txt-total-ordenes-ola" type="text" value="0" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group  col-md-12 text-center">
                                    <div class="input-group-btn">
                                        <button type="button" id="btn-cancelar-consolidado-ola" onclick="cancelar()" style="margin-right: 20px;" class="btn  btn-m btn-default">Cancelar</button>
                                        <button type="button" id="btn-crear-consolidado-ola" onclick="crearConsolidadoDeOla()" style="margin-right: 20px;" class="btn btn-m btn-primary" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Procesando...">Crear <span class="OlaXD_Button">Ola</span></button>
                                        <?php 
                                        /*
                                        ?>
                                        <button type="button" id="btn-borrar-consolidado-ola" onclick="borrarConsolidadoDeOla()" style="margin-right: 20px;display:none" class="btn  btn-m btn-primary">Borrar Ola</button>
                                        <button type="button" id="btn-asignar-consolidado-ola" style="display:none" class="btn  btn-m btn-primary" data-toggle="modal" data-target="#modal-asignar-usuario-ola">Asignar</button>
                                        <?php 
                                        */
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        /*
                        ?>
                            <div id="div-grid-consolidados" class="row" style="display:none">
                                <div class="jqGrid_wrapper col-md-12">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-lg-6">
                                            <h4>Consolidado</h4>
                                        </div>
                                        <div class="col-lg-6" style="text-align: right">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="consolidadoExcel()">Excel</button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="consolidadoPDF()">PDF</button>
                                        </div>
                                    </div>
                                    <table id="grid-consolidados"></table>
                                    <div id="grid-pager3"></div>
                                </div>
                            </div>
                            <div id="div-grid-surtidos" class="row" style="display:none">
                                <div class="jqGrid_wrapper col-md-12"><br/><br/>
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-lg-6">
                                            <h4>Orden de Surtido</h4>
                                        </div>
                                        <div class="col-lg-6" style="text-align: right">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="surtidoExcel()">Excel</button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="surtidoPDF()">PDF</button>
                                        </div>
                                    </div>
                                    <table id="grid-surtidos"></table>
                                    <div id="grid-pager4"></div>
                                </div>
                            </div>
                        <?php 
                        */
                        ?>
                        </div>
                    </form>
                </div>
                <div class="row">
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="sufijo_surtido" value="">
    <div class="modal inmodal" id="modalItems" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal_surtido_responsive" style="width: 96%;">
            <div class="modal-content animated bounceInRight">
                <div class="modal-content" style="width: 100%;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Surtiendo el folio <span id="folio_surtido"></span></h4>

                        <button id="SurtirPedidoCompleto" type="button" class="btn btn-primary pull-right" style="display:none;"> Surtir Todo El Pedido</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table-surtido"></table>
                                    <div id="grid-pager-surtido"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center">
                        <?php /* ?><button id="btnGuardarSurtidoPorUbicacion" type="button" class="btn btn-primary pull-right" onclick="finalizar_surtido()"><span class="fa fa-sign-out"></span> Finalizar</button><?php */ ?>
                        <button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><span class="fa fa-ban"></span > Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal_zonasembarque" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Zonas de Embarque</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="zonaembarque">Zonas Disponibles</label>
                                <select id="zonaembarque" class="chosen-select form-control">
                                <option value="">Seleccione la zona</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarZonaEmbarque()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona</button>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="modal_ubicacion_manufactura" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Mover a manufactura</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="ubicacionManufactura">Ubicaciones de manufactura</label>
                                <select id="ubicacionManufactura" class="chosen-select form-control">
                                <option value="">Seleccione la zona</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarUbicacionManufactura()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar zona</button>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_areas_recepcion" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Mover a Área de Recepción</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="arearecepcion">Áreas de Recepción</label>
                                <select id="arearecepcion" class="chosen-select form-control">
                                <option value="">Seleccione el área de recepción</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <a href="#" onclick="asignarAreaRecepcion()"><button id ="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Área de Recepción</button>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Asiganr Usuario -->
    <div class="modal fade" id="modal-asignar-usuario" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h3 class="text-center">Pedidos seleccionados</h3>
                                <h3 id="txt-cantidad-pedidos" class="text-center">0</h3><br/>
                            </div>
                            <div class="col-md-12">
                                <label for="usuario" class="text-center">Usuarios Disponibles</label>
                                <select id="usuario_selector" class="form-control">
                                    <option value="">Seleccione usuario</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <button onclick="asignarPedidoOpciones()" id="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuario
                        </button>
                        <br><br>
                        <div class="row" id="asignandopedidos" style="text-align: center;padding: 15px; display: none;
                                                                      font-size: 16px;top: 15px;position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(http://wms.dev.assistpro-adl.com/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                            <div style="height: 50px;top: 10px;position: relative;">
                                Asignando pedidos: <span id="avance"></span> de <span id="total"></span>
                            </div>
                        </div>
                      </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Asiganr Usuario División -->
    <div class="modal fade" id="modal-asignar-usuario-division" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h3 class="text-center">Pedidos seleccionados</h3>
                                <h3 id="txt-cantidad-pedidos-division" class="text-center">0</h3><br/>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="tabla-division"></table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <button onclick="asignarPedidoOpciones()" id="asignarpedidoD" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuarios
                        </button>
                        <br><br>
                        <div class="row" id="asignandopedidos" style="text-align: center;padding: 15px; display: none;
                                                                      font-size: 16px;top: 15px;position: relative;">
                            <div style="width: 50px;height: 50px;background-image: url(http://wms.dev.assistpro-adl.com/img/load.gif);background-size: 100%;background-position: center;background-repeat: no-repeat;display: inline-flex;"></div>
                            <div style="height: 50px;top: 10px;position: relative;">
                                Asignando pedidos: <span id="avance"></span> de <span id="total"></span>
                            </div>
                        </div>
                      </div>
                </div>
            </div>
        </div>
    </div>

  <div class="wrapper wrapper-content  animated fadeInRight" id="asignacionPedidos">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-lg-4" id="_title">
                                <h3>Asignacion de pedidos</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                  <div class="row">
                      <!--Asignando pedidos: <span id="avance"></span> de <span id="total"></span>-->
                  </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asiganr Usuario a Ola -->
    <div class="modal fade" id="modal-asignar-usuario-ola" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                        <div class="row">
                    <div class="modal-body">

                            <div class="col-md-12">
                                <h3 class="text-center">Pedidos seleccionados</h3>
                                <h3 id="txt-cantidad-pedidos-ola" class="text-center">0</h3><br/>
                            </div>

                            <div class="col-md-12">
                                <label for="usuario-ola" class="text-center">Usuarios Disponibles</label>
                                <select id="usuario-ola" class="chosen-select form-control">
                                <option value="">Seleccione usuario</option>
                                <?php foreach( $usuarios as $key => $value ): ?>
                                    <option value="<?php echo $value->cve_usuario; ?>"><?php echo $value->nombre_completo; ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                        <button onclick="planificarConsolidadoOla()" id="asignarpedido" class="btn btn-primary pull-right" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuario
                        </button>
                        <button id="asignarAE" class="btn btn-primary pull-right" onclick="asiganarAreaEmbarqueOla()" type="button">
                            <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Area de embarque
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-prioridad" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Cambiar Prioridad de Pedido</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Pedido</label>
                            <input type="text" name="pedido-prioridad" id="pedido-prioridad" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Prioridad</label>
                            <select class="form-control chosen-select" name="select-prioridad" id="select-prioridad">
                            <!--<option value="">Seleccione una Prioridad</option>-->
                            <!--<option value="I">Editando</option>-->
                            <?php foreach( $prioridad_pedidos AS $prioridad ): ?>
                                <option value="<?php echo $prioridad['ID_Tipoprioridad']; ?>"><?php echo "(".$prioridad['ID_Tipoprioridad'].") ".$prioridad['Descripcion']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="EditarPrioridad()">Cambiar Prioridad</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-status" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Cambiar Status de Pedido</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nº de Orden</label>
                            <input type="text" name="txt-status-actual-folio" id="txt-status-actual-folio" class="form-control" readonly>
                            <input type="hidden" name="txt-status-actual-sufijo" id="txt-status-actual-sufijo">
                        </div>
                        <div class="form-group">
                            <label>Status de la Orden</label>
                            <select class="form-control chosen-select" name="select-nuevo-status-folio" id="select-nuevo-status-folio">
                            <option value="">Seleccione un Status</option>
                            <!--<option value="I">Editando</option>-->
                            <option value="A">Listo por asignar</option>
                            <option value="S">Surtiendo</option>
                            <option value="L">Pendiente de auditar</option>
                            <option value="R">Auditando</option>
                            <option value="P">Pendiente de empaque</option>
                            <option value="M">Empacando</option>
                            <option value="C">Pendiente de embarque</option>
                            <option value="E">Embarcando</option>
                            <option value="T">Enviado</option>
                            <option value="F">Entregado</option>
                            <option value="K">Cancelado</option>
                        </select>
                        </div>
                        <div id="modal-status-motivo" class="form-group">
                            <label>Describa el mótivo</label>
                            <textarea id="modal-status-motivo-txt" class="form-control" rows="10"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="asignarStatus()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-status-varios" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Cambiar Status de Pedidos</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Status de la Orden</label>
                            <select class="form-control chosen-select" name="select-nuevo-status-varios" id="select-nuevo-status-varios">
                            <option value="">Seleccione un Status</option>
                            <!--<option value="I">Editando</option>-->
                            <!--<option value="A">Listo por asignar</option>-->
                            <!--<option value="S">Surtiendo</option>-->
                            <!--<option value="L">Pendiente de auditar</option>-->
                            <!--<option value="R">Auditando</option>-->
                            <!--<option value="P">Pendiente de empaque</option>-->
                            <!--<option value="M">Empacando</option>-->
                            <!--<option value="C">Pendiente de embarque</option>-->
                            <!--<option value="E">Embarcando</option>-->
                            <option value="T">Enviado</option>
                            <option value="F">Entregado</option>
                            <option value="K">Cancelado</option>
                        </select>
                        </div>
                        <div id="modal-status-motivo" class="form-group">
                            <label>Describa el motivo</label>
                            <textarea id="modal-status-motivo-txt-varios" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="asignarStatusVarios()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Área de Embarque -->
    <div class="modal fade" id="modal-area-embarque" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Asignación de Área de embarque</h4>
                    </div>
                    <div class="modal-body" style="padding: 30px !important;">
                        <div class="row">
                            <div class="jqGrid_wrapper">
                                <table id="grid-areas-embarque"></table>
                                <div id="gridpager-areas-embarque"></div>
                            </div>
                        </div>
                        <div id="areaemb" style="display: none; margin-top:15px">
                            <label for="areaembarque">Área de embarque</label>
                            <div class="input-group">
                                <select name="areaembarque" id="areaembarque" class="chosen-select form-control">
                                    <option value="">Seleccione una opcion</option>
                                    <?php foreach( $AreaEmbarq->getAll() AS $p ): ?>
                                        <option value="<?php echo $p->cve_ubicacion; ?>"><?php echo $p->descripcion; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-btn">
                                    <button type="submit" onclick="btnasiganarAreaEmbarqueOla()" class="btn btn-primary" id="btnAsignar"><i class="fa fa-plus"></i> Asignar </button>
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

    

    <!-- Modal Usuario Wave -->
    <div class="modal fade" id="waveModal" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Asignar a Usuarios</h4>
                    </div>
                    <div class="modal-body">

                        <div class="dragdrop">
                            <div class="form-group" id="clientesEditar">
                                <div class="col-md-6">
                                    <label for="email">Usuarios Disponibles</label>
                                    <ol data-draggable="target" id="fromw" class="wi">
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <label for="email">Usuarios para Asignar</label>
                                    <ol data-draggable="target" id="tow" class="wi">
                                    </ol>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="#" onclick="planificarConsolidadoOla()"><button id ="guardar" class="btn btn-primary pull-right" type="button"><i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Usuario</button></a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8" style="float: none;margin: 0 auto;">
                                    <span><b>Nota: </b>Seleccionar un item de los <b>"Disponibles"</b> y arrastralo a los <b>"Asignados"</b></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button id="asignarAE" class="btn btn-primary pull-right" onclick="asiganarAreaEmbarqueOla()" type="button">
                                    <i class="fa fa-plus"></i>&nbsp;&nbsp;Asignar Area de embarque
                                </button>
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

    <!-- Modal articulo -->
    <div class="modal inmodal" id="coModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="width: 90%">
            <div class="modal-content animated bounceInRight">
                <!-- Modal content-->
                <div class="modal-content" style="width: 100%;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Detalle del Pedido <span id="folio_detalle"></span><br><br>
                        <span id="ruta_pedido"></span><br><br>
                        <span id="articulo_OT_detalle"></span><br><br>
                        <span id="folio_ws" style="text-align: left; display: inline-block;"></span><br><br>
                        <span id="cliente_detalle"></span><br><br>
                        <span id="surtidor_detalle"></span>
                      </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                        <div class="col-md-12">
                            <div class="input-group" style="margin: 7px;">
                                <?php 
                                /*
                                ?>
                                <button id="btnRealizarSurtido" class="btn btn-primary pull-right permiso_registrar" onclick="prepararSurtido()" style="margin-right: 5px;"><i class="fa fa-cubes"></i>Realizar surtido</button>
                                <?php 
                                */
                                ?>
                                <!--<button class="btn btn-primary pull-right" id="btnImprimirRemision" onclick="imprimirRemision()" style="margin-right: 5px; display: none;"><i class="fa fa-print"></i>Imprimir Remisión</button>-->

                                <?php 
                                /*
                                ?>
                                <button class="btn btn-primary pull-right" onclick="imprimirGuiasEmbarque()" style="margin-right: 5px;"><i class="fa fa-print"></i>Imprimir Guías de Embarque</button>
                                <?php  
                                */
                                ?>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="fol_cambio" name="fol_cambio" value="">
                    <input type="hidden" id="suf_cambio" name="suf_cambio" value="">
                    <?php 
                    /*
                    ?>
                    <div id="cabecera_detalles" class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Pedidos</th>
                                            <th>Disponible</th>
                                            <th>BackOrder</th>
                                            <th>Volumen</th>
                                            <th>Peso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="total_productos_detalle" align="center">0</td>
                                            <td id="total_disponibles_detalle" align="center">0</td>
                                            <td id="backorder" align="center">0</td>
                                            <td id="total_volumen_detalle" align="right">0</td>
                                            <td id="total_peso_detalle" align="right">0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="cabecera_caja_detalles" class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tipo de Caja</th>
                                            <th>Peso de la Caja</th>
                                            <th>Dimensiones</th>
                                            <th>Volumen</th>
                                            <th>Cajas Requeridas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="tipo_caja_detalle">0</td> <!-- EDG117117 -->
                                            <td id="tipo_caja_detalle_selector">
                                              <select name="" id="tipo_caja_detalle_select">
                                                <option value="" id="tipo_caja_detalle_option">0</option>
                                              </select>
                                            </td>

                                            <td id="peso_caja_detalle" align="right">0</td>
                                            <td id="dimensiones_caja_detalle" align="right">0</td>
                                            <td id="volumen_caja_detalle" align="right">0</td>
                                            <td id="cantidad_caja_detalle" align="center">0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                          <input type="hidden" id="fol_cambio" name="fol_cambio" value="">
                          <button class="btn btn-primary pull-right" onclick="editar_cajas()" style="margin-right: 5px;" id="editBox"><i class="fa fa-save"></i>Guardar</button>

                        </div>
                    </div>
                    <?php 
                    */
                    ?>

                    <label id="titulo1" style="display: none;">Pendiente de Surtir</label>
                    <div class="row" id="tabla_surtiendo" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table2"></table>
                                <div id="grid-pager2"></div>
                            </div>
                        </div>
                    </div>
                     <br id="br_titulo" style="display: none;">
                    <label id="titulo2" style="display: none;">Surtidos</label>
                    <div class="row" id="tabla_surtido1" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table-surtido1"></table>
                                <div id="grid-pager-surtido1"></div>
                            </div>
                        </div>
                    </div>

                    <label id="titulo3" style="display: none;">Pedido</label>
                    <div class="row" id="tabla_listo_por_asignar" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table-listo-por-asignar"></table>
                                <div class="class_pager" id="grid-pager-listo-por-asignar" ></div>
                            </div>
                        </div>
                    </div>

                    <br><br>
                    <label id="titulo_promo" style="display: none;">Promoción</label>
                    <div class="row" id="tabla_promo" style="display: none;">
                        <div class="col-md-12">
                            <div class="jqGrid_wrapper">
                                <table id="grid-table-promo"></table>
                                <div class="class_pager" id="grid-pager-promo"></div>
                            </div>
                        </div>
                    </div>

                    <select name="select_proveedores" id="select_proveedores" class="form-control pull-right" style="width: 400px; display: none;">
                        <?php 
                        $sql = "SELECT ID_Proveedor, cve_proveedor, Nombre FROM c_proveedores WHERE Activo = 1 AND es_cliente = 1 ORDER BY Nombre ASC";
                        $result = mysqli_query(\db2(), $sql);
                        $num_res = mysqli_num_rows($result);
                        if($num_res > 1 || $num_res == 0)
                        {
                        ?>
                        <option value="">Seleccione una Empresa|Proveedor</option>
                        <?php 
                        }
                        while($row = mysqli_fetch_array($result))
                        {
                            $nombre_proveedor = (utf8_encode($row["Nombre"]))?utf8_encode($row["Nombre"]):utf8_decode($row["Nombre"]);
                        ?>
                            <option value="<?php echo $row['ID_Proveedor']; ?>"><?php echo $nombre_proveedor; ?></option>
                        <?php 
                        }
                        ?>
                    </select>
                    <br><br>

                    <div id="imprimir_observaciones">
                        
                    </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button id="btnRealizarSurtido" class="btn btn-primary pull-right permiso_registrar" onclick="prepararSurtido()" style="margin-right: 5px;"><i class="fa fa-cubes"></i>Realizar surtido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal articulo -->
    <div class="modal fade" id="modal_surtido" role="dialog">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4>Surtir Artículos</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="jqGrid_wrapper">
                                    <table id="grid-table6"></table>
                                    <div id="grid-pager6"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="volverSurtido">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarSurtido">Guardar surtido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="modal_fotos" role="dialog">
  <div class="vertical-alignment-helper">
    <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 align="center">Foto de Empaque</h4>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-12">
                    <!--contenido-->
                    <div class="row" align="center">
                      <img id="foto1" src="" alt="" class="fotos" width="33%">
                    </div>                                                                   
                   </div>
                </div>
              </div>
        </div>
        <!-- Modal Contenido de Fotos Embarque-->
        <div class="modal-header align-center"> 
          <h4 align="center">Fotos  de Embarque</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <div class="row col-md-12" align="center">
                <img id="foto2" src="" alt="" class="fotos" width="100%">
              </div>
            </div>
            <div class="col-md-4">
              <div class="row col-md-12" align="center">
                <img id="foto3" src="" alt="" class="fotos" width="100%">
              </div>
            </div>
            <div class="col-md-4">
              <div class="row col-md-12" align="center">
                <img id="foto4" src="" alt="" class="fotos" width="100%">
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





<div class="modal inmodal" id="modal-destinatario" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <i class="fa fa-laptop modal-icon"></i>
        <h4 class="modal-title" id="modaltitle">Destinatario</h4>
      </div>
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                  <div class="form-group">
                      <label>Razón Social</label>
                      <input type="text" class="form-control" id="destinatario_razonsocial">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label>Dirección</label>
                      <input type="text" class="form-control" id="destinatario_direccion">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label>Colonia</label>
                      <input type="text" class="form-control" id="destinatario_colonia">
                  </div>
              </div>

              <div class="col-md-4">                    
                  <div class="form-group">
                      <label>Código Dane / Código Postal</label>
                      <?php if(isset($codDane) && !empty($codDane)): ?>
                          <select id="destinatario_dane" class="form-control" required="true">
                              <option value="">Código</option>
                              <?php foreach( $codDane AS $p ): ?>
                                  <option value="<?php echo $p["cod_municipio"];?>"><?php echo $p["cod_municipio"]." ".$p["des_municipio"]; ?></option>
                              <?php endforeach; ?>
                          </select>
                      <?php else: ?>
                          <input type="text" name="destinatario_dane" id="destinatario_dane" class="form-control" required="true">
                      <?php endif; ?> 
                  </div>
              </div>

              <div class="col-md-4">
                  <div class="form-group">
                      <label>Municipio / Ciudad</label>
                      <input type="text" class="form-control" id="destinatario_ciudad" <?php if(isset($codDane) && !empty($codDane)): ?>readonly<?php endif; ?> >
                  </div>
              </div>

              <div class="col-md-4">
                  <div class="form-group">
                      <label>Departamento / Estado</label>
                      <input type="text" class="form-control" id="destinatario_estado" <?php if(isset($codDane) && !empty($codDane)): ?>readonly<?php endif; ?> >
                  </div>
              </div>

              <div class="col-md-6">
                  <div class="form-group">
                      <label>Contacto</label>
                      <input type="text" class="form-control" id="destinatario_contacto">
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label>Teléfono</label>
                      <input type="text" class="form-control" id="destinatario_telefono">
                  </div>
              </div>

          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-white" data-dismiss="modal" id="btnCancel">Cerrar</button>
        <button type="submit" class="btn btn-primary ladda-button permiso_registrar" onclick="guardarDestinatario()">Guardar</button>
      </div>
    </div>
  </div>
</div>


<form target="_blank" class="hidden" action="/administradorpedidos/consolidado/pdf" method="post" id="pdfConsolidado">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>
<form target="_blank" class="hidden" action="/administradorpedidos/consolidado/excel" method="post" id="excelConsolidado">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>
<form target="_blank" class="hidden" action="/administradorpedidos/surtido/pdf" method="post" id="pdfSurtido">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>
<form target="_blank" class="hidden" action="/administradorpedidos/surtido/excel" method="post" id="excelSurtido">
    <input type="hidden" name="folio" value="0">
    <input type="hidden" name="nofooternoheader" value="true">
</form>


<div id="inner_download"></div>
</div>




<script>

    $('#btn-layout').on('click', function(e) {
    //e.preventDefault();  //stop the browser from following
    //window.location.href = 'http://www.tinegocios.com/proyectos/wms/Layout_Articulos_Importador.xlsx';
    window.location.href = '/Layout/Layout_Olas.xlsx';
}); 
  

  var detalle_subpedido = "", cantidades_disponibles = [], backorder_array = [];

    var MOSTRAR_MENSAJE_BACKORDER = 0;
    $('#btn-import').on('click', function() {

        $('#btn-import').prop('disabled', true);
        var bar = $('.progress-bar');
        var percent = $('.percent');
        //var status = $('#status');

        var formData = new FormData();
        formData.append("clave", "valor");

        $.ajax({
            // Your server script to process the upload
            url: '/pedidos/importarOlas',
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
                            percent.html(percentComplete + '%');
                            if (percentComplete === 100) {
                                setTimeout(function() {
                                    $('.progress').hide();
                                }, 2000);
                            }
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function(data) {
                setTimeout(
                    function() {
                        if (data.statusText != "") {
                            swal("No se puede Planificar la Ola", data.statusText, "error");
                            $('#modal-importar').modal('hide');
                            console.log("ReloadGrid 1");
                            ReloadGrid();
                        } else {
                            console.log("folios_generados", data.folios_generados);
                            VerificarTiposPedidosPlanificar('', data.folios_generados);
                        }
                    }, 1000)
            },
        });
    });
</script>



    <script>
//imageFormat(item.orden, item.surtido, item.TieneOla, '', item.sufijo, is_backorder, item.cliente, item.asignado, '', item.folio_ws, item.status, item.status_pedido, item.fecha_pedido, item.es_ot, item.ruta_pedido)

//imageFormat(item.orden,item.surtido, item.TieneOla, '', item.sufijo, is_backorder, '', item.cliente, item.asignado, item.folio_ws, item.status, item.status_pedido, item.fecha_pedido, item.es_ot, item.ruta_pedido)

        function imageFormat(id, surtido, tieneola, tienda, sufijo, is_backorder, cliente, surtidor, articulo_ot, folio_ws, status, status_pedido, fecha_pedido, es_ot, ruta_pedido, statusaurora, TipoOT, usuario_pedido, tiene_foto, EstipoLP) {
           //console.log("tienda", tienda);
           //console.log("status == ", status);
           console.log("folio_ws == ", folio_ws);
           //console.log("status_pedido == ", status_pedido);
           //console.log("es_ot/es_tr == ", es_ot, " FOLIO = ", id);
           cliente = cliente.replace(/"/g, '');
           var cve_cia = <?php echo $_SESSION['cve_cia']; ?>;
            var html = '';
            status = status_pedido;
            html += '<a href="#" id="detalles_articulos_true'+id+'" onclick="articulos(\'' + id + '\', \'' + sufijo + '\', \'' + is_backorder + '\', \'' + cliente + '\', \'' + surtidor + '\', \'' + articulo_ot + '\', \'' + folio_ws + '\', \'' + ruta_pedido + '\', \'' + status_pedido + '\', \'' + statusaurora + '\', \'' + TipoOT + '\', \'' + usuario_pedido + '\', \'' + EstipoLP + '\')"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;<a href="#" id="detalles_articulos_false'+id+'" style="display: none;"><i class="fa fa-search" title="Ver Detalles"></i></a>&nbsp;';

            if($("#permiso_editar").val() == 1 && status != 'F' && status != 'T' && status != 'O')
                html += '<a href="#" onclick="cambiarStatus(\'' + id + '\', \'' + sufijo + '\', \'' + surtido + '\')"><i class="fa fa-check" title="Cambiar Status"></i></a>&nbsp;';
             if($("#permiso_editar").val() == 1 && status == 'C' && status == 'E' && status == 'T' && status == 'F' && status == 'K')
             {
            if(status_pedido == 'A' || status_pedido == 'S')
            html += '<a href="#" onclick="cambiarPrioridad(\'' + id + '\')"><i class="fa fa-share-square" title="Cambiar Prioridad"></i></a>&nbsp;';
             }
            html += '<a href="#" onclick="destinatario(\'' + id + '\')"><i class="fa fa-truck" title="Ver Destinatarios"></i></a>&nbsp;';


            //html += '<a href="#" onclick="embarqueFoto(\'' + id + '\')"><i class="fa fa-camera" title="Ver fotos de embarque"></i></a>&nbsp;';


            if(status == 'T' || status == 'F')
            {
                if(tiene_foto == 'S')
                    html += '<a href="#" onclick="ver_fotos(\'' + id + '\')"><i class="fa fa-camera" title="Ver fotos de embarque"></i></a>&nbsp;';
                html += '<a href="/api/koolreport/export/reportes/embarques/salida_de_inventario?folio_pedido='+id+'&cve_cia='+cve_cia+'" target="_blank" title="SALIDA DE INVENTARIO"><i class="fa fa-file-pdf-o"></i></a>&nbsp&nbsp';
            }


            if($("#tipopedido").val() == 'W')
            {
                html += '<a href="/api/koolreport/export/reportes/pedidos/reporte-ws?id='+id+'&cve_cia='+cve_cia+'&folio_ws='+folio_ws+'&ruta_pedido='+ruta_pedido+'" target="_blank" title="Reporte Consolidado"><i class="fa fa-file-pdf-o"></i></a>&nbsp&nbsp';
                html += '<a href="/api/koolreport/excel/pedidosws/export.php?id='+id+'" target="_blank" title="Reporte Consolidado"><i class="fa fa-file-excel-o"></i></a>&nbsp&nbsp';
                html += '<a href="/api/koolreport/excel/puntosdeventa/export.php?id='+id+'" target="_blank" title="Reporte Puntos de Venta"><i class="fa fa-file-excel-o"></i></a>&nbsp&nbsp';
            }

            if($("#tipopedido").val() == 'W2')
            {
                html += '<a href="/api/koolreport/export/reportes/pedidos/reporte-ws2?id='+id+'&cve_cia='+cve_cia+'" target="_blank" title="Reporte Detalle de Olas"><i class="fa fa-file-pdf-o"></i></a>&nbsp&nbsp';
                html += '<a href="/api/koolreport/export/reportes/pedidos/reporte-ws2-clientes?id='+id+'&cve_cia='+cve_cia+'" target="_blank" title="Reporte Detalle de Olas Por Cliente"><i class="fa fa-file-pdf-o"></i></a>&nbsp&nbsp';
            }


            if (status == 'L' || status == 'R' || status == 'P' || status == 'M' || status == 'C' || status == 'E' || status == 'T' || status == 'F' || status == 'S') 
            {
                var nombre = "Lista de Surtido";
                if(status != 'S')
                {
                    html += '<a href="#" onclick="imprimirRemision(\'' + id + '\')" id="btnImprimirRemision"><i class="fa fa-file-pdf-o" title="Imprimir Remisión"></i></a>&nbsp;';
                    nombre = "Reporte de Productos Surtidos";
                }

                html += '<a href="/api/koolreport/export/reportes/pedidos/reporte_surtido?folio='+id+'&sufijo='+sufijo+'&cve_cia='+cve_cia+'&fecha_pedido='+fecha_pedido+'&status='+status+'" target="_blank" id="btn_reporte_surtido" title="'+nombre+'"><i class="fa fa-file-pdf-o"></i></a>&nbsp;';
                html += '<a href="/api/koolreport/excel/reporte-pedidos-surtidos/export.php?folio='+id+'&sufijo='+sufijo+'&cve_cia='+cve_cia+'&fecha_pedido='+fecha_pedido+'&status='+status+'" target="_blank" id="btn_reporte_surtido" title="'+nombre+'"><i class="fa fa-file-excel-o"></i></a>&nbsp;';
            }

            //if(es_ot == 2) //es traslado
            if($("#tipopedido").val() == 'R' || $("#tipopedido").val() == 'RI')
            {
                //html += '<a href="/api/koolreport/export/reportes/pedidos/venta-pedido?folio='+id+'&cve_cia='+cve_cia+'&pedido_venta" target="_blank" id="btn_reporte_surtido" title="Reporte Traslado"><i class="fa fa-file-pdf-o"></i></a>&nbsp;';
                html += '<a href="/api/koolreport/export/reportes/pedidos/traslado?folio='+id+'&cve_cia='+cve_cia+'&fecha_pedido='+fecha_pedido+'" target="_blank" title="Reporte Traslado"><i class="fa fa-file-pdf-o"></i></a>&nbsp;';
                html += '<a href="/api/koolreport/excel/reporte-traslados/export.php?folio='+id+'" title="Reporte Traslado"><i class="fa fa-file-excel-o"></i></a>&nbsp;';
                //if(status == 'A')
                //{
                //    html += '<a href="#" onclick="AbrirModalEdicion(\'' + id + '\')"><i class="fa fa-edit" title="Editar Pedido"></i></a>&nbsp;';
                //}
            }

            if(tienda != 0)
            {
                if(tieneola == 1)
                    html += '<a href="#" onclick="exportarPDFCodigoBarrasConsolidado(\'' + id + '\')"><i class="fa fa-barcode" title="Ver Informe Consolidado"></i></a>&nbsp;';
                else
                    html += '<a href="#" onclick="exportarPDFCodigoBarras(\'' + id + '\')"><i class="fa fa-barcode" title="Ver Informe"></i></a>&nbsp;';
            }

            if(status == 'S' && es_ot == '1' && $("#permiso_eliminar").val() == 1)
                html += '&nbsp;&nbsp;<a href="#" onclick="BorrarOT(\'' + id + '\')"><i class="fa fa-eraser" title="Eliminar OT"></i></a>&nbsp;';

            if(status == 'A' && es_ot != '1' && $("#permiso_eliminar").val() == 1)
                html += '&nbsp;&nbsp;<a href="#" onclick="BorrarPedido(\'' + id + '\')"><i class="fa fa-eraser" title="Eliminar Pedido"></i></a>&nbsp;';

            <?php 

            if($ValorSAP == 1 && ($_SERVER['HTTP_HOST'] != 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] != 'www.dicoisa.assistprowms.com'))
            {
            ?>
                if((status == 'C' || status == 'E' || status == 'F' || status == 'T') && $("#permiso_registrar").val() == 1)
                    html += '<a href="#" onclick="OVSap(\''+id+'\', \''+sufijo+'\')"><i class="fa fa-server" title="Enviar Pedido a SAP"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            <?php 
            }
            ?>
            html += '<a href="/api/koolreport/export/reportes/pedidos/venta-pedido?arrDetalle=&folio='+id+'&cve_cia='+cve_cia+'&cliente='+cliente+'&destinatario=&tipoventa=&tipo_negociacion=&sfa=&cve_almacen=" target="_blank" title="Reporte de Venta"><i class="fa fa-file-pdf-o"></i></a>&nbsp;';

            if(status == 'A')// && $("#tipopedido").val() == 'P'
            {
                html += '<a href="/api/koolreport/export/reportes/pedidos/reporte_existencias_pedido?folio='+id+'&cve_cia='+cve_cia+'&fecha_pedido='+fecha_pedido+'" target="_blank" id="btn_reporte_surtido_existencias" title="Existencias Pedido PDF"><i class="fa fa-file-pdf-o"></i></a>&nbsp;';
                html += '<a href="/api/koolreport/excel/reporte_existencias_pedido/export.php?folio='+id+'" target="_blank" title="Existencias Pedido EXCEL"><i class="fa fa-file-excel-o"></i></a>&nbsp&nbsp';
            }

            return html;
        }

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid() {
          console.log("fechai = ", $("#fechai").val());
          console.log("fechaf = ", $("#fechaf").val());
            $.ajax({
                type: "GET",
                dataType: "JSON",
                url: '/api/v2/pedidos',
                data: {
/*
                    search: $("#criteriob").val(),
                    //filtro: $("#filtro").val(),
                    fecha_inicio: $("#fechai").val(),
                    fecha_fin: $("#fechaf").val(),
                    status: $("#status").val(),
                    almacen: $("#almacen").val(),
                    page : $('#dt-detalles .page').val(),
                    rows : $('#dt-detalles .count').val(),
*/
            search: $("#criteriob").val(), //Input Buscar
            criterio: $("#criteriob").val(),
            tipopedido: $("#tipopedido").val(),
            ciudad_pedido_list: $("#ciudad_pedido_list").val(),
            ruta_pedido_list: $("#ruta_pedido_list").val(),
            fecha_inicio: $("#fechai").val(),
            fecha_fin: $("#fechaf").val(),
            status: $("#status").val(),
            almacen: $("#almacen").val(),
            page: $('#dt-detalles .page').val(),
            rows: $('#dt-detalles .count').val(),
            //filtro: $("#filtro").val(),
            facturaInicio: $("#factura_inicio").val(),
            facturaFin: $("#factura_final").val()

                },
                success: function(data) 
                {
                    //lilo
                    console.log("success res pedidos = ", data);
                    //console.log("success res sql = ", data.sql);
                    if (data.status == 200) {
                        $('#dt-detalles .total_pages').text(data.total_pages);
                        $('#dt-detalles .page').text(data.page);
                        $('#dt-detalles .from').text(data.from);
                        $('#dt-detalles .to').text(data.to);
                        $('#dt-detalles .total').text(data.total);

                        var row = '', i = 0, color = ''; //lilo
                        $.each(data.data, function(index, item){
                            i++;
                            var sub_pedido = "";
                            if(item.sufijo != 0)
                                sub_pedido = item.orden+"-"+item.sufijo;
                            else 
                                sub_pedido = item.Folio_BackO;

                            var is_backorder = 0;
                            if(sub_pedido)
                            {
                                var backorder_item = sub_pedido[0]+sub_pedido[1];
                                //console.log("backorder_item = "+backorder_item);
                                if(backorder_item == 'BO')
                                    is_backorder = 1;
                            }
                            //console.log("item.status_pedido = ", item.status_pedido, " - item.sufijo = ",item.sufijo);
                            //if(item.status_pedido != 'S' && item.sufijo != 0)
                            //{
                                //var style_asignar = " disabled ";
                                var style_asignar = "", style_planificar = "", titleXD="", entrada_xd = "", style_dividir = "";
                                //if(item.disponible >= item.cantidad) {color = 'verde'; style_asignar = "";}else if(item.disponible < item.cantidad && item.disponible > 0) {color = 'amarillo';style_asignar = "";} else {color = 'rojo';}
                                //if(item.bloqueado == 1) style_asignar = " disabled ";
                                style_planificar = " style='display: none;' ";
                                style_dividir = " style='display: none;' ";
                                if(item.folio_xd == 1 && item.folio_xd_asignable == 0) {style_asignar = " disabled "; titleXD = "title='El pedido tipo CrossDocking debe estar relacionado a una entrada para poder ser asignado' ";}
                                if(item.Ship_Num != '' && item.folio_xd_asignable == 1) {entrada_xd = 'Entrada: '+item.Ship_Num;}
                                //if(item.es_ot == 1 || item.es_ot == 2 || item.es_ot == 3 || item.es_ot == 4 || item.es_ot == 5) 

                                /*
 && $("#ruta_pedido_list").val() != '') || ($("#ruta_pedido_list").val() == '' && $("#tipopedido").val() != '') || $("#tipopedido").val() == '4'
 */

                                if(($("#tipopedido").val() == 'P' || $("#tipopedido").val() == 'W') )//&& sub_pedido == ""
                                {
                                    style_dividir = " style='display: block;' ";
                                    style_planificar = " style='display: block;' "; 
                                }

                                if((($("#tipopedido").val() == 'R' || $("#tipopedido").val() == 'RI') && item.statusaurora != 0 && item.statusaurora == $("#almacen").val()) || (item.TipoOT == 'IMP_LP' && item.es_ot != 1)) 
                                {
                                    style_asignar = " disabled ";
                                    style_dividir = " style='display: none;' ";
                                }

                                console.log("item.status_pedido = ", item.status_pedido);
                                if(item.status_pedido != 'A')
                                    style_asignar = " style='display: none;' ";


                                var style_dividir = " style='display: none;' ";
                                if(item.num_registros > 1)
                                    style_dividir = " style='display: block;' ";

                                var rows_welldex = "";
                                if($("#instancia").val() == "welldex") 
                                    rows_welldex = '<td>'+item.Ref_Wel+'</td>'+
                                                   '<td>'+item.Ref_Imp+'</td>'+
                                                   '<td>'+item.Pedimento+'</td>'+
                                                   '<td>'+item.Factura_Vta+'</td>'+
                                                   '<td>'+item.Ped_Imp+'</td>';

                                var rows_iberofarmacos1 = '<td>'+sub_pedido+entrada_xd+'</td>'+
                                                          '<td>'+item.ruta_pedido+'</td>', 
                                    rows_iberofarmacos2 = '<td>'+item.orden_cliente+'</td>',
                                    rows_iberofarmacos3 = "";
                                if($("#instancia").val() == "iberofarmacos") 
                                {
                                    rows_iberofarmacos3 = rows_iberofarmacos1+rows_iberofarmacos2;
                                    rows_iberofarmacos1 = "";
                                    rows_iberofarmacos2 = "";
                                }

                                var observaciones = item.observaciones+'';
                                console.log("ITEMS = ", item);
                                row += '<tr>'+'<td align="center">'+imageFormat(item.orden, item.surtido, item.TieneOla, '', item.sufijo, is_backorder, item.cliente, item.asignado, '', item.folio_ws, item.status, item.status_pedido, item.fecha_pedido, item.es_ot, item.ruta_pedido, item.statusaurora, item.TipoOT, item.usuario_pedido, item.tiene_foto, item.EsTipoLP)+'</td>'+
//function imageFormat(id, surtido, tieneola, tienda, sufijo, is_backorder, cliente, surtidor, articulo_ot, folio_ws, status, status_pedido, fecha_pedido, es_ot, ruta_pedido, statusaurora, TipoOT)

                                    //'<td align="center"><input type="checkbox" /></td>'+
                                    '<td align="center" id="semaforo_'+item.id+"_"+item.sufijo+'" class="column-semaforo">'+'<i class="fa fa-circle '+color+'" aria-hidden="true"></i>'+'</td>'+
                                    '<td align="center" class="column-asignar" data-id="'+item.orden+'" data-subpedido="'+item.sufijo+'"><input type="checkbox" '+style_asignar+' '+titleXD+' data-id="'+item.orden+'" data-idpedido="'+item.id+'" data-subpedido="'+item.sufijo+'" data-disponible="'+item.disponible+'" class="check-asignar" id="asig'+item.orden+"-"+item.sufijo+'" /></td>'+
                                    '<td align="center" class="column-planificar" data-id="'+item.orden+'" data-sufijo="'+item.sufijo+'"><input type="checkbox" data-id="'+item.orden+'" data-sufijo="'+item.sufijo+'" '+style_planificar+' /></td>'+
                                    '<td align="center" class="column-dividir" data-id="'+item.orden+'"><input type="checkbox" '+style_dividir+' id="div-'+item.orden+'" data-id="'+item.orden+'" data-registros="'+item.num_registros+'" /></td>'+
                                    '<td align="center" style="display: none;" class="column-enviado" data-id="'+item.orden+'" data-subpedido="'+item.sufijo+'"><input type="checkbox" data-id="'+item.orden+'" data-subpedido="'+item.sufijo+'" /></td>'+
                                    '<td>'+item.orden+'</td>'+
                                    rows_iberofarmacos1 +
                                    '<td>'+item.cliente+'</td>'+
                                    rows_iberofarmacos2 +
                                    '<td>'+item.total_factura+'</td>'+
                                    '<td>'+observaciones.substring(0,30)+'...</td>'+
                                    //'<td>'+item.destinatario+'</td>'+
                                    '<td align="center">'+item.fecha_pedido+'</td>'+
                                    '<td align="center">'+item.fecha_aprobacion+'</td>'+
                                    '<td align="center">'+item.fecha_compromiso+'</td>'+
                                    rows_iberofarmacos3 +
                                    '<td align="center">'+item.rango_hora+'</td>'+
                                    '<td>'+item.prioridad+'</td>'+
                                    '<td>'+item.status+'</td>'+
                                    //'<td align="right">'+item.num_articulos+'</td>'+
                                    //'<td align="right">'+item.cantidad+'</td>'+
                                    //'<td align="right">'+item.cantidad_surtida+'</td>'+
                                    //'<td align="right">'+item.volumen+'</td>'+
                                    //'<td align="right">'+item.peso+'</td>'+
                                    '<td align="center">'+item.fecha_ini+'</td>'+
                                    '<td align="center">'+item.fecha_fi+'</td>'+
                                    //'<td align="center">'+item.TiempoSurtido+'</td>'+
                                    '<td>'+item.asignado+'</td>'+
                                    '<td>'+item.proyectos+'</td>'+
                                    //'<td align="center">'+item.surtido+'</td>'+
                                    //'<td>'+item.direccion+'</td>'+                                            
                                    //'<td align="center">'+item.dane+'</td>'+
                                    //'<td>'+item.ciudad+'</td>'+
                                    rows_welldex
                                    //'<td>'+item.Colonia+'</td>'+
                                    //'<td>'+item.estado+'</td>'+
                                +'</tr>';                            
                            //}
                        });
                        $('#dt-detalles tbody').html(row);
                        
                        if ($("#status").val() == 'A') {
                            if($("#permiso_registrar").val() == 1) $('#btn-asignar').show();
                            $('#planificalo').show();
                            $('#planificaloTodo').show();
                            $('#planificaloTodo').show();
                            //$('#btn-planificar').show();
                            $("#col-asignar, #col-planificar, #col-semaforo, #col-dividir").show();
                            $(".column-asignar, .column-planificar, .column-semaforo, .column-dividir").show();
                            $("#chb_asignar").show();
                            $("#chb_planificar").show();
                        }
                        else {
                            $('#btn-asignar').hide();
                            $('#planificalo').hide();
                            $('#planificaloTodo').hide();
                            $('#planificaloTodo').hide();
                            //$('#btn-planificar').hide();
                            $("#col-asignar, #col-planificar, #col-semaforo, #col-dividir").hide();
                            $(".column-asignar, .column-planificar, .column-semaforo, .column-dividir").hide();
                            $("#chb_asignar").hide();
                            $("#chb_planificar").hide();
                        }
                        
                    } else {
                        swal({
                            title: "Error",
                            text: "No se pudo planificar el inventario",
                            type: "error"
                        });
                    }
                }, error: function(data)
                {
                    console.log("res pedidos = ", data);
                }
            });                    
        }

        
        function asyncSqrt(callback) {
            setTimeout(function() {
                callback();
            }, 0 | Math.random() * 100);
        }
/*
        asyncSqrt(function() {
            console.log("ReloadGrid 3");
            ReloadGrid()
        });
*/
        var itemsArticulosPedidos = [];
        var itemActual = -1;
        var folio = '';


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
        
        var destinatario_folio = '';
        function destinatario( folio ){
            destinatario_folio = folio;
            $.ajax({
                url: '/api/administradorpedidos/lista/index.php',
                dataType: 'json',
                data: {
                    action: 'destinatarioDelPedido',
                    folio: folio
                },
                type: 'POST'
            }).done(function(data) {

                $("#destinatario_razonsocial").val(data.data.razonsocial);
                $("#destinatario_direccion").val(data.data.direccion);
                $("#destinatario_colonia").val(data.data.colonia);
                $("#destinatario_dane").val(data.data.postal);
                $("#destinatario_ciudad").val(data.data.ciudad);
                $("#destinatario_contacto").val(data.data.contacto);
                $("#destinatario_telefono").val(data.data.telefono);
                $("#destinatario_ciudad").val(data.data.ciudad);
                $("#destinatario_estado").val(data.data.departamento);

                $('#modal-destinatario').modal('show');

            });
        }

        function VerificarReabasto(almacen){
            $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                dataType: 'json',
                data: {
                    action: 'VerificarReabasto',
                    almacen: almacen
                },
                type: 'POST'
            }).done(function(data) {
                    console.log("*************Reabasto*************");
                    console.log("enlace = ", '<?php echo $_SERVER['HTTP_HOST']; ?>');
                    console.log("Reabasto ->", data);
                    if(data.datos == "1")
                        console.log("Reabasto Disponible");
                    else
                        console.log("Reabasto NO Disponible");
            });
        }

        function guardarDestinatario(){
            $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                dataType: 'json',
                data: {
                    action: 'guardarDestinatario',
                    folio: destinatario_folio,
                    razonsocial : $("#destinatario_razonsocial").val(),
                    direccion : $("#destinatario_direccion").val(),
                    colonia : $("#destinatario_colonia").val(),
                    postal : $("#destinatario_dane").val(),
                    ciudad : $("#destinatario_ciudad").val(),
                    contacto : $("#destinatario_contacto").val(),
                    telefono : $("#destinatario_telefono").val(),
                    estado : $("#destinatario_estado").val(),
                },
                type: 'POST'
            }).done(function(data) {

                swal("Éxito", "Datos del destinatario actualizado con éxito", "success");
                $('#modal-destinatario').modal('hide');

            });
        }

        function prepararSurtido() {

            console.log("con_recorrido =", $("#con_recorrido").val());
            var backorder_sin_recorrido_surtido = [];
            if($("#con_recorrido").val() != "1")
            {
                console.log("prepararSurtidoS()");
                console.log("detalle_subpedido",detalle_subpedido);
                var articulos_a_surtir = [], cantidad_enviada = 0, cantidad_total_pedido = 0, cantidad_total_a_surtir = 0;


                //if(item.clave == detalle_subpedido[i].Cve_articulo)
                //    articulos_a_surtir = [{Folio: item.folio, Sufijo: item.sufijo, Cve_Articulo: item.clave, Lote_Serie: item.lote, Cantidad: item.pedidas}];

                for(var i = 0; i < detalle_subpedido.length; i++)
                {
                    cantidad_enviada = 0;
                    cantidad_disponible = 0;
                    cantidad_total_pedido += parseFloat(detalle_subpedido[i].Num_cantidad);
                  window.detalle_pedido["articulos"].forEach(function (item,index){
                    console.log("item detalle",item);
                    console.log("index detalle",index);
                    if(item.clave == detalle_subpedido[i].Cve_articulo && (item.lote == $.trim(detalle_subpedido[i].Cve_Lote) || $.trim(detalle_subpedido[i].Cve_Lote) == '' || item.serie == $.trim(detalle_subpedido[i].Cve_Lote)) )
                    {
                        cantidad_enviada += parseFloat($("#surtido-item-"+index+"").val());
                        cantidad_total_a_surtir += parseFloat($("#surtido-item-"+index+"").val());
                    }
                  });
                  articulos_a_surtir.push({fol_folio: detalle_subpedido[i].fol_folio, Sufijo: detalle_subpedido[i].Sufijo, Cve_Articulo: detalle_subpedido[i].Cve_articulo, Cve_Lote: $.trim(detalle_subpedido[i].Cve_Lote), Num_cantidad: cantidad_enviada});
               }
               console.log("articulos_a_surtir",articulos_a_surtir);
               console.log("cantidad_total_a_surtir",cantidad_total_a_surtir);
               console.log("cantidad_total_pedido",cantidad_total_pedido);
               console.log("cantidades_disponibles",cantidades_disponibles);

                 var cero_articulos = true, cantidad_mayor = false, surtir_incompleto = false, excede_cantidad_ubicacion_lp = false;


                 for(var i = 0; i < detalle_subpedido.length; i++)
                 {
                    if(articulos_a_surtir[i].Num_cantidad > 0) 
                    {
                        cero_articulos = false;
                    }
                    if(articulos_a_surtir[i].Num_cantidad > parseFloat(detalle_subpedido[i].Num_cantidad))
                    {
                        cantidad_mayor = true;
                        //break;
                    }

                    if(articulos_a_surtir[i].Num_cantidad < parseFloat(detalle_subpedido[i].Num_cantidad))
                    {
                        backorder_sin_recorrido_surtido.push({fol_folio: detalle_subpedido[i].fol_folio, Sufijo: detalle_subpedido[i].Sufijo, Cve_Articulo: detalle_subpedido[i].Cve_articulo, Cve_Lote: $.trim(detalle_subpedido[i].Cve_Lote), Cantidad_Pedido: parseFloat(detalle_subpedido[i].Num_cantidad), Cantidad_BO: (parseFloat(detalle_subpedido[i].Num_cantidad)-articulos_a_surtir[i].Num_cantidad)});
                    }

                 }

                 for(var i = 0; i < cantidades_disponibles.length; i++)
                 {
                    if(parseFloat($("#surtido-item-"+i+"").val()) > cantidades_disponibles[i])
                        excede_cantidad_ubicacion_lp = true;
                 }
                console.log("backorder_sin_recorrido_surtido",backorder_sin_recorrido_surtido);


                 if(excede_cantidad_ubicacion_lp == true)
                 {
                    swal("Error", "No se puede surtir más de lo existente en el LP o Ubicación", "error");
                    return;
                 }
                 else if(cero_articulos == true)
                 {
                    swal("Error", "Se debe surtir una cantidad mayor a cero", "error");
                    return;
                 }
                 else if(cantidad_mayor == true)
                 {
                    swal("Error", "No se puede surtir más de lo solicitado", "error");
                    return;
                 }else if(cantidad_total_a_surtir < cantidad_total_pedido)
                 {
                    swal("Advertencia", "El pedido se surtirá de manera incompleta y se creará o se actualizará el respectivo Backorder", "warning");
                    //return;
                 }
                 backorder_array = backorder_sin_recorrido_surtido;
            }
              //return;

              
            $("#SurtirPedidoCompleto").show();

            if($("#tipopedido").val() != 'P' || $("#con_recorrido").val() == 0 || $("#PedidoTipoLP").val() == 'S') $("#SurtirPedidoCompleto").hide();

            if($("#status").val() == 'S' && $("#tipopedido").val() == 'RI' && $("#select_proveedores").val() == '' && $("#cve_proveedor").val() == '')
            {
                swal("Error", "Debe Seleccionar una Empresa|Proveedor", "error");
                return;
            }

            siguienteItem();
            
        }
/* ES LO QUE HACE ABRIRSE MODALES INNECESARIOS
        $('#modalItems').on('hidden.bs.modal', function(e) {
            itemActual = -1;
            articulos($("#fol_cambio").val(), 0, 0);
        });
*/
        //EDG117
        function siguienteItem() {
            $("#grid-table-surtido").jqGrid("clearGridData");
            if(window.detalle_pedido["articulos"].length > 0){
              $("#folio_surtido").html(window.detalle_pedido["articulos"][0].folio);
              $("#sufijo_surtido").val(window.detalle_pedido["articulos"][0].sufijo);
              window.detalle_pedido["articulos"].forEach(function (item,index){
                //console.log("item detalle",item);
                //console.log("backorder_array",backorder_array);

                if(item["surtidas"] == 0 && item.idy_ubica != '' && $('#surtido-item-' + index).val() > 0)
                {
                    //console.log("siguienteItem()");
                  obj = {
                      lp: item["LP"],
                      clave: item["clave"],
                      articulo: item["articulo"],
                      Lote: item["lote"],
                      Caducidad: item["caducidad"],
                      Serie: item["serie"],
                      bl: item["ubicacion"],
                      ruta: item["ruta"],
                      surtidor: item["surtidor"],
                      existencia: $('#surtido-item-' + index).val(), //item["existencia"],
                      ya_surtido: '<span id="surtido-item-' + item.ubicacion + item.clave + item.lote + item.serie + item.idy_ubica + '">'+item["surtidas"]+'</span>',
                      solicitado: item["pedidas"],
                      surtido: $('#surtido-item-' + index).val(),
                      //'<input id="surtir-item-' + index + '" value="'+item.existencia+'" type="text" style="width:60px;background-color: transparent;border: none;text-align:right;" readonly />',
                      acciones: '<button  onclick="guardarSurtidoPorUbicacion('+index+', '+$('#surtido-item-' + index).val()+', \''+((item["LP"]==undefined)?(""):(item["LP"]))+'\', \''+((item["LPNtarima"]==undefined)?(""):(item["LPNtarima"]))+'\')" type="button" class="btn btn-m btn-primary btnShowHide btn-block '+((item["LP"]==undefined || item["LP"] == "" || $("#con_recorrido").val() == 0)?(""):("TipoLP"))+' LP'+item["LPNtarima"]+'" id ="btn_surtir_'+index+'" style="width:85px; display:'+((!item["idy_ubica"])?'none':'block')+'">Surtir '+(((item["LP"]==undefined || item["LP"] == "") || $("#con_recorrido").val() == 0)?(""):("Todo"))+'</button>'
                  };
                  //item.clave + item.lote + item.serie + item.idy_ubica
                  //((item["clave"]).replace("/", "").replace("-", "").replace(".", "") + (item.lote).replace("/", "").replace("-", "").replace(".", "") + (item.serie).replace("/", "").replace("-", "").replace(".", "") + (item.idy_ubica).replace("/", "").replace("-", "").replace(".", "")).replace(" ", "")+"LP"+(item["LPNtarima"]).replace("/", "").replace("-", "").replace(".", "").replace(" ", "")+'"
                  //display:'+((item["surtidas"]>0)?'none':'block')+'
                  emptyItem = [obj];
                  $("#grid-table-surtido").jqGrid('addRowData', 0, emptyItem);
              }
              });
            }
            $('#modalItems').modal('show');
            $('#coModal').modal('hide');
        }
      
        function exportarPDFCodigoBarras(folio)
        {
                console.log(folio);
          
                    var folio = folio,
                            form = document.createElement('form'),
                            input_nofooter = document.createElement('input'),
                            input_folio = document.createElement('input'),
                            input_proveedor = document.createElement('input');

                    form.setAttribute('method', 'post');
                    form.setAttribute('action', '/pedidos/export/informe');//Class: ReportePDF/EntradasPDF.php->generarReporteEntradas($folio);
                    form.setAttribute('target', '_blank');

                    input_nofooter.setAttribute('name', 'nofooternoheader');
                    input_nofooter.setAttribute('value', '1');

                    input_folio.setAttribute('name', 'folio');
                    input_folio.setAttribute('value', folio);
                    //input_proveedor.setAttribute('name', 'proveedor');
                    //input_proveedor.setAttribute('value', proveedor);

                    form.appendChild(input_nofooter);
                    form.appendChild(input_folio);
                    //form.appendChild(input_proveedor);

                    document.getElementsByTagName('body')[0].appendChild(form);
                    form.submit();
                  
        }

        function exportarPDFCodigoBarrasConsolidado(folio)
        {
                console.log(folio);

                var folio = folio,
                        form = document.createElement('form'),
                        input_nofooter = document.createElement('input'),
                        input_folio = document.createElement('input'),
                        input_proveedor = document.createElement('input');

                form.setAttribute('method', 'post');
                form.setAttribute('action', '/pedidos/export/informeconsolidado');//Class: ReportePDF/EntradasPDF.php->generarReporteEntradas($folio);
                form.setAttribute('target', '_blank');

                input_nofooter.setAttribute('name', 'nofooternoheader');
                input_nofooter.setAttribute('value', '1');

                input_folio.setAttribute('name', 'folio');
                input_folio.setAttribute('value', folio);
                //input_proveedor.setAttribute('name', 'proveedor');
                //input_proveedor.setAttribute('value', proveedor);

                form.appendChild(input_nofooter);
                form.appendChild(input_folio);
                //form.appendChild(input_proveedor);

                document.getElementsByTagName('body')[0].appendChild(form);
                form.submit();
                  
        }

        $("#SurtirPedidoCompleto").click(function()
        {
            console.log("Folio Surtido = ", $("#folio_surtido").html());
            console.log("Sufijo Surtido = ", $("#sufijo_surtido").val());

            $.ajax({
              url: '/api/administradorpedidos/update/index.php',
              dataType: 'json',
              data: {
                action: 'SurtirPedidoCompleto',
                folio: $("#folio_surtido").html(),
                sufijo:$("#sufijo_surtido").val()
              },
              type: 'POST'
            }).done(function(data) {
                finalizar_surtido(0, 'P', '');
            }
            ).fail(function(data){console.log("ERROR SURTIDO", data);});
          

        });

        function guardarSurtidoPorUbicacion(item, existencia_surtir, lp, LpNtarima) 
        {
         /* var myData = $("#grid-table-surtido").jqGrid('getRowData');
         
          var camposurtido = (myData[0].ya_surtido);
          var allRowsOnCurrentPage = $('#grid-table-surtido').jqGrid('getDataIDs');
          console.log(allRowsOnCurrentPage);
          return;
          var rowData = $('#my-jqgrid-table').jqGrid('getRowData', rowId);
          rowData.Currency = '12321';
          $('#my-jqgrid-table').jqGrid('setRowData', rowId, rowData);
          return;*/
          //return;

            window.detalle_pedido["articulos"][item]["existencia"] = existencia_surtir;
          var articulo = window.detalle_pedido["articulos"][item];
          console.log("window.detalle_pedido[articulos][item] = ", window.detalle_pedido["articulos"][item]);
          //var ultimo = (item == (window.detalle_pedido["articulos"].length-1))?true:false;
          var id_boton = articulo.clave+articulo.lote+articulo.serie+articulo.idy_ubica+"LP"+LpNtarima;
          id_boton = id_boton.replace("/", "").replace(".", "").replace("-", "");
          id_boton = id_boton.replace(" ", "");
          console.log("BOTON SURTIR ID = #btn_surtir_"+item);
          //$("#btn_surtir_"+id_boton.toString()+"").hide();
          $("#btn_surtir_"+item+"").hide();
          var surtir = 0;
          var existencia = 0;
          var surtir_pedido = true;
          var ultimo = true;

          //if(lp != '') $(".LP"+LpNtarima+"").hide();
          if(lp != '') $(".TipoLP").hide();
          

            //console.log("detalle_pedido BOTON SURTIR = ", window.detalle_pedido["articulos"]);
  /*
            var id_boton2 = "";
  
            window.detalle_pedido["articulos"].forEach(function (ittem,index)
            {
                    console.log("BOTON SURTIR ID = #btn_surtir_"+ittem["clave"]+ ittem["lote"] + ittem["serie"] + ittem["idy_ubica"]+LpNtarima);
                    id_boton2 = ittem["clave"]+ ittem["lote"] + ittem["serie"] + ittem["idy_ubica"]+"LP"+LpNtarima;
                    id_boton2 = id_boton2.replace("/", "").replace(".", "").replace("-", "");
                    id_boton2 = id_boton2.replace(" ", "");
                    if($("#btn_surtir_"+id_boton2).is(":visible"))
                       ultimo = false;
            });
*/
            
            $(".btnShowHide").each(function ()
            {
                //console.log("ittem = ", ittem, " index = ", index);
                    if($(this).is(":visible"))
                       ultimo = false;
            });

            //articulo.clave+articulo.lote+articulo.serie+articulo.idy_ubica
          surtir = parseInt($("#surtir-item-"+item).val());
          //surtir = existencia_surtir;
          //console.log("SURTIR ITEM = #surtir-item-"+articulo.clave+articulo.lote+articulo.serie+articulo.idy_ubica);
          //console.log("SURTIR ITEM VAL = ", $("#surtir-item-"+articulo.clave+articulo.lote+articulo.serie+articulo.idy_ubica).val());
          
          existencia = parseInt(articulo.existencia);


            console.log("surtir->articulo = ", articulo);
            console.log("surtir->ultimo = ", ultimo);
            console.log("surtir->surtir = ", surtir);
            console.log("surtir->existencia = ", existencia);

          if(surtir <= existencia)
          {
            articulo["surtidas"] = surtir;
            window.detalle_pedido["articulos"][item]["surtidas"] = surtir;
          }
/*          
          else
          {
            alert("No se puede colocar un valor mayor a lo solicitado 1: clave del producto: "+ articulo.clave);
            surtir_pedido = false;
          }
*/
          if(surtir > articulo["surtidas"])
          {
            alert("No se puede colocar un valor mayor a lo solicitado: clave del producto: "+ articulo.clave);
            surtir_pedido = false;
          }

          console.log("****************************************");
          console.log("****************************************");
          console.log("almacen = ", $('#almacen').val());
          console.log("items = ", articulo);
          console.log("folio = ", $('#fol_cambio').val());
          console.log("ultimo = ", ultimo);
          console.log("surtir_pedido = ", surtir_pedido);
          console.log("lp = ", lp);
          console.log("proveedor = ", ($("#cve_proveedor").val() == '')?($("#select_proveedores").val()):($("#cve_proveedor").val()));
          console.log("backorder_array",backorder_array);
          console.log("window.detalle_pedido",window.detalle_pedido["articulos"]);
          console.log("****************************************");
          console.log("****************************************");
          //return;
          if(surtir_pedido)
          {
            var actionSurtido = 'guardarSurtidoPorUbicacion';
            $("#SurtirPedidoCompleto").show();
            if((lp != '' && $("#con_recorrido").val() == 1) || $("#PedidoTipoLP").val() == 'S') {actionSurtido = 'guardarSurtidoPorLP';$("#SurtirPedidoCompleto").hide();}

            if($("#tipopedido").val() != 'P') $("#SurtirPedidoCompleto").hide();
            $.ajax({
              url: '/api/administradorpedidos/update/index.php',
              dataType: 'json',
              data: {
                almacen: $('#almacen').val(),
                action: actionSurtido,
                con_recorrido: $("#con_recorrido").val(),
                items: articulo,
            id_proveedor: ($("#cve_proveedor").val() == '')?($("#select_proveedores").val()):($("#cve_proveedor").val()),
                folio: $("#fol_cambio").val(),
                backorder_array: backorder_array,
                ultimo:ultimo
              },
              type: 'POST'
            }).done(function(data) {
              console.log("Surtiendo pedido",data);

                if(data.borro_de_t_recorrido_surtido == false)
                {
                    swal("Articulo ya surtido", "Este artículo ya se ha surtido anteriormente", "error");
                    $("#btn_surtir_"+item+"").hide();
                    return;
                }
                var id_boton = articulo.ubicacion + articulo.clave + articulo.lote + articulo.serie + articulo.idy_ubica;
                id_boton = id_boton.replace("/", "").replace(".", "");
                id_boton = id_boton.replace(" ", "");

                var id_boton2 = articulo.clave+articulo.lote+articulo.serie+articulo.idy_ubica;
                id_boton2 = id_boton2.replace("/", "").replace(".", "");
                id_boton2 = id_boton2.replace(" ", "");

              $("#surtido-item-"+item).html(surtir);
              $("#surtir-item-"+id_boton2).val(articulo.existencia-surtir);

                      if(ultimo)
                      {
                        //colocar la funcion que llama el boton de finalizar 
                        finalizar_surtido(data.porcentaje, data.es_ot, lp);
                      }

            }
            ).fail(function(data){console.log("ERROR SURTIDO", data);});
          }
  
/*
          if(ultimo)
          {
            //colocar la funcion que llama el boton de finalizar 
            finalizar_surtido();
          }
*/
        }

        function finalizar_surtido(porc, tipo_pedido, lp)
        {
            window.porcentajeSurtido = 0;
            var total = 0;
            var p_surtido = 0;

            console.log("finalizar_surtido()");
            console.log("#fol_cambio = ",$("#fol_cambio").val());
            console.log("#tipo_pedido = ",tipo_pedido);
            console.log("finalizar_surtido()");

            var folio = $("#fol_cambio").val();
            //if(!folio.includes("OT"))
/*
            if(es_rb != '0')
            {
            $.ajax({
              url: '/api/administradorpedidos/update/index.php',
              dataType: 'json',
              data: {
                action: 'FinalizarReabasto',
                folio: $("#fol_cambio").val()
              },
              type: 'POST'
            }).done(function(data) 
            {
                swal({
                        title: "Reabasto Realizado",
                        text: "El traslado "+folio+" ha sido realizado con éxito",
                        type: "success",

                        showCancelButton: false,
                        cancelButtonText: "No",
                        cancelButtonColor: "#14960a",

                        confirmButtonColor: "#55b9dd",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(e) {

                        if (e == true) {
                            //Reabasto
                            window.location.reload();
                        }

                    });
            }
            ).fail(function(data){console.log("ERROR REABASTO", data);});



            }
            else 
*/
            if(tipo_pedido == 'P')
            {
                /*
                 $.ajax({
                     url: '/api/administradorpedidos/update/index.php',
                     data: {
                        action: 'traerporcentaje',
                        folio: $("#fol_cambio").val()
                      },
                      type: 'POST',
                      dataType: 'json'
                      })
                      .done(function(data) {
                      console.log("porcentaje surtido",data.resp);
                      window.porcentajeSurtido = parseInt(data.resp);
                      enviar_finalizar_pedido();
                 });
                 */
                  console.log("porcentaje surtido",porc);
                  window.porcentajeSurtido = parseInt(porc);
                  enviar_finalizar_pedido(lp);
            }
            else if(tipo_pedido == 'W' || tipo_pedido == 'W2')
            {
                obtenerZonasDisponibles();
            }
            else if(tipo_pedido == 'T')
            {
                console.log($("#fol_cambio").val());
                obtenerUbicacionManufactura();
            }
            else if(tipo_pedido == 'RI')
            {
                console.log($("#fol_cambio").val());
                obtenerAreasRecepcion();
            }

        }
      
        function enviar_finalizar_pedido(lp)
        {
             //if (window.porcentajeSurtido >= 99) {
                /*
               $.ajax({
                 url: '/api/administradorpedidos/update/index.php',
                 data: {
                    almacen:$('#almacen').val(),
                    folio:  $("#fol_cambio").val(),
                    action: 'horafin_surtir'
                  },
                  type: 'POST',
                  dataType: 'json'
                  })
                  .done(function(data) {
                    console.log("horafin",data);
                    console.log("enviar a qa");
                    enviarAEbarque_o_Qa();
                  });
                  */
                  enviarAEbarque_o_Qa(lp);
                 
                //}
                /* else {
                    $('#modalItems').modal('hide');
                    var status = "";
                    console.log("finalizar_almacen = ", $('#almacen').val());
                    console.log("finalizar_fol_cambio = ", $("#fol_cambio").val());
                    console.log("finalizar_sufijo = ", window.detalle_pedido["articulos"][0]["sufijo"]);
                    console.log("finalizar_status = ", status);

                    swal({
                        title: "Surtido completado",
                        text: "Pedido Incompleto , ¿Desea cerrar el pedido?",
                        type: "success",

                        showCancelButton: true,
                        cancelButtonText: "Si",
                        cancelButtonColor: "#14960a",

                        confirmButtonColor: "#55b9dd",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(e) {
                        if (e == true) 
                        {
                            //No
                            swal("Éxito", "Surtido realizado con artículos pendientes por surtir por falta de existencia", "warning");
                        } else {
                            //Si
                            $.ajax({
                              url: '/api/administradorpedidos/update/index.php',
                              data: {
                                  almacen:$('#almacen').val(),
                                  folio:  $("#fol_cambio").val(),
                                  sufijo: window.detalle_pedido["articulos"][0]["sufijo"],
                                  status: status,
                                  action: 'cerrarPedido'
                                  
                              },
                              type: 'POST',
                              dataType: 'json'
                            })
                            .done(function(data) {
                                if (data.success) {
                                    console.log("filtralo 1");
                                    filtralo();
                                    $('#modalItems').modal('hide');
                                    swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                                } else {
                                    swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                                }
                            });
                        }
                    });
                }
                */
        }

        function enviarAEbarque_o_Qa(lp) {

            var boton = true;
            //if(lp != '') 
            if($("#PedidoTipoLP").val() == 'S') {boton = false;}
            swal({
                    title: "Surtido completado",
                    text: "¿A donde desea enviar el pedido?",
                    type: "success",

                    showCancelButton: boton,
                    cancelButtonText: "Enviar a QA (Auditar)",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "Enviar a embarque",
                    closeOnConfirm: true
                },
                function(e) {
                    if (e == true) {
                        //Embarque
                        obtenerZonasDisponibles();
                    } else if(boton == true){
                        //QA
                        var status = 'L';
                        var folio = $("#fol_cambio").val();

                        console.log("enviarAEbarque_o_Qa()");
                        console.log("folio = ", folio);
                        console.log("sufijo = ", (window.detalle_pedido["articulos"].length>0)?(window.detalle_pedido["articulos"][0]["sufijo"]):($("#suf_cambio").val()));
                        console.log("status = ", status);
                        console.log("enviarAEbarque_o_Qa()");

                        $.ajax({
                                url: '/api/administradorpedidos/update/index.php',
                                data: {
                                    folio: $("#fol_cambio").val(),
                                    sufijo: (window.detalle_pedido["articulos"].length>0)?(window.detalle_pedido["articulos"][0]["sufijo"]):($("#suf_cambio").val()),
                                    status: 'L',
                                    almacen: $('#almacen').val(),
                                    motivo: 'QASEND',
                                    action: 'cambiarStatus'
                                },
                                type: 'POST',
                                dataType: 'json'
                            })
                            .done(function(data) {
                                console.log("QA -> ",data);
                                console.log("QA data.success -> ",data.success);
                                console.log("QA data.msj -> ",data.msj);
                                console.log("QA data.folio -> ",data.folio);
                                console.log("QA data.status -> ",data.status);
                                /* || data.folio == 'undefined' || data.folio == '' || $("#fol_cambio").val() != data.folio /*mientras veo que pasa con el folio*/
                                window.location.reload(); //PROVISIONAL MIENTRAS SE ARREGLA LO DE CAMBIO DE ESTATUS 
                                if (data.success == true) {
                                    //filtralo();
                                    $('#modalItems').modal('hide');
                                    swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                                    window.location.reload();
                                } 
                                //else swal("Cambio No Permitido", data.msj, "error");//PROVISIONAL MIENTRAS SE ARREGLA LO DE CAMBIO DE ESTATUS 
                            })/*.fail(function(data){
                                console.log("QA ERROR -> ",data);
                            })*/;
                    }
                });
        }

        function obtenerZonasDisponibles() {
            $.ajax({
                    url: '/api/administradorpedidos/lista/index.php',
                    data: {
                        folio: $("#fol_cambio").val(),
                        almacen: $('#almacen').val(),
                        action: 'obtenerZonasEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {

                        options = '';
                        $.each(data.data, function(key, value) {
                            options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                        });

                        $('#zonaembarque').html(options);
                        $('#zonaembarque').trigger("chosen:updated");
                        $('#modal_zonasembarque').modal('show');
                    } else {

                    }
                });
        }


        function asignarZonaEmbarque() {
            console.log("sufijo asignarZonaEmbarque() = ", (window.detalle_pedido["articulos"].length>0)?(window.detalle_pedido["articulos"][0]["sufijo"]):($("#suf_cambio").val()));

            $.ajax({
                    url: '/api/administradorpedidos/update/index.php',
                    data: {
                        folio: $("#fol_cambio").val(),
                        almacen: $('#almacen').val(),
                        status: 'C',
                        sufijo: (window.detalle_pedido["articulos"].length>0)?(window.detalle_pedido["articulos"][0]["sufijo"]):($("#suf_cambio").val()),
                        zonaembarque: $('#zonaembarque').val(),
                        action: 'asignarZonaEmbarque'
                    },
                    type: 'POST',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.success) {
                        $('#modal_zonasembarque').modal('hide');
                        $('#modalItems').modal('hide');
                        //swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                        swal("Éxito", "Pedido Enviado a Embarques exitosamente", "success");

                        console.log("filtralo 2");
                        filtralo();
                        window.location.reload();
                    } else {
                        //swal("Error", "Ocurrió un error al cambiar el status de la orden", "success");
                        swal("Error", "Ocurrió un error al enviar el pedido a Embarques", "success");
                    }
                }).fail(function(data){
                    console.log("ERROR EMBARQUE = ", data);
                });
        }
      

        function obtenerAreasRecepcion() {
            $.ajax({
                url: '/api/administradorpedidos/lista/index.php',
                data: {
                    folio: $("#fol_cambio").val(),
                    action: 'obtenerAreasRecepcion'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                if (data.success) {

                    options = '';
                    $.each(data.data, function(key, value) {
                        options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                    });

                    $('#arearecepcion').html(options);
                    $('#arearecepcion').trigger("chosen:updated");
                    $('#modal_areas_recepcion').modal('show');
                } else {

                }
            });
        }

        function obtenerUbicacionManufactura() {
            $.ajax({
                url: '/api/administradorpedidos/lista/index.php',
                data: {
                    almacen: $('#almacen').val(),
                    action: 'obtenerUbicacionManufactura'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                if (data.success) {

                    options = '';
                    $.each(data.data, function(key, value) {
                        options += '<option value="' + value.id + '">' + value.nombre + '</option>';
                    });

                    $('#ubicacionManufactura').html(options);
                    $('#ubicacionManufactura').trigger("chosen:updated");
                    $('#modal_ubicacion_manufactura').modal('show');
                } else {

                }
            });
        }


        function asignarAreaRecepcion() 
        {
            console.log('almacen: ', $("#almacen").val());
            console.log('folio: ', $("#fol_cambio").val());
            console.log('arearecepcion: ', $("#arearecepcion").val());

            $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                data: {
                    arearecepcion:$('#arearecepcion').val(),
                    folio:  $("#fol_cambio").val(),
                    action: 'asignarAreaRecepcion'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                if (data.success) {
                    //filtralo();
                    $('#modalItems').modal('hide');
                    $('#modal_areas_recepcion').modal('hide');
                    swal("Éxito", "Surtido Completo", "success");
                    window.location.reload();
                } else {
                    swal("Error", "Ocurrió un error al realizar el surtido", "error");
                }
            }).fail(function(data){
                console.log("ERROR asignarAreaRecepcion() = ", data);
            });

        }

        function asignarUbicacionManufactura() 
        {
            console.log('ubicacion: ', $('#ubicacionManufactura').val());
            console.log('almacen: ', $("#almacen").val());
            console.log('folio: ', $("#fol_cambio").val());
            console.log("sufijo asignarZonaEmbarque() = ", (window.detalle_pedido["articulos"].length>0)?(window.detalle_pedido["articulos"][0]["sufijo"]):($("#suf_cambio").val()));
            $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                data: {
                    ubicacion:$('#ubicacionManufactura').val(),
                    almacen:$("#almacen").val(),
                    folio:  $("#fol_cambio").val(),
                    sufijo: (window.detalle_pedido["articulos"].length>0)?(window.detalle_pedido["articulos"][0]["sufijo"]):($("#suf_cambio").val()),
                    action: 'existenciasManufactura'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                console.log("asignarUbicacionManufactura() = ", data);
                if (data.success) {
                    //filtralo();
                    $('#modalItems').modal('hide');
                    $('#modal_ubicacion_manufactura').modal('hide');
                    swal("Éxito", "Se han movido las existencias a manufactura", "success");
                    //window.location.reload();
                } else {
                    swal("Error", "Ocurrió un error al mover las existencias a manufactura", "error");
                }
            })/*.fail(function(data){
                console.log("ERROR asignarUbicacionManufactura() = ", data);
            })*/;

        }
    </script>


    <!-- Grid de inventario -->
    <script type="text/javascript">
        function isParamsEmpty() {
            if ($("#criteriob").val() == '' && $("#fechai").val() == '' && $("#fechaf").val() == '' && $("#status").val() == '') //&& $("#filtro").val() == ''//
            {
                return true;
            }
            return false;
        }


        /**
         * @author Ricardo Delgado.
         * Busca y selecciona el almacen predeterminado para el usuario.
         */

        function almacenPrede() {
            console.log("predeterminado = ", <?php echo $_SESSION["id_user"]?>);
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
                            console.log("filtralo 3");
                            filtralo();
                        }, 1000);
                        //VerificarReabasto(data.codigo.id);
                    }
                },
                error: function(res) {
                    window.console.log(res);
                }
            });
        }
        almacenPrede();

        //////////////////////////////////////Aqui se contruye el Grid de inventario//////////////////////////////////////////////
/*
  function alertarefresh()
  {
    $(function($)
    {
      var html="x";
      $.ajax({
        url: '/api/correcciondir/lista/index.php',
        dataType: 'json',
        type: 'POST'
      }).done(function(data){
        //console.log(data["rows"].length);
        if(data["rows"].length >= 1)
          html = "<div style='margin-bottom:0;' class='alert alert-warning' role='alert'>Hay "+data["rows"].length+" pedidos sin Guias generadas <a class='btn btn-primary btn-sm' href='http://wms.nikken.assistpro-adl.com/correcciondir/lists'>Ver</a></div>";
        else
          html="";
        $("#alerta").html(html);
      });
    });
  }
  
  alertarefresh();
  var i_alerta = setInterval(alertarefresh,20000);
*/
      
      
        $('#dt-detalles .page').keypress(function(e) {
            if(e.which == 13) {
                console.log("filtralo 4");
                filtralo()
            }
        });

        $('#dt-detalles .count').change(function() {       
            console.log("filtralo 5");
            filtralo()        
        });
      ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $('#exportalo').click(function(){
          console.log("exportar");
          fnExcelReport();
          
          
        });
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        function fecha()
        {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();
            if(dd<10) { dd = '0'+dd } 
            if(mm<10) { mm = '0'+mm } 
            return dd + '-' + mm + '-' + yyyy;
        }
      
        "use strict";jQuery.base64=(function($){var _PADCHAR="=",_ALPHA="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",_VERSION="1.0";function _getbyte64(s,i){var idx=_ALPHA.indexOf(s.charAt(i));if(idx===-1){throw"Cannot decode base64"}return idx}function _decode(s){var pads=0,i,b10,imax=s.length,x=[];s=String(s);if(imax===0){return s}if(imax%4!==0){throw"Cannot decode base64"}if(s.charAt(imax-1)===_PADCHAR){pads=1;if(s.charAt(imax-2)===_PADCHAR){pads=2}imax-=4}for(i=0;i<imax;i+=4){b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6)|_getbyte64(s,i+3);x.push(String.fromCharCode(b10>>16,(b10>>8)&255,b10&255))}switch(pads){case 1:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6);x.push(String.fromCharCode(b10>>16,(b10>>8)&255));break;case 2:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12);x.push(String.fromCharCode(b10>>16));break}return x.join("")}function _getbyte(s,i){var x=s.charCodeAt(i);if(x>255){throw"INVALID_CHARACTER_ERR: DOM Exception 5"}return x}function _encode(s){if(arguments.length!==1){throw"SyntaxError: exactly one argument required"}s=String(s);var i,b10,x=[],imax=s.length-s.length%3;if(s.length===0){return s}for(i=0;i<imax;i+=3){b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8)|_getbyte(s,i+2);x.push(_ALPHA.charAt(b10>>18));x.push(_ALPHA.charAt((b10>>12)&63));x.push(_ALPHA.charAt((b10>>6)&63));x.push(_ALPHA.charAt(b10&63))}switch(s.length-imax){case 1:b10=_getbyte(s,i)<<16;x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_PADCHAR+_PADCHAR);break;case 2:b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8);x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_ALPHA.charAt((b10>>6)&63)+_PADCHAR);break}return x.join("")}return{decode:_decode,encode:_encode,VERSION:_VERSION}}(jQuery));
      
        function fnExcelReport()
          {
              var tab_text="<table id='download_table' border='2px'><tr bgcolor='#87AFC6'>";
              var textRange; var j=0;
              tab = document.getElementById('dt-detalles'); // id of table

              for(j = 0 ; j < tab.rows.length-1 ; j++) 
              {     
                  tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
              }

              tab_text=tab_text+"</table>";
              tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
              tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
              tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params
            
              $("#inner_download").html(tab_text);
            
              $("#download_table tr").each(function() {
                  console.log($(this).find("td:eq(0),th:eq(0)").remove());
                  console.log($(this).find("td:eq(0),th:eq(0)").remove());
                  console.log($(this).find("td:eq(0),th:eq(0)").remove());
                  console.log($(this).find("td:eq(-1),th:eq(-1)").remove());
              });
            
              tab_text = $("#inner_download").html();
            
              //tab_text = decodeURIComponent(escape(tab_text));
            
              $("#inner_download").html('');
              
              var filename = "pedidos_"+fecha()+ ".xls";
              
            
              //tableToExcel(tab_text,'Rastreo',filename);
            
              uri = 'data:application/vnd.ms-excel;base64,' + $.base64.encode(tab_text);
              console.log(uri);
              var link = document.createElement("a");    
              link.href = uri;
              link.style = "visibility:hidden";
              link.download = filename;
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
              
              //return (sa);
          }
      

        $("#generarExcel").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);

            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos/export.php?almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");

        });
/*
        $("#generarExcelPedidos").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+'&status='+$("#status").val());

            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_general/export.php?almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+'&status='+$("#status").val()+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");

        });
*/

        $("#generarExcelPedidos").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            //console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);
/*
            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_general/export.php?pedidos_articulos=1&almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+'&status='+$("#status").val()+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");
*/
            var folio = folio,
                form   = document.createElement("form"),
                input1 = document.createElement("input"),
                input2 = document.createElement("input"),
                input3 = document.createElement("input"),
                input4 = document.createElement("input"), 
                input5 = document.createElement("input"), 
                input6 = document.createElement("input"), 
                input7 = document.createElement("input"), 
                input8 = document.createElement("input"), 
                input9 = document.createElement("input");
                    //var tipo = (tipo == "OC")?1:0;
                
            input1.setAttribute('name', 'almacen');
            input1.setAttribute('value', almacen);
            input2.setAttribute('name', 'criterio');
            input2.setAttribute('value', criterio);
            input3.setAttribute('name', 'tipopedido');
            input3.setAttribute('value', tipopedido);
            input4.setAttribute('name', 'status');
            input4.setAttribute('value', $("#status").val());
            input5.setAttribute('name', 'ruta_pedido_list');
            input5.setAttribute('value', ruta_pedido_list);
            input6.setAttribute('name', 'ciudad_pedido_list');
            input6.setAttribute('value', ciudad_pedido_list);
            input7.setAttribute('name', 'fechaInicio');
            input7.setAttribute('value', fechaInicio);
            input8.setAttribute('name', 'fechaFin');
            input8.setAttribute('value', fechaFin);
            input9.setAttribute('name', 'action');
            input9.setAttribute('value', 'generarExcelPedidos');
            form.setAttribute('action', '/api/administradorpedidos/update/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            form.appendChild(input5);
            form.appendChild(input6);
            form.appendChild(input7);
            form.appendChild(input8);
            form.appendChild(input9);
            document.body.appendChild(form);
            form.submit();
        });

        $("#generarReporteSalidas").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            //console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);
/*
            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_general/export.php?pedidos_articulos=1&almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+'&status='+$("#status").val()+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");
*/
            var folio = folio,
                form   = document.createElement("form"),
                input1 = document.createElement("input"),
                input2 = document.createElement("input"),
                input3 = document.createElement("input"),
                input4 = document.createElement("input"), 
                input5 = document.createElement("input"), 
                input6 = document.createElement("input"), 
                input7 = document.createElement("input"), 
                input8 = document.createElement("input"), 
                input9 = document.createElement("input");
                    //var tipo = (tipo == "OC")?1:0;
                
            input1.setAttribute('name', 'almacen');
            input1.setAttribute('value', almacen);
            input2.setAttribute('name', 'criterio');
            input2.setAttribute('value', criterio);
            input3.setAttribute('name', 'tipopedido');
            input3.setAttribute('value', tipopedido);
            input4.setAttribute('name', 'status');
            input4.setAttribute('value', $("#status").val());
            input5.setAttribute('name', 'ruta_pedido_list');
            input5.setAttribute('value', ruta_pedido_list);
            input6.setAttribute('name', 'ciudad_pedido_list');
            input6.setAttribute('value', ciudad_pedido_list);
            input7.setAttribute('name', 'fechaInicio');
            input7.setAttribute('value', fechaInicio);
            input8.setAttribute('name', 'fechaFin');
            input8.setAttribute('value', fechaFin);
            input9.setAttribute('name', 'action');
            input9.setAttribute('value', 'ReporteDeSalidas');
            form.setAttribute('action', '/api/administradorpedidos/update/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            form.appendChild(input5);
            form.appendChild(input6);
            form.appendChild(input7);
            form.appendChild(input8);
            form.appendChild(input9);
            document.body.appendChild(form);
            form.submit();
        });

        $("#generarExcelPedidosArticulos").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);
/*
            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_general/export.php?pedidos_articulos=1&almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+'&status='+$("#status").val()+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");
*/
            var folio = folio,
                form   = document.createElement("form"),
                input1 = document.createElement("input"),
                input2 = document.createElement("input"),
                input3 = document.createElement("input"),
                input4 = document.createElement("input"), 
                input5 = document.createElement("input"), 
                input6 = document.createElement("input"), 
                input7 = document.createElement("input"), 
                input8 = document.createElement("input"), 
                input9 = document.createElement("input");
                    //var tipo = (tipo == "OC")?1:0;
                
            input1.setAttribute('name', 'almacen');
            input1.setAttribute('value', almacen);
            input2.setAttribute('name', 'criterio');
            input2.setAttribute('value', criterio);
            input3.setAttribute('name', 'tipopedido');
            input3.setAttribute('value', tipopedido);
            input4.setAttribute('name', 'status');
            input4.setAttribute('value', $("#status").val());
            input5.setAttribute('name', 'ruta_pedido_list');
            input5.setAttribute('value', ruta_pedido_list);
            input6.setAttribute('name', 'ciudad_pedido_list');
            input6.setAttribute('value', ciudad_pedido_list);
            input7.setAttribute('name', 'fechaInicio');
            input7.setAttribute('value', fechaInicio);
            input8.setAttribute('name', 'fechaFin');
            input8.setAttribute('value', fechaFin);
            input9.setAttribute('name', 'action');
            input9.setAttribute('value', 'ExcelResumenPedidosArticulos');
            form.setAttribute('action', '/api/administradorpedidos/update/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            form.appendChild(input5);
            form.appendChild(input6);
            form.appendChild(input7);
            form.appendChild(input8);
            form.appendChild(input9);
            document.body.appendChild(form);
            form.submit();
        });

        $("#generarExcelStatus").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);

            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_status/export.php?almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+'&status='+$("#status").val()+"&fechaFin="+fechaFin+"");

        });

        $("#generarExcelConsolidado").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);

            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_consolidado/export.php?almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");

        });

        $("#generarExcelSabores").click(function(){

            var almacen = $("#almacen").val(),
            criterio = $("#criteriob").val(),
            tipopedido = $("#tipopedido").val(),
            ciudad_pedido_list = $("#ciudad_pedido_list").val(),
            ruta_pedido_list = $("#ruta_pedido_list").val(),
            fechaInicio = $("#fechai").val(),
            fechaFin = $("#fechaf").val();

            console.log("almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin);

            $(this).attr("href", "/api/koolreport/excel/detalle_pedidos_consolidado_sabores/export.php?almacen="+almacen+"&criterio="+criterio+"&tipopedido="+tipopedido+"&ruta_pedido_list="+ruta_pedido_list+"&ciudad_pedido_list="+ciudad_pedido_list+"&fechaInicio="+fechaInicio+"&fechaFin="+fechaFin+"");

        });


      

      function filtralo() 
      {
        if ($("#almacen").val() === '')
        {
          swal("Error","Seleccione un almacen","warning");
          return false;
        }
        if ($("#status").val() == 'A') 
        {
            if($("#permiso_registrar").val() == 1) $('#btn-asignar').show();
          $('#planificalo').show();
          $('#planificaloTodo').show();
          $('#planificaloTodo').show();
          //$('#btn-planificar').show();
          $("#col-asignar, #col-planificar, #col-semaforo, #col-dividir").show();
          $(".column-asignar, .column-planificar, .column-semaforo, .column-dividir").show();
          $("#chb_asignar").show();
          $("#chb_planificar").show();
        }
        else 
        {
          $('#btn-asignar').hide();
          $('#planificalo').hide();
          $('#planificaloTodo').hide();
          $('#planificaloTodo').hide();
          //$('#btn-planificar').hide();
          $("#col-asignar, #col-planificar, #col-semaforo, #col-dividir").hide();
          $(".column-asignar, .column-planificar, .column-semaforo, .column-dividir").hide();
          $("#chb_asignar").hide();
          $("#chb_planificar").hide();
        }

          if($("#tipopedido").val() == '') {console.log(".column-planificar input HIDE");$(".column-planificar input").hide();}
          console.log("fechai2 = ", $("#fechai").val());
          console.log("fechaf2 = ", $("#fechaf").val());

        $.ajax({
          url: '/api/v2/pedidos',
          type: 'GET',
          cache: false,
          data: {
            search: $("#criteriob").val(), //Input Buscar
            criterio: $("#criteriob").val(),
            tipopedido: $("#tipopedido").val(),
            ciudad_pedido_list: $("#ciudad_pedido_list").val(),
            ruta_pedido_list: $("#ruta_pedido_list").val(),
            fecha_inicio: $("#fechai").val(),
            fecha_fin: $("#fechaf").val(),
            status: $("#status").val(),
            almacen: $("#almacen").val(),
            page: $('#dt-detalles .page').val(),
            rows: $('#dt-detalles .count').val(),
            //filtro: $("#filtro").val(),
            facturaInicio: $("#factura_inicio").val(),
            facturaFin: $("#factura_final").val()
/*
                    search: $("#criteriob").val(),
                    //filtro: $("#filtro").val(),
                    fecha_inicio: $("#fechai").val(),
                    fecha_fin: $("#fechaf").val(),
                    status: $("#status").val(),
                    almacen: $("#almacen").val(),
                    page : $('#dt-detalles .page').val(),
                    rows : $('#dt-detalles .count').val(),
*/
          },
          datatype: 'json'
        }).always(function(data){
            console.log("always = ", data);
        }).done(function(data)
        {
            console.log("Pedidos = ", data);
          //if (data.length < 2)
          //{
          //  return;
          //}   //lilo

          if (data.status == 200) 
          {
            $('#dt-detalles .total_pages').text(data.total_pages);
            $('#dt-detalles .page').text(data.page);
            $('#dt-detalles .from').text(data.from);
            $('#dt-detalles .to').text(data.to);
            $('#dt-detalles .total').text(data.total);

            var row = '', i = 0;
            var color = '';
            $.each(data.data, function(index, item)
            {
              i++;
                var sub_pedido = "";
                if(item.sufijo != 0)
                    sub_pedido = item.orden+"-"+item.sufijo;
                else 
                    sub_pedido = item.Folio_BackO;

                var is_backorder = 0;
                if(sub_pedido)
                {
                    var backorder_item = sub_pedido[0]+sub_pedido[1];
                    console.log("backorder_item = "+backorder_item);
                    if(backorder_item == 'BO')
                        is_backorder = 1;
                }
                //console.log("item.status_pedido = ", item.status_pedido, " - item.sufijo = ",item.sufijo);
                //if(item.status_pedido != 'S' && item.sufijo != 0)
                //{
                //var style_asignar = " disabled ";
                var style_asignar = "", style_planificar = "", titleXD = "", entrada_xd = "", style_dividir = "";
                //if(item.disponible >= item.cantidad) {color = 'verde'; style_asignar = ""}else if(item.disponible < item.cantidad && item.disponible > 0) {color = 'amarillo';style_asignar = "";} else {color = 'rojo';}
                //if(item.bloqueado == 1) style_asignar = " disabled ";
                //if(item.es_ot == 1 || item.es_ot == 2 || item.es_ot == 3 || item.es_ot == 4 || item.es_ot == 5) 
                style_planificar = " style='display: none;' ";
                style_dividir = " style='display: none;' ";
                if(item.folio_xd == 1 && item.folio_xd_asignable == 0) {style_asignar = " disabled "; titleXD = "title='El pedido tipo CrossDocking debe estar relacionado a una entrada para poder ser asignado' "; }
                if(item.Ship_Num != '' && item.folio_xd_asignable == 1) {entrada_xd = 'Entrada: '+item.Ship_Num;}

/*
 && $("#ruta_pedido_list").val() != '') || ($("#ruta_pedido_list").val() == '' && $("#tipopedido").val() != '') || $("#tipopedido").val() == '4'*/
                if(($("#tipopedido").val() == 'P' || $("#tipopedido").val() == 'W') )//&& sub_pedido == ""
                {
                 style_planificar = " style='display: block;' "; 
                 style_dividir = " style='display: block;' "; 
                }

                if((($("#tipopedido").val() == 'R' || $("#tipopedido").val() == 'RI') && item.statusaurora != 0 && item.statusaurora == $("#almacen").val()) || (item.TipoOT == 'IMP_LP' && item.es_ot != 1)) 
                {
                    style_asignar = " disabled ";
                    style_planificar = " style='display: none;' "; 
                    style_dividir = " style='display: none;' "; 
                }
                console.log("item.status_pedido = ", item.status_pedido);




                if(item.status_pedido != 'A')
                    style_asignar = " style='display: none;' ";

                    var rows_welldex = "";
                    if($("#instancia").val() == "welldex") 
                        rows_welldex = '<td>'+item.Ref_Wel+'</td>'+
                                       '<td>'+item.Ref_Imp+'</td>'+
                                       '<td>'+item.Pedimento+'</td>'+
                                       '<td>'+item.Factura_Vta+'</td>'+
                                       '<td>'+item.Ped_Imp+'</td>';

                    var rows_iberofarmacos1 = '<td>'+sub_pedido+entrada_xd+'</td>'+
                                              '<td>'+item.ruta_pedido+'</td>', 
                        rows_iberofarmacos2 = '<td>'+item.orden_cliente+'</td>',
                        rows_iberofarmacos3 = "";
                    if($("#instancia").val() == "iberofarmacos") 
                    {
                        rows_iberofarmacos3 = rows_iberofarmacos1+rows_iberofarmacos2;
                        rows_iberofarmacos1 = "";
                        rows_iberofarmacos2 = "";
                    }

                    var style_dividir = " style='display: none;' ";
                    if(item.num_registros > 1)
                        style_dividir = " style='display: block;' ";

                    var observaciones = item.observaciones+'';
                    console.log("ITEMS2 = ", item);
                  row += '<tr>'+'<td align="center">'+imageFormat(item.orden,item.surtido, item.TieneOla, '', item.sufijo, is_backorder, item.cliente, item.asignado, '', item.folio_ws, item.status, item.status_pedido, item.fecha_pedido, item.es_ot, item.ruta_pedido, item.statusaurora, item.TipoOT, item.usuario_pedido, item.tiene_foto, item.EsTipoLP)+'</td>'+
                  //'<td align="center"><input type="checkbox" /></td>'+
//function imageFormat(id, surtido, tieneola, tienda, sufijo, is_backorder, cliente, surtidor, articulo_ot, folio_ws, status, status_pedido, fecha_pedido, es_ot, ruta_pedido, statusaurora, TipoOT)
                  '<td align="center" id="semaforo_'+item.id+"_"+item.sufijo+'" class="column-semaforo">'+'<i class="fa fa-circle '+color+'" aria-hidden="true"></i>'+'</td>'+
                  '<td align="center" class="column-asignar"  data-id="'+item.orden+'" data-subpedido="'+item.sufijo+'"><input type="checkbox" class="check-asignar" id="asig'+item.orden+"-"+item.sufijo+'" data-id="'+item.orden+'" '+style_asignar+' '+titleXD+' data-idpedido="'+item.id+'" data-subpedido="'+item.sufijo+'" data-disponible="'+item.disponible+'" /></td>'+
                  '<td align="center" class="column-planificar" data-id="'+item.orden+'" data-sufijo="'+item.sufijo+'"><input type="checkbox" data-id="'+item.orden+'" data-sufijo="'+item.sufijo+'" '+style_planificar+' /></td>'+
                  '<td align="center" class="column-dividir" data-id="'+item.orden+'"><input type="checkbox" '+style_dividir+' id="div-'+item.orden+'" data-id="'+item.orden+'" '+style_dividir+' data-registros="'+item.num_registros+'" /></td>'+
                  '<td align="center" class="column-enviado" style="display: none;" data-id="'+item.orden+'" data-subpedido="'+item.sufijo+'"><input type="checkbox" data-id="'+item.orden+'" data-subpedido="'+item.sufijo+'" /></td>'+
                  '<td>'+item.orden+'</td>'+
                   rows_iberofarmacos1 +
                  '<td>'+item.cliente+'</td>'+
                  rows_iberofarmacos2 +
                  //'<td>'+item.destinatario+'</td>'+
                  '<td>'+item.total_factura+'</td>'+
                  '<td>'+observaciones.substring(0,30)+'...</td>'+

                  '<td align="center">'+item.fecha_pedido+'</td>'+
                  '<td align="center">'+item.fecha_aprobacion+'</td>'+
                  '<td align="center">'+item.fecha_compromiso+'</td>'+
                  rows_iberofarmacos3 +
                  '<td align="center">'+item.rango_hora+'</td>'+
                  '<td>'+item.prioridad+'</td>'+
                  '<td>'+item.status+'</td>'+
                  //'<td align="right">'+item.num_articulos+'</td>'+
                  //'<td align="right">'+item.cantidad+'</td>'+
                  //'<td align="right">'+item.cantidad_surtida+'</td>'+
                  //'<td align="right">'+item.volumen+'</td>'+
                  //'<td align="right">'+item.peso+'</td>'+
                  '<td align="center">'+item.fecha_ini+'</td>'+
                  '<td align="center">'+item.fecha_fi+'</td>'+
                  //'<td align="center">'+item.TiempoSurtido+'</td>'+
                  '<td>'+item.asignado+'</td>'+
                  '<td>'+item.proyectos+'</td>'+
                  //'<td align="center">'+item.surtido+'</td>'+
                  //'<td>'+item.direccion+'</td>'+                                            
                  //'<td align="center">'+item.dane+'</td>'+
                  //'<td>'+item.ciudad+'</td>'+
                  //'<td>'+item.Colonia+'</td>'+
                  //'<td>'+item.estado+'</td>'+
                    rows_welldex
                  +'</tr>';                            
                //}
            });
            $('#dt-detalles tbody').html(row);


        $(".check-asignar").click(function(id_pedido, idpedido = $(this).data("idpedido"), subpedido = $(this).data("subpedido"), id = $(this).data("id")){

            //if(item.disponible >= item.cantidad) {color = 'verde'; style_asignar = "";}else if(item.disponible < item.cantidad && item.disponible > 0) {color = 'amarillo';style_asignar = "";} else {color = 'rojo';}
            console.log("almacen = ", $("#almacen").val());
            console.log("con_recorrido = ", $("#con_recorrido").val());
            console.log("check-asignar = ", idpedido, subpedido, id);


            //if(id.substr(0,2) == 'XD')
            //{
            //    $("#semaforo"+"_"+idpedido+"_"+subpedido+" i").addClass('verde');
            //    return;
            //}
            //return;
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "semaforo",
                        con_recorrido: $("#con_recorrido").val(),
                        folio: id, //$(this).data("id"),
                        almacen: $("#almacen").val()
                    },
                    url: '/api/administradorpedidos/update/index.php',
                    success: function(item) 
                    {
                        var color = item.color; //lilo
                        console.log("sql_disp = ", item.sql_disp);
                        console.log("sql_dispXarticulo = ", item.sql_dispXarticulo);
                        console.log("num_productos_disponibles = ", item.num_productos_disponibles);
                        console.log("num_productos_pedido = ", item.num_productos_pedido);
                        console.log("COLOR check-asignar", color);
                        console.log("check-asignar", item);
                        /*
                        console.log("check-asignar", item);
                        if(item.disponible >= item.cantidad) {color = 'verde';}
                        else if(item.disponible < item.cantidad && item.disponible > 0) {color = 'amarillo';} 
                        else {color = 'rojo';}
                        console.log("color", color);

                        console.log("#semaforo"+"_"+idpedido+"_"+subpedido+" i");
                        */
                        if(color == 'rojo')
                        {
                            swal("No se puede asignar el pedido", "El pedido no cumple con los requerimientos para poder asignarlo, Revise el Reporte de Existencias de Pedido", "error");
                            $("#asig"+item.idfolio+"-"+"0").prop("checked", false);
                            $("#asig"+item.idfolio+"-"+"0").prop("disabled", true);
                        }

                        if(color == 'amarillo')
                        {
                            swal("Revise el Reporte de Existencias del Pedido", "El pedido puede asignarse parcialmente si no tiene todas las existencias necesarias o si las ubicaciones no están asignadas correctamente a la ruta de surtido.\n\nEn el reporte de existencias de pedido puede verificar ambos casos.", "warning");
                        }



                        $("#semaforo"+"_"+idpedido+"_"+subpedido+" i").addClass(color);
                        //$("#semaforo"+"_"+idpedido+"_"+subpedido+" i").css("color", 'green');
                    }, 
                    error: function(data) 
                    {
                        console.log("ERROR check-asignar() -> data = ", data);
                    }

                });

        });



            if ($("#status").val() == 'A') 
            {
                if($("#permiso_registrar").val() == 1) $('#btn-asignar').show();
              $('#planificalo').show();
              $('#planificaloTodo').show();
              $('#planificaloTodo').show();
              //$('#btn-planificar').show();
              $("#col-asignar, #col-planificar, #col-semaforo, #col-dividir").show();
              $(".column-asignar, .column-planificar, .column-semaforo, .column-dividir").show();
            }
            else 
            {
              $('#btn-asignar').hide();
              $('#planificalo').hide();
              $('#planificaloTodo').hide();
              $('#planificaloTodo').hide();
              //$('#btn-planificar').hide();
              $("#col-asignar, #col-planificar, #col-semaforo, #col-dividir").hide();
              $(".column-asignar, .column-planificar, .column-semaforo, .column-dividir").hide();
            }
          }
          else 
          {
            swal({
              title: "Error",
              text: "No se pudo planificar el inventario",
              type: "error"
            });
          }
          $("#totalpeso").val(data.pesototal);
          //$("#totalpedidos").val(data.totalpedidos);
          $("#totalpedidos").val(data.total);
          $("#totalsubpedidos").val(data.totalsubpedidos);
          $("#volumentotal").val(data.volumentotal);

          if ($("#status").val() == 'S') 
          {
            $("#grid-table").showCol("surtido");
            $("#grid-table").showCol("fecha_surtido");
            $("#grid-table").showCol("asignado");
          } 
          else 
          {
            $("#grid-table").hideCol("surtido");
            $("#grid-table").hideCol("fecha_surtido");
            $("#grid-table").hideCol("asignado");
          }
        })/*.fail(function(data){
            console.log("ERROR PEDIDOS = ", data);
        })*/;

      }

        function ReloadGrid1() {
            $('#grid-table2').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        //           criterio: $("#txtCriterio1").val(),
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


        /**
         */
        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }


        /**
         * Crea un consolidado con lo items seleccionados del Grid Principal
         * @author Brayan Rincon <brayan262@gmail.com>
         * @return void
         */
        function crearConsolidadoDeOla() 
        {
          var folios = [], sufijos = [];
          var almacen = $("#almacen").val();

          var folios_import = $("#folios_ola_import").val();

          if(folios_import.length == 0)
          {
              $.each( $('.column-planificar'), function (index, item) {
                var i = $(item).children().first();
                if ( i.prop('checked') == true ) {
                    folios.push(i.data('id'));
                    sufijos.push(i.data('sufijo'));
                }
              });
          }
          else
          {
                folios = folios_import.split(",");

                for(var i = 0; i < folios.length; i++)
                    sufijos.push(0);

          }

          $('#btn-crear-consolidado-ola').prop('disabled', true);
          $('#btn-cancelar-consolidado-ola').prop('disabled', true);
          var dfecha = $('#txt-ola-fechaentrega').val().split("-");
          var fecha = dfecha[2]+"-"+dfecha[1]+"-"+dfecha[0];
          console.log("Consolidado Folios = ", folios);
          console.log("Consolidado Sufijos = ", sufijos);
          console.log("Consolidado Fecha = ", fecha);
          console.log("Consolidado Almacen = ", almacen);
          console.log("Consolidado Tipo = ", $(".OlaXD_Button").text());

          //return;

          $.ajax({
            url: '/api/v2/pedidos/crearConsolidadoDeOla',
            data: {
                folios: folios,
                sufijos: sufijos,
                folioXD: $("#txt-nro-ola").val(),
                fecha_entrega: fecha,
                almacen: almacen,
                tipo: $(".OlaXD_Button").text(),
                tipopedido: $("#tipopedido").val(),
                action: 'crearConsolidadoDeOla'
            },
            type: 'POST',
            datatype: 'json'
          })
          .done(function(data) {
            console.log("Consolidado SUCCESS: ", data);
            $('#btn-crear-consolidado-ola').prop('disabled', false);
            $('#btn-cancelar-consolidado-ola').prop('disabled', false);

            //$('#div-grid-consolidados').show();
            //$('#div-grid-surtidos').show();

            $('#btn-cancelar-consolidado-ola').hide();
            $('#btn-crear-consolidado-ola').hide();
            $('#btn-borrar-consolidado-ola').show();
            $('#btn-asignar-consolidado-ola').show();
            $('#txt-ola-fechaentrega').prop('disabled', true);

            swal({
                title: "Éxito",
                text: "Consolidado de Ola creado correctamente",
                type: "success",

                //cancelButtonText: "No",
                //cancelButtonColor: "#14960a",
                //showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "OK",
                closeOnConfirm: true
            }, function() {
                //ReloadGrid();
                window.location.reload();
            });

            //$('#txt-nro-ola').val(data.data.nro_ola);
            //$("#txt-total-ordenes-ola").val(folios.length);
            //$("#txt-peso-ola").val(data.data.peso);
            //$("#txt-volumen-ola").val(data.data.volumen);

            //cargarConsolidado(folios);
            //cargarSurtido(folios);
          }).fail(function(data){
                console.log("ERROR 3", data);
          });
        }
        

        /**
         * Borra un consolidado de Ola
         * @author Brayan Rincon <brayan262@gmail.com>
         * @return void
         */
        function borrarConsolidadoDeOla() {

            swal({
                title: "Borrar Consolidado de Ola",
                text: "¿Desea borrar la operación de Ola?, esta operación no se puede reversar",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {

                $('#btn-borrar-consolidado-ola').prop('disabled', true);
                $('#btn-asignar-consolidado-ola').prop('disabled', true);

                $.ajax({
                        url: '/api/v2/consolidadosOla',
                        data: {
                            consololidado: $('#txt-nro-ola').val()
                        },
                        type: 'DELETE',
                        datatype: 'json'
                    })
                    .done(function(data) {
                        $('#btn-borrar-consolidado-ola').prop('disabled', false);
                        $('#btn-asignar-consolidado-ola').prop('disabled', false)
                        cancelarCallback();
                        console.log("ReloadGrid 4");
                        ReloadGrid();
                    });
            });
        }


        /**
         * 
         */
        function cargarConsolidado(folio) {
            $('#grid-consolidados').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        folio: folio,
                        action: 'cargarConsolidado'
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }


        /**
         * 
         */
        function cargarSurtido(folio) {
            $('#grid-surtidos').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    postData: {
                        folio: folio,
                        action: 'cargarSurtido'
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        $modal0 = null;
      
      
        function cancelar() {

            swal({
                title: "Cancelar operación",
                text: "¿Desea cancelar la operación de crear Ola?",
                type: "info",

                cancelButtonText: "No",
                cancelButtonColor: "#14960a",
                showCancelButton: true,

                confirmButtonColor: "#55b9dd",
                confirmButtonText: "Si",
                closeOnConfirm: true
            }, function() {
                cancelarCallback();
            });
        }

        function cancelarCallback() {
            $(':input', '#myform')
                .removeAttr('checked')
                .removeAttr('selected')
                .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
                .val('');

            $('#FORM').removeAttr('class').attr('class', '');
            $('#FORM').addClass('animated');
            $('#FORM').addClass("fadeOutRight");
            $('#FORM').hide();

            $('#list').show();
            $('#planificarOla').hide();
            $('#list').removeAttr('class').attr('class', '');
            $('#list').addClass('animated');
            $('#list').addClass("fadeInRight");
            $('#list').addClass("wrapper");
            $('#list').addClass("wrapper-content");

            $('#btn-crear-consolidado-ola').show();
            $('#btn-borrar-consolidado-ola').hide();
            $('#btn-asignar-consolidado-ola').hide();
            $('#btn-cancelar-consolidado-ola').show();

            $('#txt-ola-fechaentrega').prop('disabled', false)

            //$('#div-grid-consolidados').hide();
            //$('#div-grid-surtidos').hide();
        }


        function asignarUsuariosOla() {
            var rows = $("#grid-consolidados").jqGrid('getRowData');
            var bandera = false;

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['seleccion'] == "Yes") {
                    bandera = true;
                }
            }

            if (bandera == true) {

                $(".itemlist").remove();

            } else {
                swal({
                    title: "¡Advertencia!",
                    text: "Debe seleccionar al menos un artículo de la lista de consolidado",
                    type: "warning"
                });
            }
        }

        //function asignar()
        $("#btn-asignar").click(function()
        {
            var folios = [];
            var asignados = [];
            var sufijos = [];
            var almacen = $("#almacen").val();

            MOSTRAR_MENSAJE_BACKORDER = 0;
            $('#usuario_selector').empty();
            $("#usuario_selector").append(new Option("Seleccione un Usuario", ""));

            console.log("asignar() -> almacen = ", almacen);

//********************************************************************************
          $.each( $('.column-asignar'), function (index, item) {
            var i = $(item).children().first();
            if ( i.prop('checked') == true ) 
            {
              asignados.push(i.data('id'));
            }
          });

          var dividir = 0;
          var hay_division = 0;

          asignados.forEach(function(value, key) {
            dividir = 0;
            console.log("Folio = ", value);
            console.log("usuario = ", $('#usuario_selector').val());
            if($("#div-"+value).is(":checked"))
            {
                dividir = 1;
                hay_division = 1;
            }
            console.log("Dividir = ", dividir);
            console.log("***************************");
          });

          if(hay_division == 1)
          {
            console.log("Hay Division");
          }
          else
            console.log("NO Hay Division");
//********************************************************************************

            asignados = [];
            var asignados_send = "", tipo_unico = false, tipo_tr = false;
            $.each( $('.column-asignar'), function (index, item) 
            {
                var i = $(item).children().first(), str, fol;
                if (i.prop('checked') == true) 
                {
                    asignados.push(i.data('id'));
                    sufijos.push(i.data('subpedido'));
                    console.log("i.data('disponible') = ", i.data('disponible'));
                    console.log("i.data('folio') = ", i.data('id'));
                    str = i.data('id').toString(); 
                    res = str.substr(0, 2);
                    console.log("i.data('folioTipo') = ", res);

                    if(res == 'TR')
                    {
                        tipo_tr = true;
                    }

                    if(res != 'TR')
                    {
                        tipo_unico = true;
                    }

                    if(i.data('disponible') != 1 && MOSTRAR_MENSAJE_BACKORDER == 0)
                        MOSTRAR_MENSAJE_BACKORDER = 1;
                }
            });


            if(tipo_tr == true && tipo_unico == true)
            {
                swal({title: "Error",text: "No puede seleccionar un Pedido Tipo Traslado, Junto con otro tipo de pedido",type: "error"});
                return;
            }
            console.log("TIPO OK");
            //return;

            for(var i = 0; i < asignados.length; i++)
            {
                asignados_send += "'"+asignados[i]+"',";
            }

            $.each( $('.column-planificar'), function (index, item) 
            {
                var i = $(item).children().first();
                if (i.prop('checked') == true) 
                {
                    folios.push(i.data('id'));
                }
            });
          
            if(almacen == "")
            {
                swal({title: "Error",text: "Seleccione el almacén",type: "error"});
                return;
            }
          
            if(hay_division == 0)
                $('#txt-cantidad-pedidos').text(asignados.length);
            else
                $('#txt-cantidad-pedidos-division').text(asignados.length);

            asignados_send = asignados_send.substring(0,asignados_send.length - 1);

            console.log("asignar() -> asignados_send = ", asignados);
            console.log("sufijos() -> sufijos = ", sufijos);
            console.log("almacen() -> almacen = ", almacen);

            console.log("MOSTRAR_MENSAJE_BACKORDER = ", MOSTRAR_MENSAJE_BACKORDER);
            var claves = "";
            var claves_users = [];

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "loadArticulos",
                        id_pedido: asignados,
                        sufijos: sufijos,
                        almacen: almacen,
                        //is_backorder: 0,
                        status: 'A'
                    },
                    //cache: false,
                    //beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");} console.log("OK ", x);},
                    url: '/api/administradorpedidos/update/index.php',
                    success: function(data) 
                    {
                        console.log("**** asignar() -> loadArticulos SUCCESS **** ");
                        console.log("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
                        console.log("asignar() -> data = ", data);
                        console.log("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");

                        if (data.success == true) 
                        {
                            if(data.datos_options)
                            {
                                $modal0 = $("#modal-asignar-usuario");
                                if(hay_division == 1)   
                                {
                                    $modal0 = $("#modal-asignar-usuario-division");
    
                                    $("#tabla-division").empty();
                                    $("#tabla-division").append("<thead><tr><th style='width:20%;padding:5px !important;'>Folio</th><th style='width:20%;padding:5px !important;'>Cantidad a dividir</th><th style='width:60%;padding:5px !important;'>Usuarios</th></tr></thead>");
                        /*
                                    <select id="usuario_selector" class="form-control">
                                    <option value="">Seleccione usuario</option>
                                    </select>
                        */
                                      asignados = [];

                                      $.each( $('.column-asignar'), function (index, item) {
                                        var i = $(item).children().first();
                                        if ( i.prop('checked') == true ) 
                                        {
                                          asignados.push(i.data('id'));
                                        }
                                      });
                                        $(".usuarios_divisiones, .span_divisiones, .usuarios_sin_divisiones").empty();
                                     $("#tabla-division").append("<tbody>");
                                      asignados.forEach(function(value, key) {
                                        console.log("Folio = ", value);
                                        console.log("Folio Key= ", (key+1));
                                        //console.log("usuario = ", $('#usuario_selector').val());
                                        console.log("***************************");
                                        if($("#div-"+value).is(":checked"))
                                        {
                                            $("#tabla-division").append("<tr><td style='width:20%'>"+value+"</td><td style='width:20%'><input type='number' class='form-control' name='cant-"+value+"' id='cant-"+value+"' value='"+$("#div-"+value).data("registros")+"' min='2' max='"+$("#div-"+value).data("registros")+"' /></td><td style='width:60%'><span class='span_divisiones span_divisiones"+value+"' id='usuario-"+value+"-"+(key+1)+"'></span></td></tr>");
                                            $("#cant-"+value).change(function(){

                                                console.log("#cant-"+value, $(this).val());
                                                $(".usuarios_divisiones"+value+", .span_divisiones"+value+"").empty();

                                                for(var z = 0; z < $(this).val(); z++)
                                                {
                                                    $("#usuario-"+value+"-"+(key+1)).append("<div class='row usuarios_divisiones usuarios_divisiones"+value+"'><div class='col-xs-2'><b>"+(z+1)+"</b></div><div class='col-xs-10'><select class='form-control usuarios_divisiones usuarios_divisiones"+value+"' name='usuario_selector-"+value+"-"+(z+1)+"' id='usuario_selector-"+value+"-"+(z+1)+"'><option value='N0'>Seleccione Usuario</option></select><br></div></div>");
                                                    $("#usuario_selector-"+value+"-"+(z+1)).append(data.datos_options);
                                                }

                                            });

                                            $("#cant-"+value).keyup(function(){

                                                console.log("#cant-"+value, $(this).val());

                                                console.log("#cant-"+value, $(this).val());
                                                $(".usuarios_divisiones"+value+", .span_divisiones"+value+"").empty();

                                                for(var z = 0; z < $(this).val(); z++)
                                                {
                                                    $("#usuario-"+value+"-"+(key+1)).append("<div class='row usuarios_divisiones usuarios_divisiones"+value+"'><div class='col-xs-2'><b>"+(z+1)+"</b></div><div class='col-xs-10'><select class='form-control usuarios_divisiones usuarios_divisiones"+value+"' name='usuario_selector-"+value+"-"+(z+1)+"' id='usuario_selector-"+value+"-"+(z+1)+"'><option value='N0'>Seleccione Usuario</option></select><br></div></div>");
                                                    $("#usuario_selector-"+value+"-"+(z+1)).append(data.datos_options);
                                                }

                                            });
                                            $("#cant-"+value).trigger('change');

                                        }
                                        else
                                        {
                                            $(".usuarios_sin_divisiones usuarios_sin_divisiones"+value+"").empty();
                                            $("#tabla-division").append("<tr><td style='width:20%'>"+value+"</td><td style='width:20%'></td><td style='width:60%'><select class='form-control usuarios_sin_divisiones usuarios_sin_divisiones"+value+"' name='usuario_selector-"+value+"-0' id='usuario_selector-"+value+"-0'><option value='N0'>Seleccione Usuario</option></select></td></tr>");

                                            $("#usuario_selector-"+value+"-0").append(data.datos_options);
                                        }

                                      });
                                      $("#tabla-division").append("</tbody>");

                                }
                                else
                                    $("#usuario_selector").append(data.datos_options);


                                $modal0.modal('show');
                            }
                            else
                            {
                                 swal({
                                      title: "Error",
                                      text: "Usuarios no Asignados a Ruta de Surtido",//"BL o Usuario no Asignado a Ruta de Surtido",
                                      type: "error"
                                  });
                            }
                        }
                    }, 
                    error: function(data) 
                    {
                        console.log("**** asignar() -> loadArticulos ERROR **** ");
                        console.log("asignar() -> data = ", data);
                    }

                });

            /*
            for (var i = 0; i < asignados.length; i++)
            {
                //console.log("asignar() -> asignados = ", asignados);//{'array': JSON.stringify(asignados)},//asignados[i],

                $.ajax({
                    type: "POST",
                    //dataType: "json",
                    data: {
                        action: "loadArticulos",
                        id_pedido: asignados[i],
                        almacen: almacen,
                        status: 'A'
                    },
                    cache: false,
                    //beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");} console.log("OK ", x);},
                    url: '/api/administradorpedidos/update/index.php',
                    success: function(data) 
                    {
                        console.log("**** asignar() -> loadArticulos SUCCESS **** ");
                        console.log("asignar() -> asignados["+i+"] = ", asignados[i]);
                        console.log("asignar() -> data.success   = ", data.success);
                        console.log("asignar() -> data.res_sp    = ", data.res_sp);
                        console.log("asignar() -> data.sql       = ", data.sql);
                        console.log("asignar() -> data.status    = ", data.status);
                        console.log("asignar() -> data.articulos = ", data.articulos);
                        console.log("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
                        console.log("asignar() -> data = ", data);
                        console.log("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");

                        if (data.success == true) 
                        {
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: '/api/administradorpedidos/update/index.php',
                                data: {
                                    action: "traerUsuarios",
                                    acticulos: data.articulos
                                },
                                beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
                                success: function(data) 
                                {
                                    if (data.success == true) 
                                    {
                                        console.log(data);
                                        //$('#usuario_selector').empty();
                                        $modal0 = $("#modal-asignar-usuario");
                                        $modal0.modal('show');
                                        //$("#usuario_selector").append(new Option("Seleccione un Usuario", ""));
                                        //$("#usuario_selector").append(new Option("**************************", ""));
                                        var j = 0;
                                        $.each(data.usuarios.clave, function(key, value) {

                                                var find = false;
                                                for(var k = 0; k < claves_users.length; k++)
                                                    if(data.usuarios.clave[j] == claves_users[k])
                                                    {
                                                        find = true;
                                                        break;
                                                    }

                                                if(find == false) 
                                                {
                                                    $("#usuario_selector").append(new Option("("+data.usuarios.clave[j]+") - "+data.usuarios.nombre[j], data.usuarios.clave[j]));
                                                    claves_users[j] = data.usuarios.clave[j];
                                                    j++;
                                                }
                                        });
                                    }
                                }
                            });
                        }
                    }, 
                    error: function(data) 
                    {
                        console.log("**** asignar() -> loadArticulos ERROR **** ");
                        //console.log("asignar() -> asignados["+(i)+"] = ", asignados[i]);
                        console.log("asignar() -> data = ", data);
                        //console.log("asignar() -> data.success   = ", data.success);
                        //console.log("asignar() -> data.res_sp    = ", data.res_sp);
                        //console.log("asignar() -> data.sql       = ", data.sql);
                        //console.log("asignar() -> data.status    = ", data.status);
                        //console.log("asignar() -> data.articulos = ", data.articulos);
                    }

                });
            }
            */
        });


        function planificarConsolidadoOla() {

            var rels = [];
            var localRels = [];

            if ($("#usuario-ola").val() == "") {

            }

            var date = new Date();
            var dia = date.getDate();
            var mes = date.getMonth();
            var anio = date.getFullYear();
            var hora = date.getHours();
            var min = date.getMinutes();
            var seg = date.getSeconds();
            if (dia < 10) {
                dia = "0" + dia;
            }
            if (mes < 10) {
                mes = "0" + mes;
            }
            var fechaHoy = anio + "-" + mes + "-" + dia + " " + hora + ":" + min + ":" + seg;

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    numero_ola: $('#txt-nro-ola').val(),
                    fec_asignacion: $('#p_fechaAsignacion').val(),
                    fec_entrega: $('#txt-ola-fechaentrega').val(),
                    id_ruta: $('#ruta').val(),
                    pedidos: $('#hiddenPedidos').val(),
                    ids: $('#hiddenID_Pedidos').val(),
                    fecha: fechaHoy,
                    usuarios: rels,
                    action: "planificarOla"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/administradorpedidos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        var rows = jQuery("#grid-consolidados").jqGrid('getRowData');
                        for (var i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            if (row['seleccion'] == "Yes") {
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        pedidos: $('#hiddenPedidos').val(),
                                        ids: $('#hiddenID_Pedidos').val(),
                                        articulo: row['clave'],
                                        pedidas: row['pedidas'],
                                        surtidas: row['surtidas'],
                                        action: "planificarSubPedido"
                                    },
                                    beforeSend: function(x) {
                                        if (x && x.overrideMimeType) {
                                            x.overrideMimeType("application/json;charset=UTF-8");
                                        }
                                    },
                                    url: '/api/administradorpedidos/update/index.php',
                                    success: function(data) {
                                        if (data.success == true) {
                                            console.log("Agrego");
                                        }
                                    }
                                });
                            }

                        }
                        swal({
                            title: "Ola " + $('#txt-nro-ola').val() + " Planificada",
                            text: "Los pedidos han sido asignados a la misma ola",
                            type: "success"
                        });
                        console.log("ReloadGrid 5");
                        ReloadGrid();
                        $("#grid-table").jqGrid("clearGridData");
                        //	$('#waveModal').modal().hide();
                        $(".close").click();
                        cancelar();
                    }
                }
            });


        }

        function VerificarTiposPedidosPlanificar(tipo, folios_import)
        {

            if(tipo == '')
            {   
                $('.OlaXD').text('Ola');
                $('.OlaXD_Button').text('Ola');
            }
            else
            {   
                $('.OlaXD').text('CrossDocking');
                $('.OlaXD_Button').text('XD');
            }
          var grid = $('#dt-detalles'),
              folios = [], sufijos = [],
              pedidosId = [],
              rowId = grid.jqGrid("getDataIDs"),
              rowCount = rowId.length;

              var tipo_mismo_sufijo = true, sufijos_en_cero = false, sufijos_mayor_a_cero = false, 
                  sufijos_mayor_a_cero_diferente_sufijo = false, suf_ant = 0;

            if(folios_import.length == 0)
            {
                $.each( $('.column-planificar'), function (index, item) {
                    var i = $(item).children().first();
                    if ( i.prop('checked') == true ) {
                        folios.push(i.data('id'));
                        sufijos.push(i.data('sufijo'));

                        if(i.data('sufijo') == 0)
                            sufijos_en_cero = true;
                        else
                        {
                            if(suf_ant != i.data('sufijo') && suf_ant == 0 && sufijos_mayor_a_cero == false)
                            {
                                suf_ant = i.data('sufijo');
                                sufijos_mayor_a_cero = true;
                            }
                            else if(suf_ant != i.data('sufijo'))
                            {
                                sufijos_mayor_a_cero = false;
                                sufijos_mayor_a_cero_diferente_sufijo = true;
                            }
                        }
                    }
                });
            }
            else
            {
                folios = folios_import;
                for(var i = 0; i < folios.length; i++)
                {
                    sufijos.push(0);
                }
            }
            console.log("folios ola = ", folios);
            console.log("folios.length = ", folios.length);
            console.log("Consolidado Sufijos = ", sufijos);

            //return;

            if((sufijos_en_cero == sufijos_mayor_a_cero || sufijos_mayor_a_cero_diferente_sufijo == true) && folios_import.length == 0)
            {
                 swal({
                      title: "Error",
                      text: "Debe Seleccionar Todos los Pedidos con Sufijo del mismo número o Todos sin Sufijo",
                      type: "error"
                  });
                  return false;
            }
            else if(tipo == '')
            {
              if(folios.length < 2){
                 swal({
                      title: "Error",
                      text: "Debe seleccionar al menos dos folios",
                      type: "error"
                  });
                  return false;
              }
            }
            else
            {
              if(folios.length == 0){
                 swal({
                      title: "Error",
                      text: "Debe seleccionar al menos 1 folio",
                      type: "error"
                  });
                  return false;
              }
            }


            $.ajax({
              url: '/api/administradorpedidos/lista/index.php',
              data: {
                folios: folios,
                tipo: tipo,
                action: 'VerificarTiposPedidosPlanificar'
              },
              type: 'POST',
              datatype: 'json'
            })
            .done(function(data) {
              var data = JSON.parse(data);
              console.log("tipo = ", data);
              //return;
              if(data.data == 1)
              {
                if(folios.length == 0)
                    planificar(tipo, data.tipo, folios_import);
                else 
                    planificar(tipo, 'XD', folios_import);
              }
              else
              {
                 swal({
                      title: "Error",
                      text: "Los pedidos seleccionados son de diferentes tipos",
                      type: "error"
                  });
                  return false;
              }

            });
        }

        function planificar(tipo, tipopedido, folios_import) {
          var grid = $('#dt-detalles'),
              folios = [],
              pedidosId = [],
              rowId = grid.jqGrid("getDataIDs"),
              rowCount = rowId.length;

         if(folios_import.length == 0)
         {
          $.each( $('.column-planificar'), function (index, item) {
                var i = $(item).children().first();
                if ( i.prop('checked') == true ) {
                    folios.push(i.data('id'));
                }
            });
         }
         else
         {
            folios = folios_import;
            $("#folios_ola_import").val(folios);
            $('#modal-importar').modal('hide');
         }


            console.log("folios = ", folios);
            console.log("folios.length = ", folios.length);
            console.log("tipo = ", tipo);
            console.log("tipopedido = ", tipopedido);

          if(folios.length > 1 || tipo == 'XD'){
            $('#list').hide();
            $('#hiddenPedidos').val(folios);
            $('#hiddenID_Pedidos').val(pedidosId);
            $('#planificarOla').removeAttr('class').attr('class', '');
            $('#planificarOla').addClass('animated');
            $('#planificarOla').addClass("fadeInRight");
            $('#planificarOla').addClass("wrapper");
            $('#planificarOla').addClass("wrapper-content");
            $('#planificarOla').show();

            $.ajax({
              url: '/api/administradorpedidos/lista/index.php',
              data: {
                folios: folios,
                tipo: tipo,
                tipopedido: tipopedido,
                action: 'obtenerPesoVolumen'
              },
              type: 'POST',
              datatype: 'json'
            })
            .done(function(data) {
              var data = JSON.parse(data);
              console.log(data);
              $("#txt-total-ordenes-ola").val(folios.length);
              $("#txt-peso-ola").val(data.pesototal);
              $("#txt-volumen-ola").val(data.volumentotal);
              $("#txt-ola-fechaentrega").val(data.fecha_entrega);
              $("#txt-nro-ola").val(data.folio);
            });

            $("#pdfConsolidado input[name='folio'], #excelConsolidado input[name='folio'], #pdfSurtido input[name='folio'], #excelSurtido input[name='folio']").each(function(i, v) {
                v.value = folios
                console.log('Valores del formulario', v.value);
            });
          }
          else{
             swal({
                  title: "Error",
                  text: "Debe seleccionar al menos dos folios",
                  type: "error"
              });
              return false;
          }
        }
      
        function asiganarAreaEmbarqueOla() {

            $("#areaemb")[0].style.display = 'none';
            $("#waveModal").modal('hide');
            $("#modal-area-embarque").modal('show');
            $('#grid-areas-embarque').jqGrid('clearGridData');

            var rows = $("#grid-table").jqGrid('getRowData');

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['planear'] == "Yes") {
                    $("#grid-areas-embarque").jqGrid('addRowData', i, row);
                }
            }
        }


        function VerificarSiTodosLosProductosFueronAsignados(folio)
        {
            $.ajax({
              type: "POST",
              dataType: "json",
              //async: false,
              data: {
                folio: folio,
                almacen: $("#almacen").val(),
                action: "VerificarSiTodosLosProductosFueronAsignados"
              },
              beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                  x.overrideMimeType("application/json;charset=UTF-8");
                }
              },
              url: '/api/administradorpedidos/update/index.php',
              success: function(data) {
                console.log("SUCCESS ->", data);

                }, error: function(data) {
                    console.log("**** ERROR ****");
                    console.log(data);
                }
            });
        }

        function asignarPedidoOpciones()
        {

            if(MOSTRAR_MENSAJE_BACKORDER == 1)
            {
                console.log("MOSTRAR MENSAJE BACKORDER");
                swal({
                    title: "Asignar Pedidos",
                    text: "¿Que desea Hacer con esta Asignación?",
                    type: "warning",

                        showCancelButton: true,
                        cancelButtonText: "Ajustar Pedido",
                        cancelButtonColor: "#14960a",

                        confirmButtonColor: "#55b9dd",
                        //confirmButtonText: "Enviar a BackOrder",
                        confirmButtonText: "Procesar",
                        closeOnConfirm: true
                    },
                    function(e) {
                        if (e == true) { //Enviar a BackOrder
                            //window.location.reload();
                            console.log("backorder");
                            asignarPedido('backorder');
                        }
                        else //Ajustar Pedido
                        {
                            console.log("ajustar");
                            asignarPedido('ajustar');
                        }
                    });
            }
            else 
            {
                console.log("NO MOSTRAR MENSAJE BACKORDER");
                asignarPedido('backorder');
            }

        }

        function asignarPedido(opcion)
        {
          var asignados = [];
          //$('#modal-asignar-usuario').modal("hide");
          $.each( $('.column-asignar'), function (index, item) {
            var i = $(item).children().first();
            if ( i.prop('checked') == true ) 
            {
              asignados.push(i.data('id'));
            }
          });
          if(asignados.length == 0)
          {
            swal({title: "Folios",text: "Por favor seleccione uno o mas pedidos",type: "error"});
            return;
          }
          if ($('#usuario').val() == "") 
          {
            swal({title: "Solo puede asignar un usuario",text: "Por favor seleccione un unico usuario para asignar el pedido",type: "error"});
            return;
          }
          console.log("folios",asignados);
          console.log("con_recorrido",$("#con_recorrido").val());

          var avance = 0;
          var tipo_mensaje = "success";
          var titulo_mensaje = "Asignación Completa";
          var total = asignados.length;
          var sinExistencia = "";
          console.log(total);
          $("#avance").html(avance);
          $("#total").html(total);
          $('#asignandopedidos').show();


          var no_asignados = '';
          var dividir = 0;
          var hay_division = 0;
          var asignar = true;

          asignados.forEach(function(value, key) {

            if($("#div-"+value).is(":checked"))
            {
                hay_division = 1;
            }

          });

        var usuarios_enviar = [], usuario_division_0 = "";

        if(hay_division == 1)
        {
            console.log("Hay Division");
            asignados.forEach(function(value, key) {
            dividir = 0;
            if($("#div-"+value).is(":checked"))
            {
                dividir = 1;
            }

            usuarios_enviar = []; usuario_division_0 = "";
            if(hay_division == 1)
            {
                if(dividir == 1)
                {
                      $.each( $('.usuarios_divisiones'+value), function (index, item) {

                            /* if($('#usuario_selector-'+value+'-'+(index+1)).val() && $('#usuario_selector-'+value+'-'+(index+1)).val() != 'N0')
                            usuarios_enviar.push($('#usuario_selector-'+value+'-'+(index+1)).val());
                            else */
                            if($('#usuario_selector-'+value+'-'+(index+1)).val() == 'N0')
                            {
                                swal("Usuario Vacío", "Debe Seleccionar todos los usuarios del Pedido "+value+" para realizar la división", "error");
                                asignar = false;
                                return;
                            }


                      });
                }
                else
                {
                    /*
                    if($("#usuario_selector-"+value+"-0").val() != 'N0')
                        usuario_division_0 = $("#usuario_selector-"+value+"-0").val();
                    else
                    */
                    if($("#usuario_selector-"+value+"-0").val() == 'N0')
                    {
                        swal("Usuario Vacío", "Debe Seleccionar el usuario del Pedido "+value, "error");
                        asignar = false;
                        return;
                    }

                }
            }
            /*
            console.log("Folio = ", value);
            console.log("Dividir = ", dividir);
            console.log("n_div = ", $("#cant-"+value).val());
            console.log("usuario(s) = ", (hay_division==1)?(((dividir==1)?(usuarios_enviar):(usuario_division_0))):($('#usuario_selector').val()) );//$('#usuario_selector').val()
            console.log("***************************");
            */
          });
        }
        else
        {
            console.log("NO Hay Division");
            console.log("usuario(s) = ", (hay_division==1)?(((dividir==1)?(usuarios_enviar):(usuario_division_0))):($('#usuario_selector').val()) );//$('#usuario_selector').val()
        }


          if ($("#usuario_selector").val() == "" && hay_division == 0) 
          {
            swal({title: "Seleccione un usuario",text: "Por favor seleccione un unico usuario para asignar el pedido",type: "error"});
            return;
          }

            if(!asignar)
            {
                swal({title: "Usuario Vacío",text: "Debe registrar todos los usuarios para poder asignar",type: "error"});
                return;
            }

          //return;
        if(asignar)
          asignados.forEach(function(value, key) {
            console.log(value);

            if(hay_division == 1)
            {
                dividir = 0;
                if($("#div-"+value).is(":checked"))
                {
                    dividir = 1;
                }

                usuarios_enviar = []; usuario_division_0 = "";
                if(hay_division == 1)
                {
                    if(dividir == 1)
                    {
                          $.each( $('.usuarios_divisiones'+value), function (index, item) {

                                if($('#usuario_selector-'+value+'-'+(index+1)).val() && $('#usuario_selector-'+value+'-'+(index+1)).val() != 'N0')
                                usuarios_enviar.push($('#usuario_selector-'+value+'-'+(index+1)).val());
                                else if($('#usuario_selector-'+value+'-'+(index+1)).val() == 'N0')
                                {
                                    swal("Usuario Vacío", "Debe Seleccionar todos los usuarios del Pedido "+value+" para realizar la división", "error");

                                    return;
                                }


                          });
                    }
                    else
                    {
                        if($("#usuario_selector-"+value+"-0").val() != 'N0')
                            usuario_division_0 = $("#usuario_selector-"+value+"-0").val();
                        else
                        {
                            swal("Usuario Vacío", "Debe Seleccionar el usuario del Pedido "+value, "error");
                            return;
                        }

                    }
                }
                console.log("Folio = ", value);
                console.log("Dividir = ", dividir);
                console.log("con_recorrido = ", $("#con_recorrido").val());
                console.log("n_div = ", $("#cant-"+value).val());
                console.log("usuario(s) = ", (hay_division==1)?(((dividir==1)?(usuarios_enviar):(usuario_division_0))):($('#usuario_selector').val()) );//$('#usuario_selector').val()
                console.log("***************************");
            }
            //return;
            var usuario = (hay_division==1)?(((dividir==1)?(usuarios_enviar):(usuario_division_0))):($('#usuario_selector').val());
            var n_div = (hay_division==1)?(((dividir==1)?($("#cant-"+value).val()):(1))):(1);
            $.ajax({
              type: "POST",
              dataType: "json",
              //async: false,
              data: {
                usuarios: usuario,
                pedidos: value,
                n_div: n_div,
                almacen: $("#almacen").val(),
                con_recorrido: $("#con_recorrido").val(),
                action: "asignar",
                opcion: opcion,
                dividir: dividir,
                hora_inicio: moment().format("YYYY-MM-DD HH:mm:ss"),
                fecha: moment().format("YYYY-MM-DD")
              },
              beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                  x.overrideMimeType("application/json;charset=UTF-8");
                }
              },
              url: '/api/administradorpedidos/update/index.php',
              success: function(data) {
                console.log("ASIGNACION ->", data);
                //console.log("sql_ejecutados = ", data.sql_ejecutados);
                if (data.success == true) {
//                  if(data.sin_existencia == "")
//                  {
                    avance +=1;
                    /*
                    if(avance == 1)
                    {
                      swal({
                        title: "Asignando...",
                        text: "Se estan asignado los pedidos...",
                        type: "warning"
                      });
                      //alert("Asignando...");  //edg
                    }
                    */

                    if(data.val_sp == '-1') no_asignados += value+'\n';
                    console.log("Asignados "+avance+" de "+total);
                    $("#avance").html(avance);
                    $("#total").html(total);
                    //VerificarSiTodosLosProductosFueronAsignados(value);
                    if(avance == total)
                    {
                        /*
                      swal({
                        title: "Asignados",
                        text: "Asignados "+avance+" de "+total,
                        type: "success"
                      });*/
                      if(no_asignados!='')
                      {
                            no_asignados = '\n\n'+'Pedidos que no se pudieron asignar: \n\n'+no_asignados;
                            avance--;
                            titulo_mensaje = "Asignación no Terminada";
                            tipo_mensaje = "warning";
                      }

                    swal({
                        title: titulo_mensaje,
                        text: "Asignados "+avance+" de "+total+no_asignados,
                        type: tipo_mensaje,

                            showCancelButton: false,
                            cancelButtonText: "",
                            cancelButtonColor: "#14960a",

                            confirmButtonColor: "#55b9dd",
                            confirmButtonText: "ok",
                            closeOnConfirm: true
                        },
                        function(e) {
                            if (e == true) {
                                window.location.reload();
                            } 
                        });

                      //alert("Asignados "+avance+" de "+total);  
                    if(hay_division == 0)
                      $('#modal-asignar-usuario').modal("hide");
                    else
                        $('#modal-asignar-usuario-division').modal("hide");
                      //window.location.reload();
                    }
                  }
                  else
                  {
                    sinExistencia+=data.sin_existencia;
                  }
                }, error: function(data) {
                    console.log("---**** ERROR ****---");
                    console.log(data);
                    //VerificarSiTodosLosProductosFueronAsignados(value);
                    if(hay_division == 0)
                      $('#modal-asignar-usuario').modal("hide");
                    else
                        $('#modal-asignar-usuario-division').modal("hide");

                    swal({
                            title: "Error en Pedidos Asignados",
                            text: "",
                            type: "error",

                            showCancelButton: false,
                            cancelButtonText: "",
                            cancelButtonColor: "#14960a",

                            confirmButtonColor: "#55b9dd",
                            confirmButtonText: "ok",
                            closeOnConfirm: true
                        },
                        function(e) {
                            if (e == true) {
                                window.location.reload();
                            } 
                        });
                }
            });
          });


                  if(avance == total)
                  {
                  $("#asignacionPedidos").hide();
                  $('#list').show();
                  console.log("ReloadGrid 6");
                  ReloadGrid();
                  $(".close").click();

                  if(sinExistencia != "")
                  {
                  swal({
                  title: "Error",
                  text: "Los siguientes folios no cuentan con existencia: \n "+sinExistencia,
                  type: "warning"
                  });
                  }
                  else
                  {

                  }
                  }
        }

        function editar_cajas()
        {
          var folio = $("#fol_cambio").val();
          var numero = $("#cajasCantidad").val();
          $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                data: {
                    action: 'changeNumberBox',
                    folio: folio,
                    numero: numero
                },
                dataType: 'json',
                method: 'POST'
            }).done(function(data) {
                if (data.success) {
                    console.log('success');
                    $("#coModal").modal('hide');
                  swal({
                            title: "Éxito",
                            text: "Se ha actualizado el número de cajas correctamente",
                            type: "success"
                        }, function(confirm) {
                        });
                } else {
                    //alert("Error");
                    console.log('error');
                    $("#coModal").modal('hide');
                  swal({
                            title: "Error",
                            text: data.error,
                            type: "error"
                        }, function(confirm) {
                        });
                }
            });
        }        
      
        //$(".modal-backdrop.in").click()

        function articulos(_codigo, sufijo, is_backorder, cliente, surtidor, articulo_ot, folio_ws, ruta_pedido, status_pedido, statusaurora, TipoOT, usuario_pedido, EsTipoLP)
        {
          //EDG117
            $("#folio_detalle").html("");
            $("#cliente_detalle").html("");
            $("#articulo_OT_detalle").html("");
            $("#folio_ws").html("");
            $("#surtidor_detalle").html("");
            $("#ruta_pedido").html("");

            if($("#status").val() == 'S' && $("#tipopedido").val() == 'RI' && $("#cve_proveedor").val() == '')
               $("#select_proveedores").show();
            else
               $("#select_proveedores").hide();

/*
          console.log("articulos codigo = ",_codigo);
          console.log("articulos sufijo = ",sufijo);
          console.log("articulos is_backorder = ",is_backorder);
*/
          console.log("**********************ARTICULOS************************");
          console.log("1.-_codigo = ",_codigo);
          console.log("2.-sufijo = ",sufijo);
          console.log("3.-is_backorder = ",is_backorder);
          console.log("4.-cliente = ",cliente);
          console.log("5.-surtidor = ",surtidor);
          console.log("6.-articulo_ot = ",articulo_ot);
          console.log("7.-folio_ws = ",folio_ws);
          console.log("8.-ruta_pedido = ",ruta_pedido);
          console.log("9.-status_pedido = ",status_pedido);
          console.log("10.-statusaurora = ",statusaurora);
          console.log("11.-TipoOT = ",TipoOT);
          console.log("12.-EsTipoLP = ",EsTipoLP);
          console.log("*******************************************************");
          $("#PedidoTipoLP").val(EsTipoLP);
          $("#detalles_articulos_true"+_codigo).hide();
          $("#detalles_articulos_false"+_codigo).show();

          var suf = "", ub_reabasto = "";
          if(_codigo == ""){_codigo = folio;}
          if(sufijo > 0) suf = "-"+sufijo;
          //if(ubicacion_reabasto != '') ub_reabasto = " / REABASTECER ("+ubicacion_reabasto+")";
          $("#folio_detalle").html(_codigo+suf+ub_reabasto);
          if(cliente != '' && cliente != '--')
                $("#cliente_detalle").html(cliente);
          if(ruta_pedido != '')
                $("#ruta_pedido").html("Ruta: "+ruta_pedido);
          if(articulo_ot != '--')
                $("#articulo_OT_detalle").html(articulo_ot);
          if(folio_ws != '')
          {
            /*
                if($("#tipopedido").val() == 'W2')
                {
                    $("#folio_ws").html(folio_ws.replace(";;;;;", "<br>"));
                    console.log("folio_ws replace = ",folio_ws.replace(";;;;;", "\n"));
                }
                else
                    */
                    $("#folio_ws").html(folio_ws);
          }
          if(sufijo > 0) $("#surtidor_detalle").html("Surtidor: "+surtidor);
          $('#btnRealizarSurtido').hide();
          //$('#btnImprimirRemision').hide();
          //$('#btn_reporte_surtido').hide();
          
//btnRealizarSurtido
//btnImprimirRemision

              console.log("******************************************");
              console.log("is_backorder:", is_backorder);
              console.log("sufijo:", sufijo);
              console.log("folio:", _codigo);
              console.log("******************************************");


          var pedido_status_surtiendo = false;
          var estatus_del_pedido= "";
          window.estado_del_pedido = "";


              estatus_del_pedido = status_pedido;
              window.estado_del_pedido = status_pedido;
              console.log("HERE",window.estado_del_pedido);

              if (status_pedido == 'L' || status_pedido == 'R' || status_pedido == 'RI' || status_pedido == 'P' || status_pedido == 'M' || status_pedido == 'C' || status_pedido == 'E' || status_pedido == 'T' || status_pedido == 'F') 
              {
                //$('#btnImprimirRemision').show();
                $('#btn_reporte_surtido').show();
              } 

              if (status_pedido == 'A' || status_pedido == 'O') 
              {
                $('#btnRealizarSurtido').hide();
              } 
              if(status_pedido == 'S') 
              {
                if($("#permiso_registrar").val() == 1) $('#btnRealizarSurtido').show();
                pedido_status_surtiendo = true;
              }
              articulos_detalles(_codigo, sufijo, is_backorder, statusaurora, TipoOT, usuario_pedido);

/*
          $.ajax({
            url: '/api/administradorpedidos/update/index.php',
            data: {
              action: 'verificarSiElPedidoEstaSurtiendose',
              is_backorder: is_backorder,
              sufijo: sufijo,
              folio: _codigo
            },
            //dataType: 'json',
            method: 'POST'
          }).done(function(data) 
          {
              estatus_del_pedido = data.status;
              console.log("DATA = ", data);
              console.log(data.status);
              window.estado_del_pedido = data.status;
              console.log("HERE",window.estado_del_pedido);

              if (data.status == 'L' || data.status == 'R' || data.status == 'P' || data.status == 'M' || data.status == 'C' || data.status == 'E' || data.status == 'T' || data.status == 'F') 
              {
                //$('#btnImprimirRemision').show();
                $('#btn_reporte_surtido').show();
              } 

              if (data.status == 'A' || data.status == 'O') 
              {
                $('#btnRealizarSurtido').hide();
              } 
              if(data.status == 'S') 
              {
                $('#btnRealizarSurtido').show();
                pedido_status_surtiendo = true;
              }
              articulos_detalles(_codigo, sufijo, is_backorder);

          }).fail(function(data)
          {
            console.log("ERROR 4", data);
          });
*/
/*
          $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: '/api/administradorpedidos/update/index.php',
            data: {
              action: 'verificarSiElPedidoEstaSurtiendose',
              is_backorder: is_backorder,
              sufijo: sufijo,
              folio: _codigo
            },
            //beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            success: function(data) 
            {
              estatus_del_pedido = data.status;
              console.log("DATA = ", data);
              console.log(data.status);
              window.estado_del_pedido = data.status;
              console.log("HERE",window.estado_del_pedido);

              if (data.status == 'L' || data.status == 'R' || data.status == 'P' || data.status == 'M' || data.status == 'C' || data.status == 'E' || data.status == 'T' || data.status == 'F') 
              {
                //$('#btnImprimirRemision').show();
                $('#btn_reporte_surtido').show();
              } 

              if (data.status == 'A' || data.status == 'O') 
              {
                $('#btnRealizarSurtido').hide();
              } 
              if(data.status == 'S') 
              {
                // || data.status == 'C' || data.status == 'E' || data.status == 'F' || data.status == 'I' || data.status == 'K' || data.status == 'L'|| data.status == 'M'|| data.status == 'P'|| data.status == 'R'|| data.status == 'T'
                $('#btnRealizarSurtido').show();
                pedido_status_surtiendo = true;
              }
              articulos_detalles(_codigo, sufijo, is_backorder);
            },
            error: function(data) 
            {
                console.log("ERROR 4", data);
            }
          });
          */
          $.ajax({
            url: '/api/administradorpedidos/update/index.php',
            data: {
              action: 'loadCajaDetalle',
              folio: _codigo
            },
            dataType: 'json',
            method: 'POST'
          }).done(function(data) 
          {
            var detalles_productos = data.detalles_productos,
            detalles_cajas = data.detalles_cajas;
            if (data.success) 
            {
              var peso = parseFloat(detalles_productos.peso) + parseFloat(detalles_cajas.peso);
              $("#total_volumen_detalle").html(detalles_productos.volumen + " m&sup3;");
              $("#total_peso_detalle").html(peso + " Kg");
              $("#tipo_caja_detalle").html(detalles_cajas.descripcion);
              $("#dimensiones_caja_detalle").html(detalles_cajas.dimensiones);
              $("#peso_caja_detalle").html(detalles_cajas.peso+ " Kg");
              $("#volumen_caja_detalle").html(detalles_cajas.volumen + " m&sup3;");
              $("#cantidad_caja_detalle").html('<input type="text" id="cajasCantidad" name="fname" value="'+parseFloat(detalles_cajas.cantidad)+'" size="4">');
              $("#fol_cambio").val(data.info.data.folio);
            } 
            else
            {
              swal("Error", data.error, "error");
              $("#total_volumen_detalle").html(detalles_productos.volumen + " m&sup3;");
              $("#total_peso_detalle").html(detalles_productos.peso + " Kg");
              $("#tipo_caja_detalle").html('0');
              $("#dimensiones_caja_detalle").html('0');
              $("#peso_caja_detalle").html('0');
              $("#volumen_caja_detalle").html('0');
              $("#cantidad_caja_detalle").html('<input type="text" id="cajasCantidad" name="fname" value="0" size="4">');
            }
          });
          console.log("ID_PEDIDO = ", _codigo);
          $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/administradorpedidos/update/index.php',
            data: {
              action: "detallesPedidoCabecera",
              id_pedido: _codigo,
              almacen:$("#almacen").val()
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
            success: function(data){
              console.log("DATA PEDIDO = ", data);
              if(data.success == true)
              {
                var productos_existentes = 0;
                var backorder = 0;
                if(parseInt(data.articulos_existentes) > parseInt(data.articulos_pedidos))
                {
                  console.log("cabecera_mayor",data);
                  productos_existentes = data.articulos_pedidos;
                  detalle_subpedido = data.detalle_pedido;
                  backorder = data.articulos_pedidos - productos_existentes;
                }
                else
                {
                    detalle_subpedido = "";
                  console.log("cabecera_menor",data);

                  detalle_subpedido = data.detalle_pedido;
                  productos_existentes = data.articulos_existentes
                  backorder = data.articulos_pedidos - productos_existentes;
                }
                if(estatus_del_pedido == "A" || estatus_del_pedido == "O") //Listo por asignar
                {
                  $("#cabecera_detalles").hide();
                  $("#cabecera_caja_detalles").hide();
                }
                else
                {
                  $("#cabecera_detalles").show();
                  $("#cabecera_caja_detalles").show();
                }
                
                if(pedido_status_surtiendo) //Listo por asignar
                {
                  //Mostrar las cajas
                  //$("#tipo_caja_detalle").
                }
                else
                {
                  //No mostrar las cajas
                }
                
                $("#total_productos_detalle").html(data.articulos_pedidos);
                $("#total_disponibles_detalle").html(productos_existentes);
                $("#backorder").html(backorder);
              }
            }
          });


          function articulos_detalles(_codigo, sufijo, is_backorder, statusaurora, TipoOT, usuario_pedido)
          {
            console.log("HERE2",window.estado_del_pedido);
            console.log("is_backorder articulos_detalles = ",is_backorder);
            console.log("_codigo = ",_codigo);
            //if(is_backorder == 1) window.estado_del_pedido = 'A'; //si está en backorder, será listo por asignar 

            console.log("***********************");
            console.log("A ENVIAR --v");
            console.log("***********************");
            console.log("id_pedido: ",_codigo);
            console.log("sufijo: ",sufijo);
            console.log("is_backorder: ",is_backorder);
            console.log("almacen: ",$("#almacen").val());
            console.log("status: ",window.estado_del_pedido);
            console.log("TipoOT: ",TipoOT);
            console.log("usuario_pedido: ",usuario_pedido);
            console.log("***********************");
            $("#suf_cambio").val(sufijo);
            cantidades_disponibles = [];
            $.ajax({
              type: "POST",
              dataType: "json",
              url: '/api/administradorpedidos/update/index.php',
              data: {
                action: "loadArticulos",
                id_pedido: _codigo,
                sufijo: sufijo,
                is_backorder: is_backorder,
                almacen:$("#almacen").val(),
                con_recorrido: $("#con_recorrido").val(),
                modo: 'detalles',
                TipoOT: TipoOT,
                status: window.estado_del_pedido,
              },
              beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}},
              success: function(data) 
              {
                console.log("pedido = ", data);
                var num_art = data.articulos.length;
                console.log("num_art = ", num_art);

                $("#imprimir_observaciones").html("");
                if(data.observaciones != '')
                    $("#imprimir_observaciones").html(data.observaciones);


                  console.log("**** is_backorder = ", is_backorder);
                  console.log("**** pedido_status_surtiendo = ", pedido_status_surtiendo);
                  console.log("**** data.status = ", data.status);
                  console.log("**** data.success = ", data.success);

                if (data.success == true) 
                {
                  if(pedido_status_surtiendo == false && data.status == 'A')
                  {
                    console.log("pedido_status_surtiendo 0 == false");
                    $("#titulo3").show();
                    $("#tabla_listo_por_asignar").show();


                    if(data.promocion.length)
                    {
                        console.log("Si hay promocion");
                        $("#titulo_promo").show();
                        $("#tabla_promo").show();
                        $("#grid-table-promo").jqGrid("clearGridData");
                        var obj_pr, emptyItemPr;
                        for(var pr = 0; pr < data.promocion.length; pr++)
                        {
                          obj_pr = {
                            cve_articulo: data.promocion[pr].cve_articulo,
                            descripcion: data.promocion[pr].des_articulo,
                            cve_ruta: data.promocion[pr].ruta,
                            cliente: data.promocion[pr].cliente,
                            cantidad: data.promocion[pr].Cant,
                            unidad_medida: data.promocion[pr].unidad_medida
                          };
                          emptyItemPr = [obj_pr];
                          $("#grid-table-promo").jqGrid('addRowData', 0, emptyItemPr);
                        }

                    }
                    else
                    {
                        console.log("No hay promocion");
                        $("#titulo_promo").hide();
                        $("#tabla_promo").hide();
                        $("#grid-table-promo").jqGrid("clearGridData");
                    }


                    $("#titulo1").hide();
                    $("#tabla_surtiendo").hide();

                    $("#br_titulo").hide();
                    $("#titulo2").hide();
                    $("#tabla_surtido1").hide();

                    window.detalle_pedido = data;
                    console.log("Cambiar Status Sufijo 1 = ", window.detalle_pedido);
                    $("#grid-table-listo-por-asignar").jqGrid("clearGridData");
                    var folio = _codigo;
                    var back_order = 0;
                    var back_order_total = 0;
                    var disponible_total_header = 0 ;
                    var pedido_total = 0;
                    var disponible_total = 0;
                    for(var i = 0; i < num_art; i++) 
                    {
                      if(data.articulos[i].Existencia_Total==null){data.articulos[i].Existencia_Total=0;}
                      if(parseFloat(data.articulos[i].Existencia_Total) > parseFloat(data.articulos[i].Pedido_Total) || parseFloat(data.articulos[i].Existencia_Total) == parseFloat(data.articulos[i].Pedido_Total))
                      {
                        data.articulos[i].Existencia_Total = data.articulos[i].Pedido_Total;
                      }
                      //var back_order_asignar_articulo = (data.articulos[i].Pedido_Total-data.articulos[i].Existencia_Total);
                      //var disponible_total_articulo = data.articulos[i].Existencia_Total;
                      //var cantidad_pedida_articulo = data.articulos[i].Pedido_Total;

                      var existencia_tot = data.articulos[i].existencia;
                      console.log("************************************");
                      console.log("existencia_tot = ", existencia_tot);
                      console.log("************************************");
                      var back_order_asig = Math.abs(data.articulos[i].pedidas - data.articulos[i].existencia);
                      if(is_backorder == 1)
                      {
                            existencia_tot = Math.abs(data.articulos[i].pedidas - data.articulos[i].existencia);
                            back_order_asig = data.articulos[i].existencia;
                      }


//**********************************************************************************************************************
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
        var a_piezas = 1;
/*
        if(data.articulos[i].id_medida == 'XBX')
        {
            console.log("Entró XBX");
            a_piezas = data.articulos[i].piezasxcajas;
            //existencia_tot *= a_piezas;
        }
*/
        var valor1 = 0;
        if(data.articulos[i].piezasxcajas > 0)
           valor1 = (data.articulos[i].pedidas*a_piezas)/data.articulos[i].piezasxcajas;

        if(data.articulos[i].cajasxpallets > 1)
        {
           valor1 = valor1/data.articulos[i].cajasxpallets;
        }
       else
           valor1 = 0;

        var Pallet = parseInt(valor1);

        var valor2 = 0;
        var cantidad_restante = (data.articulos[i].pedidas*a_piezas) - (Pallet*data.articulos[i].piezasxcajas*data.articulos[i].cajasxpallets);
        if(!isNaN(valor1) || valor1 == 0)
        {
            if(data.articulos[i].piezasxcajas > 0)
               valor2 = (cantidad_restante/data.articulos[i].piezasxcajas);// - ($Pallet*$pedidas);
        }
        var Caja = parseInt(valor2);

        var Piezas = 0;
        if(data.articulos[i].piezasxcajas == 1) 
        {
            valor2 = 0; 
            //Caja = cantidad_restante;
            //Piezas = 0;
            Piezas = cantidad_restante;
            Caja = 0;
            Pallet = 0;
        }
        else if(data.articulos[i].piezasxcajas == 0 || data.articulos[i].piezasxcajas == "")
        {
            if(data.articulos[i].piezasxcajas == "") data.articulos[i].piezasxcajas = 0;
            //Caja = 0;
            //Piezas = cantidad_restante;
            Piezas = 0;
            Caja = cantidad_restante;

        }

        cantidad_restante = cantidad_restante - (Caja*data.articulos[i].piezasxcajas);

        if(!isNaN(valor2))
        {
            Piezas = cantidad_restante;
        }
        //**************************************************

//**********************************************************************************************************************

                    if(statusaurora == $("#almacen").val() && $("#tipopedido").val() == 'R')
                    {
                        existencia_tot = parseFloat(existencia_tot) + parseFloat(back_order_asig);
                        back_order_asig = 0;
                    }

                      obj = {

                        folio: data.articulos[i].folio,
                        cliente: data.articulos[i].cliente,
                        clave: data.articulos[i].clave,
                        articulo: data.articulos[i].articulo,
                        lote: data.articulos[i].lote,
                        caducidad: data.articulos[i].caducidad,
                        serie: data.articulos[i].serie,
                        proyecto: data.articulos[i].proyecto,
                        LP: data.articulos[i].LP,
                        pallet: Pallet,
                        cajas: Caja,
                        piezas: Piezas,
                        volumen: data.articulos[i].volumen,
                        peso: data.articulos[i].peso,
                        cantidad_pedida: (data.articulos[i].pedidas*a_piezas),
                        unidad_medida: data.articulos[i].unidad_medida,
                        existencia_total: existencia_tot,
                        back_order_asignar: back_order_asig,
                      };
                      emptyItem = [obj];
                        //data.status == 'A' ? data.articulos[i].existencia : 0,

                      $("#grid-table-listo-por-asignar").jqGrid('addRowData', 0, emptyItem);
                    }
                    $modal0 = $("#coModal");
                    $('#coModal').hide();
                    $modal0.modal('show');
                  }
                  else if(pedido_status_surtiendo == true)
                  {
                    $("#titulo1").show();
                    $("#tabla_surtiendo").show();
                    $("#br_titulo").show();

                    $("#titulo2").hide();
                    $("#tabla_surtido1").hide();
                    $("#titulo3").hide();
                    $("#tabla_listo_por_asignar").hide();
                    console.log("**********Entro en  if(pedido_status_surtiendo == true) ");
                    window.detalle_pedido = data;


                    $("#grid-table2").jqGrid("clearGridData");
                    $("#grid-table-surtido1").jqGrid("clearGridData");

                    itemsArticulosPedidos = [];
                    folio = _codigo;
                    var back_order = 0;
                    var j = 0;
                    //console.log("here3",data);
                    if(/*num_art > 0 &&*/ pedido_status_surtiendo == true) //data.articulos.length
                    {

                        console.log("**********promocion = ", data.promocion);
                        console.log("**********promocion.length = ", data.promocion.length);
/*
                    if(data.promocion.length)
                    {
                        console.log("Si hay promocion");
                        $("#titulo_promo").show();
                        $("#tabla_promo").show();
                        $("#grid-table-promo").jqGrid("clearGridData");
                        var obj_pr, emptyItemPr;
                        for(var pr = 0; pr < data.promocion.length; pr++)
                        {
                          obj_pr = {
                            cve_articulo: data.promocion[pr].cve_articulo,
                            descripcion: data.promocion[pr].des_articulo,
                            cve_ruta: data.promocion[pr].ruta,
                            cliente: data.promocion[pr].cliente,
                            cantidad: data.promocion[pr].Cant,
                            unidad_medida: data.promocion[pr].unidad_medida
                          };
                          emptyItemPr = [obj_pr];
                          $("#grid-table-promo").jqGrid('addRowData', 0, emptyItemPr);
                        }

                    }
                    else
                    {
                        console.log("No hay promocion");
                        $("#titulo_promo").hide();
                        $("#tabla_promo").hide();
                        $("#grid-table-promo").jqGrid("clearGridData");
                    }
*/

                        console.log("pedido_status_surtiendo == true");
                        var surtidor_actual = "";
                        if(num_art > 0) surtidor_actual = data.articulos[0].surtidor;
                        var pedido_surtido = "";
                        var lp_actual = "";
                        if(num_art > 0) lp_actual = data.articulos[0].LP;
                        var lp_orden = 1;
                    if(num_art > 0)
                    {
                      for (var i = 0; i < num_art; i++) //data.articulos.length
                      {
                        j++;
                        if(lp_actual == '')
                        {
                            if(surtidor_actual == data.articulos[i].surtidor)
                            {
                              var ordenDeSecuencia_contador = j;
                            }
                            else
                            {
                              j = 1;
                              var ordenDeSecuencia_contador = j;
                              surtidor_actual = data.articulos[i].surtidor;
                            }
                        }
                        else
                        {
                            //console.log("lp_actual = ", lp_actual);
                            if(lp_actual != data.articulos[i].LP)
                            {
                              lp_orden++;
                              j = lp_orden;
                              var ordenDeSecuencia_contador = j;
                              lp_actual = data.articulos[i].LP;
                            }
                            else
                            {
                              j = lp_orden;
                              var ordenDeSecuencia_contador = j;
                            }
                        }

                            //console.log("existencia Surtiendo = ", data.articulos[i]);

                        if(data.articulos[i].existencia==null){data.articulos[i].existencia=0;}
                        if(parseFloat(data.articulos[i].existencia) > parseFloat(data.articulos[i].pedidas) || parseFloat(data.articulos[i].existencia) == parseFloat(data.articulos[i].pedidas))
                        {
                          data.articulos[i].existencia = data.articulos[i].pedidas;
                        }

                        var back_order_surtir_articulo = Math.abs(data.articulos[i].pedidas - data.articulos[i].existencia);
                        var disponible_total_surtir = data.articulos[i].existencia;
                        var cantidad_pedida_surtir = data.articulos[i].pedidas;

                        var sufijo_add = '';
                        if(data.articulos[i].sufijo > 0)
                            sufijo_add = "-"+data.articulos[i].sufijo;

                        var solicitadas_cant = data.articulos[i].pedidas, 
                            surtidas_cant = data.articulos[i].surtidas,
                            existencia_cant = data.articulos[i].existencia;


                        if(data.articulos[i].control_peso == 'N')
                        {
                            solicitadas_cant = parseInt(solicitadas_cant);//.toFixed(0);
                            surtidas_cant = parseInt(surtidas_cant);//.toFixed(0);
                            existencia_cant = parseInt(existencia_cant);//.toFixed(0);
                        }

//**********************************************************************************************************************
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
        var a_piezas = 1;
        if(data.articulos[i].id_medida == 'XBX')
        {
            a_piezas = data.articulos[i].piezasxcajas;
            console.log("OK XBX a_piezas = ", a_piezas, " solicitadas_cant = ", solicitadas_cant);
        }

        var valor1 = 0;
        if(data.articulos[i].piezasxcajas > 0)
           valor1 = (solicitadas_cant*a_piezas)/data.articulos[i].piezasxcajas;

        if(data.articulos[i].cajasxpallets > 0)
           valor1 = valor1/data.articulos[i].cajasxpallets;
       else
           valor1 = 0;

        var Pallet = parseInt(valor1);

        var valor2 = 0;
        var cantidad_restante = (solicitadas_cant*a_piezas) - (Pallet*data.articulos[i].piezasxcajas*data.articulos[i].cajasxpallets);
        if(!isNaN(valor1) || valor1 == 0)
        {
            if(data.articulos[i].piezasxcajas > 0)
               valor2 = (cantidad_restante/data.articulos[i].piezasxcajas);// - ($Pallet*$pedidas);
        }
        var Caja = parseInt(valor2);

        var Piezas = 0;
        if(data.articulos[i].piezasxcajas == 1) 
        {
            valor2 = 0; 
            //Caja = cantidad_restante;
            //Piezas = 0;
            Piezas = cantidad_restante;
            Caja = 0;

        }
        else if(data.articulos[i].piezasxcajas == 0 || data.articulos[i].piezasxcajas == "")
        {
            if(data.articulos[i].piezasxcajas == "") data.articulos[i].piezasxcajas = 0;
            //Caja = 0;
            //Piezas = cantidad_restante;
            Piezas = 0;
            Caja = cantidad_restante;
        }

        cantidad_restante = cantidad_restante - (Caja*data.articulos[i].piezasxcajas);

        if(!isNaN(valor2))
        {
            Piezas = cantidad_restante;
        }
        //**************************************************

//**********************************************************************************************************************
                        //**(1)**//
                        //EN LA REUNIÓN DEL DIA 21-05-2024 ME DIJERON QUE COLOCARA LA MISMA CANTIDAD QUE TENIA EN DISPONIBLE
                        //QUE NO LO CONVIERTA A PIEZAS (ASÍ ESTABA ANTES)
                        //**(1)**//
                        var cantidad_surtir = ($("#con_recorrido").val() != "1")?(0):(existencia_cant),
                            ordenDeSecuencia_contador = ($("#con_recorrido").val() != "1")?(""):(ordenDeSecuencia_contador);
                        obj = {
                          lp: data.articulos[i].LP,
                          folio: data.articulos[i].folio,
                          subpedido1: data.articulos[i].folio+sufijo_add,
                          cliente: data.articulos[i].cliente,
                          clave: data.articulos[i].clave,
                          articulo: data.articulos[i].articulo,
                          volumen: data.articulos[i].volumen,
                          peso: data.articulos[i].peso,
                          solicitadas: data.articulos[i].pedidas*1, //(solicitadas_cant*a_piezas), //**(1)**//
                          unidad_medida: data.articulos[i].unidad_medida,
                          surtidas: surtidas_cant, //data.articulos[i].surtidas,
                          Lote: data.articulos[i].lote,
                          Caducidad: data.articulos[i].caducidad,
                          Serie: data.articulos[i].serie,
                            pallet: Pallet,
                            cajas: Caja,
                            piezas: Piezas,
                          existencia: existencia_cant,
                          back_order: 0,//Math.abs(data.articulos[i].pedidas - data.articulos[i].existencia),
                          ubicacion: data.articulos[i].ubicacion,
                          ruta: data.articulos[i].ruta,
                          secuencia:data.articulos[i].secuencia,
                          ordenDeSecuencia: ordenDeSecuencia_contador,
                          surtir: '<input id="surtido-item-' + i + '" value="'+cantidad_surtir+'" type="number" style="width:70px;text-align:right;"  />',
                          surtidor: data.articulos[i].surtidor,
                        };
                        cantidades_disponibles.push(existencia_cant);
                        emptyItem = [obj];
                        //data.articulos[i].clave + data.articulos[i].lote + data.articulos[i].serie + data.articulos[i].ubicacion

                        pedido_surtido += data.articulos[i].ubicacion;

                        $("#grid-table2").jqGrid('addRowData', 0, emptyItem);
                      }
                  }
                      console.log("Detalle Pedido Directo:");
                      console.log("pedido_surtido = ", $.trim(pedido_surtido));
                      console.log("surtidor_actual = ", surtidor_actual);
                      console.log("usuario_pedido = ", usuario_pedido);
                      console.log("Condicion:", "if(($.trim(pedido_surtido) == null || surtidor_actual == '' || $.trim(pedido_surtido) == '') && usuario_pedido != 'PTL') ");

                      
                        $("#fol_cambio").val(_codigo);
                        if(($.trim(pedido_surtido) == "null" || surtidor_actual == '' || $.trim(pedido_surtido) == '') && usuario_pedido != 'PTL') 
                        {
                            $('#btnRealizarSurtido').hide();
                            console.log("-->>fol_cambio = ",$("#fol_cambio").val());
                            if($("#tipopedido").val() == 'RI') obtenerAreasRecepcion();
                            else if($("#tipopedido").val() == 'P') enviar_finalizar_pedido("");
                            else if($("#tipopedido").val() == 'W' || $("#tipopedido").val() == 'W2') obtenerZonasDisponibles();
                            else if($("#tipopedido").val() == 'T') obtenerUbicacionManufactura();
                            $modal0 = $("#coModal");
                            $('#coModal').hide();
                            $modal0.modal('hide');
                        }
                        else
                        {
                            $modal0 = $("#coModal");
                            $('#coModal').hide();
                            $modal0.modal('show');
                        }
                    }
                  }
                  else if(is_backorder == 0 && pedido_status_surtiendo == false && data.status != 'A' && data.status != 'S')
                  {
                    console.log("pedido_status_surtiendo == false");
                    $("#titulo2").show();
                    $("#tabla_surtido1").show();
                    $("#br_titulo").show();

                    $("#titulo1").hide();
                    $("#tabla_surtiendo").hide();

                    $("#titulo3").hide();
                    $("#tabla_listo_por_asignar").hide();

                    $("#grid-table-surtido1").jqGrid("clearGridData");

                      if(data.articulos.length > 0)
                      {
                        for (var i = 0; i < data.articulos.length; i++) 
                        {
                            if(data.articulos[i].existencia==null){data.articulos[i].existencia=0;}
                            if(parseFloat(data.articulos[i].existencia) > parseFloat(data.articulos[i].pedidas) || parseFloat(data.articulos[i].existencia) == parseFloat(data.articulos[i].pedidas))
                            {
                              data.articulos[i].existencia = data.articulos[i].pedidas;
                            }

                            var sufijo_add = '';
                            if(data.articulos[i].sufijo > 0)
                                sufijo_add = "-"+data.articulos[i].sufijo;

                            var surtidas_cant = data.articulos[i].surtidas;


                            if(data.articulos[i].control_peso == 'N')
                            {
                                surtidas_cant = parseInt(surtidas_cant);//.toFixed(0);
                            }

//**********************************************************************************************************************
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
        var a_piezas = 1;
        if(data.articulos[i].id_medida == 'XBX')
        {
            a_piezas = data.articulos[i].piezasxcajas;
        }

        var valor1 = 0;
        if(data.articulos[i].piezasxcajas > 0)
           valor1 = (surtidas_cant*a_piezas)/data.articulos[i].piezasxcajas;

        if(data.articulos[i].cajasxpallets > 0)
           valor1 = valor1/data.articulos[i].cajasxpallets;
       else
           valor1 = 0;

        var Pallet = parseInt(valor1);

        var valor2 = 0;
        var cantidad_restante = (surtidas_cant*a_piezas) - (Pallet*data.articulos[i].piezasxcajas*data.articulos[i].cajasxpallets);
        if(!isNaN(valor1) || valor1 == 0)
        {
            if(data.articulos[i].piezasxcajas > 0)
               valor2 = (cantidad_restante/data.articulos[i].piezasxcajas);// - ($Pallet*$pedidas);
        }
        var Caja = parseInt(valor2);

        var Piezas = 0;
        if(data.articulos[i].piezasxcajas == 1) 
        {
            valor2 = 0; 
            //Caja = cantidad_restante;
            //Piezas = 0;
            Piezas = cantidad_restante;
            Caja = 0;

        }
        else if(data.articulos[i].piezasxcajas == 0 || data.articulos[i].piezasxcajas == "")
        {
            if(data.articulos[i].piezasxcajas == "") data.articulos[i].piezasxcajas = 0;
            //Caja = 0;
            //Piezas = cantidad_restante;
            Piezas = 0;
            Caja = cantidad_restante;
        }

        cantidad_restante = cantidad_restante - (Caja*data.articulos[i].piezasxcajas);

        if(!isNaN(valor2))
        {
            Piezas = cantidad_restante;
        }
        //**************************************************

//**********************************************************************************************************************

                            tabla2 = {
                              folio: data.articulos[i].folio,
                              subpedido2: data.articulos[i].folio+sufijo_add,
                              cliente: data.articulos[i].cliente,
                              clave: data.articulos[i].clave,
                              articulo: data.articulos[i].articulo,
                              lote: data.articulos[i].lote,
                              caducidad: data.articulos[i].caducidad,
                              serie: data.articulos[i].serie,
                                pallet: Pallet,
                                cajas: Caja,
                                piezas: Piezas,
                              volumen: data.articulos[i].volumen,
                              peso: data.articulos[i].peso,
                              solicitadas: data.articulos[i].pedidas,
                              surtidas: surtidas_cant,//data.articulos[i].surtidas,
                              existencia: data.articulos[i].existencia,
                              back_order: Math.abs(data.articulos[i].pedidas - data.articulos[i].existencia),
                              ordenDeSecuencia: (i+1)
                            };

                            empty_tabla2 = [tabla2];

                            console.log("empty_tabla2 = ", empty_tabla2);
                            $("#grid-table-surtido1").jqGrid('addRowData', 0, empty_tabla2);
                          }
                            $modal0 = $("#coModal");
                            $('#coModal').hide();
                            $modal0.modal('show');
                        }
                  }
                }
              }, error: function(data) 
              {
                console.log("pedido error = ", data);
              }

            });
          }
          $("#detalles_articulos_true"+_codigo).show();
          $("#detalles_articulos_false"+_codigo).hide();

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
                  action : "cargarFotosTHPedido"
              },/*
              beforeSend: function(x)
              {
                  if(x && x.overrideMimeType) 
                  { 
                    x.overrideMimeType("application/json;charset=UTF-8"); 
                  }
              },*/
              url: '/api/embarques/lista/index.php',
              success: function(data) 
              {
                //console.log("SUCCESS IMAGENES = ", data);
                $("#fotos_th").html(data);

              }, error: function(data) 
              {
                  console.log("ERROR Fotos Datos = ", data);
              }
          });
    }

    function ver_fotos(_codigo)
    {
        $("#n_folio").text(_codigo);
        $modal0 = $("#fotos_oc_th");
        $modal0.modal('show');
        ImprimirFotos_th(_codigo);
    }

//////////////////////FOTOS////////////////////////
        function embarqueFoto(_codigo)
        {
          $("#foto1,#foto2,#foto3,#foto4").attr("src","");
          $(".idPedido").val(_codigo);
          console.log("Fotos ajax");
          $.ajax({
            type: "POST",
            dataType: "json",
            data: {
              id_pedido: _codigo,
              action: "loadFotos"
            },
            url: '/api/administradorpedidos/update/index.php',
          }).done(
          function(data)
          {
            console.log("mostrar Fotos");
            $modal00 = $("#modal_fotos");
            $modal00.modal('show');
            console.log(data);
            window.resurned_data = data;
              if(data.data[0].foto1!=""){$("#foto1").attr("src","../to_img.php?img=embarques/"+data.data[0].foto1);}
              if(data.data[0].foto2!=""){$("#foto2").attr("src","../to_img.php?img=embarques/"+data.data[0].foto2);}
              if(data.data[0].foto3!=""){$("#foto3").attr("src","../to_img.php?img=embarques/"+data.data[0].foto3);}
              if(data.data[0].foto4!=""){$("#foto4").attr("src","../to_img.php?img=embarques/"+data.data[0].foto4);}
          });
        }

      
        function traeModal() {

            $('#myModalClientes').show();

        }

        function minimizar() {

            $('#myModalClientes').hide();

        }
    </script>

    <!-- grid de articulos -->
    <script type="text/javascript">
/////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table2";
            var pager_selector = "#grid-pager2";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table2").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
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
                colNames: ['License Plate', 'Pedido', 'Subpedido', 'Cliente', 'Clave', 'Articulo', 'Orden de Surtido', 'BL', 'Lote', 'Caducidad', 'Serie', 'Pallet', 'Cajas', 'Piezas | Kg', 'Solicitadas','Disponible', 'Surtir', 'Unidad Medida', 'Back Order','Surtidas', 'Volumen (m3)', 'Peso', 'Secuencia', 'Surtidor', 'Ruta'],
                colModel: [
                    {name: 'lp',index: 'lp',width: 150,editable: false,sortable: false},
                    {name: 'folio',index: 'folio',width: 80,editable: false,sortable: false, hidden: true},
                    {name: 'subpedido1',index: 'subpedido1',width: 120,editable: false,sortable: false, hidden: true},
                    {name: 'cliente',index: 'cliente',width: 350,editable: false,sortable: false, hidden: true},
                    {name: 'clave',index: 'clave',width: 80,editable: false,sortable: false},
                    {name: 'articulo',index: 'articulo',width: 350,editable: false,sortable: false},
                    {name: 'ordenDeSecuencia',index: 'ordenDeSecuencia',width: 120,editable: false,sortable: false,align: 'center'},
                    {name: 'ubicacion',index: 'ubicacion',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'Lote',index: 'Lote',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'Caducidad',index: 'Caducidad',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'Serie',index: 'Serie',width: 80,editable: false,sortable: false,align: 'center'},
                    {name: 'pallet',index: 'pallet',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'cajas',index: 'cajas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'piezas',index: 'piezas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'solicitadas',index: 'solicitadas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'existencia',index: 'existencia',width: 80,editable: false,sortable: false,align: 'right'},
                    {name: 'surtir',index: 'surtir',width: 80,editable: false,sortable: false,align: 'right'},  
                    {name: 'unidad_medida',index: 'unidad_medida',width: 120,editable: false,sortable: false},
                    {name: 'back_order',index: 'back_order',width: 80,editable: false,sortable: false,align: 'right'},  
                    {name: 'surtidas',index: 'surtidas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'volumen',index: 'volumen',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'peso',index: 'peso',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'secuencia',index: 'secuencia',width: 80,editable: false,sortable: false,align: 'center', hidden: true},
                    {name: 'surtidor',index: 'surtidor',width: 150,editable: false,sortable: false,align: 'center', hidden: true},
                    {name: 'ruta',index: 'ruta',width: 140,editable: false,sortable: false,align: 'center'},
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
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
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
            function actionFormat(cellvalue, options, rowObject) {
                var serie = rowObject.folio + ' | ' + rowObject.clave,
                    html = '';
                html += '<a href="#" onclick="surtir(\'' + serie + '\')"><i class="fa fa-check" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                            format: 'dd-mm-yyyy',
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

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });
      
        $(function($) {
            var grid_selector = "#grid-table-surtido1";
            var pager_selector = "#grid-pager-surtido1";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-surtido1").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
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
                colNames: ['Pedido', 'Subpedido', 'Cliente', 'Clave', 'Articulo','Surtidas', 'Lote', 'Caducidad', 'Serie', 'Pallet', 'Cajas', 'Piezas', 'Solicitadas', 'Disponible', 'Back Order', 'Volumen (m3)', 'Peso', 'Orden de Surtido'],
                colModel: [
                    {name: 'folio',index: 'folio',width: 80,editable: false,sortable: false},
                    {name: 'subpedido2',index: 'subpedido2',width: 80,editable: false,sortable: false},
                    {name: 'cliente',index: 'cliente',width: 350,editable: false,sortable: false},
                    {name: 'clave',index: 'clave',width: 80,editable: false,sortable: false},
                    {name: 'articulo',index: 'articulo',width: 350,editable: false,sortable: false},
                    {name: 'surtidas',index: 'surtidas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'lote',index: 'lote',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'caducidad',index: 'caducidad',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'serie',index: 'serie',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'pallet',index: 'pallet',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'cajas',index: 'cajas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'piezas',index: 'piezas',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'solicitadas',index: 'solicitadas',width: 100,editable: false,sortable: false,align: 'right', hidden:true},
                    {name: 'existencia',index: 'existencia',width: 80,editable: false,sortable: false,align: 'right', hidden:true},
                    {name: 'back_order',index: 'back_order',width: 80,editable: false,sortable: false,align: 'right', hidden:true},
                    {name: 'volumen',index: 'volumen',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'peso',index: 'peso',width: 100,editable: false,sortable: false,align: 'right'},
                    {name: 'ordenDeSecuencia',index: 'ordenDeSecuencia',width: 120,editable: false,sortable: false,align: 'center'},
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table-surtido1").jqGrid('navGrid', '#grid-pager-surtido1', {
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
            function actionFormat(cellvalue, options, rowObject) {
                var serie = rowObject.folio + ' | ' + rowObject.clave,
                    html = '';
                html += '<a href="#" onclick="surtir(\'' + serie + '\')"><i class="fa fa-check" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                            format: 'dd-mm-yyyy',
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

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

//////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

        
/////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-table-listo-por-asignar";
            var pager_selector = "#grid-pager-listo-por-asignar";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-listo-por-asignar").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
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
                colNames: ['Proyecto', 'License Plate', 'Pedido', 'Cliente', 'Clave', 'Articulo', 'Lote', 'Caducidad | Elaboración', 'Serie', 'Pallet', 'Cajas', 'Piezas', 'Cantidad Pedida', 'Unidad Medida','Disponible','BackOrder', 'Volumen (m3)', 'Peso'],
                colModel: [{
                        name: 'proyecto',
                        index: 'proyecto',
                        width: 150,
                        editable: false,
                        sortable: false,
                        align: 'left'
                    },{
                        name: 'LP',
                        index: 'LP',
                        width: 150,
                        editable: false,
                        sortable: false,
                        align: 'left'
                    },{
                        name: 'folio',
                        index: 'folio',
                        width: 80,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'cliente',
                        index: 'cliente',
                        width: 350,
                        editable: false,
                        sortable: false,
                        hidden: true
                    }, {
                        name: 'clave',
                        index: 'clave',
                        width: 80,
                        editable: false,
                        sortable: false
                    }, {
                        name: 'articulo',
                        index: 'articulo',
                        width: 350,
                        editable: false,
                        sortable: false
                    },{
                        name: 'lote',
                        index: 'lote',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'caducidad',
                        index: 'caducidad',
                        width: 160,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'serie',
                        index: 'serie',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },
                    {name: 'pallet',index: 'pallet',width: 130,editable: false,sortable: false,align: 'center'},
                    {name: 'cajas', index: 'cajas',width: 130,editable: false,sortable: false,align: 'center'},
                    {name: 'piezas',index: 'piezas',width: 130,editable: false,sortable: false,align: 'center'},
                    {
                        name: 'cantidad_pedida',
                        index: 'cantidad_pedida',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },
                    {name: 'unidad_medida',index: 'unidad_medida',width: 120,editable: false,sortable: false},
                    {
                        name: 'existencia_total',
                        index: 'existencia_total',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'back_order_asignar',
                        index: 'back_order_asignar',
                        width: 130,
                        editable: false,
                        sortable: false,
                        align: 'center'
                    },{
                        name: 'volumen',
                        index: 'volumen',
                        width: 100,
                        editable: false,
                        sortable: false,
                        align: 'right'
                    }, {
                        name: 'peso',
                        index: 'peso',
                        width: 100,
                        editable: false,
                        sortable: false,
                        align: 'right'
                    },

                    //{name:'myac',index:'', width:80, fixed:true, sortable:false, resize:false, formatter:actionFormat}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table-listo-por-asignar").jqGrid('navGrid', '#grid-pager-listo-por-asignar', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });

            //**********************************************************************************************
            var grid_selector = "#grid-table-promo";
            var pager_selector = "#grid-pager-promo";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-promo").jqGrid('setGridWidth', $("#coModal").width() - 50);
            })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
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
                {name: 'cve_articulo', index: 'cve_articulo', width: 180, editable: false, sortable: false, hidden: false },
                {name: 'descripcion', index: 'descripcion', width: 280, editable: false, sortable: false, hidden: false },
                {name: 'cve_ruta', index: 'cve_ruta', width: 230, editable: false, sortable: false, hidden: true },
                {name: 'cliente', index: 'cliente', width: 280, editable: false, sortable: false, hidden: true },
                {name: 'cantidad', index: 'cantidad', width: 100, editable: false, align: 'right', sortable: false, hidden: false },
                {name: 'unidad_medida', index: 'unidad_medida', width: 100, editable: false, sortable: false, hidden: false }
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'cve_articulo',
                viewrecords: true,
                sortorder: "desc"
            });

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

            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //////////////////////////////////////////////////////////aqui se construye la funcion de cada item del grid//////////////////////////////////////////////////////////
            function actionFormat(cellvalue, options, rowObject) {
                var serie = rowObject.folio + ' | ' + rowObject.clave,
                    html = '';
                html += '<a href="#" onclick="surtir(\'' + serie + '\')"><i class="fa fa-check" alt="Detalle"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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
                            format: 'dd-mm-yyyy',
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

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

//////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
      
      
        function downloadxml(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;
    </script>


    <!-- grid de articulos 2-->
    <script type="text/javascript">
        /////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-consolidados";
            var pager_selector = "#grid-pager3";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/administradorpedidos/update/index.php',
                datatype: "local",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Clave', 'Articulo', 'Pedidas', 'Surtidas', 'Selección'],
                colModel: [{
                    name: 'clave',
                    index: 'clave',
                    width: 150,
                    editable: false,
                    sortable: false
                }, {
                    name: 'articulo',
                    index: 'articulo',
                    width: 250,
                    editable: false,
                    sortable: false
                }, {
                    name: 'pedidas',
                    index: 'pedidas',
                    width: 100,
                    editable: false,
                    sortable: false
                }, {
                    name: 'surtidas',
                    index: 'surtidas',
                    width: 100,
                    editable: false,
                    sortable: false
                }, {
                    name: 'seleccion',
                    width: 80,
                    align: 'center',
                    formatter: "checkbox",
                    formatoptions: {
                        disabled: false
                    },
                    edittype: 'checkbox',
                    editoptions: {
                        value: "Yes:No",
                        defaultValue: "No"
                    },
                    stype: "select",
                    searchoptions: {
                        sopt: ["eq", "ne"],
                        value: ":Any;true:Yes;false:No"
                    }
                }],
                beforeSelectRow: function(rowid, e) {
                    var $self = $(this),
                        $td = $(e.target).closest("tr.jqgrow>td"),
                        iCol = $td.length > 0 ? $td[0].cellIndex : -1,
                        cmName = iCol >= 0 ? $self.jqGrid("getGridParam", "colModel")[iCol].name : "",
                        localData = $self.jqGrid("getLocalRow", rowid);
                    if (cmName === "seleccion") {
                        localData.seleccion = $(e.target).is(":checked");
                    }

                    return true;
                },
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });
/*
            // Setup buttons
            $("#grid-consolidados").jqGrid('navGrid', '#grid-pager3', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });
*/
            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        /////////////////////////////////////Aqui se contruye el Grid de articulos/////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-surtidos";
            var pager_selector = "#grid-pager4";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/administradorpedidos/update/index.php',
                datatype: "local",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['Familia', 'Clave', 'Artículo', 'Lote', 'Ubicación', 'Existencias', 'Pedidas', 'Cajas'],
                colModel: [
                  {name: 'familia',index: 'familia',width: 150,editable: false,sortable: false}, 
                  {name: 'clave',index: 'clave',width: 150,editable: false,sortable: false}, 
                  {name: 'articulo',index: 'articulo',width: 250,editable: false,sortable: false}, 
                  {name: 'lote',index: 'lote',width: 100,editable: false,sortable: false}, 
                  {name: 'ubicacion',index: 'ubicacion',width: 100,editable: false,sortable: false}, 
                  {name: 'existencias',index: 'existencias',width: 100,editable: false,sortable: false}, 
                  {name: 'pedidas',index: 'pedidas',width: 100,editable: false,sortable: false}, 
                  {name: 'cajas',index: 'cajas',width: 100,editable: false,sortable: false}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });
/*
            // Setup buttons
            $("#grid-surtidos").jqGrid('navGrid', '#grid-pager3', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });
*/



            $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });
      
      
        $(function($) {
            var grid_selector = "#grid-table-surtido";
            var pager_selector = "#grid-pager-surtido";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                $("#grid-table-surtido").jqGrid('setGridWidth', $('#modalItems').width() - 50);
            })
            //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth',  $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/administradorpedidos/update/index.php',
                datatype: "local",
                shrinkToFit: false,
                height: 'auto',
                mtype: 'POST',
                colNames: ['License Plate', 'Clave', 'Artículo', 'Lote', 'Caducidad', 'Serie', 'BL', 'Ruta', 'Surtidor', 'Disponible', 'Solicitado','Surtido','Surtir','Acciones'],
                colModel: [
                  {name: 'lp',index: 'lp',width: 150,editable: false,sortable: false}, 
                  {name: 'clave',index: 'clave',width: 100,editable: false,sortable: false}, 
                  {name: 'articulo',index: 'articulo',width: 150,editable: false,sortable: false}, 
                  {name: 'Lote',index: 'Lote',width: 80,editable: false,sortable: false,align: 'center'},
                  {name: 'Caducidad',index: 'Caducidad',width: 80,editable: false,sortable: false,align: 'center'},
                  {name: 'Serie',index: 'Serie',width: 80,editable: false,sortable: false,align: 'center'},
                  {name: 'bl',index: 'bl',width: 70,editable: false,sortable: false}, 
                  {name: 'ruta',index: 'ruta',width: 100,editable: false,sortable: false}, 
                  {name: 'surtidor',index: 'surtidor',width: 100,editable: false,sortable: false}, 
                  {name: 'existencia',index: 'existencia',width: 80,editable: false,sortable: false, align: 'right'}, 
                  {name: 'solicitado',index: 'solicitado',width: 80,editable: false,sortable: false, align: 'right'}, 
                {name: 'ya_surtido',index: 'ya_surtido',width: 80,editable: false,sortable: false, align: 'right'}, 
                  {name: 'surtido',index: 'surtido',width: 70,editable: false,sortable: false},
                  {name: 'acciones',index: 'acciones',width: 100,editable: false,sortable: false}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc"
            });

            // Setup buttons
            $("#grid-table-surtido").jqGrid('navGrid', '#grid-pager-surtido', {
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

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////

        function downloadxml(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        function viewPdf(url) {
            var win = window.open(url, '_blank');
            win.focus();
        }

        $modal0 = null;
    </script>



    <script>
        $(document).ready(function() {
            //TODO Quitar comentario
            $('td[aria-describedby="grid-table_planear"] input[type="checkbox"]').change(function() {
                //	console.log($('td[aria-describedby="grid-table_planear"] input[type="checkbox"]:checked').length);
                if ($('td[aria-describedby="grid-table_planear"] input[type="checkbox"]:checked').length > 0)
                    $("#planificalo").show();
                else
                    $("#planificalo").hide();
            });

            $('td[aria-describedby="grid-table_asignar"] input[type="checkbox"]').change(function() {
                console.log($('td[aria-describedby="grid-table_asignar"] input[type="checkbox"]:checked').length);
                if ($('td[aria-describedby="grid-table_asignar"] input[type="checkbox"]:checked').length > 0 && $("#permiso_registrar").val() == 1)
                    $("#btn-asignar").show();
                else
                    $("#btn-asignar").hide();
            });




            $('#planificarOla').hide();
            $('#asignacionPedidos').hide();
            $(function() {
                $('.chosen-select').chosen();
                $('.chosen-select-deselect').chosen({
                    allow_single_deselect: true
                });
            });
            $("div.ui-jqgrid-bdiv").css("max-height", $("#list").height() - 80);


            $("#btn-asignarTodo").change(function(e) {
                var val = $(e.currentTarget).prop('checked');                
                $.each( $('.column-asignar'), function (index, item) {
                    $(item).children().first().prop('checked', val) 
                }); 
                if($("#permiso_registrar").val() == 1) $("#btn-asignar").show();
            });


            $("#btn-planificarTodo").change(function(e) {
                var val = $(e.currentTarget).prop('checked');                
                $.each( $('.column-planificar'), function (index, item) {
                    $(item).children().first().prop('checked', val) 
                });               
                //$("#btn-planificar").show();
            });
        });
    </script>



    <script>
        //enable datepicker
        function pickDate(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'dd-mm-yyyy',
                        autoclose: true
                    });
            }, 0);
        }

        function pickDate(cellvalue, options, cell) {
            setTimeout(function() {
                $(cell).find('input[type=text]')
                    .datepicker({
                        format: 'dd-mm-yyyy',
                        autoclose: true
                    });
            }, 0);
        }
    </script>

    <script>
        $("#exportExcel").on("click", function() {
            if ($("#almacen").val() === '') return false;
            var options = {
                criterio: $("#criteriob").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                status: $("#status").val(),
                almacen: $("#almacen").val(),
//                 filtro: $("#filtro").val(),
                facturaInicio: $("#factura_inicio").val(),
                facturaFin: $("#factura_final").val()
            };
            var form = document.createElement("form"),
                input1 = document.createElement("input"),
                input2 = document.createElement("input"),
                input3 = document.createElement("input");
            input4 = document.createElement("input");
            input5 = document.createElement("input");
            input6 = document.createElement("input");
            input7 = document.createElement("input");
            input8 = document.createElement("input");
            input9 = document.createElement("input");
            input10 = document.createElement("input");
            input1.setAttribute('name', 'nofooternoheader');
            input1.setAttribute('value', 'true');
            input2.setAttribute('name', 'action');
            input2.setAttribute('value', 'getDataExcel');
            input3.setAttribute('name', 'criterio');
            input3.setAttribute('value', options.criterio);
            input4.setAttribute('name', 'fechaInicio');
            input4.setAttribute('value', options.fechaInicio);
            input5.setAttribute('name', 'fechaFin');
            input5.setAttribute('value', options.fechaFin);
            input6.setAttribute('name', 'status');
            input6.setAttribute('value', options.status);
            input7.setAttribute('name', 'almacen');
            input7.setAttribute('value', options.almacen);
//             input8.setAttribute('name', 'filtro');
//             input8.setAttribute('value', options.filtro);
            input9.setAttribute('name', 'facturaInicio');
            input9.setAttribute('value', options.facturaInicio);
            input10.setAttribute('name', 'facturaFin');
            input10.setAttribute('value', options.facturaFin);
            form.setAttribute('action', '/api/administradorpedidos/update/index.php');
            form.setAttribute('method', 'post');
            form.setAttribute('target', '_blank');
            form.appendChild(input1);
            form.appendChild(input2);
            form.appendChild(input3);
            form.appendChild(input4);
            form.appendChild(input5);
            form.appendChild(input6);
            form.appendChild(input7);
            form.appendChild(input8);
            form.appendChild(input9);
            form.appendChild(input10);
            document.body.appendChild(form);
            form.submit()
        })
        $("#exportPDF").on("click", function() {
            if ($("#almacen").val() === '') return false;
            var options = {
                criterio: $("#criteriob").val(),
                fechaInicio: $("#fechai").val(),
                fechaFin: $("#fechaf").val(),
                status: $("#status").val(),
                almacen: $("#almacen").val(),
//                 filtro: $("#filtro").val(),
                facturaInicio: $("#factura_inicio").val(),
                facturaFin: $("#factura_final").val(),
                action: 'getDataPDF'
            };
            var title = "Reporte de Pedidos";
            var cia = <?php echo $_SESSION['cve_cia'] ?>;
            var content = '';

            $.ajax({
                url: "/api/administradorpedidos/lista/index.php",
                type: "POST",
                data: options,
                success: function(data, textStatus, xhr) {
                    var data = JSON.parse(data);
                    var content_wrapper = document.createElement('div');
                    /*Detalle*/
                    var table = document.createElement('table');
                    table.style.width = "100%";
                    table.style.borderSpacing = "0";
                    table.style.borderCollapse = "collapse";
                    var thead = document.createElement('thead');
                    var tbody = document.createElement('tbody');
                    var head_content = '<tr>' +
                        '<th style="border: 1px solid #ccc">No. Orden</th>' +
                        '<th style="border: 1px solid #ccc">No. OC Cliente</th>' +
                        '<th style="border: 1px solid #ccc">Prioridad</th>' +
                        '<th style="border: 1px solid #ccc">Status</th>' +
                        '<th style="border: 1px solid #ccc">Cliente</th>' +
                        '<th style="border: 1px solid #ccc">Dirección</th>' +
                        '<th style="border: 1px solid #ccc">Código Dane</th>' +
                        '<th style="border: 1px solid #ccc">Ciudad</th>' +
                        '<th style="border: 1px solid #ccc">Estado</th>' +
                        '<th style="border: 1px solid #ccc">Cantidad</th>' +
                        '<th style="border: 1px solid #ccc">Volumen</th>' +
                        '<th style="border: 1px solid #ccc">Peso</th>' +
                        '<th style="border: 1px solid #ccc">Fecha Pedido</th>' +
                        '<th style="border: 1px solid #ccc">Fecha Entrega</th>' +
                        '<th style="border: 1px solid #ccc">Usuario Activo</th>' +
                        '<th style="border: 1px solid #ccc">% Surtido</th>' +
                        '</tr>';
                    var body_content = '';

                    data.header.forEach(function(item, index) {
                        body_content += '<tr>' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.orden + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.orden_cliente + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.prioridad + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.status + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.cliente + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.direccion + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.dane + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.ciudad + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.estado + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.cantidad + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.volumen + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.peso + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.fecha_pedido + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.fecha_entrega + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.usuario + '</td> ' +
                            '<td style="border: 1px solid #ccc; white-space:nowrap;">' + item.surtido + '</td> ' +
                            '</tr>  ';

                    });

                    tbody.innerHTML = body_content;
                    thead.innerHTML = head_content;
                    table.appendChild(thead);
                    table.appendChild(tbody);

                    content_wrapper.appendChild(table);

                    content = content_wrapper.innerHTML;

                    /*Creando formulario para ser enviado*/

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
                }
            });
        })



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

        $('#data_3').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            useCurrent: false
        });

        function consolidadoExcel() {
            var form = document.getElementById('excelConsolidado');
            form.submit();
        }

        function surtidoExcel() {
            var form = document.getElementById('excelSurtido');
            form.submit();
        }
    </script>
    <script type="text/javascript">
        <!-- GRID ASIGNAR AREA EMBARQUE -->
        //////////////////////////////////////////////////////////Aqui se contruye el Grid//////////////////////////////////////////////////////////
        $(function($) {
            var grid_selector = "#grid-areas-embarque";
            var pager_selector = "#gridpager-areas-embarque";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '/api/administradorpedidos/lista/index.php',
                datatype: isParamsEmpty() ? "local" : "json",
                shrinkToFit: false,
                height: 'auto',
                postData: {
                    criterio: $("#criteriob").val(),
                    tipopedido: $("#tipopedido").val(),
                    ciudad_pedido_list: $("#ciudad_pedido_list").val(),
                    ruta_pedido_list: $("#ruta_pedido_list").val(),
                    fechaInicio: $("#fechai").val(),
                    fechaFin: $("#fechaf").val(),
                    status: $("#status").val(),
                    //filtro: $("#filtro").val()
                },
                mtype: 'POST',
                colNames: ['Fotos','ID','No. Orden','No. OC Cliente','Status','Cliente','Ciudad','Estado','Fecha Pedido','Fecha Entrega', '', ''
                ],
                colModel: [
                  {name: 'fotos',width: 100,index: 'fotos',editable: false,sortable: false},
                  {name: 'id_pedido',index: 'id_pedido',editable: false,sortable: false,hidden: true,key: true}, 
                  {name: 'orden',width: 100,index: 'orden',editable: false,sortable: false}, 
                  {name: 'orden_cliente',width: 100,index: 'orden_cliente',editable: false,sortable: false}, 
                  {name: 'status',index: 'status',editable: false,sortable: false,width: 150}, 
                  {name: 'cliente',index: 'cliente',editable: false,sortable: false,width: 220}, 
                  {name: 'ciudad',index: 'ciudad',editable: false,sortable: false,width: 200}, 
                  {name: 'estado',index: 'estado',editable: false,sortable: false,width: 200}, 
                  {name: 'fecha_pedido',width: 100,index: 'fecha_pedido',editable: false,sortable: false,width: 150}, 
                  {name: 'fecha_entrega',width: 100,index: 'fecha_entrega',editable: false,sortable: false,width: 150}, 
                  {name: 'asigando',width: 100,index: 'asigando',editable: false,sortable: false}, 
                  {name: 'myac',index: '',width: 90,fixed: true,sortable: false,resize: false,formatter: imageFormat,align: "center"  }, 
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                pager: pager_selector,
                sortname: 'ID_Pedido',
                viewrecords: true,
                sortorder: "desc",
                autowidth: true
            });

            // Setup buttons
            $("#grid-areas-embarque").jqGrid('navGrid', '#gridpager-areas-embarque', {
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
                var folio = rowObject.orden;
                var html = '';
                html += '<a href="#" onclick="mostrarAreaEmbarque(\'' + folio + '\')"><i class="fa fa-check" alt="Borrar"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
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

            //var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        //////////////////////////////////////////////////////////hace un reload del grid llamando al php en la carpera "c" para hacer la consulta//////////////////////////////////////////////////////////
        function ReloadGrid5() {
            var rows = jQuery("#grid-table").jqGrid('getRowData');
            $('#grid-areas-embarque').jqGrid('clearGridData');
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['planear'] == "Yes") {
                    $("#grid-areas-embarque").jqGrid('addRowData', i, row);
                }
            }
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
        window.folio;

        function btnasiganarAreaEmbarqueOla() {
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    cve_ubicacion: $("#areaembarque")[0].value,
                    Fol_folio: window.folio,
                    action: "guardarAreaEmbarque"
                },
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: '/api/administradorpedidos/update/index.php',
                success: function(data) {
                    if (data.success == true) {
                        $("#grid-areas-embarque").jqGrid('delRowData', window.cont);
                        //    $('#grid-areas-embarque').jqGrid('clearGridData');
                        $("#areaemb")[0].style.display = 'none';
                        //    $("#areaembarque")[0].val("");
                        document.ready = document.getElementById("areaembarque").value = "";

                    }
                }
            });
        }

        function mostrarAreaEmbarque(_folio) {
            $("#areaemb")[0].style.display = 'block';
            window.folio = _folio;
            var rows = jQuery("#grid-areas-embarque").jqGrid('getRowData');
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row['orden'] == _folio) {
                    window.cont = i;
                }
            }

        }

        $("#volverSurtido").on("click", function(e) {
            $("#modal_surtido").modal('hide');
            $("#coModal").modal('show');
        });

        var itemSurtido = 0;

        function surtir(_folio, _articulo) {
            item = itemsArticulosPedidos[itemSurtido];

            item = item.folio + ' | ' + item.clave

            $("#coModal").modal('hide');
            $("#modal_surtido").modal('show');
            ReloadSurtido(item, _articulo)
        }

        $(function($) {
            var grid_selector = "#grid-table6";
            var pager_selector = "#grid-pager6";

            //resize to fit page size
            $(window).on('resize.jqGrid', function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                })
                //resize on sidebar collapse/expand
            var parent_column = $(grid_selector).closest('[class*="col-"]');
            $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
                if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') {
                    //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                    setTimeout(function() {
                        $(grid_selector).jqGrid('setGridWidth', $(window).width() - 150);
                    }, 0);
                }
            })

            $(grid_selector).jqGrid({
                url: '',
                shrinkToFit: false,
                dataType: 'json',
                height: 'auto',
                mtype: 'POST',
                colNames: ['Folio', 'Clave', 'Artículo', 'Lote', 'Caducidad', 'Fecha de Ingreso', 'Ubicación', 'Existencia', 'Pedidas', 'Surtidas'],
                colModel: [
                  {name: 'folio',width: 130,index: 'folio',editable: false,sortable: false,hidden: true}, 
                  {name: 'clave',width: 130,index: 'clave',editable: false,sortable: false}, 
                  {name: 'articulo',width: 130,index: 'articulo',editable: false,sortable: false}, 
                  {name: 'lote',width: 130,index: 'lote',editable: false,sortable: false}, 
                  {name: 'caducidad',width: 130,index: 'caducidad',editable: false,sortable: false}, 
                  {name: 'fecha',width: 130,index: 'fecha',editable: false,sortable: false}, 
                  {name: 'ubicacion',width: 130,index: 'ubicacion',editable: false,sortable: false}, 
                  {name: 'existencia',width: 130,index: 'existencia',editable: false,sortable: false}, 
                  {name: 'pedidas',width: 130,index: 'pedidas',editable: false,sortable: false}, 
                  {name: 'surtidas',width: 130,index: 'surtidas',editrules: {integer: true},editable: true,edittype: 'text',sortable: false  }, 
                ],
                rowNum: 30,
                rowList: [30, 40, 50],
                pager: pager_selector,
                sortname: 'clave',
                viewrecords: true,
                sortorder: "desc",
                autowidth: true,
                editurl: 'clientArray',
                onSelectRow: editRow,
            });
            var lastSelection;

            function editRow(id) {
                if (id && id !== lastSelection) {
                    var grid = $(grid_selector);
                    grid.jqGrid('restoreRow', lastSelection);
                    grid.jqGrid('editRow', id, {
                        keys: true
                    });
                    lastSelection = id;
                }
            }

            // Setup buttons
            $(grid_selector).jqGrid('navGrid', '#grid-pager', {
                edit: false,
                add: false,
                del: false,
                search: false
            }, {
                height: 200,
                reloadAfterSubmit: true
            });


            $(window).triggerHandler('resize.jqGrid');

            $(document).one('ajaxloadstart.page', function(e) {
                $(grid_selector).jqGrid('GridUnload');
                $('.ui-jqdialog').remove();
            });
        });

        function ReloadSurtido(_folio, _articulo) {
            $('#grid-table6').jqGrid('clearGridData')
                .jqGrid('setGridParam', {
                    url: '/api/administradorpedidos/lista/index.php',
                    postData: {
                        folio: _folio,
                        articulo: _articulo,
                        action: 'getArticuloSurtido'
                    },
                    datatype: 'json',
                    page: 1
                })
                .trigger('reloadGrid', [{
                    current: true
                }]);
        }

        document.getElementById("guardarSurtido").addEventListener('click', function(e) {
            var data = $('#grid-table6').jqGrid('getRowData'),
                pedidas = parseInt(data[0].pedidas),
                existencia = parseInt(data[0].existencia),
                surtidas = 0;

            for (var i = 0; i < data.length; i++) {
                var current = data[i];
                surtidas += parseInt(current.surtidas);
            }
            if (surtidas === 0) {
                swal("Error", "Debes indiciar la cantidad de piezas surtidas", "error");
            } else if (surtidas > pedidas) {
                swal("Error", "La cantidad de piezas a surtir es mayor a solicitadas", "error");
            } else if (surtidas > existencia) {
                swal("Error", "La cantidad de piezas a surtir es mayor a la existencia", "error");
            } else {
                $.ajax({
                    url: '/api/administradorpedidos/update/index.php',
                    dataType: 'json',
                    data: {
                        action: 'guardarsurtido',
                        folio: data[0].folio,
                        cve_articulo: data[0].clave,
                        lote: data[0].lote,
                        caducidad: data[0].caducidad,
                        pedidas: pedidas,
                        surtidas: surtidas,
                        ubicacion: data[0].ubicacion
                    },
                    type: 'POST'
                }).done(function(data) {
                    if (data.success) {
                        swal({
                            title: "Éxito",
                            text: "El surtido se guardó correctamente",
                            type: "success"
                        }, function(confirm) {
                            $("#modal_surtido").modal('hide');
                            $("#coModal").modal('show');
                        });
                    } else {
                        swal("Error", "Ocurrió un error al guardar el surtido", "error");
                    }
                });
            }
        })
    </script>
    <script type="text/javascript">

    function cambiarPrioridad(_folio) {

        $.ajax({
            url: '/api/administradorpedidos/lista/index.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'obtenerPrioridad',
                folio: _folio
            }
        }).done(function(data) {
            console.log("obtenerPrioridad = ", data);

            $("#modal-prioridad").modal('show');
            $("#pedido-prioridad").val(_folio);
            if(data.prioridad)
            {
            $("#select-prioridad").val(data.prioridad);
            $("#select-prioridad").trigger('chosen:updated');
            $(".chosen-select").trigger("chosen:updated");
            }

        });
    }

    function EditarPrioridad()
    {
        $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                data: {
                    folio: $("#pedido-prioridad").val(),
                    prioridad: $("#select-prioridad").val(),
                    action: 'EditarPrioridad'
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(data) {
                /* || data.folio == 'undefined' || data.folio == '' || $("#fol_cambio").val() != data.folio /*mientras veo que pasa con el folio*/
                
                swal("Éxito", "Cambio de Prioridad realizado", "success");
                ReloadGrid();
                $("#modal-prioridad").modal('hide');
                //else swal("Cambio No Permitido", data.msj, "error");//PROVISIONAL MIENTRAS SE ARREGLA LO DE CAMBIO DE ESTATUS 
            })/*.fail(function(data){
                console.log("QA ERROR -> ",data);
            })*/;
    }

    var EstatusActual = null;

    /**Levanta el Modal */
    function cambiarStatus(_folio, sufijo, surtido) {

        folio = _folio;
        if ($('#status').val() == 'S' && surtido >= 99) {
            enviarAEbarque_o_Qa("");
            return true;
        }

        $.ajax({
            url: '/api/administradorpedidos/lista/index.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'obtenerStatus',
                folio: _folio
            }
        }).done(function(data) {
            console.log("obtenerStatus = ", data);
            if (data.status) {
                EstatusActual = data.status;
                console.log("EstatusActual = ", EstatusActual);
                $("#select-nuevo-status-folio").val(data.status);
                var hide = false;
                $("#select-nuevo-status-folio option").each(function(i){

                    console.log("Valor Select = ", $(this).val(), "EstatusActual = ", EstatusActual);
                    if($(this).val() != EstatusActual)
                    {
                        console.log("Valor Select OK");
                        if(($(this).val() == 'A') )//&& data.surtiendo == '0'
                            $(this).show();
                        else
                        {
                            if((EstatusActual == 'S' && $(this).val() == 'K') || (EstatusActual == 'S' && $(this).val() == 'T'))
                                $(this).show();
                            else if((EstatusActual == 'A' && $(this).val() == 'K') || (EstatusActual == 'A' && $(this).val() == 'T'))
                                $(this).show();
                            else if(EstatusActual == 'T' || $(this).val() == 'S')
                                $(this).show();
                            else
                                $(this).hide();
                        }
                    }
                    else
                        hide = true;

                });
                $("#select-nuevo-status-folio").trigger('chosen:updated');
            }
            $("#modal-status-motivo-txt").val('');
            $("#modal-status-motivo").hide();
            $("#modal-status").modal('show');
            $("#txt-status-actual-folio").val(_folio);
            $("#txt-status-actual-sufijo").val(sufijo);
        });
    }

    function CambiarStatusVarios()
    {

      var folios = [];
      $.each( $('.column-planificar'), function (index, item) {
            var i = $(item).children().first();
            if ( i.prop('checked') == true ) {
                folios.push(i.data('id'));
            }
        });

        if(folios.length == 0)
        {
            swal("No hay Pedidos Seleccionados", "No ha seleccionado ningún pedido para cambiar el Status", "error");
            return;
        }

        $("#modal-status-varios").modal('show');
    }

    function BorrarPedido(folio) {

            swal({
                    title: "Eliminar Pedido",
                    text: "Seguro desea Eliminar el Pedido: "+folio,
                    type: "warning",

                    showCancelButton: true,
                    cancelButtonText: "No",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "Si",
                    closeOnConfirm: true
                },
                function(e) {
                    console.log("BorrarPedido = ", e);
                    if (e == true) {

                                $.ajax({
                                    url: '/api/administradorpedidos/update/index.php',
                                    method: 'POST',
                                    dataType: 'json',
                                    data: {
                                        action: 'BorrarPedido',
                                        folio: folio
                                    }
                                }).done(function(data) {
                                    swal("Se Ha Eliminado El Pedido: "+folio, "", "success");
                                    ReloadGrid();
                                }).fail(function(data){
                                    console.log("ERROR BorrarPedido = ", data);
                                });

                    } 
            });

    }

    function BorrarOT(folio) {

            swal({
                    title: "Eliminar OT",
                    text: "Seguro desea Eliminar la Orden de Trabajo: "+folio,
                    type: "warning",

                    showCancelButton: true,
                    cancelButtonText: "No",
                    cancelButtonColor: "#14960a",

                    confirmButtonColor: "#55b9dd",
                    confirmButtonText: "Si",
                    closeOnConfirm: true
                },
                function(e) {
                    console.log("BorrarOT = ", e);
                    if (e == true) {

                                $.ajax({
                                    url: '/api/administradorpedidos/update/index.php',
                                    method: 'POST',
                                    dataType: 'json',
                                    data: {
                                        action: 'BorrarOT',
                                        folio: folio
                                    }
                                }).done(function(data) {
                                    swal("Se Ha Eliminado la Orden de Trabajo: "+folio, "", "success");
                                    ReloadGrid();
                                }).fail(function(data){
                                    console.log("ERROR BorrarOT = ", data);
                                });

                    } 
            });

    }

    // Al cambia el estatus del Select
    $("#select-nuevo-status-folio").change(function() {
      if ($(this).val() == 'K') {
          $("#modal-status-motivo").show();
      } else {
          $("#modal-status-motivo").hide();
      }

      if (EstatusActual == 'K' && $("#select-nuevo-status-folio").val() != 'K') {
          $("#modal-status-motivo").show();
      }
    });

    $("#status, #tipopedido, #ruta_pedido_list, #ciudad_pedido_list").change(function(){

        console.log("filtralo 6");
        filtralo();

        //if($("#tipopedido").val() != '' && $("#ruta_pedido_list").val() != '')
        if(($("#tipopedido").val() == '' && $("#ruta_pedido_list").val() != '' && $("#tipopedido").val() != 'W') || ($("#ruta_pedido_list").val() == '' && $("#tipopedido").val() != '' && $("#tipopedido").val() != 'W') || $("#tipopedido").val() == '4')

        {
            console.log("Entro hide");
           $("#btn-planificar, #btn-cambiarstatus, .column-planificar input, .column-dividir input").hide();
        }
        else
        {
            console.log("Entro show");
           $("#btn-planificar, #btn-cambiarstatus, .column-planificar input, .column-dividir input").show();
        }

    });
/*
    $("#tipopedido").change(function(){

        if($(this).val() != '')
           $("#btn-planificar, .column-planificar input").show();
        else
           $("#btn-planificar, .column-planificar input").hide();

    });
*/
    function asignarStatusVarios()
    {
        var nuevo_status = $("#select-nuevo-status-varios").val(),
            motivo = $("#modal-status-motivo-txt-varios").val();

          var folios = [];
          $.each( $('.column-planificar'), function (index, item) {
                var i = $(item).children().first();
                if ( i.prop('checked') == true ) {
                    folios.push(i.data('id'));
                }
            });

            if(nuevo_status == '')
            {
                swal("Debe Seleccionar un Status", "No ha seleccionado ningún status para cambiar", "error");
                return;
            }

            console.log("nuevo_status = ", nuevo_status);
            console.log("motivo = ", motivo);
            console.log("folios = ", folios);


            $.ajax({
                url: '/api/administradorpedidos/update/index.php',
                data: {
                    status: nuevo_status,
                    folios: folios,
                    action: 'CambiarStatusVarios',
                    motivo: motivo
                }, success: function(data)
                {
                    swal({
                        title: "Éxito",
                        text: "Cambios de Status Realizados con éxito",
                        type: "success",

                        cancelButtonText: "No",
                        cancelButtonColor: "#14960a",
                        showCancelButton: false,

                        confirmButtonColor: "#55b9dd",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    }, function() {
                        window.location.reload();
                    });


                }, error: function(data)
                {
                    console.log("ERROR? -> ", data);
                    //solución rápida ya que aunque entre en error igual se cambia el status
                    swal({
                        title: "Éxito",
                        text: "Cambios de Status Realizados con éxito",
                        type: "success",

                        cancelButtonText: "No",
                        cancelButtonColor: "#14960a",
                        showCancelButton: false,

                        confirmButtonColor: "#55b9dd",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    }, function() {
                        window.location.reload();
                    });

                },
                type: 'POST',
                dataType: 'json'
            });
    }

    function asignarStatus() 
    {
        /*if( EstatusActual == 'S' ){
            enviarAEbarque_o_Qa();
            return true;
        }*/

        var folio = $("#txt-status-actual-folio").val(),
            sufijo = $("#txt-status-actual-sufijo").val(),
            nuevo_status = $("#select-nuevo-status-folio").val(),
            motivo = $("#modal-status-motivo-txt").val();
            console.log("folio = ", folio);
            console.log("nuevo_status = ", nuevo_status);
            console.log("motivo = ", motivo);
        /*
        if (nuevo_status == 'K' && motivo == '') {
            swal("Cancelación de Pedido", "Debe ingresar el mótivo por el cual se está cancelando el pedido", "warning");
            return false;
        }
        */
        if (EstatusActual == 'K' && $("#select-nuevo-status-folio").val() != 'K' && motivo == '') {
            swal("Cancelación de Pedido", "Debe ingresar el mótivo por el cual se está reversando el cancelado del pedido", "warning");
            return false;
        }
        console.log("Cambiar Status Sufijo = ", window.detalle_pedido);

        //return;

        $.ajax({
            url: '/api/administradorpedidos/update/index.php',
            data: {
                folio: folio,
                status: nuevo_status,
                sufijo: sufijo, //window.detalle_pedido["articulos"][0]["sufijo"],
                almacen: $('#almacen').val(),
                action: 'cambiarStatus',
                motivo: motivo
            }, success: function(data)
            {
              console.log("Cambiando status...", data);

                if (data.success) {

                    console.log("filtralo 7");
                    filtralo();
                    $("#modal-status").modal('hide');
                    swal("Éxito", "Status de la orden cambiado exitosamente", "success");

                }else {
                    if(data.msj != undefined)
                    {
                        console.log("msj",data.msj);
                        swal("Error", data.msj, "error");
                    }
                    else
                    {
                        swal("Error", "Ocurrió un error al cambiar el status de la orden", "error");
                    }
                }
                //window.location.reload();

            }, error: function(data)
            {
                console.log("ERROR -> ", data);
                console.log("ERROR -> ", data.sql);
                console.log("ERROR -> ", data.success);
                //solución rápida ya que aunque entre en error igual se cambia el status
                swal("Éxito", "Status de la orden cambiado exitosamente", "success");
                //window.location.reload();
            },
            type: 'POST',
            dataType: 'json'
        });/*.done(function(data) {
          console.log("Cambiando status...");
            if (data.success) {

                filtralo();
                $("#modal-status").modal('hide');
                swal("Éxito", "Status de la orden cambiado exitosamente", "success");

            }else {
                if(data.msj != undefined)
                {
                    console.log("msj",data.msj);
                    swal("Error", data.msj, "error");
                }
                else
                {
                    swal("Error", "Ocurrió un error al cambiar el status de la orden", "error");
                }
            }
        });*/
    }

    function AbrirModalEdicion(folio)
    {

      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/administradorpedidos/update/index.php',
        data: 
        {
          action: 'getDetalleEdicion',
          folio: folio
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS", data);
            $("#datos_folio").empty();
            $("#datos_folio").append(data.datos);
            fillSelectArti();
            //return;

        }, error: function(data) 
        {
          console.log("ERROR 5", data);
        }
      });

        $('#pedidoAEditar').text(folio);
        $("#editarPedido").modal('show');
    }


    var inicio = 0;
    function fillSelectArti()
    {  
        //return;
      var almacen = document.getElementById("almacen").value;

      //console.log("almacen init = ", almacen);
      //console.log("grupo = ", $("#gr_marca").val());
      //console.log("clasificacion = ", $("#mod_clasif").val());
      $.ajax({
        url: "/api/articulos/lista/index.php",
        type: "GET",
        data: {
          modulo: 'nuevospedidos',
          grupo: '', 
          clasificacion: '', 
          almacen: almacen
        },
        beforeSend: function(x){if(x && x.overrideMimeType) x.overrideMimeType("application/json;charset=UTF-8");
        },
        success: function(res){
          //console.log(res);
          fillSelect(res);
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
        var basic = document.getElementById('articulo');
        var option = "<option value =''>Seleccione un Articulo ("+node.length+")</option>";
        if(node.length > 0)
        {
          for(var i = 0; i < node.length; i++)
          {

            option += "<option value = '"+(node[i].cve_articulo)+"' data-id='"+(node[i].id)+"'>"+""+(node[i].cve_articulo)+" - "+(node[i].des_articulo)+"</option>";
          }
          //console.log("node = ", option);
        }
        basic.innerHTML = option;
        $(basic).trigger("chosen:updated");
      }
    }

    $('#articulo').change(function(e) {
        var cve_articulo= $(this).val(),
            option = $(this).find('option:selected'),
            id = option.data('id');

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
                console.log("articulo L = ", data.control_lotes);
                console.log("articulo S = ", data.control_numero_series);

                $('#tiene_lote').val(data.control_lotes);
                $('#tiene_serie').val(data.control_numero_series);

                console.log("#tiene_lote", $("#tiene_lote").val());
                console.log("#tiene_serie", $("#tiene_serie").val());
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        cve_articulo : cve_articulo,
                        tiene_lote:  $("#tiene_lote").val(),
                        tiene_serie: $("#tiene_serie").val(),
                        action : "load_lotes_series"
                    },
                    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                                            },
                    url: '/api/nuevospedidos/lista/index.php',
                    success: function(data) {
                        console.log("SUCCESS LOTES: ", data);
                        $("#lote_serie").empty();
                        $("#lote_serie").append(data.res);
                        $('.chosen-select').trigger('chosen:updated');
                    }, error: function(data){
                        console.log("ERROR 1: ", data);
                    }
                });


            }, error: function(data){
                console.log("ERROR CHANGE: ", data);
            }
        });
    });

    function OVSap(folio, sufijo){
        console.log("folio = ", folio);
        console.log("sufijo = ", sufijo);

        //return;
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/administradorpedidos/update/index.php',
        data: 
        {
          //action: 'ConectarSAP',
          //funcion: 'Login',
          action: 'EjecutarOVSap',
          funcion: 'DeliveryNotes',
          metodo: 'POST',
          folio: folio,
          sufijo: sufijo
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            console.log("SUCCESS", data);
            return;
            if(data.SessionId)
            {
                console.log("SessionId OK:", data.SessionId);
                    //return;
                  $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/api/adminordentrabajo/update/index.php',
                    data: 
                    {
                      action: 'EjecutarOVSap',
                      funcion: 'DeliveryNotes',
                      //funcion: 'Drafts',
                      metodo: 'POST',
                      folio: folio,
                      sufijo: sufijo,
                      sesion_id: data.SessionId
                    },
                    beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
                    },
                    success: function(data) 
                    {
                      console.log("SUCCESS EjecutarOVSap", data);
                      if(data.error)
                      {
                         swal("Error", data.error.message.value, "error");
                      }
                      else
                         swal("Envío Correcto", "Actualización a OT Correcta", "success");
                    }, error: function(data) 
                    {
                      console.log("ERROR EjecutarOVSap", data);
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
          console.log("ERROR 1", data);
        }
      });

    }

    $("#AgregarProductoEdicion").click(function(){

        var cve_articulo = $("#articulo").val(),
            lote_serie = $("#lote_serie").val(),
            CantPiezas = $("#CantPiezas").val(),
            id_unimed = $("#id_unimed").val();

            if(cve_articulo == '')
            {
                swal("Artículo Vacío", "Debe seleccionar un artículo", "error");
                return;
            }

            if(CantPiezas == '' || CantPiezas == '0')
            {
                swal("Cantidad Vacía", "Debe registrar una cantidad", "error");
                return;
            }

            if(id_unimed == '')
            {
                swal("Unidad de Medida Vacía", "Debe seleccionar una unidad de medida", "error");
                return;
            }

        $("#datos_folio").append('<div class="row">'+
                  '<input type="hidden" class="id_edicion datos_edicion" value="add">'+
                  '<input type="hidden" class="unidad_medida_ed datos_edicion" value="'+id_unimed+'">'+
                  '<div class="col-md-3">'+
                  '<input type="text" class="form-control cve_articulo_ed datos_edicion" readonly value="'+cve_articulo+'">'+
                  '</div>'+
                  '<div class="col-md-3">'+
                  '<input type="text" class="form-control cve_lote_ed datos_edicion" readonly value="'+lote_serie+'">'+
                  '</div>'+
                  '<div class="col-md-3">'+
                  '<input type="text" class="form-control cantidad_ed datos_edicion" placeholder="Cantidad..." value="'+CantPiezas+'">'+
                  '</div>'+
                  '<div class="col-md-3">'+
                  '<input type="checkbox" class="eliminar_ed datos_edicion">'+
                  '</div>'+
              '</div>'+
              '<br>');

            $("#articulo").val("");
            $("#lote_serie").val("");
            $("#CantPiezas").val("");
            $("#id_unimed").val("");
            $('#articulo, #lote_serie').trigger("chosen:updated");

    });

    $("#btn-editar-pedido").click(function(){

        var arr_id_edicion = [], arr_unidad_medida_ed = [], arr_cve_articulo_ed = [], arr_cve_lote_ed = [], arr_cantidad_ed = [], arr_eliminar_ed = [];
        $(".id_edicion").each(function(i, j){
            arr_id_edicion[i] = j.value;
        });

        $(".unidad_medida_ed").each(function(i, j){
            arr_unidad_medida_ed[i] = j.value;
        });

        $(".cve_articulo_ed").each(function(i, j){
            arr_cve_articulo_ed[i] = j.value;
        });

        $(".cve_lote_ed").each(function(i, j){
            arr_cve_lote_ed[i] = j.value;
        });

        $(".cantidad_ed").each(function(i, j){
            arr_cantidad_ed[i] = j.value;
        });

        $(".eliminar_ed").each(function(i, j){
            if($(this).is(':checked'))
                arr_eliminar_ed[i] = 1;
            else
                arr_eliminar_ed[i] = 0;
        });

        console.log("arr_id_edicion: ", arr_id_edicion);
        console.log("arr_unidad_medida_ed: ", arr_unidad_medida_ed);
        console.log("arr_cve_articulo_ed: ", arr_cve_articulo_ed);
        console.log("arr_cve_lote_ed: ", arr_cve_lote_ed);
        console.log("arr_cantidad_ed: ", arr_cantidad_ed);
        console.log("arr_eliminar_ed: ", arr_eliminar_ed);
        console.log("folio: ", $("#pedidoAEditar").text());

        //return;
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/administradorpedidos/update/index.php',
        data: 
        {
          action: 'EditarPedido',
          arr_id_edicion: arr_id_edicion,
          arr_unidad_medida_ed: arr_unidad_medida_ed,
          arr_cve_articulo_ed: arr_cve_articulo_ed,
          arr_cve_lote_ed: arr_cve_lote_ed,
          arr_cantidad_ed: arr_cantidad_ed,
          arr_eliminar_ed: arr_eliminar_ed,
          folio: $("#pedidoAEditar").text()
        },
        beforeSend: function(x){if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
        },
        success: function(data) 
        {
            swal("Éxito", "Pedido Editado Con Éxito", "success");
            $("#editarPedido").modal('hide');
        }, error: function(data) 
        {
          console.log("ERROR 2", data);
        }
      });


    });

      function ReportePDF(folio, fecha_pedido)
      {
        //console.log("ok koolreport");
        $("#btn_reporte_surtido").attr("href", "/api/koolreport/export/reportes/pedidos/reporte_surtido?folio="+folio+"&fecha_pedido="+fecha_pedido);
      }

    function imprimirRemision(folio) 
    {
      var //folio = $("#fol_cambio").val(),
          form = document.createElement('form'),
          input_nofooter = document.createElement('input'),
          input_folio = document.createElement('input');

      form.setAttribute('method', 'post');
      form.setAttribute('action', '/reportes/pdf/remision');
      form.setAttribute('target', '_blank');

      input_nofooter.setAttribute('name', 'nofooternoheader');
      input_nofooter.setAttribute('value', '1');

      input_folio.setAttribute('name', 'folio');
      input_folio.setAttribute('value', folio);

      form.appendChild(input_nofooter);
      form.appendChild(input_folio);

      document.getElementsByTagName('body')[0].appendChild(form);
      form.submit();
    }

    $('.ui-icon-seek-prev').click(function() {
        var p = $("#page").val();

        if(p > 1) p--;

        $("#page").val(p);
        ReloadGrid();
    });

    $('.ui-icon-seek-next').click(function() {

        var p = $("#page").val();

        if(p < parseInt($(".total_pages").text())) p++;

        $("#page").val(p);


        ReloadGrid();
    });


    </script>


