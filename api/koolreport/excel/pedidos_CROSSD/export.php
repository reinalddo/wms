<?php
include "MyReport.php";
$report = new MyReport;
$report->run();
$report->exportToExcel('MyReportExcel')->toBrowser("Pedidos CROSS DOCKING.xlsx");
