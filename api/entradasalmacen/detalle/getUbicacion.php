<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$sql = "SELECT
			c_ubicacion.idy_ubica,
			c_ubicacion.cve_almac,
			c_ubicacion.cve_pasillo,
			c_ubicacion.cve_rack,
			c_ubicacion.cve_nivel,
			c_ubicacion.num_ancho,
			c_ubicacion.num_largo,
			c_ubicacion.num_alto,
			c_ubicacion.num_volumenDisp,
			c_ubicacion.`Status`,
			c_ubicacion.picking,
			c_ubicacion.Seccion,
			c_ubicacion.Ubicacion,
			c_ubicacion.orden_secuencia,
			c_ubicacion.PesoMaximo,
			c_ubicacion.PesoOcupado,
			c_ubicacion.claverp,
			c_ubicacion.CodigoCSD,
			c_ubicacion.TECNOLOGIA,
			c_ubicacion.Maneja_Cajas,
			c_ubicacion.Maneja_Piezas,
			c_ubicacion.Reabasto,
			c_ubicacion.Activo,
			c_ubicacion.Tipo,
			c_almacen.des_almac
			FROM
			c_ubicacion
			INNER JOIN ts_ubicxart ON ts_ubicxart.idy_ubica = c_ubicacion.idy_ubica
			INNER JOIN c_almacen ON c_ubicacion.cve_almac = c_almacen.cve_almac
			WHERE
			cve_articulo = '".$_POST["codigo"]."' and c_ubicacion.Tipo = 'R'
			GROUP BY c_ubicacion.cve_almac;";

	if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

	$responce = array();
	
	$i = 0;
	
    while ($row = mysqli_fetch_array($res)) {

        $responce->rows[$i]['id']=$row['cve_almac'];
        $responce->rows[$i]['cell']=array($row['cve_almac'], $row['des_almac']);
        $i++;
    }
    echo json_encode($responce);
}