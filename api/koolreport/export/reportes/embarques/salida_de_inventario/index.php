<?php
require_once "../../../../core/autoload.php";
require_once "Report.php";

$report = new Report;
// $report->run()->render();
$id = "";
if(isset($_GET['id']))
    $id = $_GET['id'];
else 
    $id = $_GET['folio_pedido'];
$report->run()->export()
->pdf(array(
    "format"=>"Letter",//A4, Letter
    "orientation"=>"portrait",//portrait, landscape
    //"zoom"=>2
))
->toBrowser("Salida de Inventario {$id}.pdf", true);