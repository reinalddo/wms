<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$cve_cia = $_GET['cve_cia'];
$status = $_GET['status'];
$titulo = "";
if($status == 'S') $titulo = "Lista de Surtido.xlsx";else $titulo = "Reporte de Producto Surtido.xlsx";

$report->exportToExcel('MyReportExcel')->toBrowser($titulo);
