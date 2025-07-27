<?php
include "MyReport.php";
$id_inventario = $_GET['id'];
$conteo        = $_GET['conteo_usuario'];
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Reporte por Conteo_".$conteo."_".$id_inventario.".xlsx");
