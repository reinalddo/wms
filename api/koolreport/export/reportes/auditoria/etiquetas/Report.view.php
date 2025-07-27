<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;
    use \koolreport\barcode\BarCode;

include '../../../../../../config.php';
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Etiquetas</title>
</head>
<body style="margin: 10px;">
<style>
    @font-face {
    font-family: 'Arial Regular';

    src: local('Arial Regular'), url('ARIAL.woff') format('woff');
    }
    .compania
    {
        width: 100%;
        background: #000000 !important;
        color: #ffffff !important;
        text-align: left;
        padding-left: 80px;
    }


    .info
    {
        position: relative;
        font-size: 10px;
        padding-left: 15px;
        display: inline-block;
        font-family: 'Arial Regular';
    }

    .titulo
    {
        font-size: 16px;
        width: 13.5cm;
        display: inline-block;
        text-align: center;
    }
    .direccion
    {
        font-size: 18px;
        width: 3cm;
        display: inline-block;
        text-align: right;
        vertical-align: top;
    }
    .direccion_dato
    {
        font-size: 14px;
        width: 10cm;
        display: inline-block;
        text-align: left;
    }

    .pedido_dato
    {
        font-size: 20px;
        width: 10cm;
        display: inline-block;
        text-align: left;
    }

    .caja
    {
        font-size: 16px;
        width: 13cm;
        display: inline-block;
        text-align: right;
        vertical-align: top;
    }
    .caja_dato
    {
        font-size: 14px;
        width: 3cm;
        display: inline-block;
        text-align: left;
    }

    .bold
    {
        font-weight: bolder;
    }
</style>

<?php 
function cortar_string ($string, $largo) { 
   $marca = "..."; 
 
   if (strlen($string) > $largo) { 
        
       $string = wordwrap($string, $largo, $marca); 
       $string = explode($marca, $string); 
       $string = $string[0].$marca; 
   } 

   return $string; 
} 


function Etiquetas($folio)
{
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$sql_embarque = " IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$folio') ";

$sql = "";
$sql = "SELECT DISTINCT razonsocial, direccion, ciudad FROM c_destinatarios WHERE id_destinatario = (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE fol_folio = '$folio')";

if(!isset($_GET['ncajas']))
{
    //$sql = "SELECT DISTINCT razonsocial, direccion FROM c_destinatarios WHERE id_destinatario = (SELECT Id_Destinatario FROM Rel_PedidoDest WHERE fol_folio  IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$folio') )";
    $sql = "SELECT DISTINCT razonsocial, direccion, ciudad FROM c_destinatarios WHERE id_destinatario = (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE fol_folio = '$folio' LIMIT 1)";
}

if (!($res = mysqli_query($conn, $sql))){
    echo "Falló la preparación(21): (" . mysqli_error($conn) . $sql. ") ";
}

if(mysqli_num_rows($res) == 0)
{
    $sql = "SELECT DISTINCT RazonSocial AS razonsocial, CONCAT(CalleNumero, ' ', Estado) AS direccion, Ciudad as ciudad FROM c_cliente WHERE Cve_Clte = (SELECT Cve_clte FROM th_pedido WHERE fol_folio = '$folio') LIMIT 1";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(22): (" . mysqli_error($conn) . $sql. ") ";
    }
}

$row_destinatario = mysqli_fetch_array($res);


$sql = "SELECT tc.*, (SELECT clave FROM c_tipocaja WHERE id_tipocaja = tc.cve_tipocaja) AS cve_tipocaja FROM th_cajamixta tc WHERE tc.fol_folio = '$folio' ORDER BY NCaja LIMIT 1";

if (!($res = mysqli_query($conn, $sql))){
    echo "Falló la preparación(23): (" . mysqli_error($conn) . $sql. ") ";
}
$row_cajamixta = mysqli_fetch_array($res);
//$folio = $row_cajamixta['fol_folio'];
//$sufijo = $row_cajamixta['Sufijo'];

