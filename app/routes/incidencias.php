<?php
/**
 * clientes
 **/

$app->map('/incidencias', function() use ($app) {

    $app->render( 'page/incidencias/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/incidencias/lists', function() use ($app) {

    $app->render( 'page/incidencias/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/incidencias/pending', function() use ($app) {

    $app->render( 'page/incidencias/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/incidencias/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/incidencias/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/incidencias/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


$app->post('/incidencias/pdf', function() use ($app) {
    $sql = "SELECT
            i.ID_Incidencia AS numero,
            a.nombre AS almacen,
            c.Cve_Clte AS clave,
            p.Nombre AS proveedor,
            c.RazonSocial AS razon_social,
            i.Fol_folio AS folio,
            (
                CASE
                        WHEN i.tipo_reporte = 'P' THEN 'Petición'
                        WHEN i.tipo_reporte = 'Q' THEN 'Queja'
                        WHEN i.tipo_reporte = 'R' THEN 'Reclamo'
                        WHEN i.tipo_reporte = 'S' THEN 'Sugerencia'
                END
            ) AS tipo_reporte,
            DATE_FORMAT(i.Fecha, '%Y-%m-%d') AS fecha_inicio,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.reportador) AS usuario_registro,
            DATE_FORMAT(i.Fecha_accion, '%Y-%m-%d') AS fecha_fin,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.responsable_recibo) AS usuario_cierre,
            (
                CASE
                      WHEN i.status = 'A' THEN 'Abierto'
                      WHEN i.status = 'C' THEN 'Cerrado'
                END
            ) AS status
    FROM th_incidencia i
    LEFT JOIN c_almacenp a ON a.clave = i.centro_distribucion
    LEFT JOIN c_cliente c ON c.Cve_Clte = i.cliente
    LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
    WHERE i.Activo = 1 ";
    mysqli_set_charset(\db2(), 'utf8');
    $query = mysqli_query(\db2(), $sql);
    ?>
        <table style="width: 100%; border-collapse: collapse; border-spacing: 0">
            <thead>
                <tr>
                    <th style="border: 1px solid #ccc">Incidencia Nº</th>
                    <th style="border: 1px solid #ccc">Almacén</th>
                    <th style="border: 1px solid #ccc">Clave</th>
                    <th style="border: 1px solid #ccc">Proveedor</th>
                    <th style="border: 1px solid #ccc">Razón Social</th>
                    <th style="border: 1px solid #ccc">Folio Pedido/Factura</th>
                    <th style="border: 1px solid #ccc">Tipo Reporte</th>
                    <th style="border: 1px solid #ccc">Fecha/Hora Inicio</th>
                    <th style="border: 1px solid #ccc">Usuario Registro</th>
                    <th style="border: 1px solid #ccc">Fecha/Hora Fin</th>
                    <th style="border: 1px solid #ccc">Usuario Cierre</th>
                    <th style="border: 1px solid #ccc">Status</th>
                </tr>
            </thead>
    <?php
    $content = ob_get_clean();
    if($query->num_rows > 0){
        $body = '<tbody>';
        while ($res = mysqli_fetch_array($query)){
            extract($res);
            $body .= "<tr>
                <td style='border: 1px solid #ccc'>$numero</td>
                <td style='border: 1px solid #ccc'>$almacen</td>
                <td style='border: 1px solid #ccc'>$clave</td>
                <td style='border: 1px solid #ccc'>$proveedor</td>
                <td style='border: 1px solid #ccc'>$razon_social</td>
                <td style='border: 1px solid #ccc'>$folio</td>
                <td style='border: 1px solid #ccc'>$tipo_reporte</td>
                <td style='border: 1px solid #ccc'>$fecha_inicio</td>
                <td style='border: 1px solid #ccc'>$usuario_registro</td>
                <td style='border: 1px solid #ccc'>$fecha_fin</td>
                <td style='border: 1px solid #ccc'>$usuario_cierre</td>
                <td style='border: 1px solid #ccc'>$status</td>
            </tr>";
        }
        $body .= '</tbody>';
    }
    $content .= $body;
    $content .= '</table>';
    $pdf = new \ReportePDF\PDF($_POST['cia'], $_POST['title'], 'L');
    $pdf->setContent($content);
    $pdf->stream();
    $app->stop();
});

$app->post('/incidencias/excel', function() use ($app) {
    include dirname(__DIR__).'/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = $_POST['title'].".xlsx";
    $header = array(
        'Incidencia Nº'   => 'string',
        'Almacén'       => 'string',
        'Clave'             => 'string',
        'Proveedor'            => 'string',
        'Razón Social'            => 'string',
        'Folio Pedido/Factura'            => 'string',
        'Tipo Reporte'            => 'string',
        'Fecha/Hora Inicio'            => 'string',
        'Usuario Registro'            => 'string',
        'Fecha/Hora Fin'            => 'string',
        'Usuario Cierre'            => 'string',
        'Status'            => 'string'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );
    $sql = "SELECT
            i.ID_Incidencia AS numero,
            a.nombre AS almacen,
            c.Cve_Clte AS clave,
            p.Nombre AS proveedor,
            c.RazonSocial AS razon_social,
            i.Fol_folio AS folio,
            (
                CASE
                        WHEN i.tipo_reporte = 'P' THEN 'Petición'
                        WHEN i.tipo_reporte = 'Q' THEN 'Queja'
                        WHEN i.tipo_reporte = 'R' THEN 'Reclamo'
                        WHEN i.tipo_reporte = 'S' THEN 'Sugerencia'
                END
            ) AS tipo_reporte,
            DATE_FORMAT(i.Fecha, '%Y-%m-%d') AS fecha_inicio,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.reportador) AS usuario_registro,
            DATE_FORMAT(i.Fecha_accion, '%Y-%m-%d') AS fecha_fin,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.responsable_recibo) AS usuario_cierre,
            (
                CASE
                      WHEN i.status = 'A' THEN 'Abierto'
                      WHEN i.status = 'C' THEN 'Cerrado'
                END
            ) AS status
    FROM th_incidencia i
    LEFT JOIN c_almacenp a ON a.clave = i.centro_distribucion
    LEFT JOIN c_cliente c ON c.Cve_Clte = i.cliente
    LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
    WHERE i.Activo = 1 ";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        while ($res = mysqli_fetch_array($query)){
            extract($res);
            $row = array(
                $numero,
                $almacen,
                $clave,
                $proveedor,
                $razon_social,
                $folio,
                $tipo_reporte,
                $fecha_inicio,
                $usuario_registro,
                $fecha_fin,
                $usuario_cierre,
                $status
            );
            $excel->writeSheetRow('Sheet1', $row );
        }
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
});

$app->post('/incidencias/singlepdf', function() use ($app) {
    $id = $_POST['id'];
    $sql = "SELECT
                    Fol_folio AS folio,
                    (SELECT nombre FROM c_almacenp WHERE clave = centro_distribucion) AS centro,
                    (SELECT RazonSocial FROM c_cliente WHERE Cve_Clte = cliente) AS cliente,
                    ID_Incidencia AS numero,
                    (
                        CASE
                                WHEN tipo_reporte = 'P' THEN 'Petición'
                                WHEN tipo_reporte = 'Q' THEN 'Queja'
                                WHEN tipo_reporte = 'R' THEN 'Reclamo'
                                WHEN tipo_reporte = 'S' THEN 'Sugerencia'
                        END
                    ) AS tipo_reporte,
                    reportador,
                    cargo_reportador,
                    DATE_FORMAT(Fecha, '%d-%m-%Y') AS fecha_reporte,
                    Descripcion  AS descripcion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_recibo) AS responsable_recibo,
                    responsable_caso,
                    plan_accion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_plan) AS responsable_plan,
                    DATE_FORMAT(Fecha_accion, '%d-%m-%Y') AS fecha_plan,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_verificacion) AS responsable_verificacion,
                    (
                        CASE
                                WHEN status = 'A' THEN 'Abierto'
                                WHEN status = 'C' THEN 'Cerrado'
                        END
                    ) AS status
            FROM th_incidencia
            WHERE ID_Incidencia = {$id} AND Activo = 1;";
    mysqli_set_charset(\db2(), 'utf8');
    $query = mysqli_query(\db2(), $sql);
    ob_clean();
    ob_start();
    ?>

    <?php
    $content = ob_get_clean();
    if($query->num_rows > 0){
        $res = mysqli_fetch_array($query);
        ob_start();
        ?>
        <table width="780" cellspacing="1" cellpadding="10" border="0" bgcolor="#020202" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
   <b>CENTRO DISTRIBUCION</b><?php echo $res['centro']?> &nbsp;&nbsp;&nbsp;&nbsp;<b>CLIENTE</b>  <?php echo $res['cliente']?>  &nbsp;&nbsp;&nbsp;&nbsp;<b>NRO</b>  <?php echo $res['folio']?>  
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
   <b>TIPO DE REPORTE:</b> 
    <?php if ($res['tipo_reporte'] == "Petición"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;PETICION <input type="checkbox" id="cbox1" value="primer_checkbox" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;PETICION <input type="checkbox" id="cbox1" value="primer_checkbox">
    <?php  } 
    if ($res['tipo_reporte'] == "Queja"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;QUEJA <input type="checkbox" id="cbox2" value="second_checkbox" checked="true"> <?php } 
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;QUEJA <input type="checkbox" id="cbox2" value="second_checkbox"> <?php } 
    if ($res['tipo_reporte'] == "Reclamo"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;RECLAMO <input type="checkbox" id="cbox3" value="tercer_checkbox" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;RECLAMO <input type="checkbox" id="cbox3" value="tercer_checkbox"> <?php }
    if ($res['tipo_reporte'] == "Sugerencia"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;SUGERENCIA <input type="checkbox" id="cbox4" value="cuarto_checkbox" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;SUGERENCIA <input type="checkbox" id="cbox4" value="cuarto_checkbox"> <?php } ?>
   </font> 
  
   </td> 
</tr>
</table>
<br>
<table width="750" height="10" cellspacing="1" cellpadding="7" border="0" bgcolor="#020202" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
<tr align="center">
   <td width="360" bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>NOMBRE DE QUIEN REPORTA</b>
   </font> 
   </td> 
   <td width="200" bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>CARGO</b>
   </font> 
   </td>
   <td bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>FECHA</b>
   </font> 
   </td>
</tr> 
<tr align="center" height="30"> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
    <?php echo $res['reportador']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
    <?php echo $res['cargo_reportador']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
   <?php echo $res['fecha_reporte']?>
   </font> 
   </td>
 
</tr>
</table>
<br>
 <table width="750" height="10" cellspacing="1" cellpadding="7" border="0" bgcolor="#020202" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
<tr>
   <td bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>1. DESCRIPCION DE LA PQRS: (Hechos y datos concretos, claros, precisos y verificables</b>
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['descripcion']?>
   </font> 
   </td> 
</tr>
</table>
<br>
<table width="750" height="10" cellspacing="1" cellpadding="7" border="0" bgcolor="#020202"> 
<tr>
   <td bgcolor="#C4C1C1" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>2. PARA DILIGENCIAMIENTO DE LA ATENCION DE LA PQRS</b>
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
   <font size=2 face="arial, verdana, helvetica"> 
   Responsable de recibir la PQRS: <h3><?php echo $res['responsable_recibo']?></h3>
   </font> 
   </td> 
</tr>
</table>
 <table width="750" height="10" border=0 cellpadding="7" cellspacing="1" style="border:0.5px solid black;" bordercolor=#020202> 
<tr>
   <td bgcolor="#C4C1C1" style="border:0.5px solid black;border-right:0px; border-top: 0px; border-width: 1px 0"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>3. VERIFICACION DE LA PQRS</b>
   </font> 
   </td> 
</tr> 
<tr > 
   <td bgcolor="#FEFDFD"> 
   <font size=2 face="arial, verdana, helvetica"> 
   Responsable del caso reportado: 
   </font> 
   </td> 
</tr>
<tr > 
   <td bgcolor="#FEFDFD"> 
   <font size=2 face="arial, verdana, helvetica"> 
    <?php if ($res['responsable_caso'] == "ASL"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;ASL <input type="radio" name="group1" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;ASL <input type="radio" name="group1">
    <?php  }
    if ($res['responsable_caso'] == "Transportador"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;TRANSPORTADOR <input type="radio" name="group2" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;TRANSPORTADOR <input type="radio" name="group2">
    <?php  }
    if ($res['responsable_caso'] == "Cliente"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;CLIENTE <input type="radio" name="group3" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;CLIENTE <input type="radio" name="group3">
    <?php  }
    if ($res['responsable_caso'] != "Cliente" && $res['responsable_caso'] != "Transportador" && $res['responsable_caso'] != "ASL"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;OTRO <input type="radio" name="group4" checked="true"> ¿Cual?:<?php echo $res['responsable_caso']?> <?php } 
     else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;OTRO <input type="radio" name="group4"> &nbsp;&nbsp;¿Cual?: ______________________
    <?php  }?>
    </font>
   </td> 
</tr>
</table>
<table width="750" height="10" border=0 cellpadding="7" bgcolor="#C4C1C1" style="border:1px solid black; "> 
<tr>
   <td bgcolor="#C4C1C1" style="border:1px solid black; border-right:0px; border-top: 0px; border-width: 1px 0;" COLSPAN="3"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>4. PLANTEAMIENTO DE ACCIONES</b>
   </font> 
   </td> 
</tr> 
<tr align="center" height="5"> 
   <td bgcolor="#FEFDFD"> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>PLAN DE ACCION</b>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>RESPONSABLE</b>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>FECHA</b>
   </font> 
   </td> 
</tr> 
<tr align="center" height="25"> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['plan_accion']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['responsable_plan']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['fecha_plan']?>
   </font> 
   </td> 
</tr>
</table>
<table width="750" height="10" border=0 cellpadding="7" style="border:0.5px solid black;"cellspacing="1" bordercolor=#000000> 
<tr>
   <td bgcolor="#C4C1C1" style="border:1px solid black; border-right:0px; border-top: 0px; border-width: 1px 0;" COLSPAN="2"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>5. VERIFICACION DE ACCIONES TOMADAS: SOLUCION Y SATISFACCION</b>
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font size=2 face="arial, verdana, helvetica"> 
   RESPONSABLE:<?php echo $res['responsable_verificacion']?> 
   <br>
   <p>
   <font size=2 face="arial, verdana, helvetica"> 
   FIRMA: __________________________
   </font> 
    </p>
</br>
   </font> 
   </td>
   <td style="border:1px solid black; border-right:0px; border-top: 0px; border-width: 1px; "> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>Cierre de la PQRS</b>
   <div row>
   <?php 
    if ($res['status'] == "Abierto"){ ?>
    <h2>SI</h2> <input type="checkbox" id="cbox5"><div row><h2>NO</h2> <input type="checkbox" id="cbox5" checked="true"> <?php }
    else{ ?>
    <h2>SI</h2> <input type="checkbox" id="cbox5" checked="true"><div row><h2>NO</h2> <input type="checkbox" id="cbox5">  <?php } ?>
   </font> 
   </td>  
</tr>

</table>
        <?php
        $body = ob_get_clean();
        ob_end_flush();
    }
    $content .= $body;
    $content .= '</table>';
    $pdf = new \ReportePDF\PDF($_POST['cia'], $_POST['title']." Nº".$res['numero']);
    $pdf->setContent($content);
    ob_clean();
    $pdf->stream();
    $app->stop();
    exit;
});

