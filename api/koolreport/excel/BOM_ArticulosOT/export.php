<?php
include "MyReport.php";
$articulo = "";
if(isset($_GET['txtArticuloParte']))
	$articulo = $_GET['txtArticuloParte'];
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Componentes Articulo {$articulo}.xlsx");
