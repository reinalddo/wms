<?php

include '../../../config.php';

set_time_limit(0);

$json = file_get_contents('php://input');

//$json = '{"action":"update","id_user":"1","arreglo":"{\"DetalleOrden\":[{\"Producto\":\"1234\",\"Folio\":\"11111111111\",\"idoc\":\"19\"},{\"Producto\":\"1\",\"Folio\":\"11111111111\",\"idoc\":\"19\"}]}"}';

$obj = json_decode($json);

if (isset($obj) && !empty($obj)) {
	if (isset($obj->{'id_user'})) {
		session_start();
		$_SESSION['id_user'] = $obj->{'id_user'};
	}
	if( $obj->{'action'} == 'update' ) {

	    $arr = (array) json_decode($obj->{'arreglo'});

	    if (!empty($arr)) {

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $a = (array)$arr["DetalleOrden"];

            // prepara la llamada al procedimiento almacenado Lis_Facturas
            $sql = "Select * from th_aduana Where ID_Aduana = '".$a[0]->idoc."';";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $row = mysqli_fetch_array($res);

	        //foreach ($arr["DetalleOrden"] as $det) {
            //$a = (array)$det;
            $sql = "insert into th_entalmacen(Fec_Entrada, id_ocompra, Cve_Almac) values (now(), '".$a[0]->idoc."', '".$row["Cve_Almac"]."');";
            mysqli_query($conn, $sql);

            /*foreach ($arr["DetalleOrden"] as $det) {

            }*/
        }

		$arr = array("success" => true);

		echo json_encode($arr);

	}
}