<?php
include "MyReport.php";
$id_lista = $_GET['id_lista'];
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Layout_ListaDePrecio_".$id_lista.".xlsx");
