<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$titulo = "Lps Generados.xlsx";

$report->exportToExcel('MyReportExcel')->toBrowser($titulo);
