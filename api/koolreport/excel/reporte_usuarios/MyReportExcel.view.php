<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Reporte de Usuarios";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Usuario</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Correo</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Ingreso</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Almacenes Asignados</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Empresa</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Perfil</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Activo</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql = "SELECT DISTINCT 
                c_usuario.id_user,
                c_usuario.cve_usuario,
                c_usuario.cve_cia,
                c_usuario.nombre_completo,
                c_usuario.email,
                c_usuario.des_usuario,
                DATE_FORMAT(c_usuario.fec_ingreso, '%d-%m-%Y') as fec_ingreso,
                c_usuario.pwd_usuario,
                c_usuario.ban_usuario,
                c_usuario.`status`,
                IF(c_usuario.Activo = 1, 'Si', 'No') as Activo,
                c_usuario.`timestamp`,
                c_usuario.identifier,
                c_usuario.image_url,
                GROUP_CONCAT(c_almacenp.clave SEPARATOR ',') AS almacenes,
                c_compania.des_cia AS empresa,
                IF(c_usuario.es_cliente=1, 'Cliente', IF(c_usuario.es_cliente=2, 'Proveedor', t_roles.rol)) AS perfil
                FROM
                c_usuario
                LEFT JOIN c_compania ON c_compania.cve_cia = c_usuario.cve_cia
                LEFT JOIN t_roles ON c_usuario.perfil = t_roles.id_role
                LEFT JOIN trel_us_alm ON trel_us_alm.cve_usuario = c_usuario.cve_usuario
                LEFT JOIN c_almacenp ON c_almacenp.clave = trel_us_alm.cve_almac
            WHERE c_usuario.Activo = '1' AND c_usuario.cve_usuario != 'wmsmaster' 
            GROUP BY c_usuario.cve_usuario 
            ORDER BY c_usuario.cve_usuario
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $cve_usuario; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $nombre_completo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $email; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $des_usuario; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $fec_ingreso; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $almacenes; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $empresa; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $perfil; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $Activo; ?></div>
        <?php 
        $i++;

    }
  ?>
    
</div>