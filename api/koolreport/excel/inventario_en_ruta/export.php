<?php
include "MyReport.php";
$ruta = $_GET['ruta'];
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Inventario de la Ruta".$ruta.".xlsx");
