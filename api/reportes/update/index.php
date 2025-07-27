<?php
/*
if(!isset( $_SESSION['id_user'] ))
{
    if($_GET['action'] != 'existenciaUbicacion')
    {
        session_start();
        $_SESSION['cve_usuario'] = 'user_email_wms';

        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT id_user FROM c_usuario WHERE cve_usuario = 'user_email_wms'";
        //$query = mysqli_query($conn, $sql);
        $result2 = \db()->prepare($sql);
        $result2->execute();
        $row2 = $result2->fetch();
        //$id_user = mysqli_fetch_array($query)['id_user'];
        $id_user = $row2['id_user'];

        if(!$id_user)
        {
            $sql = "INSERT INTO c_usuario (cve_usuario, des_usuario, ban_usuario, Activo) VALUES ('user_email_wms', 'user_email_wms', 1, 0)";
            //$query = mysqli_query($conn, $sql);
            $result2 = \db()->prepare($sql);

            $sql = "SELECT id_user FROM c_usuario WHERE cve_usuario = 'user_email_wms'";
            //$query = mysqli_query($conn, $sql);
            $result2 = \db()->prepare($sql);
            $result2->execute();
            $row2 = $result2->fetch();
            //$id_user = mysqli_fetch_array($query)['id_user'];
            $id_user = $row2['id_user'];        
        }

        $_SESSION['id_user'] = $id_user;
        $_SESSION['identifier'] = "";
        $_SESSION['subdomain'] = "";
    }
}
*/
include '../../../app/load.php';
// Initalize Slim

use Framework\Http\Response;
error_reporting(0);

$app = new \Slim\Slim();

if($_GET['action'] != 'existenciaUbicacion')
{
if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}
}

$ga = new \Reportes\Reportes();

if( $_POST['action'] == 'productosmasubicacion' ) {   
    $data = $ga->productosmasubicacion();
    echo json_encode($data);
}

if( $_POST['action'] == 'concentradoexistencia' ) {   
    $data = $ga->concentradoexistencia();
    echo json_encode($data);
}

