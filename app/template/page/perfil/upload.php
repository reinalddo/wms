<?php
$dir = "../../../../img/imageperfil/";
if(move_uploaded_file($_FILES["image"]["tmp_name"], $dir. $_FILES["image"]["name"])){
    
    chmod($dir. $_FILES["image"]["name"], 0777);
    chown($dir. $_FILES["image"]["name"], 99);
    chgrp($dir. $_FILES["image"]["name"], 1005);
    
    echo "1";
}
else
{
    echo "No cargo";
}




?>


