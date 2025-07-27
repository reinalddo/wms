<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';
/*
    $category_amount = array(
        array("category"=>"Books","sale"=>32000,"cost"=>20000,"profit"=>12000),
        array("category"=>"Accessories","sale"=>43000,"cost"=>36000,"profit"=>7000),
        array("category"=>"Phones","sale"=>54000,"cost"=>39000,"profit"=>15000),
        array("category"=>"Movies","sale"=>23000,"cost"=>18000,"profit"=>5000),
        array("category"=>"Others","sale"=>12000,"cost"=>6000,"profit"=>6000),
    );

    $category_sale_month = array(
        array("category"=>"Books","January"=>32000,"February"=>20000,"March"=>12000),
        array("category"=>"Accessories","January"=>43000,"February"=>36000,"March"=>7000),
        array("category"=>"Phones","January"=>54000,"February"=>39000,"March"=>15000),
        array("category"=>"Others","January"=>12000,"February"=>6000,"March"=>6000),
    );
    */
/*
    $id_venta = $_GET['id_venta'];
    $cia = $_GET['cve_cia'];
    $folio = $_GET['folio'];
    $cliente = $_GET['cliente'];
    $responsable = $_GET['responsable'];
    $operacion = $_GET['operacion'];
    $dia_operativo = $_GET['dia_operativo'];
    $ruta = $_GET['ruta'];
    $fecha = $_GET['fecha'];
*/

    $almacen   = $_GET['almacen'];
    $ruta      = $_GET['ruta'];
    $diao      = $_GET['diao'];
    $operacion = $_GET['operacion'];
    $criterio  = $_GET['criterio'];
    $cve_cia   = $_GET['cve_cia'];
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Control de Envases</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php
/*
    <div style="margin-bottom:50px;">
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT des_articulo  FROM c_articulo WHERE cve_articulo = '5000LC';";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $des_articulo = mysqli_fetch_array($res)['des_articulo'];
    echo $des_articulo;
*/
    /*
    ColumnChart::create(array(
        "title"=>"Sale Report",
        "dataSource"=>$category_amount,
        "columns"=>array(
            "category",
            "sale"=>array("label"=>"Sale","type"=>"number","prefix"=>"$"),
            "cost"=>array("label"=>"Cost","type"=>"number","prefix"=>"$"),
            "profit"=>array("label"=>"Profit","type"=>"number","prefix"=>"$"),
        )
    ));
    </div>
    */
    ?>
    <div style="margin:50px;">
    <?
    /*
    Table::create(array(
        "dataSource"=>$category_amount
    ));
    */
    ?>
    </div>

  <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo, DATE_FORMAT(CURDATE(), '%d-%m-%Y') AS Fecha_actual, 
    (SELECT v.Nombre FROM t_vendedores v LEFT JOIN Rel_Ruta_Agentes a ON a.cve_vendedor = v.Id_Vendedor LEFT JOIN t_ruta r ON r.ID_Ruta = a.cve_ruta WHERE r.cve_ruta = '$ruta' LIMIT 1) as Agente
     FROM c_compania WHERE cve_cia = ".$cve_cia;
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }
    $row_logo = mysqli_fetch_array($res);
    $logo = $row_logo['logo'];
    $Fecha_actual = $row_logo['Fecha_actual'];
    $Agente = $row_logo['Agente'];

    $SQLRuta = "";
    if($ruta)
        $SQLRuta = " AND e.RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '$ruta') ";

    $SQLDiaO = "";
    if($diao != '')
        $SQLDiaO = " AND e.DiaO = $diao ";

   $SQLOperacion = "";
   if($tipo_dev_envase != '')
        $SQLOperacion = " AND e.Tipo = '$tipo_dev_envase' ";

    $SQLCriterio = "";
    if($criterio != '')
    {
       $SQLCriterio = " AND (ru.cve_ruta LIKE '%$criterio%' OR v.Documento LIKE '%$criterio%' OR d.Cve_Clte LIKE '%$criterio%' OR d.razonsocial LIKE '%$criterio%' OR e.Envase LIKE '%$criterio%') ";
       //$SQLRuta = "";
       //$SQLDiaO = "";
       //$SQLOperacion = "";
    }

    $consultar = 1;

    if($SQLRuta == "" &&  $SQLDiaO == "" &&  $SQLOperacion == "" &&  $SQLCriterio == "") $consultar = 0;

    $sql = "SELECT v.Id, DATE_FORMAT(v.Fecha, '%d-%m-%Y') AS Fecha, ru.cve_ruta, e.DiaO , v.Documento, d.Cve_Clte, d.razonsocial, 
                   e.Envase, a.des_articulo AS des_envase, e.Tipo, e.Cantidad, e.Devuelto, a.Tipo_Envase AS tipo_env_clave, 
                   IF(a.Tipo_Envase = 'G', 'Garrafón', IF(a.Tipo_Envase = 'P','Plástico', IF(a.Tipo_Envase = 'C', 'Cristal', ''))) AS Tipo_Envase 
            FROM DevEnvases e 
            INNER JOIN Venta v ON v.Documento=e.Docto AND v.RutaId=e.RutaId AND v.DiaO=e.DiaO
            INNER JOIN c_destinatarios d ON e.CodCli=d.id_destinatario
            INNER JOIN t_ruta ru ON ru.ID_Ruta = v.RutaId 
            LEFT JOIN c_articulo a ON a.cve_articulo = e.Envase
            WHERE {$consultar} {$SQLCriterio} {$SQLRuta} {$SQLOperacion} {$SQLDiaO} 
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ". $sql;
    }

