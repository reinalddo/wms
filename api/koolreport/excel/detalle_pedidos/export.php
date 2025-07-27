<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Resumen Pedidos por Ruta.xlsx");
