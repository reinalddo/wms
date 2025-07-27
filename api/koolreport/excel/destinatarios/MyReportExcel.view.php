<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Destinatarios";
?>
<meta charset="UTF-8">
<meta name="description" content="Free Web tutorials">
<meta name="keywords" content="Excel,HTML,CSS,XML,JavaScript">
<meta name="creator" content="John Doe">
<meta name="subject" content="subject1">
<meta name="title" content="title1">
<meta name="category" content="category1">

<div sheet-name="<?php echo $sheet1; ?>">



    <?php
    $styleArray = [
        'font' => [
            'name' => 'Calibri', //'Verdana', 'Arial'
            'size' => 14,
            'bold' => true
        ]
    ];
?>

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Secuencia</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Cliente</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Razón Comercial</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Destinatario</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Destinatario</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Dirección</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Colonia</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Código Postal</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ciudad | Departamento</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Alcaldía | Municipio</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Latitud</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Longitud</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Contacto</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Teléfono</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Agente</div>

<?php 

//**************************************************************************************************
//**************************************************************************************************
        $codigo   = $_GET['codigo'];
        $rutas    = $_GET['rutas'];
        $dias     = $_GET['dias'];
        $agentes  = $_GET['agentes'];
        $criterio = $_GET['criterio'];
        $almacen  = $_GET['almacen'];

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
$charset = mysqli_fetch_array($res_charset)['charset'];
mysqli_set_charset($conn , $charset);


        $ands ="";
        $ands2 ="";
        $cp_and = "";
        $cp_and2 = "";
        $and_agente = ""; 
        $and_vendedor = "";
        $and_vendedor_relday = "";

        if (!empty($criterio)){
           $criterio = trim($criterio);

           $ands .= " AND (d.RazonSocial LIKE '%{$criterio}%' OR d.Cve_Clte LIKE '%{$criterio}%' OR c.Cve_Clte LIKE '%{$criterio}%' OR d.clave_destinatario LIKE '%{$criterio}%' OR d.id_destinatario LIKE '%{$criterio}%' OR d.contacto LIKE '%{$criterio}%' OR d.direccion LIKE '%{$criterio}%' OR c.CalleNumero LIKE '%{$criterio}%') ";

           $ands2 .= " AND (des.RazonSocial LIKE '%{$criterio}%' OR des.Cve_Clte LIKE '%{$criterio}%' OR c.Cve_Clte LIKE '%{$criterio}%' OR des.clave_destinatario LIKE '%{$criterio}%' OR des.id_destinatario LIKE '%{$criterio}%' OR des.contacto LIKE '%{$criterio}%' OR des.direccion LIKE '%{$criterio}%') ";
        }

        if (!empty($codigo)) {
            $cp_and .= " AND d.postal = '{$codigo}' ";
            $cp_and2 .= " AND des.postal = '{$codigo}' ";
        }

        if (!empty($rutas)) {
            $ands .= " AND t_ruta.cve_ruta = '{$rutas}' ";
            //$ands2 .= " AND 0 ";
        }

        if (!empty($agentes)) {
            $and_agente = " AND d.id_destinatario IN (SELECT Id_destinatario FROM RelDayCli WHERE Cve_Vendedor = '{$agentes}') ";
            $and_vendedor = "AND ra.cve_vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";
            $and_vendedor_relday = "AND RelDayCli.Cve_Vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";
        }

        $order_by = "ruta DESC";//id AND
        $and_dias = "";
        $solo_dias = "";
        $comparar_dias = ""; $contador_dias ="";
        if($dias != "''")
        {
            $and_dias = " AND RelDayCli.Cve_Cliente = d.Cve_Clte AND d.id_destinatario = RelDayCli.Id_Destinatario";
            $order_by = "CASE WHEN Secuencia = '' THEN 200000 END ASC, Secuencia*1 ASC"; //Secuencia*1 permite pasar a entero y que se organice por entero cómo números y no como varchar
              if($dias == "IFNULL(RelDayCli.Lu, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Lu, 20000) = RelDayCli.Lu"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Lu, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Ma, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Ma, 20000) = RelDayCli.Ma"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Ma, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Mi, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Mi, 20000) = RelDayCli.Mi"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Mi, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Ju, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Ju, 20000) = RelDayCli.Ju"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Ju, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Vi, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Vi, 20000) = RelDayCli.Vi"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Vi, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Sa, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Sa, 20000) = RelDayCli.Sa"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Sa, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Do, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Do, 20000) = RelDayCli.Do"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Do, 20000) != 20000";
                }
                $solo_dias = " AND $dias != 20000 ";
        }

        $and_select2 = "";
        if($and_dias || $and_vendedor_relday || $comparar_dias || $and_vendedor /*|| $ands*/)
        {
            //$and_select2 = "AND c.cve_ruta = '00xxyy77'"; // esto es para que cuando hay algún filtro, no busque en el select 2 de la union 
        }

        $sql = "SELECT DISTINCT
                                IFNULL(d.id_destinatario, '--') AS id,
                                $dias AS Secuencia,
                                IFNULL(c.Cve_Clte, '__') AS clave_cliente,
                                IFNULL(c.RazonSocial, '--') AS cliente, 
                                IFNULL(c.RazonComercial, '--') AS razoncomercial, 
                                IFNULL(d.razonsocial, '--') AS destinatario,
                                GROUP_CONCAT(DISTINCT IFNULL(t_ruta.cve_ruta,'--') SEPARATOR ', ') AS ruta,
                                #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                                #IFNULL(d.clave_destinatario, '--') AS clave_sucursal,
                                #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                                IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                                IF(ra.cve_vendedor != '', GROUP_CONCAT(DISTINCT u.nombre_completo SEPARATOR ', '), '') AS Agente,
                                IFNULL(d.direccion, '--') AS direccion,
                                IFNULL(d.colonia, '--') AS colonia,
                                IFNULL(d.postal, '--') AS postal,
                                IFNULL(d.ciudad, '--') AS ciudad,
                                IFNULL(d.estado, '--') AS estado,
                                IF(d.dir_principal = 1, IFNULL(c.latitud, '--'), IFNULL(d.latitud, '--')) AS latitud,
                                IF(d.dir_principal = 1, IFNULL(c.longitud, '--'), IFNULL(d.longitud, '--')) AS longitud,
                                IFNULL(d.contacto, '--') AS contacto,
                                IFNULL(d.telefono, '--') AS telefono
                        FROM c_destinatarios d
                            LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                            LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                            LEFT JOIN RelDayCli ON t_ruta.ID_Ruta = RelDayCli.Cve_Ruta $and_dias $and_vendedor_relday $comparar_dias
                            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta $and_vendedor
                            LEFT JOIN c_usuario u ON u.id_user = ra.cve_vendedor
                            LEFT JOIN c_dane cp ON cp.cod_municipio = d.postal
                        WHERE d.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = d.Cve_Clte AND t_ruta.cve_almacenp = '{$almacen}' $ands $cp_and
                        $solo_dias
                        GROUP BY clave_cliente
