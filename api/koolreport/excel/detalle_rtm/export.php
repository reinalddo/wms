<?php
include "MyReport.php";
$folio = $_GET['folio'];
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Detalle RTM #{$folio}.xlsx");
