<?php
include '../../../config.php';

//error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT
                c_articulo.cve_articulo,
                c_articulo.des_articulo,
                c_articulo.num_multiplo,
                c_articulo.cve_umed,
                c_lotes.LOTE,
                DATE_FORMAT(c_lotes.CADUCIDAD,'%d-%m-%Y') as CADUCIDAD
                FROM
                c_articulo
                LEFT JOIN c_lotes ON c_articulo.cve_articulo = c_lotes.cve_articulo
				Where Cast(c_articulo.cve_articulo AS UNSIGNED INTEGER)='".$_POST["cve_articulo"]."'
            GROUP BY c_articulo.cve_articulo LIMIT 1;";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

	//$responce = array();
	
	$i = 0;
	
    while ($row = mysqli_fetch_array($res)) {
		$lote = (empty($row['LOTE'])) ? "" : $row['LOTE'];
        $CADUCIDAD = (empty($row['CADUCIDAD'])) ? "" : $row['CADUCIDAD'];
		
        $responce->rows[$i]['id']=$row['cve_articulo'];
        $responce->rows[$i]['cell']=array($row['cve_articulo'], utf8_encode($row['des_articulo']), $lote, $CADUCIDAD, $row['num_multiplo'], $row['cve_umed']);
        $i++;
    }
    echo json_encode($responce);
}