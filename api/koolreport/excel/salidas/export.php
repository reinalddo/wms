<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$folio = $_GET['folio'];

$report->exportToExcel('MyReportExcel')->toBrowser("Salidas {$folio}.xlsx");
