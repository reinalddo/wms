<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Puntos de Venta";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>CEDI</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cod</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Punto de venta</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fact. Madre</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fact. Hija</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Orden De Compra</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripcion</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidades</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio = $_GET['id'];

    $sql = "SELECT  C.Colonia CEDI,W.Cod_PV Cod,C.RazonSocial `Puntodeventa`,W.Fol_PedidoCon `FactMadre`,W.Fol_Folio `FactHija`,
                    W.No_OrdComp `OrdenDeCompra`, W.Cve_Articulo Clave,A.Des_Articulo Descripcion,SUM(W.Cant_Pedida) Unidades
                    , IF(IFNULL(A.num_multiplo, 0) = 0, 1, A.num_multiplo) AS num_multiplo
            FROM    td_consolidado W JOIN c_cliente C ON W.Cve_Clte=C.Cve_Clte AND W.Cve_CteProv=C.Cve_CteProv
                    JOIN c_articulo A ON W.cve_articulo=A.cve_articulo
            WHERE   Fol_PedidoCon='{$folio}'
            GROUP BY C.Colonia,W.Cod_PV,C.RazonSocial,W.Fol_PedidoCon,W.Fol_Folio,W.No_OrdComp,W.Cve_Articulo,A.Des_Articulo
            ORDER BY W.Cod_PV;
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    $cedi_anterior = ""; $imprimir_cedi = true;
    $cod_anterior = ""; $imprimir_cod = true;
    $pdv_anterior = ""; $imprimir_pdv = true;
    $fm_anterior = ""; $imprimir_fm = true;
    $fh_anterior = ""; $imprimir_fh = true;
    $oc_anterior = ""; $imprimir_oc = true;
    $articulo_anterior = ""; $imprimir_articulo = true;
    $desc_anterior = ""; $imprimir_desc = true;

    $tot_unidades = 0;
    $tot_cajas = 0;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        if($cedi_anterior == $CEDI)        $imprimir_cedi = false;    else {$cedi_anterior = $CEDI;        $imprimir_cedi = true;}
        if($cod_anterior == $Cod)          $imprimir_cod = false;     else {$cod_anterior = $Cod;          $imprimir_cod = true;}
        if($pdv_anterior == $Puntodeventa) $imprimir_pdv = false;     else {$pdv_anterior = $Puntodeventa; $imprimir_pdv = true;}
        if($fm_anterior == $FactMadre)     $imprimir_fm = false;      else {$fm_anterior = $FactMadre;     $imprimir_fm = true;}
        if($fh_anterior == $FactHija)      $imprimir_fh = false;      else {$fh_anterior = $FactHija;      $imprimir_fh = true;}
        if($oc_anterior == $OrdenDeCompra) $imprimir_oc = false;      else {$oc_anterior = $OrdenDeCompra; $imprimir_oc = true;}
        if($articulo_anterior == $Clave)   $imprimir_articulo = false;else {$articulo_anterior = $Clave;   $imprimir_articulo = true;}
        if($desc_anterior == $Descripcion) $imprimir_desc = false;    else {$desc_anterior = $Descripcion; $imprimir_desc = true;}

        $cajas = ceil($Unidades/$num_multiplo);
        ?>
        <div cell="A<?php echo $i; ?>"><?php if($imprimir_cedi == true) echo $CEDI; ?></div>
        <div cell="B<?php echo $i; ?>"><?php if($imprimir_cod == true) echo $Cod; ?></div>
        <div cell="C<?php echo $i; ?>"><?php if($imprimir_pdv == true) echo $Puntodeventa; ?></div>
        <div cell="D<?php echo $i; ?>"><?php if($imprimir_fm == true) echo $FactMadre; ?></div>
        <div cell="E<?php echo $i; ?>"><?php if($imprimir_fh == true) echo $FactHija; ?></div>
        <div cell="F<?php echo $i; ?>"><?php if($imprimir_oc == true) echo $OrdenDeCompra; ?></div>
        <div cell="G<?php echo $i; ?>"><?php if($imprimir_articulo == true) echo $Clave; ?></div>
        <div cell="H<?php echo $i; ?>"><?php if($imprimir_desc == true) echo $Descripcion; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $Unidades; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $cajas; ?></div>
        <?php 
        $tot_unidades += $Unidades;
        $tot_cajas += $cajas;
        $i++;

    }
  ?>
        <div cell="H<?php echo $i; ?>" excelStyle='<?php echo json_encode($styleArray); ?>'>Total:</div>
        <div cell="I<?php echo $i; ?>"><?php echo $tot_unidades; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $tot_cajas; ?></div>

    
</div>