<?php
include "MyReport.php";
$id_inventario = $_GET['id'];
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Vista Previa de Ajustes Inventario_".$id_inventario.".xlsx");