/*
                        UNION 

                        SELECT DISTINCT
                            IFNULL(des.id_destinatario, '--') AS id,
                            '' AS Secuencia,
                            IFNULL(c.Cve_Clte, '__') AS clave_cliente,
                            IFNULL(c.RazonSocial, '--') AS cliente, 
                            IFNULL(c.RazonComercial, '--') AS razoncomercial, 
                            IFNULL(des.razonsocial, '--') AS destinatario,
                            '--' AS ruta,
                            #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                            #IFNULL(d.clave_destinatario, '--') AS clave_sucursal,
                            #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                            IFNULL(des.id_destinatario, '--') AS clave_destinatario,
                            '' AS Agente,
                            IFNULL(des.direccion, '--') AS direccion,
                            IFNULL(des.colonia, '--') AS colonia,
                            IFNULL(des.postal, '--') AS postal,
                            IFNULL(des.ciudad, '--') AS ciudad,
                            IFNULL(des.estado, '--') AS estado,
                            IF(des.dir_principal = 1, IFNULL(c.latitud, '--'), IFNULL(des.latitud, '--')) AS latitud,
                            IF(des.dir_principal = 1, IFNULL(c.longitud, '--'), IFNULL(des.longitud, '--')) AS longitud,
                            IFNULL(des.contacto, '--') AS contacto,
                            IFNULL(des.telefono, '--') AS telefono
                        FROM c_destinatarios des
                            LEFT JOIN c_cliente c ON des.Cve_Clte = c.Cve_Clte 
                            LEFT JOIN c_dane cp ON cp.cod_municipio = des.postal
                        WHERE des.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = des.Cve_Clte $and_select2 $cp_and2
                        AND des.id_destinatario NOT IN (SELECT DISTINCT clave_cliente FROM t_clientexruta) $ands2
*/
                        ORDER BY $order_by 
                ";

$res = "";

        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

//**************************************************************************************************
//**************************************************************************************************
    $i = 2;

    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        if($Secuencia == '20000') $Secuencia = "";
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Secuencia; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $ruta; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $clave_cliente; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo utf8_encode($razoncomercial); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo utf8_encode($cliente); ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo utf8_encode($clave_destinatario); ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo utf8_encode($destinatario); ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo utf8_encode($direccion); ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo utf8_encode($colonia); ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $postal; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo utf8_encode($ciudad); ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo utf8_encode($estado); ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo $latitud; ?></div>
        <div cell="N<?php echo $i; ?>"><?php echo $longitud; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo utf8_encode($contacto); ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $telefono; ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo utf8_encode($Agente); ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>