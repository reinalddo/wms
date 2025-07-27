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
<title>Monitoreo de Pedidos | Entregas</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $search  = $_GET['search'];
    $status  = $_GET['status'];
    $fecha_inicio  = $_GET['fecha_inicio'];
    $fecha_fin    = $_GET['fecha_fin'];
    $cve_cliente  = $_GET['cve_cliente'];
    $cve_proveedor  = $_GET['cve_proveedor'];
    $status_entrega  = $_GET['status_entrega'];


        $queryWhere = " WHERE p.Fol_folio NOT IN (SELECT Folio_Pro FROM t_ordenprod) ";
        if($search != "")
            $queryWhere .=" AND (p.Fol_folio LIKE '%".$search."%' OR p.Cve_clte LIKE '%".$search."%' OR tdor.ID_OEmbarque LIKE '%".$search."%') ";
          //$queryWhere .=" AND (p.destinatario LIKE '%".$search."%' OR d.razonsocial LIKE '%".$search."%' OR transp.Nombre LIKE '%".$search."%' OR thor.Num_Guia LIKE '%".$search."%' OR transp.Nombre LIKE '%".$search."%' OR thor.cve_usuario LIKE '%".$search."%' OR p.Cve_clte LIKE '%".$search."%') ";



        if($fecha_inicio != '')
        {
          $date_inicio=date("Y-m-d H:i:s",strtotime($fecha_inicio));
          $queryWhere .= " AND p.Fec_Entrada >= DATE('".$date_inicio."') ";
        }
      
         if($fecha_fin != '')
        {
          $date_fin=date("Y-m-d H:i:s",strtotime($fecha_fin)); 
          $queryWhere .= " AND p.Fec_Entrada <= DATE('".$date_fin."')"; 
        }

        if($status_entrega != '')
        {
            $queryWhere .= " AND p.status = '".$status_entrega."'";
        }

        if($cve_cliente != '')
        {
            $queryWhere .= " AND p.Cve_clte = '".$cve_cliente."'";
        }

        if($cve_cliente != '')
        {
            $queryWhere .= " AND p.Cve_clte = '".$cve_cliente."'";
        }

        if($cve_proveedor != '')
        {
            $queryWhere .= " AND ct.ID_Proveedor = '".$cve_proveedor."'";
        }

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">Monitoreo de Pedidos | Entregas</span></h1>
<?php 
/*
?>
                    <p class="lead">
                        Pedido: <span lang="th"><?php echo $_GET['folio']; ?></span>
                    </p>
                    <p class="lead">
                        Fecha Pedido: <span lang="th"><?php echo $_GET['fecha_pedido']; ?></span>
                    </p>
<?php 
*/
 ?>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>