$app->post('/incidencias/singleexcel', function() use ($app) {
    include dirname(__DIR__).'/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = $_POST['title'].".xlsx";
    $excel = new XLSXWriter();
    $header = array(
        'Nº'                                => 'string',
        'Centro de Distribución'            => 'string',
        'Cliente'                           => 'string',
        'Tipo de Reporte'                   => 'string',
        'Nombre (Quien Reporta)'            => 'string',
        'Cargo'                             => 'string',
        'Fecha de Reporte'                  => 'string',
        'Descripcion de la PQRS'            => 'string',
        'Responsable de recibir la PQRS'    => 'string',
        'Responsable del caso reportado'    => 'string',
        'Plan de Acción'                    => 'string',
        'Responsable del Plan de Acción'    => 'string',
        'Fecha de Acción'                   => 'string',
        'Responsable de Verificación'       => 'string',
        'Status de la PQRS'                 => 'string',
        'Número de Folio o Factura'         => 'string',
    );
    $excel->writeSheetHeader('Sheet1', $header);
    $id = $_POST['id'];
    $sql = "SELECT
                    Fol_folio AS folio,
                    (SELECT nombre FROM c_almacenp WHERE clave = centro_distribucion) AS centro,
                    (SELECT RazonSocial FROM c_cliente WHERE Cve_Clte = cliente) AS cliente,
                    ID_Incidencia AS numero,
                    (
                        CASE
                                WHEN tipo_reporte = 'P' THEN 'Petición'
                                WHEN tipo_reporte = 'Q' THEN 'Queja'
                                WHEN tipo_reporte = 'R' THEN 'Reclamo'
                                WHEN tipo_reporte = 'S' THEN 'Sugerencia'
                        END
                    ) AS tipo_reporte,
                    reportador,
                    cargo_reportador,
                    DATE_FORMAT(Fecha, '%d-%m-%Y') AS fecha_reporte,
                    Descripcion  AS descripcion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_recibo) AS responsable_recibo,
                    responsable_caso,
                    plan_accion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_plan) AS responsable_plan,
                    DATE_FORMAT(Fecha_accion, '%d-%m-%Y') AS fecha_plan,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_verificacion) AS responsable_verificacion,
                    (
                        CASE
                                WHEN status = 'A' THEN 'Abierto'
                                WHEN status = 'C' THEN 'Cerrado'
                        END
                    ) AS status
            FROM th_incidencia
            WHERE ID_Incidencia = {$id} AND Activo = 1;";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        $res = mysqli_fetch_array($query);
        $row = array(
            $res['numero'],
            $res['centro'],
            $res['cliente'],
            $res['tipo_reporte'],
            $res['reportador'],
            $res['cargo_reportador'],
            $res['fecha_reporte'],
            $res['descripcion'],
            $res['responsable_recibo'],
            $res['responsable_caso'],
            $res['plan_accion'],
            $res['responsable_plan'],
            $res['fecha_plan'],
            $res['responsable_verificacion'],
            $res['status'],
            $res['folio'],
        );
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
});
