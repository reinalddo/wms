<?php

require_once "../../../../core/autoload.php";
include '../../../../../../config.php';
require_once "Report.php";
require_once '../../../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$report = new Report;
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$cveCia = $_POST['cveCia'];
$datosReporte = $_POST['datosReporte'];
$sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = ' . $cveCia;
if (!($res = mysqli_query($conn, $sql))) {
    echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
}
$row_logo = mysqli_fetch_array($res);
$logo = $row_logo['logo'];
//si logo contiene http o https se deja igual, si no se le agrega el host actual con http o https segun corresponda
if (strpos($logo, 'http') === false) {
    $logo = 'https://' . $_SERVER['HTTP_HOST'] . $logo;
}
$htmlResponse = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reporte Cuentas por Cobrar | Consolidado</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.42857143;
            color: #333;
        }

        .logo {
            width: 100px;
            height: 100px;
        }

        .title {
            text-align: center;
        }

        .date {
            text-align: right;
        }

        .company {
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .table th, .table td {
            padding: 5px;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #f9f9f9;
        }

        .table td {
            text-align: right;
        }

        .table td:first-child {
            text-align: left;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
      <table width="100%">
        <tr>
          <td width="30%" align="left"><img src="' . $logo . '" class="logo"></td>
          <td width="40%" align="center">
          <div>
          <h2>Reporte Cuentas por Cobrar | Consolidado</h2>
          <br>
          <h2>Sucursal: ' . $datosReporte[0]['almacen'] . '</h2>
</div></td>
          <td width="30%" align="right"><h3>Fecha del reporte: ' . date('d/m/Y') . '</h3></td>
        </tr>
        </table>
      </div>
    <br>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th style="text-align: center">Cliente</th>
                                            <th style="text-align: center">Nombre Comercial</th>
                                            <th style="text-align: center">Limite de Crédito</th>
                                            <th style="text-align: center">Saldo Deudor</th>
                                            <th style="text-align: center">Pagos</th>
                                            <th style="text-align: center">Saldo Final</th>
                                            <th style="text-align: center">Crédito Disponible</th>
            </tr>
            </thead>
            <tbody>
            ';
$totalSaldoFinal = 0;
foreach ($datosReporte as $key => $value) {
    $saldoFinal = str_replace(['$', ','], '', $value['saldoFinal']);
    $totalSaldoFinal += (float)$saldoFinal;
    $htmlResponse .= '<tr>
                <td style="text-align: center">' . $value['numCliente'] . '</td>
                <td style="text-align: center">' . $value['nombreCliente'] . '</td>
                <td style="text-align: right">' . $value['limiteCredito'] . '</td>
                <td style="text-align: right">' . $value['saldoDeudor'] . '</td>
                <td style="text-align: right">' . $value['pagos'] . '</td>
                <td style="text-align: right">' . $value['saldoFinal'] . '</td>
                <td style="text-align: right">' . $value['creditoDisponible'] . '</td>
            </tr>';
}
$htmlResponse .= '</tbody>
<tfoot>
            <tr>
                   <th colspan="7" style="text-align: right">Adeudo Final</th>
                     <th style="text-align: right">' . number_format($totalSaldoFinal, 2) . '</th>
            </tr>
            </tfoot>
        </table>
    </div>
    <br>
</div>
</body>
</html>';
//Aquí se crea el objeto a utilizar
$options = new Options();

//Y debes activar esta opción "TRUE"
$options->set('isRemoteEnabled', TRUE);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($htmlResponse);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$output = $dompdf->output();
//base64_encode($output);
$archivoBase64 = base64_encode($output);
echo $archivoBase64;
?>