$ncajas = 1;
if(isset($_GET['ncajas']))
{
   $ncajas = $_GET['ncajas'];
    $sql = "SELECT COUNT(*) AS existe FROM t_configuraciongeneral WHERE cve_conf = 'generar_cajas_vacias_packing' AND Valor = '1'";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(24): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row_packing = mysqli_fetch_array($res);
    $existe = $row_packing['existe'];

    if($existe)
    {
        for($nc = 1; $nc <= ($ncajas-1); $nc++)
        {
            $sql = "INSERT IGNORE INTO th_cajamixta (Cve_CajaMix, fol_folio, Sufijo, NCaja, abierta, embarcada, cve_tipocaja) VALUES ((SELECT (MAX(t.Cve_CajaMix)+1) FROM th_cajamixta t), '{$folio}', 1, $nc, 'N', 'S', 1)";

            if (!($res = mysqli_query($conn, $sql))){
                echo "Falló la preparación(24): (" . mysqli_error($conn) . $sql. ") ";
            }

        }
    }
}
else
{
    $sql = "SELECT COUNT(*) as ncajas FROM th_cajamixta WHERE fol_folio = '$folio'";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(24): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row_ncajas = mysqli_fetch_array($res);
    $ncajas = $row_ncajas['ncajas'];
}
//if(!$ncajas)
//echo "<span class='direccion_dato'>".$sql."</span>";

for($i = 1; $i <= $ncajas; $i++)
{
?>
<div class="report-content">
<div class="row">
<?php //echo $Cve_CajaMix; ?>
    <div class="info titulo bold">GUIA NO. <?php echo $row_cajamixta['Guia']; ?> </div>
<?php 
/*
?>
    <div class="info info2 bold">Folio:</div>
    <div class="info info2"><?php echo "ABCDE"; ?></div>
<?php 
*/
?>
</div>
<br><br>
<div class="row">


    <div class="info direccion bold">Destinatario: </div>
    <div class="info direccion_dato"><?php echo ($row_destinatario["razonsocial"]!='')?$row_destinatario["razonsocial"]:'Sin Destinatario'; ?></div>
</div>
<br>
<div class="row">
    <div class="info direccion bold">Dirección: </div>
    <div class="info direccion_dato" style="height: 1.5cm;"><?php echo ($row_destinatario["direccion"]!='')?(cortar_string ($row_destinatario["direccion"], 90)):'Sin Dirección'; ?></div>
</div>

<div class="row">
    <div class="info direccion bold">Ciudad: </div>
    <div class="info direccion_dato" style="height: 1cm;"><?php echo ($row_destinatario["ciudad"]!='')?(cortar_string ($row_destinatario["ciudad"], 90)):'Sin Ciudad'; ?></div>
</div>

<div class="row">
    <div class="info direccion bold">Pedido: </div>
    <div class="info pedido_dato"><?php echo $folio; ?></div>
</div>

<div class="row">
    <div class="info caja bold">Caja: <?php echo $i; ?> de <?php echo $ncajas; ?></div>
</div>

<br>

<div style="text-align: center; position: relative; width: 13.5cm;font-size: 20px; font-weight: bolder;">
  <?php
  $codigo_barras = $folio."-".$row_cajamixta["cve_tipocaja"];
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $codigo_barras,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 2,
    "height" => 55,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $codigo_barras; ?>
</div>
<?php 

//}
?>
<br><br><br><br>
</div>
<?php  
}
//$sql = "UPDATE th_cajamixta SET etiqueta = 'S' WHERE fol_folio = '$folio' AND Sufijo = $sufijo";
//if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";}
}//function Etiquetas

