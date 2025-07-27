<?php
$listaAP = new \AlmacenP\AlmacenP();
//$model_almacen = $almacenes->getAll();
$R = new \Ruta\Ruta();
$rutas = $R->getAll();
$U = new \Usuarios\Usuarios();
$usuarios = $U->getAll();
$invSql = \db()->prepare("SELECT *  FROM th_inventario ");
$invSql->execute();
$niventario = $invSql->fetchAll(PDO::FETCH_ASSOC);
?>
<link href="/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/ladda/select2.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/dataTables/buttons.dataTables.min.css" rel="stylesheet"/>
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<style>
    .bt{

        margin-right: 10px;
    }

    .btn-blue{

        background-color: blue !important;
        border-color: blue !important;
        color: white !important;
    }
  .mt-1
  {
    margin-top: 10px;
  }
  .mt-g
  {
    margin-top: 40px;
  }
  .table-p
  {
   padding:20px;
   overflow:auto;
  }
 .td
  {
   
    padding: 5px 7px;
    text-align: center;
   
  }
  .th
  {
   
    padding: 5px 7px;
    text-align: center;
   
  }
  .pd-left
  {
    padding-left:100px;
  }
  .table {
    
    text-align: center;
}
  
  .table>thead:first-child>tr:first-child>th{
    
    text-align: center;
  }
  
  .leftText{
    text-align: left;
  }
 

</style>