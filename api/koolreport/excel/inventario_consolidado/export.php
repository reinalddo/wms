<?php
include "MyReport.php";
$id_inventario = $_GET['id'];
$diferencia    = $_GET['diferencia_inv'];

$titulo = "REPORTE DE INVENTARIO CONSOLIDADO DE CONTEOS";
if($diferencia == 1)
  $titulo = "REPORTE DE UBICACIONES CON DIFERENCIA";
if($diferencia == 3)
  $titulo = "REPORTE DE CONTEOS ABIERTOS";

$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser($titulo."_".$id_inventario.".xlsx");
