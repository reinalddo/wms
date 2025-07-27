<?php
include "MyReport.php";
$id_inventario = $_GET['id'];

$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Inventario Consolidado por Item_".$id_inventario.".xlsx");
