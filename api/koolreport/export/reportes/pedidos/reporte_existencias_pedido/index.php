<?php
require_once "../../../../core/autoload.php";
require_once "Report.php";

$report = new Report;
// $report->run()->render();
$folio = $_GET['folio'];
$report->run()->export()
->pdf(array(
    "format"=>"Letter",//A4, Letter
    "orientation"=>"landscape",//portrait, landscape
    //"zoom"=>2
))
->toBrowser("Existencias {$folio}.pdf", true);