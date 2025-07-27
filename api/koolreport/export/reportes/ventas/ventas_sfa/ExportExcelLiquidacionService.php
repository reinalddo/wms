<?php

require_once "../../../../core/autoload.php";
include '../../../../../../config.php';
//require_once '../../../../PhpSpreadsheet/src/Bootstrap.php';
require_once '../../../../PHPExcel/PHPExcel.php';
require_once '../../../../PHPExcel/PHPExcel/IOFactory.php';
$datosExcelArticulos = $_POST['datosReporteArticulo'];
$datosExcelResumen = $_POST['datosReporteResumen'];

$excel = new PHPExcel();
$excel->getProperties()->setCreator("ADL")
    ->setLastModifiedBy("ADL")
    ->setTitle("Reporte Liquidación")
    ->setSubject("Reporte Liquidación")
    ->setDescription("Reporte Liquidación")
    ->setKeywords("office 2007 openxml php")
    ->setCategory("Reporte Liquidación");
$excel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'Clave Articulo')
    ->setCellValue('B1', 'Articulo')
    ->setCellValue('C1', 'CantxCaja')
    ->setCellValue('D1', 'CantxPieza')
    ->setCellValue('E1', 'Importe')
    ->setCellValue('F1', 'PromoxCaja')
    ->setCellValue('G1', 'PromoxPieza');

$excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//all titles auto size
foreach (range('A', 'G') as $columnID) {
    $excel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
}

$excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
$excel->getActiveSheet()->setTitle('Reporte Liquidación');
$i = 2;
foreach ($datosExcelArticulos as $row) {
    $totCajas = 0;
    $totPiezas = 0;
    if ($row['total_venta_caja'] == 0) {
        $totCajas = $row['total_cajas_devolucion'] > 0 ? $row['total_cajas_devolucion'] : ($row['total_cajas_devolucion'] > 0 ? $row['total_cajas_devolucion'] : ($row['total_cajas_preventa'] > 0 ? $row['total_cajas_preventa'] : $row['total_cajas_pedido']));
        $totPiezas = $row['total_piezas_devolucion'] > 0 ? $row['total_piezas_devolucion'] : ($row['total_piezas_devolucion'] > 0 ? $row['total_piezas_devolucion'] : ($row['total_piezas_preventa'] > 0 ? $row['total_piezas_preventa'] : $row['total_piezas_pedido']));
        if ($totCajas == 0) {
            $totCajas = $row['total_cajas_entrega'];
        }
        if ($totPiezas == 0) {
            $totPiezas = $row['total_piezas_entrega'];
        }
    } else {
        $totCajas = $row['total_venta_caja'];
        $totPiezas = $row['total_venta_pieza'];
    }
    $excel->getActiveSheet()->setCellValue('A' . $i, $row['cve_articulo']);
    $excel->getActiveSheet()->setCellValue('B' . $i, $row['articulo']);
    $excel->getActiveSheet()->setCellValue('C' . $i, $totCajas);
    $excel->getActiveSheet()->setCellValue('D' . $i, $totPiezas);
    $excel->getActiveSheet()->setCellValue('E' . $i, $row['total_articulo']);
    $excel->getActiveSheet()->setCellValue('F' . $i, $row['total_promociones_caja']);
    $excel->getActiveSheet()->setCellValue('G' . $i, $row['total_promociones_pieza']);
    $i++;
}
//data de resumen
//{
//    "Total": 1225,
//    "preventa": 0,
//    "venta_credito": 0,
//    "descuentos_vp": 0,
//    "venta_contado": 3250,
//    "devoluciones": 0,
//    "cobranza": 351,
//    "total_a_liquidar": 3601
//}
$ultimoRegistro = $i - 1;

// Agregar los títulos del array de resumen debajo de la columna "CantxPieza" en formato vertical
$j = 0;
foreach ($datosExcelResumen as $titulo => $valor) {
    // Verificar si el título es uno de los que debe ir en negrita
    $bold = false;
    if ($titulo == 'Total' || $titulo == 'venta_contado' || $titulo == 'cobranza' || $titulo == 'total_a_liquidar' || $titulo == 'devoluciones') {
        $bold = true;
    }

    // Establecer el estilo de la celda
    $style = $excel->getActiveSheet()->getStyleByColumnAndRow(3, $ultimoRegistro + 2 + $j);
    if ($bold) {
        $style->getFont()->setBold(true);
    }

    // Asignar los valores a las celdas
    $excel->getActiveSheet()->setCellValueByColumnAndRow(3, $ultimoRegistro + 2 + $j, ucfirst(str_replace('_', ' ', $titulo)));
    $excel->getActiveSheet()->setCellValueByColumnAndRow(4, $ultimoRegistro + 2 + $j, $valor);
    $j++;
}


// Actualizar auto size para las nuevas columnas
$excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$excel->getActiveSheet();
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
ob_start();
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
$writer->save('php://output');
$xlsData = ob_get_contents();
ob_end_clean();
echo base64_encode($xlsData);
//termina proceso de retorno de archivo en base64


?>

