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
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>QA | Control de Calidad | Cuarentena</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <div class="text-center">
      <br><br><br><br><br><br>
        <h1><span lang="th">QA | Control de Calidad | Cuarentena</span></h1>
<!--        
        <p class="lead">
            AssitPro WMS <span lang="th">PDF</span>
        </p>
-->
    </div>
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

<div style="padding: 10px 100px;">
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Folio</th>
      <th scope="col">clave</th>
      <th scope="col">Descripción</th>
      <th scope="col" width="100" align="center">BL</th>
      <th scope="col">Pallet|Contenedor</th>
      <th scope="col">Status</th>
      <th scope="col" width="150">Fecha QA</th>
      <th scope="col">Motivo</th>
      <th scope="col">Fecha Liberación</th>
      <th scope="col">Tiempo en QA</th>
      <th scope="col">Motivo</th>
    </tr>
  </thead>
  <tbody>
  <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $split = "";

    $almacen = $_GET['almacen'];
    $cve_articulo = $_GET['articulo'];
    $status = $_GET['status'];
    $buscar_bl =  $_GET['bl'];

    if(isset($status) && $status != "")
    {
        $split .= " AND IFNULL(t_movcuarentena.Id_MotivoLib,0) ".(($status == "Abierto")?"=":">")." 0 ";
    }

    if(isset($buscar_bl) && $buscar_bl != "")
    {
        $split .= " AND c_ubicacion.codigoCSD like '%".$buscar_bl."%' ";  
    }

    if(isset($cve_articulo) && $cve_articulo != "")
    {
        $split .=" AND t_movcuarentena.Cve_Articulo like '%".$cve_articulo."%' ";
    }

    if(isset($almacen) && $almacen != "")
    {
        $split .=" AND c_almacenp.clave = '".$almacen."' ";
    }
  

    $sql = "
        SELECT
            t_movcuarentena.Fol_Folio AS folio,
            t_movcuarentena.Cve_Articulo AS clave,
            c_articulo.des_articulo AS descripcion,
            c_ubicacion.CodigoCSD AS bl,
            c_charolas.clave_contenedor AS tipo,
            IF(IFNULL(t_movcuarentena.Id_MotivoLib,0) > 0 ,'Cerrado','Abierto') AS estatus,
            date_format(t_movcuarentena.Fec_Ingreso,'%d-%m-%Y %k:%i:%s') AS fechaQA,
            t_movcuarentena.Fec_Ingreso AS fechaOrden,
            me.Des_Motivo AS motivo,
            date_format(t_movcuarentena.Fec_Libera,'%d-%m-%Y %k:%i:%s') AS fecha,
            SEC_TO_TIME(TIMESTAMPDIFF(SECOND,t_movcuarentena.Fec_Ingreso,IFNULL(t_movcuarentena.Fec_Libera,now()))) AS tiempo,
            ms.Des_Motivo AS motivo2,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = t_movcuarentena.Usuario_Lib) AS usuario,
            c_almacenp.nombre AS almacen,
            c_almacen.des_almac AS almacenaje
        FROM  t_movcuarentena
            INNER JOIN c_articulo on c_articulo.cve_articulo = t_movcuarentena.Cve_Articulo
            LEFT JOIN c_ubicacion on c_ubicacion.idy_ubica = t_movcuarentena.Idy_Ubica
            LEFT JOIN c_charolas on c_charolas.IDContenedor = t_movcuarentena.IdContenedor
            LEFT JOIN c_motivo me on me.id = t_movcuarentena.Id_MotivoIng and me.Tipo_Cat = 'Q'
            LEFT JOIN c_motivo ms on ms.id = t_movcuarentena.Id_MotivoLib and ms.Tipo_Cat = 'S'
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            LEFT JOIN c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
        WHERE 1
            ".$split."
        ORDER BY fechaOrden DESC
    ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row = mysqli_fetch_array($res)) {

        extract($row);
  ?>
    <tr>
      <th scope="row"><?php echo $folio; ?></th>
      <td><?php echo $clave; ?></td>
      <td><?php echo utf8_decode($descripcion); ?></td>
      <td width="100" align="center"><?php echo $bl; ?></td>
      <td><?php echo $tipo; ?></td>
      <td><?php echo $estatus; ?></td>
      <td width="150"><?php echo $fechaQA; ?></td>
      <td><?php echo utf8_decode($motivo); ?></td>
      <td><?php echo $fecha; ?></td>
      <td><?php echo $tiempo; ?></td>
      <td><?php echo utf8_decode($motivo2); ?></td>
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

