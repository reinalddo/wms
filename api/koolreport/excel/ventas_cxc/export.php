<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Reporte de Cuentas por Cobrar.xlsx");
