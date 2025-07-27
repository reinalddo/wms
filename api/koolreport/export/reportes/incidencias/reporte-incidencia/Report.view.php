<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Reporte de Venta</title>
</head>
<body style="margin: 30px;">
<style>
    .encabezado
    {
        font-size: 14px;
        float: right;
        text-align: right;
        right: 0px;
        position: absolute;
        top: 0;
    }

    .under_line
    {
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
    }

    .datos_cliente_entrega
    {
        margin-top: 50px;
        font-size: 18px;
    }

</style>
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $folio = $_GET['folio'];

    $sql = "SELECT imagen, des_cia, des_direcc, distrito, des_telef, des_email FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $compania = $des_cia;

    ?>
    <div class="row">
        <div class="col-4 text-center encabezado_logo">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>

<?php 
    $sql = "SELECT  ID_Incidencia, Fol_folio, Descripcion, Respuesta, DATE_FORMAT(Fecha, '%d/%m/%Y') AS Fecha, c.RazonSocial AS cliente, 
                    reportador, u1.nombre_completo as responsable_recibo, plan_accion, u2.nombre_completo as responsable_plan, u3.nombre_completo as responsable_verificacion, DATE_FORMAT(Fecha_accion, '%d/%m/%Y') AS Fecha_accion,
                    IF(tipo_reporte = 'P', 'Petici&oacute;n', IF(tipo_reporte = 'Q', 'Queja', IF(tipo_reporte = 'R', 'Reclamo', 'Sugerencia'))) AS tipo_reporte,
                    ma.Des_Motivo AS motivo_registro,
                    i.desc_motivo_registro,
                    mc.Des_Motivo AS motivo_cierre,
                    i.desc_motivo_cierre
            FROM th_incidencia i
            LEFT JOIN c_cliente c ON c.Cve_Clte = i.cliente 
            LEFT JOIN c_motivo ma ON ma.id = i.id_motivo_registro 
            LEFT JOIN c_motivo mc ON mc.id = i.id_motivo_cierre
            LEFT JOIN c_usuario u1 ON u1.cve_usuario = i.responsable_recibo
            LEFT JOIN c_usuario u2 ON u2.cve_usuario = i.responsable_plan
            LEFT JOIN c_usuario u3 ON u3.cve_usuario = i.responsable_verificacion
            WHERE i.ID_Incidencia = {$folio}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);
?>
        <div class="col-8 encabezado">
        <span><?php echo ($Fecha); ?></span><br>
        </div>
    </div>
<br><br>
    <div style="text-align: center; font-size: 25px; font-weight: bold;">Reporte de Incidencias | PQRS</div>
<br><br><br>
<style>
    .info, .info .titulo, .info .dato, .info .firma
    {
        display: inline-block;
        font-size: 18px;
    }

    .info .titulo
    {
        font-weight: bold;
        width: 300px;
    }

    .info .firma
    {
        border-top: 2px solid #000;
        text-align: center;
        padding-top: 10px;
        width: 250px;
    }

    .info .dato
    {
        border: 1px solid #ccc;
        padding: 5px;
    }
    .info .n_indicencia
    {
        width: 100px;
    }
    .info .tipo_reporte
    {
        width: 200px;
    }
    .info .factura_folio
    {
        width: 250px;
    }
    .info .motivo
    {
        width: 898px;
    }
    .info .comentarios
    {
        width: 1104px;
        height: 100px;
    }
    .row .info
    {
        text-align: center;
    }

    .compania
    {
        margin-top: 40px;
        font-size: 20px;
        width: 100%;
        background: #000000 !important;
        color: #ffffff !important;
        text-align: center;
    }

</style>

<?php 
function cortar_string ($string, $largo) { 
   $marca = "..."; 
 
   if (strlen($string) > $largo) { 
        
       $string = wordwrap($string, $largo, $marca); 
       $string = explode($marca, $string); 
       $string = $string[0]; 
   } 
   return $string; 
} 
?>
<div class="info">
    <span class="titulo">No. Incidencia</span>
    <span class="dato n_indicencia" style="border: 0;"><?php echo $ID_Incidencia; ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Cliente</span>
    <span class="dato" style="border: 0;"><?php echo $cliente; ?></span>
</div>

<br><br><br>
<div class="info">
    <span class="titulo">Tipo de Reporte</span>
    <span class="dato tipo_reporte" style="border: 0;"><?php echo utf8_encode($tipo_reporte); ?></span>
</div>

<div class="info" style="float: right;">
    <span class="titulo" style="text-align: right;">Factura | Folio</span>
    <span class="dato factura_folio" style="border: 0;"><?php echo $Fol_folio; ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Motivo</span>
    <span class="dato motivo"><?php echo utf8_encode($motivo_registro); ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Descripción detallada</span>
    <?php $puntos = ""; if(strlen($desc_motivo_registro) > 260) $puntos = "..."; ?>
    <span class="dato comentarios"><?php echo cortar_string(utf8_encode($desc_motivo_registro), 260).$puntos; ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Acciones | Solución</span>
    <?php $puntos = ""; if(strlen($Descripcion) > 260) $puntos = "..."; ?>
    <span class="dato comentarios"><?php echo cortar_string(utf8_encode($Descripcion), 260).$puntos; ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Validación | Resultados</span>
    <?php $puntos = ""; if(strlen($plan_accion) > 260) $puntos = "..."; ?>
    <span class="dato comentarios"><?php echo cortar_string(utf8_encode($plan_accion), 260).$puntos; ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Resolución</span>
    <span class="dato motivo" style="border:0;"><?php echo utf8_encode($motivo_cierre); ?></span>
</div>
<br><br><br>
<div class="info">
    <span class="titulo">Comentarios</span>
    <?php $puntos = ""; if(strlen(utf8_encode($desc_motivo_cierre)) > 260) $puntos = "..."; ?>
    <span class="dato comentarios"><?php echo cortar_string(utf8_encode($desc_motivo_cierre), 260).$puntos; ?></span>
</div>

<br><br><br><br><br><br><br><br><br>


<div class="row">
<div class="info col-xs-4">
    <?php echo utf8_encode($responsable_recibo); ?><br>
    <span class="firma">Registro</span>
</div>

<div class="info col-xs-4">
    <?php echo utf8_encode($responsable_plan); ?><br>
    <span class="firma">Supervisor</span>
</div>

<div class="info col-xs-4">
    <?php echo utf8_encode($responsable_verificacion); ?><br>
    <span class="firma">Responsable Cierre</span>
</div>

</div>

<div class="compania">
    <?php echo utf8_encode($compania); ?>
</div>

</div>
</body>
</html>

