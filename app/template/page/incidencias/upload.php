<?php
$uploads_dir  = "../../../../img/incidencias /";
$fichero_subido = $uploads_dir . basename($_FILES['image']['name']);

if( move_uploaded_file($_FILES['image']['tmp_name'], $fichero_subido) ){
	echo "Imagen actualizada";
} else {
	echo "Error de permisos al intentar actualizar la imagen";
}
exit;

?>