if( $_POST['action'] == 'entrada' ) 
{	
  $id=$_POST['idss'];
  $data = $ga->entrada($id);
  $arr = array(
    "data" => $data,	
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
}


if( $_POST['action'] == 'asn' ) {	
    
    $id=$_POST['idss'];
    $data = $ga->asn($id);
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'setRangoGuiasEmbarqueShowReport' ) {
    $_SESSION["FolPedidoCon"] = $_POST["FolPedidoCon"];
    $_SESSION["FacturaMadre"] = $_POST["FacturaMadre"];

    $data = (array) $ga->guiasembarque($_POST["FolPedidoCon"], $_POST["FacturaMadre"]);

    $total = 0;

    foreach ($data as $d) {
        $total = $total + $d["cajas"];
    }

    $arr = array(
        "success"=>true,
        "total" => $total,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'setRangoUbicacionesShowReport' ) {
    $_SESSION["rack"]     = $_POST["rack"];
    $_SESSION["zona"]     = $_POST["zona"];
    $_SESSION["nivel"]    = $_POST["nivel"];
    $_SESSION["seccion"]  = $_POST["seccion"];
    $_SESSION["posicion"] = $_POST["posicion"];

        $arr = array(
        "success"=>true,
        
    );

    

    echo json_encode($arr);
}

if( $_POST['action'] == 'printRangeGuiasEmbarqueShowReport' ) {
    $_SESSION["desde"] = $_POST["desde"];
    $_SESSION["hasta"] = $_POST["hasta"];
    $_SESSION["FolPedidoCon"] = $_POST["FolPedidoCon"];
    //$_SESSION["FacturaMadre"] = $_POST["FacturaMadre"];

    $arr = array(
        "success"=>true,
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'printRangeUbicacionesShowReport' ) {
    $_SESSION["desde"] = $_POST["desde"];
    $_SESSION["hasta"] = $_POST["hasta"];
    $_SESSION["id_ubicacion"] = $_POST["id_ubicacion"];

    $arr = array(
        "success"=>true,
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'guiasembarqueshowreport' ) {
    $_SESSION["FolPedidoCon"] = $_POST["FolPedidoCon"];
    $_SESSION["FacturaMadre"] = $_POST["FacturaMadre"];

    $arr = array(
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'productosShowReport' ) {
    $_SESSION["cve_articulo"] = $_POST["cve_articulo"];

    $arr = array(
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'ubicacioneshowreport' ) {
    $_SESSION["id_ubicacion"] = $_POST["id_ubicacion"];
	
    $arr = array(
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'guiasembarque' ) {
    $data = $ga->guiasembarque($_POST["FolPedidoCon"], $_POST["FacturaMadre"]);

    $arr = array(
        "data" => $data,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'comprobantei' ) {	
    $data = $ga->comprobantei();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invfidet' ) {	
    $data = $ga->invfidet($_POST['id']);
		
	$arr = array(            
		"data" => $data,	
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'maxmin1' ) {	
    $data = $ga->maxmin1($_POST);
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'maxmin2' ) {   
    $data = $ga->maxmin2();
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'difconteos' ) {	
    $data = $ga->difconteos();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invcicl' ) {	
    $data = $ga->invcicl();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invcicldet' ) {	
    $data = $ga->invcicldet($_POST['id']);
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'lotesporvencer' ) {    
    $data = $ga->lotesPorVencer();
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'lotesvencidos' ) {    
    $data = $ga->lotesVencidos($_POST);
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'existenciaUbicacion' ) 
{    
  $data = $ga->existenciaubica($_POST["almacen"],$_POST["articulo"], $_POST["zona"], $_POST['bl'], $_POST['contenedor'], $_POST['cve_proveedor'], $_POST['proveedor'], $_POST['grupo'], $_POST['clasificacion'], $_POST['lp'], $_POST['art_obsoletos'], $_POST['mostrar_folios_excel_existencias'], $_POST['existencia_cajas'], $_POST['lote'], $_POST['factura_oc'], $_POST['proyecto_existencias'], $_POST['lote_alterno'], 0);
  $arr = array(
    "data" => $data,    
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
  //echo var_dump($arr);
  //die();
}

if( $_GET['action'] == 'existenciaUbicacion' ) 
{    
/*    //$rows = array();
  $sql = $ga->existenciaubica($_GET["almacen"],$_GET["articulo"], $_GET["zona"], $_GET['bl'], $_GET['contenedor'], $_GET['cve_proveedor'], $_GET['proveedor'], $_GET['grupo'], $_GET['clasificacion'], $_GET['lp'], $_GET['art_obsoletos'], $_GET['mostrar_folios_excel_existencias'], $_GET['existencia_cajas'], $_GET['lote'], $_GET['factura_oc'], $_GET['proyecto_existencias'], 1);

    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de Existencias.xlsx";

    //$query = mysqli_query(\db2(), $sql);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn, $sql);
    //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
    $rows = array();
    while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
    {
        $rows[] = $row;
        //$datos = $row;
    }

    //$rows = array_merge($rows);
    //$rows = json_encode($rows);
    //$rows = array_merge($rows);
    //var_dump($rows);
    //echo nl2br($sql);

    //$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
    $cuadro7 = array('Codigo BL', 'Pallet|Cont', 'License Plate (LP)', 'Clave', 'Clave Alterna', 'CB Pieza', 'Clasificacion', 'Descripcion', 'Lote | Serie', 'Caducidad', 'Unidad Medida', 'Total', 'RP', 'Prod QA', 'Disponible', 'Fecha Ingreso', 'Proyecto', 'Grupo', 'Proveedor');

    $excel = new XLSXWriter();
    $excel->writeSheetRow("ReporteExistencias", $cuadro7 );
    //$cuadro8 = array($sql, 'Pallet|Cont', 'License Plate (LP)', 'Clave', 'Clave Alterna', 'CB Pieza', 'Clasificacion', 'Descripcion', 'Lote | Serie', 'Caducidad', 'Unidad Medida', 'Total', 'RP', 'Prod QA', 'Disponible', 'Fecha Ingreso', 'Proyecto', 'Grupo', 'Proveedor');
    //$excel->writeSheetRow("ReporteExistencias", $cuadro8 );
    //$excel->writeSheetRow("Reporte de Existencias2", $sql.";;;OKOK" );
    $i = 0;
    foreach($rows as $row)
    {
        //$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);

        $qa_exc   = '';if($row['QA']=='No') $qa_exc = $row['cantidad']; else $qa_exc = '';
        $rp_exc   = '';if($row['RP']!=0) $rp_exc = $row['RP']; else $rp_exc = '';
        $qa2_exc  = '';if($row['QA']=='Si') $qa2_exc = $row['cantidad']; else $qa2_exc = '';
        $disp_exc = ($row['cantidad']-$row['RP']);

        $row = array($row['codigo'], $row['contenedor'], $row['LP'], $row['clave'], $row['clave_alterna'], $row['codigo_barras_pieza'], $row['des_clasif'], $row['descripcion'], $row['lote'], $row['caducidad'], $row['um'], $qa_exc, $rp_exc, $qa2_exc, $disp_exc, $row['fecha_ingreso'], $row['proyecto'], $row['des_grupo'], $row['proveedor']);
        $excel->writeSheetRow("ReporteExistencias", $row );
        $i++;
        //echo $i;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');

    //move_uploaded_file($_FILES['excel'], "$enlace"."api/reportes/update");
    //$excel->writeToStdOut($title);
    //file_put_contents($title);
*/

//include '../../../app/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
//include '../../../app/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
// PHPExcel
//require_once 'PHPExcel.php';
// PHPExcel_IOFactory
//include 'PHPExcel/IOFactory.php';

// Creamos un objeto PHPExcel
//$objPHPExcel = new \PHPExcel\PHPExcel();
// Leemos un archivo Excel 2007

$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet(); 
//Agregamos un texto a la celda A1 
$sheet->setCellValue('A1', 'Prueba'); 
//Damos formato o estilo a nuestra celda 
$sheet->getStyle('A1')->getFont()->setName('Tahoma')->setBold(true)->setSize(8); 
$sheet->getStyle('A1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->getStyle('A1')->getAlignment()->setVertical('center')->setHorizontal('center'); 
$sheet->setCellValue('B1', 'PHPExcel'); 
//usamos los mismos estilos de A1 
$sheet->getStyle('B1')->getFont()->setName('Tahoma')->setBold(true)->setSize(8); 
$sheet->getStyle('B1')->getBorders()->applyFromArray(array('allBorders' => 'thin')); 
$sheet->getStyle('B1')->getAlignment()->setVertical('center')->setHorizontal('center'); 
//exportamos nuestro documento 
$writer = new PHPExcel_Writer_Excel2007($objPHPExcel); 
$writer->save('prueba.xlsx'); 

/*
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("Reporte de Existencias.xlsx");
// Indicamos que se pare en la hoja uno del libro
$objPHPExcel->setActiveSheetIndex(0);
//Escribimos en la hoja en la celda B1
$objPHPExcel->getActiveSheet()->SetCellValue('B2', 'Hola mundo');
// Color rojo al texto
$objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
// Texto alineado a la derecha
$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
// Damos un borde a la celda
$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
//Guardamos el archivo en formato Excel 2007
//Si queremos trabajar con Excel 2003, basta cambiar el 'Excel2007' por 'Excel5' y el nombre del archivo de salida cambiar su formato por '.xls'
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save("Archivo_salida.xlsx");
*/
  //$arr = array_merge($arr);
  //echo json_encode($arr);
  //echo var_dump($arr);
  //die();
}


if( $_POST['action'] == 'pendienteAcomodo' ) 
{    
  $data = $ga->pendienteAcomodoPDF($_POST["almacen"]);
  $arr = array(
    "data" => $data,    
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
  //echo var_dump($arr);
  //die();
}

if( $_POST['action'] == 'EliminarRegistrosEntradas' ) 
{
    $borrar_id         = $_POST['borrar_id'];
    $borrar_folio      = $_POST['borrar_folio'];
    $borrar_articulo   = $_POST['borrar_articulo'];
    $borrar_lote       = $_POST['borrar_lote'];
    $borrar_contenedor = $_POST['borrar_contenedor'];
    $borrar_cantidad   = $_POST['borrar_cantidad'];

    $i = 0;
    $folio = $borrar_folio[0];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT * FROM th_entalmacen WHERE fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);
    $ID_Proveedor = mysqli_fetch_array($query)['Cve_Proveedor'];

    for($i = 0; $i < count($borrar_id); $i++)
    {
        $id = $borrar_id[$i];
        $folio = $borrar_folio[$i];
        $articulo = $borrar_articulo[$i];
        $lote = $borrar_lote[$i];
        $contenedor = $borrar_contenedor[$i];
        $cantidad = $borrar_cantidad[$i];

        $sql = "DELETE FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}' AND cve_articulo = '$articulo' AND cve_lote = '$lote' AND ClaveEtiqueta = '{$contenedor}'";
        $query = mysqli_query($conn, $sql);


        $sql = "SELECT (CantidadRecibida - $cantidad) as CantidadRecibida FROM td_entalmacen WHERE id = '{$id}'";
        $query = mysqli_query($conn, $sql);
        $CantidadRecibida = mysqli_fetch_array($query)['CantidadRecibida'];

        if($CantidadRecibida > 0)
            $sql = "UPDATE td_entalmacen SET CantidadRecibida = CantidadRecibida - $cantidad WHERE id = '{$id}'";
        else
            $sql = "DELETE FROM td_entalmacen WHERE id = '{$id}'";
        $query = mysqli_query($conn, $sql);

        $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - $cantidad WHERE CONCAT(cve_articulo, IFNULL(cve_lote, ''), cve_ubicacion, ID_Proveedor) IN (SELECT CONCAT(td.cve_articulo, IFNULL(td.cve_lote, ''), td.cve_ubicacion, (SELECT Cve_Proveedor FROM th_entalmacen WHERE fol_folio = '{$folio}}')) FROM td_entalmacen td WHERE id = '{$id}')";
        $query = mysqli_query($conn, $sql);

    }
  $arr = array(
    "data" => true,    
  );
  echo json_encode($arr);

}

