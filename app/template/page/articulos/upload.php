<?php
$dir  = "../../../../img/articulo/";
//$dir  = "../img/articulo/";
$file = $dir. $_FILES["image"]["name"];
@unlink($file);
echo move_uploaded_file($_FILES["image"]["tmp_name"], $file);
?>


