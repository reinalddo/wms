<?php
    $dir = "../../../../img/sucursal/";
    echo move_uploaded_file($_FILES["image"]["tmp_name"], $dir. $_FILES["image"]["name"]);
?>