/*
    if(mysqli_num_rows($res))
    {
        $row = mysqli_fetch_array($res);
        extract($row);
    }
*/


?>

    <table border="0">
      <tr>
        <td style="width: 200px;"></td>
        <td style="width: 200px;"><img src="<?php echo ''.$logo; ?>" alt="" height="200"></td>
        <td align="center" style="font-size: 14px;width: 950px; text-align: center; vertical-align: middle;">
        <h1><span lang="th">Control de Envases</span></h1>
        <br><br>

        <table border="0">
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Ruta:</b></span></td><td><?php echo $ruta; ?></td><td></td><td></td></tr>
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Agente de Ventas:</b></span></td><td><?php echo $Agente; ?></td><td></td><td></td></tr>
        </table>
    </td>
    <td style="font-size: 18px;top: 50px;right: 250px;position: absolute;"><span lang="th"><b>Fecha:</b></span></td>
    <td style="font-size: 18px;top: 50px;right: 130px;position: absolute;"><?php echo $Fecha_actual; ?></td>
      </tr>
    </table>

<div style="padding: 10px 100px;">
<table class="table table-striped">
  <thead>
    <tr>
        <th scope="col">Cliente</th>
        <th scope="col">Nombre Comercial</th>
        <th scope="col">Folio</th>
        <th scope="col">Fecha</th>
        <th scope="col">DiaO</th>
        <th scope="col">Envase</th>
        <th scope="col">Descripción</th>
        <th scope="col">Venta</th>
        <th scope="col">Promoción</th>
        <th scope="col">Devolución</th>
        <th scope="col">Comodato</th>
    </tr>
  </thead>
  <tbody>
<?php 
while($row = mysqli_fetch_array($res))
{
    extract($row);
?>

    <tr>
      <td align="left"><?php echo $Cve_Clte; ?></td>
      <td align="left"><?php echo $razonsocial; ?></td>
      <td align="left"><?php echo $Documento; ?></td>
      <td align="center"><?php echo $Fecha; ?></td>
      <td align="right"><?php echo $DiaO; ?></td>
      <td align="left"><?php echo $Envase; ?></td>
      <td align="left"><?php echo $des_envase; ?></td>
      <td align="right"><?php if($Tipo == 'Venta')echo $Cantidad; else echo 0; ?></td>
      <td align="right"><?php if($Tipo == 'Promoción')echo $Cantidad; else echo 0; ?></td>
      <td align="right"><?php if($Devuelto > 0) echo $Devuelto; else echo 0; ?></td>
      <td align="right"><?php if($Tipo == 'Comodato')echo $Cantidad; else echo 0; ?></td>
    </tr>
<?php 
}
?>
  </tbody>
</table>
</div>

</div>
</body>
</html>

