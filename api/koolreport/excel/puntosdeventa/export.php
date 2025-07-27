<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$folio = $_GET['id'];
$report->exportToExcel('MyReportExcel')->toBrowser("Reporte Puntos de Venta #{$folio}.xlsx");
