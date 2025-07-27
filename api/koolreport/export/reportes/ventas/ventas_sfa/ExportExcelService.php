<?php

require_once "../../../../core/autoload.php";
include '../../../../../../config.php';
//require_once '../../../../PhpSpreadsheet/src/Bootstrap.php';
require_once '../../../../PHPExcel/PHPExcel.php';
require_once '../../../../PHPExcel/PHPExcel/IOFactory.php';
$datosExcel = $_POST['datosReporte'];
//  dataReporte.push({
//                            acciones: acciones,
//                            numCliente: data.rows[i].cell[2],
//                            nombreCliente: data.rows[i].cell[3],
//                            saldoDeudor: '$' + (data.rows[i].cell[8] ? data.rows[i].cell[8] : 0),
//                            pagos: '$' + (data.rows[i].cell[9] ? data.rows[i].cell[9] : 0),
//                            saldoFinal: '$' + (data.rows[i].cell[10] ? data.rows[i].cell[10] : 0),
//                            limiteCredito: '$' + (data.rows[i].cell[18] ? data.rows[i].cell[18] : 0),
//                            creditoDisponible: '$' + (data.rows[i].cell[18] - data.rows[i].cell[8] + data.rows[i].cell[9]),
//                            almacen: data.rows[i].cell[19],
//                        });

$excel = new PHPExcel();
$excel->getProperties()->setCreator("ADL")
    ->setLastModifiedBy("ADL")
    ->setTitle("Reporte Cuentas Cobrar Cons")
    ->setSubject("Reporte Cuentas Cobrar Cons")
    ->setDescription("Reporte Cuentas Cobrar Cons")
    ->setKeywords("office 2007 openxml php")
    ->setCategory("Reporte Cuentas Cobrar Cons");

$excel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'Cliente')
    ->setCellValue('B1', 'Nombre Comercial')
    ->setCellValue('C1', 'Limite de Crédito')
    ->setCellValue('D1', 'Saldo Deudor')
    ->setCellValue('E1', 'Pagos')
    ->setCellValue('F1', 'Saldo Final')
    ->setCellValue('G1', 'Crédito Disponible');

$excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);


//termina la configuración del archivo de excel
//empieza la configuración de los datos recorrindolos con un foreach
$excel->getActiveSheet()->setTitle('Reporte Cuentas Cobrar Con');
$i = 2;
$totalSaldoFinal = 0;
foreach ($datosExcel as $row) {
    $excel->getActiveSheet()->setCellValue('A' . $i, $row['numCliente']);
    $excel->getActiveSheet()->setCellValue('B' . $i, $row['nombreCliente']);

    $limiteCredito = str_replace(['$', ','], '', $row['limiteCredito']);
    $saldoDeudor = str_replace(['$', ','], '', $row['saldoDeudor']);
    $pagos = str_replace(['$', ','], '', $row['pagos']);
    $saldoFinal = str_replace(['$', ','], '', $row['saldoFinal']);
    $creditoDisponible = str_replace(['$', ','], '', $row['creditoDisponible']);

    $totalSaldoFinal += (float)$saldoFinal;
    // Convertir los montos a números y asignarlos a las celdas
    $excel->getActiveSheet()->setCellValue('C' . $i, (float)$limiteCredito);
    $excel->getActiveSheet()->setCellValue('D' . $i, (float)$saldoDeudor);
    $excel->getActiveSheet()->setCellValue('E' . $i, (float)$pagos);
    $excel->getActiveSheet()->setCellValue('F' . $i, (float)$saldoFinal);
    $excel->getActiveSheet()->setCellValue('G' . $i, (float)$creditoDisponible);

    $i++;
}
$excel->getActiveSheet()->setCellValue('F' . $i, 'Adeudo Final');
$excel->getActiveSheet()->setCellValue('G' . $i, $totalSaldoFinal);
$excel->getActiveSheet();

// termina la configuración de los datos recorrindolos con un foreach

//empieza proceso de retorno de archivo en base64
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
ob_start();
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
$writer->save('php://output');
$xlsData = ob_get_contents();
ob_end_clean();
echo base64_encode($xlsData);
//termina proceso de retorno de archivo en base64


?>