<th scope="col">Folio</th>
<th scope="col">Cliente</th>
<?php /* ?><th scope="col">Clave Destinatario</th><?php */ ?>
<?php /* ?><th scope="col">Destinatario</th><?php */ ?>
<th scope="col">Fecha Pedido</th>
<th scope="col">Fecha Compromiso</th>
<th scope="col">Fecha Entrega</th>
<th scope="col">Estado</th>
<th scope="col">Cumplimiento</th>
<th scope="col">OTIF</th>
<?php /* ?><th scope="col">Empresa</th><?php */ ?>
<?php /* ?><th scope="col">Transporte</th><?php */ ?>
<th scope="col">Folio Embarque</th>
<th scope="col">Guia Embarque</th>
<?php /* ?><th scope="col">Fecha Envío | Entrega</th><?php */ ?>
<th scope="col">Firma Recepción</th>
<th scope="col">Dias Transito</th>
<th scope="col">Dias Retraso</th>
    </tr>
  </thead>
  
  <tbody>
  <?php 

    $sql = "
            SELECT DISTINCT
                    p.Fol_folio AS PO,
                    IFNULL(p.Cve_clte, '') AS Cliente,
                    IFNULL(d.id_destinatario, '') AS Clave_Destinatario,
                    IFNULL(d.razonsocial, '') AS Razon_Social_Destinatario,
                    IFNULL(DATE_FORMAT(p.Fec_Entrada, '%d-%m-%Y'), '') AS Fecha_Pedido,
                    IFNULL(DATE_FORMAT(p.Fec_Pedido, '%d-%m-%Y'), '') AS Fecha_Compromiso,
                    IFNULL(e.DESCRIPCION, '') AS Estado,
                    IF(transp.transporte_externo = 0, IFNULL(transp.Nombre, ''), '') AS Transporte,
                    #IF(transp.transporte_externo = 0, 
                        IFNULL(tdor.ID_OEmbarque, '')
                    #, '') 
                    AS Folio_Embarque,
                    #IF(transp.transporte_externo = 0, 
                        COUNT(c.Guia)
                        #, '') 
                    AS Guia_Embarque,
                    IF(transp.transporte_externo = 0, IFNULL(GROUP_CONCAT(c.Guia SEPARATOR ',  '), ''), '') AS Guias_Embarques,
                    IF(transp.transporte_externo = 0, IFNULL(IF(thor.status = 'T', DATE_FORMAT(thor.fecha, '%d-%m-%Y'), IF(thor.status = 'F', DATE_FORMAT(tdor.fecha_envio, '%d-%m-%Y'), '')), ''), '') AS Fecha_Envio,

                    #IF(transp.transporte_externo = 0, 
                    CASE 
                    WHEN thor.status = 'T'
                    THEN IFNULL(IF(DATEDIFF(CURDATE(), thor.fecha) <= 0, 1, DATEDIFF(CURDATE(), thor.fecha)), '')
                    WHEN thor.status = 'F'
                    THEN IFNULL(IF(DATEDIFF(pen.Fecha, p.Fec_Entrega) <= 0, 1, DATEDIFF(pen.Fecha, p.Fec_Entrega)), '')
                    ELSE ''
                    END 
                    #, '') 
                    AS Dias_Transito,

                    #IF(transp.transporte_externo = 0, 
                    CASE 
                    WHEN ((thor.status = 'T') OR (p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F')
                    THEN IFNULL(IF(DATEDIFF(CURDATE(), p.Fec_Entrega) <= 0, 1, DATEDIFF(CURDATE(), p.Fec_Entrega)), '') 
                    ELSE ''
                    END
                    #, '') 
                    AS Dias_Retraso,

                    IF(thor.status = 'F' OR (thor.status = 'T' AND transp.transporte_externo = 1), 'Si', 'No') AS Cumplimiento,
                    IFNULL(IF(((thor.status = 'F' OR (thor.status = 'T' AND transp.transporte_externo = 1) ) AND DATE_FORMAT(tdor.fecha_envio, '%Y-%m-%d') <= p.Fec_Pedido), 'Si', IF((p.status != 'F' AND p.status != 'T' AND CURDATE() > p.Fec_Entrega) OR p.status = 'F' OR p.status = 'T', 'No', '')), '') AS otif,
                    IFNULL(IF(thor.status = 'F' OR transp.transporte_externo = 1, IFNULL(DATE_FORMAT(tdor.fecha_entrega, '%d-%m-%Y'), DATE_FORMAT(thor.fecha, '%d-%m-%Y')), ''), '') AS Fecha_Entrega,
                    #IFNULL(IF(thor.status = 'F', IFNULL(pen.Recibio, ''), thor.cve_usuario), '') AS cve_usuario,
                    IFNULL(pen.Recibio, '') as cve_usuario,
                    CONCAT(IFNULL(d.postal, ''), IF(IFNULL(d.postal, '')='', '', ', '), IFNULL(d.direccion, ''), IF(IFNULL(d.direccion, '')='', '', ', '), IFNULL(d.colonia, ''), IF(IFNULL(d.colonia, '')='', '', ', '), IFNULL(d.ciudad, ''), IF(IFNULL(d.ciudad, '')='', '', ', '), IFNULL(d.estado, ''), IF(IFNULL(d.estado, '')='', '', ', '), IFNULL(d.telefono, '')) AS Destinatario,
                    IFNULL(prv.Nombre, '') AS Proveedor
                FROM th_pedido p
                LEFT JOIN c_destinatarios d ON p.Cve_Clte = d.Cve_Clte
                LEFT JOIN cat_estados e ON e.ESTADO = p.status
                LEFT JOIN td_ordenembarque tdor ON tdor.Fol_folio = p.Fol_folio
                LEFT JOIN th_ordenembarque thor ON thor.ID_OEmbarque = tdor.ID_OEmbarque
                LEFT JOIN t_transporte transp ON transp.id = thor.ID_Transporte 
                LEFT JOIN t_pedentregados pen ON pen.Fol_folio = p.Fol_folio
                LEFT JOIN th_cajamixta c ON c.fol_folio = p.Fol_folio
                LEFT JOIN c_cliente ct ON p.Cve_clte = ct.Cve_Clte
                LEFT JOIN c_proveedores prv ON prv.ID_Proveedor = ct.ID_Proveedor
                {$queryWhere}
                GROUP BY PO
                ORDER BY Fecha_Pedido DESC
        ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
        <td><?php echo $PO;?></td>
        <td><?php echo $Cliente;?></td>
        <?php /* ?><td><?php echo $Clave_Destinatario;?></td><?php */ ?>
        <?php /* ?><td><?php echo $Razon_Social_Destinatario;?></td><?php */ ?>
        <td align="center"><?php echo $Fecha_Pedido;?></td>
        <td align="center"><?php echo $Fecha_Compromiso;?></td>
        <td align="center"><?php echo $Fecha_Entrega;?></td>
        <td><?php echo $Estado;?></td>
        <td><?php echo $Cumplimiento;?></td>
        <td><?php echo $otif;?></td>
        <?php /* ?><td><?php echo $Proveedor;?></td><?php */ ?>
        <?php /* ?><td><?php echo $Transporte;?></td><?php */ ?>
        <td><?php echo $Folio_Embarque;?></td>
        <td align="right"><?php echo $Guia_Embarque;?></td>
        <?php /* ?><td align="right"><?php echo $Guias_Embarques;?></td><?php */ ?>
        <?php /* ?><td align="center"><?php echo $Fecha_Envio;?></td><?php */ ?>
        <td><?php echo $cve_usuario;?></td>
        <td align="right"><?php echo $Dias_Transito;?></td>
        <td align="right"><?php echo $Dias_Retraso;?></td>
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