if(isset($_GET['folios']))
{
    $folios_Arr = "";
    if($_GET['todos'] == 1)
    {
        $isla = $_GET['id_embarque'];
        $ruta = $_GET['id_ruta'];
        $cliente = $_GET['cliente'];
        $almacen = $_GET['almacen'];

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $cve_almac = $row["id"];
        
        $and = "";
        if($isla != '')
        {
          $and .= "AND t_ubicacionembarque.ID_Embarque = '{$isla}' ";
        }
      
        $sql_venta_preventa = ""; $sql_left_join_ruta = ""; $sql_sin_ruta = "";
        if($ruta != '')
        {
            $sql = "SELECT venta_preventa FROM t_ruta WHERE ID_Ruta = '{$ruta}'";
            $res = mysqli_query($conn, $sql);
            $row = mysqli_fetch_array($res);
            $venta_preventa = $row["venta_preventa"];

            if($venta_preventa == 2)
            {
                $sql_left_join_ruta = " LEFT JOIN rel_RutasEntregas re ON re.id_ruta_entrega = IFNULL(t_ruta.ID_Ruta, rclave.ID_Ruta)  ";
                $sql_venta_preventa = " OR t_ruta.ID_Ruta IN (SELECT id_ruta_entrega FROM rel_RutasEntregas) ";
                $and .= " AND (IFNULL(th_pedido.ruta, rclave.ID_Ruta) = re.id_ruta_venta_preventa AND re.id_ruta_entrega = '$ruta') ";
            }
            else
                $and .= "AND (t_ruta.ID_Ruta = '{$ruta}' OR th_pedido.cve_ubicacion = '{$ruta}' OR th_pedido.ruta = '{$ruta}' OR th_pedido.cve_ubicacion IN (SELECT cve_ruta FROM t_ruta WHERE ID_Ruta = '{$ruta}') {$sql_venta_preventa})";
          
        }
        else
            $sql_sin_ruta = " AND (IFNULL(th_pedido.ruta, '') = '' AND IFNULL(th_pedido.cve_ubicacion, '') = '') ";

        if($cliente != '')
        {
          $and .= "AND (th_pedido.Cve_clte LIKE '%{$cliente}%' OR c_cliente.RazonSocial LIKE '%{$cliente}%') ";
        }
/*     
        if($colonia != '')
        {
          $and .= "AND c_destinatarios.colonia LIKE '%{$colonia}%' ";
        }

        if($cpostal != '')
        {
          $and .= "AND c_destinatarios.postal LIKE '%{$cpostal}%' ";
        }
*/
        $sql = "
          SELECT DISTINCT
            GROUP_CONCAT(DISTINCT ths.fol_folio SEPARATOR ',') AS folios
          FROM th_subpedido ths
          LEFT JOIN th_pedido ON th_pedido.Fol_folio = ths.fol_folio
          LEFT JOIN Rel_PedidoDest ON Rel_PedidoDest.Fol_Folio = ths.fol_folio 
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Rel_PedidoDest.Id_Destinatario
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
          LEFT JOIN t_clientexruta tc ON tc.clave_cliente = c_destinatarios.id_destinatario
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(th_pedido.ruta, tc.clave_ruta) OR IFNULL(t_ruta.cve_ruta, '') = IFNULL(th_pedido.cve_ubicacion, '') {$sql_venta_preventa}
          LEFT JOIN t_ruta rclave ON IFNULL(rclave.cve_ruta, '') = IFNULL(th_pedido.cve_ubicacion, '') 
           {$sql_left_join_ruta} 
          LEFT JOIN rel_uembarquepedido ON rel_uembarquepedido.fol_folio = ths.fol_folio 
          LEFT JOIN t_ubicacionembarque ON t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion

          WHERE 1 AND  t_ubicacionembarque.AreaStagging = 'N'
              {$and}
              {$sql_sin_ruta}
              AND rel_uembarquepedido.cve_almac = '$cve_almac' 
              AND (SELECT GROUP_CONCAT(DISTINCT status SEPARATOR '') FROM th_subpedido WHERE Fol_folio = ths.fol_folio) = 'C' 
              AND (ths.fol_folio NOT IN (SELECT Fol_folio FROM td_ordenembarque) OR (SELECT GROUP_CONCAT(DISTINCT Ban_Embarcado SEPARATOR '') AS Ban_Embarcado FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = ths.fol_folio)) != 'S' OR (SELECT GROUP_CONCAT(DISTINCT Ban_Embarcado SEPARATOR '') AS Ban_Embarcado FROM t_tarima WHERE Fol_Folio = ths.fol_folio) != 'S')
          #GROUP BY ths.fol_folio
          #ORDER BY ths.Fec_Entrada DESC
            ";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
        }

        $row_folios = mysqli_fetch_array($res);
        $folios_Arr = explode(",", $row_folios['folios']);
    }
    else 
        $folios_Arr = explode(",", $_GET['folios']);

    for($i = 0; $i < count($folios_Arr); $i++)
    {
        $folio = $folios_Arr[$i];

        $sql = "SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = '{$folio}'";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
        }

        if(mysqli_num_rows($res) > 0)
        {
            while($row = mysqli_fetch_array($res))
            {
                Etiquetas($row['Fol_PedidoCon']);
            }
        }
        else 
            Etiquetas($folio);

        //Etiquetas($folio);
    }
}
else
{
    $folio = $_GET['folio'];
//    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//    $sql = "SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = '{$folio}'";

//    if (!($res = mysqli_query($conn, $sql))) {
//        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
//    }

//    if(mysqli_num_rows($res) > 0)
//    {
//        while($row = mysqli_fetch_array($res))
//        {
//            Etiquetas($row['Fol_PedidoCon']);
//        }
//    }
//    else 
        Etiquetas($folio);
}

?>


</body>
</html>

