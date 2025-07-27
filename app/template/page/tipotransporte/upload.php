<?php
$dir = "../../../../img/foto_tipo_transporte/";
echo move_uploaded_file($_FILES["image"]["tmp_name"], $dir. $_FILES["image"]["name"]);




?>


